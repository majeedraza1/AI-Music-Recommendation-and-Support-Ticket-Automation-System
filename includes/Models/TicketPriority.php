<?php

namespace StackonetSupportTicket\Models;

use Stackonet\WP\Framework\Abstracts\Data;
use WP_Error;
use WP_Term;

class TicketPriority extends Data {

	/**
	 * Taxonomy name
	 *
	 * @var string
	 */
	protected static $taxonomy = 'ticket_priority';

	/**
	 * @var WP_Term
	 */
	protected $term;

	/**
	 * Class constructor.
	 *
	 * @param null|WP_Term $term
	 */
	public function __construct( $term = null ) {
		if ( $term instanceof WP_Term ) {
			$this->term = $term;
			$this->data = $term->to_array();
		}
	}

	/**
	 * Color
	 *
	 * @return string
	 */
	public function get_color() {
		$color = get_term_meta( $this->term->term_id, '_color', true );

		return ! empty( $color ) ? $color : '';
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array():array {
		return [
			'term_id' => $this->get( 'term_id' ),
			'slug'    => $this->get( 'slug' ),
			'name'    => $this->get( 'name' ),
			'color'   => $this->get_color(),
		];
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
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'meta_query' => array(
				'order_clause' => array(
					'key' => 'support_ticket_priority_menu_order',
				),
			),
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
	 * @param string $term term to add
	 * @param array  $args
	 *
	 * @return int|WP_Error
	 */
	public static function create( $term, $args = [] ) {
		$data = wp_insert_term(
			$term,
			self::$taxonomy,
			[
				'description' => isset( $args['description'] ) ? $args['description'] : '',
				'slug'        => isset( $args['slug'] ) ? $args['slug'] : '',
				'parent'      => isset( $args['parent'] ) ? intval( $args['parent'] ) : 0,
			]
		);

		if ( ! is_wp_error( $data ) ) {
			$term_id    = isset( $data['term_id'] ) ? $data['term_id'] : 0;
			$categories = self::get_all();
			update_term_meta( $term_id, 'support_ticket_priority_menu_order', count( $categories ) + 1 );

			if ( ! empty( $args['color'] ) ) {
				add_term_meta( $term_id, 'ticket_priority_color', $args['color'] );
			}

			return $term_id;
		}

		return $data;
	}

	/**
	 * Update category
	 *
	 * @param int    $term_id
	 * @param string $name
	 * @param string $slug
	 *
	 * @return array|WP_Error
	 */
	public static function update( $term_id, $name, $slug ) {
		$args = [
			'name' => $name,
			'slug' => $slug,
		];

		return wp_update_term( $term_id, self::$taxonomy, $args );
	}

	/**
	 * Get category by id
	 *
	 * @param int $id
	 *
	 * @return bool|TicketPriority
	 */
	public static function find_by_id( $id ) {
		$term = get_term_by( 'term_id', $id, self::$taxonomy, OBJECT );
		if ( $term instanceof WP_Term ) {
			return new self( $term );
		}

		return false;
	}

	/**
	 * Update support ticket menu order
	 *
	 * @param array $terms_ids
	 */
	public static function update_menu_orders( array $terms_ids ) {
		$terms_ids = array_map( 'intval', $terms_ids );
		foreach ( $terms_ids as $order => $term_id ) {
			update_term_meta( $term_id, 'support_ticket_priority_menu_order', $order + 1 );
		}
	}

	/**
	 * Delete category by term id
	 *
	 * @param int $term_id
	 *
	 * @return bool
	 */
	public static function delete( $term_id ) {
		return ( wp_delete_term( $term_id, self::$taxonomy ) === true );
	}
}
