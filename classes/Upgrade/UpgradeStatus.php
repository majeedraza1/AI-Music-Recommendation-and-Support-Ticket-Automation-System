<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradeStatus {

	/**
	 * @var string
	 */
	protected static $old_term_name = 'wpsc_statuses';

	/**
	 * @var string
	 */
	protected static $new_term_name = 'ticket_status';

	/**
	 * @var string
	 */
	protected static $old_meta_name = 'wpsc_status_load_order';

	/**
	 * @var string
	 */
	protected static $new_meta_name = 'support_ticket_status_menu_order';

	/**
	 * Clone priorities
	 */
	public static function clone_statuses() {
		/** @var \WP_Term[] $categories */
		$categories = get_terms( [
			'taxonomy'   => static::$old_term_name,
			'hide_empty' => false,
		] );

		foreach ( $categories as $category ) {
			self::clone_status( $category );
		}
	}

	/**
	 * @param \WP_Term $status
	 */
	protected static function clone_status( \WP_Term $status ) {
		$data = wp_insert_term( $status->name, self::$new_term_name, [
			'description' => $status->description,
			'slug'        => $status->slug . '-1',
			'parent'      => $status->parent
		] );

		if ( ! is_wp_error( $data ) ) {
			$menu_order = get_term_meta( $status->term_id, static::$old_meta_name, true );
			$term_id    = isset( $data['term_id'] ) ? $data['term_id'] : 0;

			update_term_meta( $term_id, static::$new_meta_name, $menu_order );

			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticket',
				[ 'ticket_status' => $term_id ],
				[ 'ticket_status' => $status->term_id ]
			);
		}
	}
}