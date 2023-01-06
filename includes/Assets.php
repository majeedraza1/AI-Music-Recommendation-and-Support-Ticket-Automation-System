<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Models\AgentRole;

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
	private function is_script_debug_enabled(): bool {
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
			$this->version = $this->version . '-' . time();
		}

		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );
	}

	/**
	 * Register scripts
	 *
	 * @param  array  $scripts
	 *
	 * @return void
	 */
	private function register_scripts( array $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = $script['deps'] ?? false;
			$in_footer = $script['in_footer'] ?? true;
			$version   = $script['version'] ?? $this->version;
			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param  array  $styles
	 *
	 * @return void
	 */
	public function register_styles( array $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps = $style['deps'] ?? false;
			wp_register_style( $handle, $style['src'], $deps, $this->version );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @return array
	 */
	public function get_scripts(): array {
		return [
			$this->plugin_name . '-frontend' => [
				'src'       => $this->assets_url . '/js/frontend.js',
				'deps'      => [ 'wp-tinymce' ],
				'in_footer' => true,
			],
		];
	}

	/**
	 * Get registered styles
	 *
	 * @return array
	 */
	public function get_styles(): array {
		return [
			$this->plugin_name . '-frontend' => [
				'src' => $this->assets_url . '/css/frontend.css',
			],
		];
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
			'restRoot'        => esc_url_raw( rest_url( STACKONET_SUPPORT_TICKET_REST_NAMESPACE ) ),
		];

		if ( $current_user->exists() ) {
			$data['restNonce']    = wp_create_nonce( 'wp_rest' );
			$data['display_name'] = $current_user->display_name;
			$data['user_email']   = $current_user->user_email;
		}

		if ( current_user_can( 'manage_options' ) ) {
			$data['caps_settings'] = AgentRole::form_settings();
		}

		echo '<script>window.StackonetSupportTicket = ' . wp_json_encode( $data ) . '</script>';
	}
}
