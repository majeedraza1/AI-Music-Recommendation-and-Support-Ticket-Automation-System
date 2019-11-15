<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\SupportTicket;
use WC_Order;
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

		register_rest_route( $this->namespace, '/tickets/(?P<id>\d+)/order/(?P<order_id>\d+)', [
			[
				'methods'  => WP_REST_Server::EDITABLE,
				'callback' => [ $this, 'change_order_status' ]
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

	/**
	 * Create new order from lead
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function change_order_status( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $this->respondUnauthorized();
		}

		$id       = (int) $request->get_param( 'id' );
		$order_id = (int) $request->get_param( 'order_id' );
		$status   = $request->get_param( 'status' );

		$order_statuses = wc_get_order_statuses();

		if ( ! in_array( $status, array_keys( $order_statuses ) ) ) {
			return $this->respondUnprocessableEntity( null, 'Invalid status' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return $this->respondNotFound( null, 'No order found with this id.' );
		}

		$user           = wp_get_current_user();
		$current_status = 'wc-' . $order->get_status();

		ob_start();
		echo "<strong>{$user->display_name}</strong> has changed order status from <strong>{$order_statuses[$current_status]}</strong> to ";
		echo "<strong>{$order_statuses[$status]}</strong>";
		$post_content = ob_get_clean();


		$order->set_status( $status, $post_content, true );
		$order->save();


		( new SupportTicket() )->add_ticket_info( $id, [
			'thread_type'    => 'note',
			'customer_name'  => $user->display_name,
			'customer_email' => $user->user_email,
			'post_content'   => $post_content,
			'agent_created'  => 0,
		] );

		return $this->respondOK();
	}
}
