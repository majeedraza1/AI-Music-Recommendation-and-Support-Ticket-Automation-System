<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\SupportTicket;
use WC_Order;
use WP_Error;
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
		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/call', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'mark_as_called' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/sms', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_sms' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/order', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_order' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/order/(?P<order_id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'change_order_status' ] ],
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
	 * Retrieves a collection of devices.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function send_sms( $request ) {
		if ( ! current_user_can( 'create_twilio_messages' ) ) {
			return $this->respondUnauthorized();
		}

		$id                    = (int) $request->get_param( 'id' );
		$custom_phone          = $request->get_param( 'custom_phone' );
		$agents_ids            = $request->get_param( 'agents_ids' );
		$content               = $request->get_param( 'content' );
		$sms_for               = $request->get_param( 'sms_for' );
		$acceptable            = [ 'customer', 'custom', 'agents' ];
		$send_to_customer      = ( 'customer' == $sms_for );
		$send_to_custom_number = ( 'custom' == $sms_for );
		$send_to_agents        = ( 'agents' == $sms_for );

		if ( mb_strlen( $content ) < 5 ) {
			return $this->respondUnprocessableEntity( null, 'Message content must be at least 5 characters.' );
		}

		$supportTicket = ( new SupportTicket )->find_by_id( $id );
		if ( ! $supportTicket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! in_array( $sms_for, $acceptable ) ) {
			return $this->respondUnprocessableEntity();
		}

		$customer_phone = $supportTicket->get( 'customer_phone' );

		$phones = [];

		if ( ! empty( $customer_phone ) && $send_to_customer ) {
			$phones[] = $customer_phone;
		}

		if ( ! empty( $custom_phone ) && $send_to_custom_number ) {
			$phones[] = $custom_phone;
		}

		if ( is_array( $agents_ids ) && count( $agents_ids ) && $send_to_agents ) {
			foreach ( $agents_ids as $user_id ) {
				$billing_phone = get_user_meta( $user_id, 'billing_phone', true );
				if ( ! empty( $billing_phone ) ) {
					$phones[] = $billing_phone;
				}
			}
		}

		if ( count( $phones ) < 1 ) {
			return $this->respondUnprocessableEntity( null, 'Please add SMS receiver(s) numbers.' );
		}

		ob_start(); ?>
        <table class="table--support-order">
            <tr>
                <td>Phone Number:</td>
                <td><?php echo implode( ', ', $phones ) ?></td>
            </tr>
            <tr>
                <td>SMS Content:</td>
                <td><?php echo $content; ?></td>
            </tr>
        </table>
		<?php
		$html = ob_get_clean();

		$user = wp_get_current_user();
		$supportTicket->add_ticket_info( $id, [
			'thread_type'    => 'sms',
			'customer_name'  => $user->display_name,
			'customer_email' => $user->user_email,
			'post_content'   => $html,
			'agent_created'  => $user->ID,
		] );

		( new Twilio() )->send_support_ticket_sms( $phones, $content );

		return $this->respondOK();
	}

	/**
	 * Create new order from lead
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_order( $request ) {
		$id = (int) $request->get_param( 'id' );

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$created_via   = $support_ticket->created_via();
		$belongs_to_id = $support_ticket->belongs_to_id();

		if ( $created_via !== 'appointment' ) {
			return $this->respondUnprocessableEntity();
		}

		return $this->respondInternalServerError();
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
