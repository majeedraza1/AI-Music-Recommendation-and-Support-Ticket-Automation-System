<?php

namespace StackonetSupportTicket\REST;

use Stackonet\WP\Framework\Supports\Validate;
use StackonetSupportTicket\Emails\AdminRepliedToTicket;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\Models\TicketThread;
use WP_Error;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class TicketThreadController extends ApiController {

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
			'/tickets/(?P<id>\d+)/thread',
			[
				[
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => [ $this, 'create_item' ],
					'args'     => $this->get_create_item_params(),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/tickets/(?P<id>\d+)/thread/(?P<thread_id>\d+)',
			[
				'args' => [
					'id'        => [
						'description' => __( 'Unique identifier for the ticket.' ),
						'type'        => 'integer',
					],
					'thread_id' => [
						'description' => __( 'Unique identifier for the ticket thread.' ),
						'type'        => 'integer',
					],
				],
				[
					'methods'  => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'update_item' ],
					'args'     => $this->get_update_item_params(),
				],
				[
					'methods'  => WP_REST_Server::DELETABLE,
					'callback' => [ $this, 'delete_item' ],
				],
			]
		);
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param  WP_REST_Request  $request  Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		$id             = (int) $request->get_param( 'id' );
		$thread_type    = $request->get_param( 'thread_type' );
		$thread_content = $request->get_param( 'thread_content' );

		if ( empty( $id ) || empty( $thread_type ) || empty( $thread_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket ID, thread type and thread content is required.' );
		}

		if ( ! in_array( $thread_type, TicketThread::get_thread_types() ) ) {
			return $this->respondUnprocessableEntity( null, 'Only note and reply are supported.' );
		}

		$support_ticket = ( new SupportTicket() )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$attachments = $this->get_attachments_ids( $request );
		if ( is_wp_error( $attachments ) ) {
			return $this->respondUnprocessableEntity( $attachments->get_error_code(),
				$attachments->get_error_message() );
		}

		$user = wp_get_current_user();

		$thread_id = SupportTicket::add_thread(
			$id,
			[
				'thread_type'    => $thread_type,
				'customer_name'  => $user->display_name,
				'customer_email' => $user->user_email,
				'post_content'   => $thread_content,
				'agent_created'  => $user->ID,
				'user_type'      => 'agent',
			],
			$attachments
		);

		$send_email_notification = Validate::checked( $request->get_param( 'send_email_notification' ) );
		if ( $send_email_notification ) {
			AdminRepliedToTicket::init()->push_to_queue( [
				'ticket_id' => $id,
				'thread_id' => $thread_id
			] );
		}

		do_action( 'stackonet_support_ticket/v3/thread_created', $id, $thread_id, $request->get_params() );

		return $this->respondCreated();
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param  WP_REST_Request  $request  Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$id           = (int) $request->get_param( 'id' );
		$thread_id    = (int) $request->get_param( 'thread_id' );
		$post_content = $request->get_param( 'thread_content' );

		if ( empty( $id ) || empty( $thread_id ) || empty( $post_content ) ) {
			return $this->respondUnprocessableEntity();
		}

		$support_ticket = ( new SupportTicket() )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$thread = get_post( $thread_id );

		if ( ! $thread instanceof WP_Post ) {
			return $this->respondNotFound( null, 'Sorry, no thread found.' );
		}

		$response = wp_update_post(
			[
				'ID'           => $thread_id,
				'post_content' => $post_content,
			]
		);

		if ( ! $response instanceof WP_Error ) {

			do_action( 'stackonet_support_ticket/v3/thread_updated', $id, $thread_id, $post_content );

			return $this->respondOK( $post_content );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param  WP_REST_Request  $request  Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$id        = (int) $request->get_param( 'id' );
		$thread_id = (int) $request->get_param( 'thread_id' );

		$support_ticket = ( new SupportTicket() )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'delete_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		do_action( 'stackonet_support_ticket/v3/delete_thread', $id, $thread_id );

		if ( $support_ticket->delete_thread( $thread_id ) ) {
			return $this->respondOK( [ $id, $thread_id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_create_item_params() {
		return [
			'id'                 => [
				'description' => __( 'Unique identifier for the ticket.' ),
				'type'        => 'integer',
			],
			'thread_type'        => [
				'description' => __( 'Thread type.' ),
				'type'        => 'string',
				'enum'        => TicketThread::get_thread_types(),
			],
			'thread_content'     => [
				'description' => __( 'Thread content.' ),
				'type'        => 'string',
			],
			'thread_attachments' => [
				'description' => __( 'Thread attachments. Array of WordPress media attachment id.' ),
				'type'        => 'array',
			],
		];
	}

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_update_item_params() {
		return [
			'thread_content' => [
				'description' => __( 'Thread content.' ),
				'type'        => 'string',
			],
		];
	}
}
