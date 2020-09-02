<?php

namespace StackonetSupportTicket\REST\Admin;

use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\REST\ApiController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class TicketAgentController extends ApiController {

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
		register_rest_route( $this->namespace, '/tickets/(?P<id>\d+)/agent', [
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'update_item' ],
				'args'     => [
					'id'         => [
						'description' => __( 'Unique identifier for the ticket.' ),
						'type'        => 'integer',
					],
					'agents_ids' => [
						'description' => __( 'Array of agents ids to assign ticket.' ),
						'type'        => 'array',
					],
				],
			],
		] );
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$id    = (int) $request->get_param( 'id' );
		$agent = $request->get_param( 'agents_ids' );
		$agent = is_array( $agent ) ? array_map( 'intval', $agent ) : [];

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$support_ticket->update_agent( $agent );

		do_action( 'stackonet_support_ticket/v3/update_ticket_agent', $id, $agent );

		return $this->respondOK();
	}
}