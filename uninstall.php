<?php
/**
 * Stackonet Uninstall
 * Fired when the plugin is uninstalled.
 *
 * @package Stackonet\Uninstaller
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

if ( 'yes' === get_option( 'support_ticket_delete_all_data' ) ) {
	\StackonetSupportTicket\Uninstall::run();
}