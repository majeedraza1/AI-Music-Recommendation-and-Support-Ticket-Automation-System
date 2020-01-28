<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradeCategories {

	/**
	 * @var string
	 */
	protected static $old_term_name = 'wpsc_categories';

	/**
	 * @var string
	 */
	protected static $new_term_name = 'ticket_category';

	/**
	 * @var string
	 */
	protected static $old_meta_name = 'wpsc_category_load_order';

	/**
	 * @var string
	 */
	protected static $new_meta_name = 'support_ticket_category_menu_order';

	/**
	 * Clone categories
	 */
	public static function clone_categories() {
		/** @var \WP_Term[] $categories */
		$categories = get_terms( [
			'taxonomy'   => static::$old_term_name,
			'hide_empty' => false,
		] );

		foreach ( $categories as $category ) {
			self::clone_category( $category );
		}
	}

	/**
	 * @param \WP_Term $category
	 */
	protected static function clone_category( \WP_Term $category ) {
		$data = wp_insert_term( $category->name, self::$new_term_name, [
			'description' => $category->description,
			'slug'        => $category->slug . '-1',
			'parent'      => $category->parent
		] );

		if ( ! is_wp_error( $data ) ) {
			$menu_order = get_term_meta( $category->term_id, static::$old_meta_name, true );
			$term_id    = isset( $data['term_id'] ) ? $data['term_id'] : 0;

			update_term_meta( $term_id, static::$new_meta_name, $menu_order );

			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticket',
				[ 'ticket_category' => $term_id ],
				[ 'ticket_category' => $category->term_id ]
			);
		}
	}
}