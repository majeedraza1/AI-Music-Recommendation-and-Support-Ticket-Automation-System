<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class Upgrade {

	/**
	 * Init upgrade
	 */
	public static function init() {
		$is_upgraded = get_option( 'support_ticket_table_upgrade_done' );
		global $wpdb;
		$has_old_table = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}wpsc_ticket';" );
		// Upgrade ticket table & ticket meta table
		if ( $has_old_table && 'yes' != $is_upgraded ) {
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}support_ticket` SELECT * FROM `{$wpdb->prefix}wpsc_ticket`;" );
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}support_ticketmeta` SELECT * FROM `{$wpdb->prefix}wpsc_ticketmeta`;" );

			update_option( 'support_ticket_table_upgrade_done', 'yes' );
		}

		$is_upgraded = get_option( 'support_ticket_thread_upgrade_done' );
		if ( 'yes' != $is_upgraded ) {
			static::clone_threads();
			update_option( 'support_ticket_thread_upgrade_done', 'yes' );
		}
	}


	/**
	 */
	protected static function clone_threads() {
		global $wpdb;
		$wpdb->query( "DELETE FROM `{$wpdb->posts}` WHERE `post_type` = 'ticket_thread'" );

		$ids = $wpdb->get_results( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'wpsc_ticket_thread'", ARRAY_A );
		$ids = count( $ids ) ? wp_list_pluck( $ids, 'ID' ) : [];
		$ids = count( $ids ) ? array_map( 'intval', $ids ) : [];

		$background_process = stackonet_support_ticket()->clone_thread_background_process();
		foreach ( array_chunk( $ids, 15 ) as $chunk_ids ) {
			$background_process->push_to_queue( $chunk_ids );
		}

		add_action( 'shutdown', function () use ( $background_process ) {
			$background_process->save()->dispatch();
		}, 100 );
	}
}