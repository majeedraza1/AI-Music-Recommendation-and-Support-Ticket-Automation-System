<?php

namespace StackonetSupportTicket\Models;

use Stackonet\WP\Framework\Abstracts\Data;
use StackonetSupportTicket\Supports\Utils;
use WP_Error;
use WP_Term;
use WP_User;

defined( 'ABSPATH' ) or exit;

class SupportAgent extends Data {

	/**
	 * Taxonomy name
	 *
	 * @var string
	 */
	protected static $taxonomy = 'support_agent';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'term_id';

	/**
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * @var WP_User
	 */
	protected $user;

	/**
	 * @var WP_Term
	 */
	protected $term;

	/**
	 * @var int
	 */
	private $role_id = 0;

	/**
	 * @var string
	 */
	private $role_label = '';

	/**
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * Class constructor.
	 *
	 * @param null|WP_Term $term
	 */
	public function __construct( $term = null ) {
		if ( $term instanceof WP_Term ) {
			$this->term    = $term;
			$this->data    = $term->to_array();
			$this->user_id = (int) get_term_meta( $term->term_id, 'user_id', true );
			$this->role_id = get_term_meta( $term->term_id, 'role', true );

			$agent_role = AgentRole::get_role( $this->role_id );
			if ( $agent_role instanceof AgentRole ) {
				$this->role_label   = $agent_role->get_role_name();
				$this->capabilities = $agent_role->get_capabilities();
			}
		}
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'term_id'      => $this->get( 'term_id' ),
			'slug'         => $this->get( 'slug' ),
			'name'         => $this->get( 'name' ),
			'role_id'      => $this->role_id,
			'role_label'   => $this->role_label,
			'id'           => $this->get_user()->ID,
			'display_name' => $this->get_user()->display_name,
			'email'        => $this->get_email(),
			'phone'        => $this->get_phone_number(),
			'avatar_url'   => $this->get_avatar_url(),
		];
	}

	/**
	 * Get id
	 *
	 * @return int
	 */
	public function get_id() {
		return intval( $this->term->term_id );
	}

	/**
	 * Get agent user id
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Get user
	 *
	 * @return WP_User
	 */
	public function get_user() {
		if ( ! $this->user instanceof WP_User ) {
			$this->user = get_user_by( 'id', $this->user_id );
		}

		return $this->user;
	}

	/**
	 * Get user email address
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->get_meta( 'email', $this->get_user()->user_email );
	}

	/**
	 * Get user phone number
	 *
	 * @return mixed|string
	 */
	public function get_phone_number() {
		return $this->get_meta( 'phone_number', $this->get_user_meta( 'billing_phone' ) );
	}

	/**
	 * Get avatar url
	 *
	 * @return string
	 */
	public function get_avatar_url() {
		return Utils::get_avatar_url( $this->get_user_id() );
	}

	/**
	 * Get term meta
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed|string
	 */
	public function get_meta( string $key, $default = '' ) {
		$value = get_term_meta( $this->get_id(), $key, true );

		return ! empty( $value ) ? $value : $default;
	}

	/**
	 * Get term meta
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed|string
	 */
	public function get_user_meta( string $key, $default = '' ) {
		$value = get_user_meta( $this->get_user_id(), $key, true );

		return ! empty( $value ) ? $value : $default;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @param array $args
	 *
	 * @return self[]
	 */
	public static function get_all( $args = [] ) {
		$default          = array(
			'hide_empty' => false,
			'meta_query' => array(
				array(
					'key'     => 'agentgroup',
					'value'   => '0',
					'compare' => '='
				)
			)
		);
		$args             = wp_parse_args( $args, $default );
		$args['taxonomy'] = self::$taxonomy;

		$_terms = get_terms( $args );

		$terms = [];
		foreach ( $_terms as $term ) {
			$terms[] = new self( $term );
		}

		return $terms;
	}

	/**
	 * Crate a new term
	 *
	 * @param int $user_id
	 * @param int $role_id
	 *
	 * @return int|WP_Error
	 */
	public static function create( $user_id, $role_id ) {
		$term = wp_insert_term( 'agent_' . $user_id, self::$taxonomy );
		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$user = get_user_by( 'id', $user_id );

		add_term_meta( $term['term_id'], 'role', $role_id );
		add_term_meta( $term['term_id'], 'agentgroup', '0' );

		add_term_meta( $term['term_id'], 'user_id', $user->ID );
		add_term_meta( $term['term_id'], 'label', $user->display_name );
		add_term_meta( $term['term_id'], 'first_name', $user->first_name );
		add_term_meta( $term['term_id'], 'last_name', $user->last_name );
		add_term_meta( $term['term_id'], 'nicename', $user->user_nicename );
		add_term_meta( $term['term_id'], 'email', $user->user_email );

		$user->add_cap( 'wpsc_agent' );
		update_user_option( $user->ID, 'support_ticket_agent_roles', $role_id );

		return $term['term_id'];
	}

	/**
	 * Find agent by id
	 *
	 * @param int $id
	 *
	 * @return bool|SupportAgent
	 */
	public static function find_by_id( $id ) {
		$term = get_term_by( 'id', $id, self::$taxonomy, OBJECT );
		if ( $term instanceof WP_Term ) {
			return new self( $term );
		}

		return false;
	}

	/**
	 * Update support agent role
	 *
	 * @param int    $id
	 * @param string $role_id
	 */
	public static function update_role( $id, $role_id ) {
		$user_id = get_term_meta( $id, 'user_id', true );

		update_term_meta( $id, 'role', $role_id );
		update_user_option( $user_id, 'support_ticket_agent_roles', $role_id );
	}

	/**
	 * Delete a agent
	 *
	 * @param int $id
	 *
	 * @return bool|int|WP_Error
	 */
	public static function delete( $id ) {
		return wp_delete_term( $id, self::$taxonomy );
	}
}
