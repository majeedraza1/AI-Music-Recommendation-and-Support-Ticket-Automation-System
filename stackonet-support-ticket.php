<?php
/**
 * Project Name: AI-Music-Recommendation-and-Support-Ticket-Automation-System
 * Description: Easy & Powerful AI support ticket system
 * Version: 1.0.0-beta.1
 * Author: Majeed Raza.
 * Author URI: https://majeedraza.me
 * Requires at least: 5.0
 * Tested up to: 5.3
 * Requires PHP: 7.2
 * Text Domain: AI-Music-Recommendation-and-Support-Ticket-Automation-System
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

class StackonetSupportTicket {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	public static $instance = null;

	/**
	 * Only one instance of the class can be loaded
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->define_constants();

			if ( file_exists( STACKONET_SUPPORT_TICKET_PATH . '/vendor/autoload.php' ) ) {
				include STACKONET_SUPPORT_TICKET_PATH . '/vendor/autoload.php';
			}

			// initialize the classes
			self::$instance->init_classes();

			register_activation_hook( __FILE__, [ self::$instance, 'activation' ] );
		}

		return self::$instance;
	}

	/**
	 * Define plugin constants
	 */
	private function define_constants() {
		define( 'STACKONET_SUPPORT_TICKET_FILE', __FILE__ );
		define( 'STACKONET_SUPPORT_TICKET_PATH', dirname( STACKONET_SUPPORT_TICKET_FILE ) );
	}

	/**
	 * Instantiate the required classes
	 */
	public function init_classes() {
		StackonetSupportTicket\Plugin::init();
	}

	/**
	 * Function to run on plugin activation
	 */
	public function activation() {
		do_action( 'stackonet_support_ticket/activation' );
	}
}

function stackonet_support_ticket() {
	return StackonetSupportTicket::init();
}

stackonet_support_ticket();
