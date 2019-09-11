<?php

namespace StackonetSupportTicket\Models;

use StackonetSupportTicket\Abstracts\AbstractModel;
use WP_Error;
use WP_Term;

class TicketPriority extends AbstractModel {

	/**
	 * Taxonomy name
	 *
	 * @var string
	 */
	protected static $taxonomy = 'wpsc_priorities';

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'term_id';

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
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'term_id' => $this->get( 'term_id' ),
			'slug'    => $this->get( 'slug' ),
			'name'    => $this->get( 'name' ),
		];
	}

	/**
	 * Get ticket statuses term
	 *
	 * @param array $args
	 *
	 * @return WP_Term[]
	 */
	public static function get_all( $args = [] ) {
		$default          = array(
			'hide_empty' => false,
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'meta_query' => array(
				'order_clause' => array(
					'key' => 'support_ticket_priority_menu_order'
				)
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
	 * @param array $args
	 *
	 * @return int|WP_Error
	 */
	public static function create( $term, $args = [] ) {
		$data = wp_insert_term( $term, self::$taxonomy, [
				'description' => isset( $args['description'] ) ? $args['description'] : '',
				'slug'        => isset( $args['slug'] ) ? $args['slug'] : '',
				'parent'      => isset( $args['parent'] ) ? intval( $args['parent'] ) : 0
			]
		);

		if ( ! is_wp_error( $data ) ) {
			$term_id    = isset( $data['term_id'] ) ? $data['term_id'] : 0;
			$categories = self::get_all();
			update_term_meta( $term_id, 'support_ticket_priority_menu_order', count( $categories ) + 1 );

			return $term_id;
		}

		return $data;
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
