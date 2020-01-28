<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradePriorities {
	/**
	 * @var string
	 */
	protected static $old_term_name = 'wpsc_priorities';

	/**
	 * @var string
	 */
	protected static $new_term_name = 'ticket_priority';

	/**
	 * @var string
	 */
	protected static $old_meta_name = 'wpsc_priority_load_order';

	/**
	 * @var string
	 */
	protected static $new_meta_name = 'support_ticket_priority_menu_order';

	/**
	 * Clone priorities
	 */
	public static function clone_priorities() {
		/** @var \WP_Term[] $categories */
		$categories = get_terms( [
			'taxonomy'   => static::$old_term_name,
			'hide_empty' => false,
		] );

		foreach ( $categories as $category ) {
			self::clone_priority( $category );
		}
	}

	/**
	 * @param \WP_Term $priority
	 */
	protected static function clone_priority( \WP_Term $priority ) {
		$data = wp_insert_term( $priority->name, self::$new_term_name, [
			'description' => $priority->description,
			'slug'        => $priority->slug . '-1',
			'parent'      => $priority->parent
		] );

		if ( ! is_wp_error( $data ) ) {
			$menu_order = get_term_meta( $priority->term_id, static::$old_meta_name, true );
			$term_id    = isset( $data['term_id'] ) ? $data['term_id'] : 0;

			update_term_meta( $term_id, static::$new_meta_name, $menu_order );

			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticket',
				[ 'ticket_priority' => $term_id ],
				[ 'ticket_priority' => $priority->term_id ]
			);
		}
	}
}