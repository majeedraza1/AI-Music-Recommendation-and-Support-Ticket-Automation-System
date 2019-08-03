<?php

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
	public $version = '1.0.0';

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

			// initialize the classes
			add_action( 'plugins_loaded', array( self::$instance, 'init_plugin' ) );
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
	 * Load the plugin after all plugins are loaded
	 */
	public function init_plugin() {
		$this->include_classes();
		$this->init_classes();
	}

	/**
	 * Include classes
	 */
	public function include_classes() {
		spl_autoload_register( function ( $className ) {
			if ( class_exists( $className ) ) {
				return;
			}
			// project-specific namespace prefix
			$prefix = 'StackonetSupportTicket\\';
			// base directory for the namespace prefix
			$base_dir = STACKONET_SUPPORT_TICKET_INCLUDES . DIRECTORY_SEPARATOR;
			// does the class use the namespace prefix?
			$len = strlen( $prefix );
			if ( strncmp( $prefix, $className, $len ) !== 0 ) {
				// no, move to the next registered autoloader
				return;
			}
			// get the relative class name
			$relative_class = substr( $className, $len );
			// replace the namespace prefix with the base directory, replace namespace
			// separators with directory separators in the relative class name, append
			// with .php
			$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
			// if the file exists, require it
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		} );
	}

	/**
	 * Instantiate the required classes
	 */
	public function init_classes() {
		$this->container['assets'] = StackonetSupportTicket\Assets::init();

		if ( $this->is_request( 'admin' ) ) {
			$this->container['admin'] = StackonetSupportTicket\Admin\Admin::init();
		}
		if ( $this->is_request( 'frontend' ) ) {
			$this->container['frontend'] = StackonetSupportTicket\Frontend::init();
		}
		if ( $this->is_request( 'ajax' ) ) {
			$this->container['ajax'] = StackonetSupportTicket\Ajax::init();
		}
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

StackonetSupportTicket::init();
