<?php

namespace StackonetSupportTicket\Models;

use StackonetSupportTicket\Abstracts\AbstractModel;
use WP_Term;

class TicketStatus extends AbstractModel {


	/**
	 * Taxonomy name
	 *
	 * @var string
	 */
	protected static $taxonomy = 'wpsc_statuses';

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
					'key' => 'wpsc_status_load_order'
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
}
