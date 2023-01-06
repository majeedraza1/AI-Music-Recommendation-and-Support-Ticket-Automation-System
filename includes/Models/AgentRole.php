<?php

namespace StackonetSupportTicket\Models;

use JsonSerializable;
use WP_Error;

defined( 'ABSPATH' ) or exit;

class AgentRole implements JsonSerializable {

	/**
	 * Option name
	 *
	 * @var string
	 */
	protected static $option_name = 'support_ticket_agent_roles';

	/**
	 * Role slug
	 *
	 * @var string
	 */
	protected $role;

	/**
	 * Role Name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Support Ticket roles
	 *
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * @var array
	 */
	protected static $reserve_roles = [ 'administrator', 'agent' ];

	/**
	 * AgentRole constructor.
	 *
	 * @param string $role
	 * @param array  $data
	 */
	public function __construct( $role, $data = [] ) {
		$this->role = $role;
		if ( isset( $data['name'], $data['capabilities'] ) ) {
			$this->name         = $data['name'];
			$this->capabilities = static::format_capabilities( $data['capabilities'] );
		} else {
			$_role              = static::get_role( $role );
			$this->name         = $_role->get_role_name();
			$this->capabilities = $_role->get_capabilities();
		}
	}

	/**
	 * Get reserve roles
	 *
	 * @return array
	 */
	public static function get_reserve_roles() {
		return self::$reserve_roles;
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'role'         => $this->get_role_slug(),
			'name'         => $this->get_role_name(),
			'capabilities' => $this->get_capabilities(),
		];
	}

	/**
	 * Get role name
	 *
	 * @return string
	 */
	public function get_role_slug() {
		return $this->role;
	}

	/**
	 * Get role display name
	 *
	 * @return string
	 */
	public function get_role_name() {
		return $this->name;
	}

	/**
	 * Get role capabilities
	 *
	 * @return array
	 */
	public function get_capabilities() {
		return $this->capabilities;
	}

	/**
	 * Get all roles
	 *
	 * @return array|self[]
	 */
	public static function get_roles() {
		$agent_role = get_option( self::$option_name );

		if ( ! is_array( $agent_role ) ) {
			$agent_role = [];
		}

		$roles = [];
		foreach ( $agent_role as $role => $value ) {
			$roles[ $role ] = new self( $role, $value );
		}

		return $roles;
	}

	/**
	 * Retrieve role object.
	 *
	 * @param string $role Role name.
	 *
	 * @return self|null WP_Role object if found, null if the role does not exist.
	 */
	public static function get_role( $role ) {
		$agent_roles = get_option( self::$option_name );
		if ( ! isset( $agent_roles[ $role ] ) ) {
			return null;
		}

		return new self( $role, $agent_roles[ $role ] );
	}

	/**
	 * Add role, if it does not exist.
	 *
	 * @param string $role         Role name.
	 * @param string $display_name Display name for role.
	 * @param array  $capabilities List of capabilities, e.g. array( 'edit_posts' => true, 'delete_posts' => false );
	 *
	 * @return self|WP_Error
	 */
	public static function add_role( $role, $display_name, $capabilities = [] ) {
		$agent_roles = get_option( self::$option_name );
		if ( isset( $agent_roles[ $role ] ) ) {
			return new WP_Error( 'role_exists', __( 'A role with the name provided already exists.' ) );
		}

		$valid_caps    = static::valid_capabilities();
		$_capabilities = [];
		foreach ( $valid_caps as $cap_name => $active ) {
			$enabled                    = isset( $capabilities[ $cap_name ] ) ? $capabilities[ $cap_name ] : $active;
			$_capabilities[ $cap_name ] = static::is_checked( $enabled ) ? 1 : 0;
		}

		$agent_roles[ $role ] = [
			'name'         => $display_name,
			'capabilities' => $_capabilities,
		];

		update_option( static::$option_name, $agent_roles );

		return new self( $role, $agent_roles[ $role ] );
	}

	/**
	 * Update role, if it exist.
	 *
	 * @param string $role         Role name.
	 * @param array  $capabilities List of capabilities, e.g. array( 'edit_posts' => true, 'delete_posts' => false );
	 * @param string $display_name Display name for role.
	 *
	 * @return self|null
	 */
	public static function update_role( $role, $capabilities = [], $display_name = '' ) {
		$agent_roles = get_option( self::$option_name );
		if ( ! isset( $agent_roles[ $role ] ) ) {
			return null;
		}

		/** @var self $current_role */
		$current_role = static::get_role( $role );

		$_capabilities = [];
		foreach ( $current_role->get_capabilities() as $cap_name => $active ) {
			$enabled = isset( $capabilities[ $cap_name ] ) ? $capabilities[ $cap_name ] : $active;

			$_capabilities[ $cap_name ] = static::is_checked( $enabled ) ? 1 : 0;
		}

		$agent_roles[ $role ] = [
			'name'         => ! empty( $display_name ) ? sanitize_text_field( $display_name ) : $current_role->get_role_name(),
			'capabilities' => $_capabilities,
		];

		update_option( static::$option_name, $agent_roles );

		return new self( $role, $agent_roles[ $role ] );
	}

	/**
	 * Remove role, if it exists.
	 *
	 * @param string $role Role name.
	 *
	 * @return bool
	 */
	public static function remove_role( $role ) {
		$agent_roles = get_option( self::$option_name );
		if ( isset( $agent_roles[ $role ] ) ) {
			unset( $agent_roles[ $role ] );
			update_option( static::$option_name, $agent_roles );

			return true;
		}

		return false;
	}

	/**
	 * Format capabilities
	 *
	 * @param $capabilities
	 *
	 * @return array
	 */
	public static function format_capabilities( $capabilities ) {
		$caps = [];
		foreach ( static::valid_capabilities() as $cap_name => $default ) {
			$enabled           = isset( $capabilities[ $cap_name ] ) ? $capabilities[ $cap_name ] : false;
			$caps[ $cap_name ] = static::is_checked( $enabled );
		}

		return $caps;
	}

	/**
	 * Check if boolean
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function is_checked( $value ) {
		return in_array( $value, [ '1', 'true', 'on', 'yes', 1, true ], true );
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @return mixed data which can be serialized by json_encode
	 * which is a value of any type other than a resource.
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}

	/**
	 * Support ticket valid capabilities
	 *
	 * @return array
	 */
	public static function valid_capabilities() {
		$settings = static::form_settings();
		$ids      = wp_list_pluck( $settings, 'id' );

		return array_fill_keys( $ids, 0 );
	}

	/**
	 * Agent capabilities settings
	 *
	 * @return array
	 */
	public static function form_settings() {
		return [
			[
				'id'          => 'view_unassigned',
				'label'       => __( 'View unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned ticket list visibility.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'view_assigned_me',
				'label'       => __( 'View assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself. This will also enable private notes.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'view_assigned_others',
				'label'       => __( 'View assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents. This will also enable private notes.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'assign_unassigned',
				'label'       => __( 'Assign unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned ticket assign agent capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'assign_assigned_me',
				'label'       => __( 'Assign assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself further assign capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'assign_assigned_others',
				'label'       => __( 'Assign assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents further assign capability.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'reply_unassigned',
				'label'       => __( 'Reply unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned ticket reply capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'reply_assigned_me',
				'label'       => __( 'Reply assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself reply capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'reply_assigned_others',
				'label'       => __( 'Reply assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents reply capability.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'delete_unassigned',
				'label'       => __( 'Delete unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Delete unassigned ticket capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'delete_assigned_me',
				'label'       => __( 'Delete assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself delete capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'delete_assigned_others',
				'label'       => __( 'Delete assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents delete capability.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'change_ticket_status_unassigned',
				'label'       => __( 'Change status unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned ticket status change capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_status_assigned_me',
				'label'       => __( 'Change status assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself change ticket status capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_status_assigned_others',
				'label'       => __( 'Change status assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents change ticket status capability.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'change_ticket_field_unassigned',
				'label'       => __( 'Change ticket fields unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned change ticket fields capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_field_assigned_me',
				'label'       => __( 'Change ticket fields assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself change ticket fields capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_field_assigned_others',
				'label'       => __( 'Change ticket fields assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents change ticket fields capability.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'change_ticket_agent_only_unassigned',
				'label'       => __( 'Change agent only fields unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned change agent only fields capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_agent_only_assigned_me',
				'label'       => __( 'Change agent only fields assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself change agent only fields capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_agent_only_assigned_others',
				'label'       => __( 'Change agent only fields assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents change agent only fields capability.', 'stackonet-support-ticket' ),
			],

			[
				'id'          => 'change_ticket_raised_by_unassigned',
				'label'       => __( 'Change Raised By unassigned', 'stackonet-support-ticket' ),
				'description' => __( 'Unassigned ticket change raised by capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_raised_by_assigned_me',
				'label'       => __( 'Change Raised By assigned me', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to user himself change Raised By capability.', 'stackonet-support-ticket' ),
			],
			[
				'id'          => 'change_ticket_raised_by_assigned_others',
				'label'       => __( 'Change Raised By assigned others', 'stackonet-support-ticket' ),
				'description' => __( 'Ticket assigned to all other agents change Raised By capability..', 'stackonet-support-ticket' ),
			],
		];
	}
}
