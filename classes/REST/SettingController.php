<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Supports\SettingHandler;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class SettingController extends ApiController {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Only one instance of the class can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self;

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/settings', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_settings' ] ],
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_settings' ] ],
		] );
		register_rest_route( $this->namespace, '/settings/user', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'update_user_settings' ] ],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_settings( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$user_options = (bool) $request->get_param( 'user_options' );

		$settings = SettingHandler::init();

		$data    = $settings->to_array();
		$options = $settings->get_options( true );

		if ( $user_options ) {
			$current_user = wp_get_current_user();
			$user_options = get_user_meta( $current_user->ID, '_stackonet_support_ticket', true );
			foreach ( $options as $key => $value ) {
				$options[ $key ] = isset( $user_options[ $key ] ) ? $user_options[ $key ] : $value;

			}
		}

		$data['options'] = $options;

		return $this->respondOK( $data );
	}

	/**
	 * Updates settings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_settings( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$settings = SettingHandler::init();
		$options  = $request->get_param( 'options' );
		$settings->update( $options, true );

		return $this->respondOK();
	}

	/**
	 * Updates settings
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_user_settings( $request ) {
		$current_user = wp_get_current_user();
		if ( ! $current_user->exists() ) {
			return $this->respondUnauthorized();
		}

		$settings = SettingHandler::init();
		$options  = $request->get_param( 'options' );

		if ( current_user_can( 'manage_options' ) ) {
			$settings->update( $options, true );
		}

		$sanitized_options = $settings->sanitize_options( $options );

		$defaults = [
			'support_ticket_primary_color'   => '',
			'support_ticket_secondary_color' => '',
		];
		$_options = [];
		foreach ( $defaults as $key => $default ) {
			$_options[ $key ] = isset( $sanitized_options[ $key ] ) ? $sanitized_options[ $key ] : $default;
		}

		update_user_meta( $current_user->ID, '_stackonet_support_ticket', $_options );

		return $this->respondOK();
	}
}