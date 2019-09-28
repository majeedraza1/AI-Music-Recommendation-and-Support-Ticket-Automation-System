<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\AgentRole;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) || exit;

class AgentRoleController extends ApiController {

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
		register_rest_route( $this->namespace, '/roles', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
			],
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_item' ],
				'args'     => $this->get_create_item_params(),
			],
		] );
		register_rest_route( $this->namespace, '/role', [
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'update_item' ],
				'args'     => $this->get_update_item_params(),
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
		$roles = AgentRole::get_roles();

		return $this->respondOK( [
			'roles' => $this->prepare_items_for_response( $roles )
		] );
	}

	/**
	 * Prepares the item for the REST response.
	 *
	 * @param AgentRole[] $items WordPress representation of the item.
	 *
	 * @return array Response object on success, or WP_Error object on failure.
	 */
	public function prepare_items_for_response( $items ) {
		return array_values( $items );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$role         = $request->get_param( 'role' );
		$name         = $request->get_param( 'name' );
		$capabilities = $request->get_param( 'capabilities' );

		if ( empty( $role ) ) {
			return $this->respondUnprocessableEntity( null, 'Role is required.' );
		}

		$role = AgentRole::add_role( $role, $name, $capabilities );

		if ( is_wp_error( $role ) ) {
			return $this->respondUnprocessableEntity( $role->get_error_code(), $role->get_error_message() );
		}

		return $this->respondCreated( $role );
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$role         = $request->get_param( 'role' );
		$name         = $request->get_param( 'name' );
		$capabilities = $request->get_param( 'capabilities' );

		if ( empty( $role ) ) {
			return $this->respondUnprocessableEntity( null, 'Role is required.' );
		}

		$agent_role = AgentRole::get_role( $role );
		if ( ! $agent_role instanceof AgentRole ) {
			return $this->respondNotFound( null, 'No role found' );
		}

		$_name = $agent_role->get_role_name();
		if ( $name != $_name ) {
			$_name = $name;
		}

		$agent_role = AgentRole::update_role( $role, $capabilities, $_name );

		return $this->respondOK( $agent_role );
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$role = $request->get_param( 'role' );

		$agent_role = AgentRole::get_role( $role );
		if ( ! $agent_role instanceof AgentRole ) {
			return $this->respondNotFound( null, 'No role found' );
		}

		if ( AgentRole::remove_role( $role ) ) {
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
		$capabilities = AgentRole::valid_capabilities();

		return [
			'role'         => array(
				'description'       => 'Role slug.',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'name'         => array(
				'description'       => 'Role display name.',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'capabilities' => array(
				'description'       => 'Role capabilities. Valid capabilities are ' . implode( ', ', array_keys( $capabilities ) ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => 'rest_validate_request_arg',
			)
		];
	}

	/**
	 * Retrieves the query params for create new item.
	 *
	 * @return array
	 */
	public function get_update_item_params() {
		$capabilities = AgentRole::valid_capabilities();

		return [
			'role'         => array(
				'description'       => 'Role slug.',
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'name'         => array(
				'description'       => 'Role display name.',
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'capabilities' => array(
				'description'       => 'Role capabilities. Valid capabilities are ' . implode( ', ', array_keys( $capabilities ) ),
				'type'              => 'object',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			)
		];
	}
}