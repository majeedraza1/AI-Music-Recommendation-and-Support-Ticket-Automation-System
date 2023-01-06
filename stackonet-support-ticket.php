<?php
/**
 * Plugin Name: Stackonet Support Ticket
 * Description: Easy & Powerful support ticket system for WordPress
 * Version: 1.0.0-beta.1
 * Author: Stackonet Services (Pvt.) Ltd.
 * Author URI: https://stackonet.com
 * Requires at least: 5.0
 * Tested up to: 5.3
 * Text Domain: stackonet-support-ticket
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
	 * Plugin name slug
	 *
	 * @var string
	 */
	private $plugin_name = 'stackonet-support-ticket';

	/**
	 * Plugin version number
	 *
	 * @var string
	 */
	public $version = '1.0.0-beta.1';

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
		define( 'STACKONET_SUPPORT_TICKET', $this->plugin_name );
		define( 'STACKONET_SUPPORT_TICKET_VERSION', $this->version );
		define( 'STACKONET_SUPPORT_TICKET_REST_NAMESPACE', 'stackonet-support-ticket/v1' );
		define( 'STACKONET_SUPPORT_TICKET_FILE', __FILE__ );
		define( 'STACKONET_SUPPORT_TICKET_PATH', dirname( STACKONET_SUPPORT_TICKET_FILE ) );
		define( 'STACKONET_SUPPORT_TICKET_INCLUDES', STACKONET_SUPPORT_TICKET_PATH . '/includes' );
		define( 'STACKONET_SUPPORT_TICKET_URL', plugins_url( '', STACKONET_SUPPORT_TICKET_FILE ) );
		define( 'STACKONET_SUPPORT_TICKET_ASSETS', STACKONET_SUPPORT_TICKET_URL . '/assets' );
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
