<?php

namespace StackonetSupportTicket;

defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin name slug
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin assets url
	 *
	 * @var string
	 */
	private $assets_url = '';

	/**
	 * plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_loaded', [ self::$instance, 'register' ] );

			add_action( 'admin_head', [ self::$instance, 'localize_data' ], 9 );
			add_action( 'wp_head', [ self::$instance, 'localize_data' ], 9 );
		}

		return self::$instance;
	}

	/**
	 * Check if script debugging is enabled
	 *
	 * @return bool
	 */
	private function is_script_debug_enabled() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @return void
	 */
	public function register() {
		$this->plugin_name = STACKONET_SUPPORT_TICKET;
		$this->version     = STACKONET_SUPPORT_TICKET_VERSION;
		$this->assets_url  = STACKONET_SUPPORT_TICKET_ASSETS;

		if ( $this->is_script_debug_enabled() ) {
			$this->version = time();
		}

		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	private function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
			$version   = isset( $script['version'] ) ? $script['version'] : $this->version;
			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param array $styles
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps = isset( $style['deps'] ) ? $style['deps'] : false;
			wp_register_style( $handle, $style['src'], $deps, $this->version );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts() {
		$scripts = [
			'tinymce-editor'                 => [
				'src'       => includes_url( 'js/tinymce/tinymce.min.js' ),
				'deps'      => [],
				'in_footer' => true
			],
			$this->plugin_name . '-frontend' => [
				'src'       => $this->assets_url . '/js/frontend.js',
				'deps'      => [ 'tinymce-editor' ],
				'in_footer' => true
			],
			$this->plugin_name . '-admin'    => [
				'src'       => $this->assets_url . '/js/admin.js',
				'deps'      => [ 'tinymce-editor', 'wp-color-picker' ],
				'in_footer' => true
			]
		];

		return $scripts;
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles() {
		$styles = [
			$this->plugin_name . '-frontend' => [
				'src' => $this->assets_url . '/css/frontend.css'
			],
			$this->plugin_name . '-admin'    => [
				'src'  => $this->assets_url . '/css/admin.css',
				'deps' => [ 'wp-color-picker' ],
			],
		];

		return $styles;
	}

	/**
	 * Global localize data both for admin and frontend
	 */
	public static function localize_data() {
		$current_user = wp_get_current_user();

		$data = [
			'homeUrl'         => home_url(),
			'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			'lostPasswordUrl' => wp_lostpassword_url(),
			'isUserLoggedIn'  => $current_user->exists(),
			'wpRestRoot'      => esc_url_raw( rest_url( 'wp/v2' ) ),
			'restRoot'        => esc_url_raw( rest_url( 'stackonet-support-ticket/v1' ) ),
		];

		if ( $current_user->exists() ) {
			$data['restNonce']    = wp_create_nonce( 'wp_rest' );
			$data['display_name'] = $current_user->display_name;
			$data['user_email']   = $current_user->user_email;
		}

		echo '<script>window.StackonetToolkit = ' . wp_json_encode( $data ) . '</script>';
	}
}
