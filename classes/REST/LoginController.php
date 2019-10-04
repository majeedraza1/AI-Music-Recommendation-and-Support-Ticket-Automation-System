<?php

namespace StackonetSupportTicket\REST;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class LoginController extends ApiController {
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
		register_rest_route( $this->namespace, '/login', [
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'login' ],
			],
		] );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function login( $request ) {
		if ( is_user_logged_in() ) {
			return $this->respondUnauthorized( null, 'Sorry, Your are already logged in.' );
		}

		$user_login = $request->get_param( 'user_login' );
		$password   = $request->get_param( 'password' );
		$remember   = (bool) $request->get_param( 'remember' );

		if ( ! ( username_exists( $user_login ) || email_exists( $user_login ) ) ) {
			return $this->respondUnprocessableEntity( null, null, [
				'user_login' => [ 'No user found with this email' ]
			] );
		}

		if ( empty( $password ) ) {
			return $this->respondUnprocessableEntity( null, null, [
				'password' => [ 'Please provide password.' ]
			] );
		}

		$credentials = array(
			'user_login'    => $user_login,
			'user_password' => $password,
			'remember'      => $remember,
		);

		$user = wp_signon( $credentials, false );

		if ( is_wp_error( $user ) ) {
			return $this->respondUnprocessableEntity( null, null,
				[ 'password' => [ 'Password is not correct.' ] ] );
		}

		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID, false );

		return $this->respondOK( [ 'action' => 'reload' ] );
	}
}
