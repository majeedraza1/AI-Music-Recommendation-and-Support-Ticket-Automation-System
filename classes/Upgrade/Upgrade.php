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

		if ( 'yes' != get_option( 'support_ticket_thread_upgrade_done' ) ) {
			CloneThreadBackgroundTask::clone_threads();
			update_option( 'support_ticket_thread_upgrade_done', 'yes', false );
		}

		add_action( 'admin_notices', [ new static(), 'add_admin_upgrade_status_notice' ] );
	}

	/**
	 * Add upgrade notice
	 */
	public function add_admin_upgrade_status_notice() {
		$thread_status_text = CloneThreadBackgroundTask::get_admin_notice_text();
		if ( ! empty( $thread_status_text ) ) {
			echo $this->add_admin_notice( $thread_status_text );
		}
	}

	/**
	 * Add admin notice
	 *
	 * @param string $content
	 * @param bool $dismissible
	 *
	 * @return string
	 */
	public static function add_admin_notice( $content, $dismissible = true ) {
		$html = '<div id="message" class="notice notice-info is-dismissible">';
		$html .= '<p>' . esc_html( $content ) . '</p>';
		if ( $dismissible ) {
			$html .= '<button type="button" class="notice-dismiss">';
			$html .= '<span class="screen-reader-text">Dismiss this notice.</span>';
			$html .= '</button>';
		}
		$html .= '</div>';

		return $html;
	}
}