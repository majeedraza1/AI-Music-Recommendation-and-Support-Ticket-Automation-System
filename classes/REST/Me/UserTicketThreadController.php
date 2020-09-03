<?php

namespace StackonetSupportTicket\REST\Me;

use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\REST\ApiController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class UserTicketThreadController extends ApiController {
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
		register_rest_route( $this->namespace, '/tickets/me/(?P<id>\d+)/thread', [
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_item' ],
			],
		] );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
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

		$thread_type    = $request->get_param( 'thread_type' );
		$thread_content = $request->get_param( 'thread_content' );

		if ( empty( $id ) || empty( $thread_type ) || empty( $thread_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket ID, thread type and thread content is required.' );
		}

		$attachments = $this->get_attachments_ids( $request );
		if ( is_wp_error( $attachments ) ) {
			return $this->respondUnprocessableEntity( $attachments->get_error_code(), $attachments->get_error_message() );
		}

		$thread_id = SupportTicket::add_thread( $id, [
			'thread_type'    => $thread_type,
			'customer_name'  => $user->display_name,
			'customer_email' => $user->user_email,
			'post_content'   => $thread_content,
			'agent_created'  => $user->ID,
			'user_type'      => 'user',
		], $attachments );

		do_action( 'stackonet_support_ticket/v3/thread_created', $id, $thread_id );

		$response = [
			'ticket'  => static::format_item_for_response( $support_ticket ),
			'threads' => static::format_thread_collections( $support_ticket->get_ticket_threads() ),
		];

		return $this->respondCreated( $response );
	}
}