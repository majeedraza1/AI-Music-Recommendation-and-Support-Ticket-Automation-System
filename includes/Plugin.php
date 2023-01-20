<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Admin\Admin;
use StackonetSupportTicket\Admin\Settings;
use StackonetSupportTicket\Emails\AdminRepliedToTicket;
use StackonetSupportTicket\Integration\NinjaForms\Module as NinjaFormsModule;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\REST\Admin\AgentController;
use StackonetSupportTicket\REST\Admin\AgentRoleController;
use StackonetSupportTicket\REST\Admin\SettingController;
use StackonetSupportTicket\REST\Admin\TicketAgentController;
use StackonetSupportTicket\REST\AttachmentController;
use StackonetSupportTicket\REST\CategoryController;
use StackonetSupportTicket\REST\Me\UserTicketController;
use StackonetSupportTicket\REST\PriorityController;
use StackonetSupportTicket\REST\StatusController;
use StackonetSupportTicket\REST\SupportTicketController;
use StackonetSupportTicket\REST\TicketController;
use StackonetSupportTicket\REST\TicketSmsController;
use StackonetSupportTicket\REST\TicketThreadController;
use StackonetSupportTicket\REST\WebLoginController;
use WP_Error;

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

	private $plugin_data = [
		'Version'     => '',
		'TextDomain'  => '',
		'Name'        => '',
		'RequiresPHP' => '',
	];

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			self::$instance->read_plugin_data();

			add_action( 'plugins_loaded', [ self::$instance, 'includes' ] );
			add_action( 'plugins_loaded', [ NinjaFormsModule::class, 'init' ], 1 );
			add_action( 'stackonet_support_ticket/activation', [ self::$instance, 'activation_includes' ] );
			add_action( 'stackonet_support_ticket/deactivation', [ self::$instance, 'deactivation_includes' ] );
			add_filter( 'stackonet_support_ticket/webhook/create', [ self::$instance, 'create_ticket' ] );
		}

		return self::$instance;
	}

	/**
	 * Read plugin data
	 *
	 * @return void
	 */
	private function read_plugin_data() {
		$this->plugin_data = get_file_data( $this->get_plugin_file(), [
			'Version'     => 'Version',
			'TextDomain'  => 'Text Domain',
			'Name'        => 'Plugin Name',
			'RequiresPHP' => 'Requires PHP',
		] );
	}

	/**
	 * Instantiate the required classes
	 *
	 * @return void
	 */
	public function includes() {
		add_filter( 'map_meta_cap', [ new SupportTicket(), 'map_meta_cap' ], 10, 4 );
		add_action( 'init', array( self::$instance, 'register_taxonomy' ), 99 );

		$this->container['assets']   = Assets::init();
		$this->container['settings'] = Settings::init();

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

		AdminRepliedToTicket::init();
	}

	/**
	 * Include modules main classes
	 *
	 * @return void
	 */
	public function modules_includes() {
//		$this->container['module_ninja_forms'] = NinjaFormsModule::init();
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
	 * Register post types and taxonomies
	 */
	public function register_taxonomy() {
		// Register categories taxonomy
		register_taxonomy(
			'ticket_category',
			'support_ticket',
			[
				'public'  => false,
				'rewrite' => false,
			]
		);

		// Register status taxonomy
		register_taxonomy(
			'ticket_status',
			'support_ticket',
			[
				'public'  => false,
				'rewrite' => false,
			]
		);

		// Register priorities taxonomy
		register_taxonomy(
			'ticket_priority',
			'support_ticket',
			[
				'public'  => false,
				'rewrite' => false,
			]
		);

		register_taxonomy(
			'support_agent',
			'support_ticket',
			[
				'public'  => false,
				'rewrite' => false,
			]
		);
	}

	/**
	 * @param  array  $args
	 *
	 * @return array|WP_Error
	 */
	public function create_ticket( array $args ) {
		$request = new \WP_REST_Request();
		foreach ( $args as $key => $value ) {
			$request->set_param( $key, $value );
		}
		$response = ( new TicketController() )->create_item( $request );

		return $response->get_data();
	}

	/**
	 * Run on plugin activation
	 *
	 * @return void
	 */
	public function activation_includes() {
		$this->register_taxonomy();
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
	 * @param  string  $type  admin, ajax, rest, cron or frontend.
	 *
	 * @return bool
	 */
	public function is_request( string $type ): bool {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'rest':
				return defined( 'REST_REQUEST' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * Get plugin main file
	 *
	 * @return string
	 */
	public function get_plugin_file(): string {
		return STACKONET_SUPPORT_TICKET_FILE;
	}

	/**
	 * Get plugin rest namespace
	 *
	 * @return string
	 */
	public function get_rest_namespace(): string {
		if ( defined( 'STACKONET_SUPPORT_TICKET_REST_NAMESPACE' ) ) {
			return STACKONET_SUPPORT_TICKET_REST_NAMESPACE;
		}

		return 'stackonet-support-ticket/v1';
	}

	/**
	 * Get the plugin url.
	 *
	 * @param  string  $path  Extra path appended to the end of the URL.
	 *
	 * @return string
	 */
	public function get_plugin_url( string $path = '' ): string {
		return plugins_url( $path, $this->get_plugin_file() );
	}

	/**
	 * Get plugin path
	 *
	 * @return string
	 */
	public function get_plugin_path(): string {
		return dirname( $this->get_plugin_file() );
	}

	/**
	 * Get plugin directory/folder name.
	 *
	 * @return string
	 */
	public function get_directory_name(): string {
		return basename( $this->get_plugin_path() );
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function get_plugin_version(): string {
		if ( ! empty( $this->plugin_data['Version'] ) ) {
			return $this->plugin_data['Version'];
		}

		return date( 'Y.m.d.Gi', filemtime( $this->get_plugin_file() ) );
	}
}
