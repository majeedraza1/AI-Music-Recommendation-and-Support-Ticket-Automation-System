<?php

namespace StackonetSupportTicket\Upgrade;

defined( 'ABSPATH' ) || exit;

class Upgrade {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Init upgrade
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'admin_notices', [ self::$instance, 'add_admin_upgrade_status_notice' ] );

			add_action( 'wp_ajax_stackonet_support_ticket_upgrade', [ self::$instance, 'upgrade_database' ] );
		}

		return self::$instance;
	}

	/**
	 * Add upgrade notice
	 */
	public function add_admin_upgrade_status_notice() {

		if ( 'yes' != get_option( 'support_ticket_upgrade_done' ) ) {
			$url = add_query_arg( [ 'action' => 'stackonet_support_ticket_upgrade', ], admin_url( 'admin-ajax.php' ) );
			$url = wp_nonce_url( $url, 'stackonet_support_ticket_upgrade', '_token' );

			$html = '<div>';
			$html .= '<span>Stackonet Support Ticket need to upgrade database.</span>';
			$html .= '<span><a class="button" target="_blank" href="' . $url . '">Update Now</a></span>';
			$html .= '</div>';
			echo $this->add_admin_notice( $html );
		}

		$thread_status_text = UpgradeThreads::get_admin_notice_text();
		if ( ! empty( $thread_status_text ) ) {
			echo $this->add_admin_notice( $thread_status_text );
		}
	}

	/**
	 * Handle upgrade database
	 */
	public function upgrade_database() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sorry only admin can perform upgrade.' );
		}

		$nonce = isset( $_REQUEST['_token'] ) ? $_REQUEST['_token'] : '';
		if ( ! wp_verify_nonce( $nonce, 'stackonet_support_ticket_upgrade' ) ) {
			wp_die( 'Sorry, Invalid URL.' );
		}

		if ( 'yes' != get_option( 'support_ticket_upgrade_done' ) ) {
			self::do_upgrade();
			update_option( 'support_ticket_upgrade_done', 'yes', false );
		}

		wp_die( 'Upgrade has been set.', 'Upgrade Status', [
			'link_url'  => admin_url( 'admin.php?page=stackonet-support-ticket' ),
			'link_text' => 'Back to dashboard',
			'back_link' => true,
		] );
	}

	/**
	 * Do upgrade
	 */
	protected static function do_upgrade() {
		// Upgrade ticket table & ticket meta table
		UpgradeTicket::clone_tickets();

		// Upgrade ticket thread
		UpgradeThreads::clone_threads();
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
		$html .= '<p>' . ( $content ) . '</p>';
		if ( $dismissible ) {
			$html .= '<button type="button" class="notice-dismiss">';
			$html .= '<span class="screen-reader-text">Dismiss this notice.</span>';
			$html .= '</button>';
		}
		$html .= '</div>';

		return $html;
	}
}