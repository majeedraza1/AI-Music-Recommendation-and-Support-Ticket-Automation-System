<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\TicketCategory;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class CategoryController extends ApiController {

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
		register_rest_route( $this->namespace, '/categories', [
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
		register_rest_route( $this->namespace, '/categories/(?P<id>\d+)', [
			[
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => [ $this, 'delete_item' ],
			],
		] );
		register_rest_route( $this->namespace, '/categories/batch', [
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'update_batch_items' ],
				'args'     => $this->get_batch_update_params()
			],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$items = TicketCategory::get_all();

		return $this->respondOK( [ 'items' => $items ] );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$name        = $request->get_param( 'name' );
		$slug        = $request->get_param( 'slug' );
		$description = $request->get_param( 'description' );
		$parent      = $request->get_param( 'parent' );

		$args = [];
		if ( ! empty( $slug ) ) {
			$args['slug'] = $slug;
		}
		if ( ! empty( $description ) ) {
			$args['description'] = $description;
		}
		if ( ! empty( $parent ) ) {
			$args['parent'] = $parent;
		}

		$category_id = TicketCategory::create( $name, $args );
		if ( is_wp_error( $category_id ) ) {
			return $category_id;
		}

		return $this->respondCreated( [ 'category_id' => $category_id ] );
	}

	/**
	 * Update batch items
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function update_batch_items( $request ) {
		$menu_orders = $request->get_param( 'menu_orders' );
		if ( count( $menu_orders ) > 0 ) {
			TicketCategory::update_menu_orders( $menu_orders );
		}

		return $this->respondOK();
	}


	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$id = (int) $request->get_param( 'id' );

		if ( TicketCategory::delete( $id ) ) {
			return $this->respondOK( [ 'id' => $id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Get create items args
	 *
	 * @return array
	 */
	public function get_create_item_params() {
		return [
			'name'        => [
				'description'       => __( 'Category name.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'slug'        => [
				'description'       => __( 'Category slug. Must be unique for category.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'description' => [
				'description'       => __( 'Category description.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'parent'      => [
				'description'       => __( 'Parent category ID.', 'stackonet-support-ticker' ),
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'intval',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Get batch update items args
	 *
	 * @return array
	 */
	public function get_batch_update_params() {
		return [
			'menu_orders' => [
				'description'       => __( 'Array of all categories ID. New order will be set by numeric order.', 'stackonet-support-ticker' ),
				'type'              => 'array',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}
}