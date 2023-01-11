<?php

namespace StackonetSupportTicket\REST\Admin;

use StackonetSupportTicket\Admin\Settings;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\REST\ApiController;
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
			self::$instance = new self();

			add_action( 'rest_api_init', array( self::$instance, 'register_routes' ) );
		}

		return self::$instance;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/settings',
			[
				[
					'methods'  => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_settings' ],
				],
				[
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'update_settings' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/settings/user',
			[
				[
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => [ $this, 'update_user_settings' ],
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/settings/fields_labels',
			[
				[
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => [ $this, 'update_fields_labels' ],
				],
			]
		);
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param  WP_REST_Request  $request  Full data about the request.
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
				$options[ $key ] = $user_options[ $key ] ?? $value;

			}
		}

		$data['options']      = $options;
		$data['fields_label'] = Settings::get_custom_fields_labels();
		$data['user_fields']  = Settings::get_user_custom_fields();

		return $this->respondOK( $data );
	}

	/**
	 * Updates settings
	 *
	 * @param  WP_REST_Request  $request  Full data about the request.
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
	 * @param  WP_REST_Request  $request  Full data about the request.
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
			$_options[ $key ] = $sanitized_options[ $key ] ?? $default;
		}

		update_user_meta( $current_user->ID, '_stackonet_support_ticket', $_options );

		return $this->respondOK();
	}

	public function update_fields_labels( WP_REST_Request $request ) {
		$_fields_labels = $request->get_param( 'fields_labels' );
		$_user_fields   = $request->get_param( 'user_fields' );

		$unique_meta_keys = SupportTicket::get_unique_meta_keys();

		$fields_labels = [];
		$user_fields   = [];

		if ( is_array( $_fields_labels ) ) {
			$defaults      = array_fill_keys( $unique_meta_keys, '' );
			$fields_labels = array_replace_recursive( $defaults, $_fields_labels );
			update_option( 'support_ticket_extra_fields_labels', $fields_labels );
		}

		if ( is_array( $_user_fields ) ) {
			$defaults    = array_fill_keys( $unique_meta_keys, false );
			$user_fields = array_replace_recursive( $defaults, $_user_fields );
			update_option( 'support_ticket_user_extra_fields', $user_fields );
		}


		return $this->respondOK( [
			'fields_label' => $fields_labels,
			'user_fields'  => $user_fields
		] );
	}
}
