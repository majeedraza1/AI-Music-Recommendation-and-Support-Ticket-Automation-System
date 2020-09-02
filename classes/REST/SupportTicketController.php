<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\SupportTicket;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class SupportTicketController extends ApiController {

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
		register_rest_route( $this->namespace, '/tickets/(?P<id>\d+)/call', [
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'mark_as_called' ]
			],
		] );
	}

	/**
	 * Retrieves a collection of devices.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function mark_as_called( $request ) {
		if ( ! current_user_can( 'read_tickets' ) ) {
			return $this->respondUnauthorized();
		}

		$ticket_id = (int) $request->get_param( 'id' );
		$ticket    = ( new SupportTicket() )->find_by_id( $ticket_id );

		if ( ! $ticket instanceof SupportTicket ) {
			return $this->respondNotFound( null, 'No ticket found.' );
		}

		$ticket->update_metadata( $ticket_id, '_called_to_customer', 'yes' );

		return $this->respondOK( [ $ticket_id ] );
	}
}
