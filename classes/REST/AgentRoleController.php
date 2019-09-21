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
			],
		] );
		register_rest_route( $this->namespace, '/roles/(?P<id>\d+)', [
			[
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => [ $this, 'delete_item' ],
			],
		] );
		register_rest_route( $this->namespace, '/roles/batch', [
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'update_batch_items' ],
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
}