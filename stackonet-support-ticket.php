<?php
/**
 * Plugin Name: Stackonet Support Ticket
 * Description: Easy & Powerful support ticket system for WordPress
 * Version: 3.0.0
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
	public $version = '3.0.0';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();

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
			add_action( 'plugins_loaded', [ self::$instance, 'init_classes' ] );

			add_filter( 'map_meta_cap',
				[ new StackonetSupportTicket\Models\SupportTicket(), 'map_meta_cap' ], 10, 4 );

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
		define( 'STACKONET_SUPPORT_TICKET_FILE', __FILE__ );
		define( 'STACKONET_SUPPORT_TICKET_PATH', dirname( STACKONET_SUPPORT_TICKET_FILE ) );
		define( 'STACKONET_SUPPORT_TICKET_INCLUDES', STACKONET_SUPPORT_TICKET_PATH . '/classes' );
		define( 'STACKONET_SUPPORT_TICKET_URL', plugins_url( '', STACKONET_SUPPORT_TICKET_FILE ) );
		define( 'STACKONET_SUPPORT_TICKET_ASSETS', STACKONET_SUPPORT_TICKET_URL . '/assets' );
	}

	/**
	 * Instantiate the required classes
	 */
	public function init_classes() {
		$this->container['assets']    = StackonetSupportTicket\Assets::init();
		$this->container['settings']  = StackonetSupportTicket\Admin\Settings::init();
		$this->container['post_type'] = StackonetSupportTicket\Admin\PostType::init();

		$this->container['clone_thread'] = new StackonetSupportTicket\Upgrade\UpgradeThreads();

		// Sync tickets
		$this->container['sync_old'] = StackonetSupportTicket\Sync\ToNewTicket::init();
		$this->container['sync_new'] = StackonetSupportTicket\Sync\ToOldTicket::init();


		if ( $this->is_request( 'admin' ) ) {
			$this->container['admin'] = StackonetSupportTicket\Admin\Admin::init();

			add_action( 'admin_init', [ new StackonetSupportTicket\Upgrade\Upgrade, 'init' ] );
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->container['frontend']          = StackonetSupportTicket\Frontend::init();
			$this->container['rest-login']        = StackonetSupportTicket\REST\LoginController::init();
			$this->container['rest-attachment']   = StackonetSupportTicket\REST\AttachmentController::init();
			$this->container['rest-ticket']       = StackonetSupportTicket\REST\TicketController::init();
			$this->container['rest-thread']       = StackonetSupportTicket\REST\TicketThreadController::init();
			$this->container['rest-ticket_agent'] = StackonetSupportTicket\REST\TicketAgentController::init();
			$this->container['rest-ticket_sms']   = StackonetSupportTicket\REST\TicketSmsController::init();
			$this->container['rest-support']      = StackonetSupportTicket\REST\SupportTicketController::init();
			$this->container['rest-category']     = StackonetSupportTicket\REST\CategoryController::init();
			$this->container['rest-status']       = StackonetSupportTicket\REST\StatusController::init();
			$this->container['rest-priority']     = StackonetSupportTicket\REST\PriorityController::init();
			$this->container['rest-agent']        = StackonetSupportTicket\REST\AgentController::init();
			$this->container['rest-role']         = StackonetSupportTicket\REST\AgentRoleController::init();
			$this->container['rest-settings']     = StackonetSupportTicket\REST\SettingController::init();
			$this->container['rest-user_ticket']  = StackonetSupportTicket\REST\UserTicketController::init();
		}

		if ( $this->is_request( 'ajax' ) ) {
			$this->container['ajax'] = StackonetSupportTicket\Ajax::init();
		}
	}

	/**
	 * @return StackonetSupportTicket\Upgrade\UpgradeThreads
	 */
	public function clone_thread_background_process() {
		return $this->container['clone_thread'];
	}

	/**
	 * Function to run on plugin activation
	 */
	public function activation() {
		StackonetSupportTicket\Install::init();
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, rest, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'rest' :
				return defined( 'REST_REQUEST' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}
}

function stackonet_support_ticket() {
	return StackonetSupportTicket::init();
}

stackonet_support_ticket();
