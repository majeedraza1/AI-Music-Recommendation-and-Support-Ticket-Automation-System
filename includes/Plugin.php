<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Admin\Admin;
use StackonetSupportTicket\Admin\PostType;
use StackonetSupportTicket\Admin\Settings;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\REST\Admin\AgentController;
use StackonetSupportTicket\REST\Admin\AgentRoleController;
use StackonetSupportTicket\REST\Admin\SettingController;
use StackonetSupportTicket\REST\Admin\TicketAgentController;
use StackonetSupportTicket\REST\AttachmentController;
use StackonetSupportTicket\REST\CategoryController;
use StackonetSupportTicket\REST\Me\UserTicketController;
use StackonetSupportTicket\REST\Me\UserTicketThreadController;
use StackonetSupportTicket\REST\PriorityController;
use StackonetSupportTicket\REST\StatusController;
use StackonetSupportTicket\REST\SupportTicketController;
use StackonetSupportTicket\REST\TicketController;
use StackonetSupportTicket\REST\TicketSmsController;
use StackonetSupportTicket\REST\TicketThreadController;
use StackonetSupportTicket\REST\WebLoginController;

defined( 'ABSPATH' ) || exit;

/**
 * The main plugin handler class is responsible for initializing plugin. The
 * class registers and all the components required to run the plugin.
 */
class Plugin {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'plugins_loaded', [ self::$instance, 'includes' ] );
			add_action( 'stackonet_support_ticket/activation', [ self::$instance, 'activation_includes' ] );
			add_action( 'stackonet_support_ticket/deactivation', [ self::$instance, 'deactivation_includes' ] );
		}

		return self::$instance;
	}

	/**
	 * Instantiate the required classes
	 *
	 * @return void
	 */
	public function includes() {
		add_filter( 'map_meta_cap', [ new SupportTicket, 'map_meta_cap' ], 10, 4 );


		$this->container['assets']    = Assets::init();
		$this->container['settings']  = Settings::init();
		$this->container['post_type'] = PostType::init();

		// Load classes for admin area
		if ( $this->is_request( 'admin' ) ) {
			$this->admin_includes();
		}

		// Load classes for frontend area
		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

		// Load classes for ajax functionality
		if ( $this->is_request( 'ajax' ) ) {
			$this->ajax_includes();
		}

		$this->modules_includes();
	}

	/**
	 * Include modules main classes
	 *
	 * @return void
	 */
	public function modules_includes() {

	}

	/**
	 * Include admin classes
	 *
	 * @return void
	 */
	public function admin_includes() {
		$this->container['admin'] = Admin::init();
	}

	/**
	 * Include frontend classes
	 *
	 * @return void
	 */
	public function frontend_includes() {
		$this->container['frontend']          = Frontend::init();
		$this->container['rest-login']        = WebLoginController::init();
		$this->container['rest-attachment']   = AttachmentController::init();
		$this->container['rest-ticket']       = TicketController::init();
		$this->container['rest-thread']       = TicketThreadController::init();
		$this->container['rest-ticket_agent'] = TicketAgentController::init();
		$this->container['rest-ticket_sms']   = TicketSmsController::init();
		$this->container['rest-support']      = SupportTicketController::init();
		$this->container['rest-category']     = CategoryController::init();
		$this->container['rest-status']       = StatusController::init();
		$this->container['rest-priority']     = PriorityController::init();
		$this->container['rest-agent']        = AgentController::init();
		$this->container['rest-role']         = AgentRoleController::init();
		$this->container['rest-settings']     = SettingController::init();
		$this->container['rest-user_ticket']  = UserTicketController::init();
		$this->container['rest-user_thread']  = UserTicketThreadController::init();
	}

	/**
	 * Include frontend classes
	 *
	 * @return void
	 */
	public function ajax_includes() {
		$this->container['ajax'] = Ajax::init();
	}

	/**
	 * Run on plugin activation
	 *
	 * @return void
	 */
	public function activation_includes() {
		Install::init();
		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation
	 *
	 * @return void
	 */
	public function deactivation_includes() {
		flush_rewrite_rules();
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, rest, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( string $type ): bool {
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
