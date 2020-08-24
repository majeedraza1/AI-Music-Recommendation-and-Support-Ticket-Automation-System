<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\Models\TicketThread;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class UserTicketController extends ApiController {

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
		register_rest_route( $this->namespace, '/tickets/me', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
				'args'     => $this->get_collection_params(),
			],
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_item' ],
				'args'     => $this->get_collection_params(),
			],
		] );

		register_rest_route( $this->namespace, '/tickets/me/(?P<id>\d+)', [
			'args' => [
				'id' => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				],
			],
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_item' ]
			],
		] );

		register_rest_route( $this->namespace, '/tickets/me/(?P<id>\d+)/thread', [
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_thread' ],
			],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			return $this->respondUnauthorized();
		}

		$paged           = $request->get_param( 'page' );
		$per_page        = $request->get_param( 'per_page' );
		$search          = $request->get_param( 'search' );
		$ticket_status   = (int) $request->get_param( 'ticket_status' );
		$ticket_category = (int) $request->get_param( 'ticket_category' );
		$ticket_priority = (int) $request->get_param( 'ticket_priority' );

		$args = [ 'page' => $paged, 'per_page' => $per_page, 'agent_created' => $user->ID ];

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		if ( ! empty( $ticket_status ) ) {
			$args['ticket_status'] = $ticket_status;
		}

		if ( ! empty( $ticket_category ) ) {
			$args['ticket_category'] = $ticket_category;
		}

		if ( ! empty( $ticket_priority ) ) {
			$args['ticket_priority'] = $ticket_priority;
		}

		$results    = SupportTicket::find_for_user( $args );
		$pagination = SupportTicket::count_for_user( $args );

		$items = [];
		foreach ( $results as $result ) {
			$items[] = static::format_item_for_response( $result );
		}

		$response = [
			'items'      => $items,
			'pagination' => static::get_pagination_data( $pagination, $per_page, $paged )
		];

		return $this->respondOK( $response );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 * @throws \Exception
	 */
	public function create_item( $request ) {
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			return $this->respondUnauthorized();
		}

		$response = ( new TicketController() )->create_item( $request );

		if ( $response->get_status() === 201 ) {
			$data   = $response->get_data();
			$ticket = ( new SupportTicket )->find_by_id( $data['data']['ticket_id'] );

			$response = [
				'ticket'  => static::format_item_for_response( $ticket ),
				'threads' => static::format_thread_collections( $ticket->get_ticket_threads() ),
			];

			return $this->respondCreated( $response );
		}

		return $response;
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_thread( $request ) {
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			return $this->respondUnauthorized();
		}

		$id             = (int) $request->get_param( 'id' );
		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( $support_ticket->get( 'agent_created' ) != $user->ID ) {
			return $this->respondUnauthorized();
		}

		$thread_type        = $request->get_param( 'thread_type' );
		$thread_content     = $request->get_param( 'thread_content' );
		$ticket_attachments = $request->get_param( 'thread_attachments' );
		$attachments        = is_array( $ticket_attachments ) ? $ticket_attachments : [];

		if ( empty( $id ) || empty( $thread_type ) || empty( $thread_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket ID, thread type and thread content is required.' );
		}

		if ( ! in_array( $thread_type, TicketThread::get_thread_types() ) ) {
			return $this->respondUnprocessableEntity( null, 'Only note and reply are supported.' );
		}

		$thread_id = $support_ticket->add_ticket_info( $id, [
			'thread_type'    => $thread_type,
			'customer_name'  => $user->display_name,
			'customer_email' => $user->user_email,
			'post_content'   => $thread_content,
			'agent_created'  => $user->ID,
		], $attachments );

		do_action( 'stackonet_support_ticket/v3/thread_created', $id, $thread_id );

		$response = [
			'ticket'  => static::format_item_for_response( $support_ticket ),
			'threads' => static::format_thread_collections( $support_ticket->get_ticket_threads() ),
		];

		return $this->respondCreated( $response );
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			return $this->respondUnauthorized();
		}

		$id = (int) $request->get_param( 'id' );

		$supportTicket = ( new SupportTicket )->find_by_id( $id );
		if ( ! $supportTicket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( $supportTicket->get( 'agent_created' ) != $user->ID ) {
			return $this->respondUnauthorized();
		}

		$response = [
			'ticket'  => static::format_item_for_response( $supportTicket ),
			'threads' => static::format_thread_collections( $supportTicket->get_ticket_threads() ),
		];

		return $this->respondOK( $response );
	}

	/**
	 * @param SupportTicket $ticket
	 *
	 * @return array
	 */
	public static function format_item_for_response( SupportTicket $ticket ) {
		return [
			'id'       => intval( $ticket->get( 'id' ) ),
			'subject'  => $ticket->get( 'ticket_subject' ),
			'created'  => mysql_to_rfc3339( $ticket->get( 'date_created' ) ),
			'updated'  => mysql_to_rfc3339( $ticket->get( 'date_updated' ) ),
			'status'   => $ticket->get_ticket_status(),
			'category' => $ticket->get_ticket_category(),
			'priority' => $ticket->get_ticket_priority(),
			'creator'  => [
				'id'     => intval( $ticket->get( 'agent_created' ) ),
				'name'   => $ticket->get( 'customer_name' ),
				'email'  => $ticket->get( 'customer_email' ),
				'avatar' => get_avatar_url( $ticket->get( 'customer_email' ) ),
				'phone'  => $ticket->get( 'customer_phone' ),
				'city'   => $ticket->get( 'city' ),
				'type'   => $ticket->get( 'user_type' ),
			],
		];
	}

	/**
	 * @param TicketThread $thread
	 *
	 * @return array
	 */
	public static function format_thread_for_response( TicketThread $thread ) {
		return [
			'id'          => $thread->get( 'id' ),
			'content'     => $thread->get( 'content' ),
			'creator'     => [
				'name'   => $thread->get( 'customer_name' ),
				'email'  => $thread->get( 'customer_email' ),
				'avatar' => $thread->get_avatar_url(),
			],
			'created'     => mysql_to_rfc3339( $thread->get( 'created' ) ),
			'thread_type' => $thread->get_type(),
			'attachments' => $thread->get_attachments(),
		];
	}

	/**
	 * @param array $threads
	 *
	 * @return array
	 */
	public static function format_thread_collections( array $threads ) {
		$data = [];
		foreach ( $threads as $thread ) {
			if ( $thread instanceof TicketThread ) {
				$data[] = static::format_thread_for_response( $thread );
			}
		}

		return $data;
	}

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_collection_params() {
		return [
			'page'            => [
				'description'       => __( 'Current page of the collection.' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			],
			'per_page'        => [
				'description'       => __( 'Maximum number of items to be returned in result set.' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'search'          => [
				'description'       => __( 'Limit results to those matching a string.' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			],
			'ticket_status'   => [
				'description'       => __( 'Limit results to those matching ticket status.' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'default'           => 0,
			],
			'ticket_category' => [
				'description'       => __( 'Limit results to those matching ticket category.' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'default'           => 0,
			],
			'ticket_priority' => [
				'description'       => __( 'Limit results to those matching ticket priority.' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'default'           => 0,
			],
		];
	}
}