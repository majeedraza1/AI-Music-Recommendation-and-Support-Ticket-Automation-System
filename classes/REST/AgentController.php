<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\AgentRole;
use StackonetSupportTicket\Models\SupportAgent;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_User;

defined( 'ABSPATH' ) || exit;

class AgentController extends ApiController {

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
		register_rest_route( $this->namespace, '/agents', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
			],
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_item' ],
				'args'     => $this->get_create_item_params()
			],
		] );
		register_rest_route( $this->namespace, '/agents/(?P<id>\d+)', [
			'args' => [
				'id' => [
					'description' => __( 'Unique identifier for the agent.' ),
					'type'        => 'integer',
				],
			],
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_item' ],
			],
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'update_item' ],
				'args'     => $this->get_update_item_params()
			],
			[
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => [ $this, 'delete_item' ],
			],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$items = SupportAgent::get_all();

		return $this->respondOK( [ 'items' => $items ] );
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$id = (int) $request->get_param( 'id' );

		$agent = SupportAgent::find_by_id( $id );

		if ( ! $agent instanceof SupportAgent ) {
			return $this->respondNotFound();
		}

		return $this->respondOK( $agent );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$user_id = $request->get_param( 'user_id' );
		$role_id = $request->get_param( 'role_id' );

		$user = get_user_by( 'id', $user_id );
		if ( ! $user instanceof WP_User ) {
			return $this->respondUnprocessableEntity( 'invalid_user_id', __( 'User ID is not valid.' ) );
		}

		$roles     = AgentRole::get_roles();
		$roles_ids = array_keys( $roles );

		if ( ! in_array( $role_id, $roles_ids, true ) ) {
			return $this->respondUnprocessableEntity( 'invalid_role_id', __( 'Role ID is not valid.' ) );
		}

		$agent = SupportAgent::create( $user_id, $role_id );
		if ( is_wp_error( $agent ) ) {
			$code    = $agent->get_error_code();
			$message = $agent->get_error_message();

			if ( 'term_exists' == $agent->get_error_code() ) {
				$code    = 'agent_exists';
				$message = __( 'Agent already exists.' );
			}

			return $this->respondUnprocessableEntity( $code, $message );
		}

		return $this->respondCreated();
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$id      = (int) $request->get_param( 'id' );
		$role_id = $request->get_param( 'role_id' );

		$agent = SupportAgent::find_by_id( $id );

		if ( ! $agent instanceof SupportAgent ) {
			return $this->respondNotFound( null, 'Support agent not found.' );
		}

		$role = AgentRole::get_role( $role_id );

		if ( ! $role instanceof AgentRole ) {
			return $this->respondNotFound( null, 'Support agent role not found.' );
		}

		SupportAgent::update_role( $id, $role_id );

		return $this->respondOK();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$id = (int) $request->get_param( 'id' );

		$agent = SupportAgent::find_by_id( $id );

		if ( ! $agent instanceof SupportAgent ) {
			return $this->respondNotFound( null, 'Support agent not found.' );
		}

		if ( SupportAgent::delete( $id ) ) {
			return $this->respondOK();
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Retrieves the query params for create new item.
	 *
	 * @return array
	 */
	public function get_create_item_params() {
		return [
			'user_id' => array(
				'description'       => 'WordPress user ID.',
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			),
			'role_id' => array(
				'description'       => 'Agent role ID.',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			)
		];
	}

	/**
	 * Retrieves the query params for update an item.
	 *
	 * @return array
	 */
	public function get_update_item_params() {
		return [
			'role_id' => array(
				'description'       => 'Agent role ID.',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			)
		];
	}
}