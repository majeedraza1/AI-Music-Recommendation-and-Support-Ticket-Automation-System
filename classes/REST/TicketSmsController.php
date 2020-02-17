<?php

namespace StackonetSupportTicket\REST;

use StackonetSupportTicket\Models\SupportTicket;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class TicketSmsController extends ApiController {
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
		register_rest_route( $this->namespace, '/tickets/(?P<id>\d+)/sms', [
			'args' => [
				'id' => [
					'description' => __( 'Unique identifier for the ticket.' ),
					'type'        => 'integer',
				],
			],
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'send_sms' ],
				'args'     => $this->get_send_sms_params()
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

		/**
		 * Send support ticket SMS
		 *
		 * @param array $data Array containing numbers and message parameters
		 */
		do_action( 'stackonet_support_ticket/v3/send_short_message', $content, $phones );

		return $this->respondOK( [ 'numbers' => $phones, 'message' => $content ] );
	}

	/**
	 * Get send sms parameters
	 *
	 * @return array
	 */
	protected function get_send_sms_params() {
		return [
			'sms_for'      => [
				'description' => __( 'Who should get sms.', 'stackonet-support-ticker' ),
				'type'        => 'string',
				'enum'        => [ 'customer', 'custom', 'agents' ],
			],
			'custom_phone' => [
				'description' => __( 'Custom phone number.', 'stackonet-support-ticker' ),
				'type'        => 'string',
			],
			'agents_ids'   => [
				'description' => __( 'Support ticket agents ids.', 'stackonet-support-ticker' ),
				'type'        => 'array',
			],
			'content'      => [
				'description' => __( 'SMS content.', 'stackonet-support-ticker' ),
				'type'        => 'string',
			],
		];
	}
}