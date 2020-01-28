<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class UpgradeTicket {

	/**
	 * @var string
	 */
	protected static $old_table_name = 'wpsc_ticket';

	/**
	 * @var string
	 */
	protected static $new_table_name = 'support_ticket';

	/**
	 * Clone tickets
	 */
	public static function clone_tickets() {
		global $wpdb;
		$old_table = $wpdb->prefix . static::$old_table_name;
		$new_table = $wpdb->prefix . static::$new_table_name;

		$has_old_table = $wpdb->query( "SHOW TABLES LIKE '{$old_table}';" );
		if ( $has_old_table ) {
			$wpdb->query( "INSERT INTO `{$new_table}` SELECT * FROM `{$old_table}`;" );
			$wpdb->query( "INSERT INTO `{$wpdb->prefix}support_ticketmeta` SELECT * FROM `{$wpdb->prefix}wpsc_ticketmeta`;" );
		}
	}
}