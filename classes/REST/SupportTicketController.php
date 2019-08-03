<?php

namespace StackonetSupportTicket\REST;

use Exception;
use WP_Error;
use WP_Post;
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
		register_rest_route( $this->namespace, '/support-ticket', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_items' ], ],
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_item' ], ],
		] );

		register_rest_route( $this->namespace, '/customer-support-ticket', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_support_item' ], ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)', [
			[ 'methods' => WP_REST_Server::READABLE, 'callback' => [ $this, 'get_item' ] ],
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_item' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/agent', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_agent' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/call', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'mark_as_called' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/sms', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'send_sms' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/thread', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_thread' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/order', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'create_order' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/order/(?P<order_id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'change_order_status' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/(?P<id>\d+)/thread/(?P<thread_id>\d+)', [
			[ 'methods' => WP_REST_Server::EDITABLE, 'callback' => [ $this, 'update_thread' ] ],
			[ 'methods' => WP_REST_Server::DELETABLE, 'callback' => [ $this, 'delete_thread' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_item' ] ],
		] );

		register_rest_route( $this->namespace, '/support-ticket/batch_delete', [
			[ 'methods' => WP_REST_Server::CREATABLE, 'callback' => [ $this, 'delete_items' ] ],
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
	 * Retrieves a collection of devices.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'read_tickets' ) ) {
			return $this->respondUnauthorized();
		}

		$status          = $request->get_param( 'ticket_status' );
		$ticket_category = $request->get_param( 'ticket_category' );
		$ticket_priority = $request->get_param( 'ticket_priority' );
		$per_page        = $request->get_param( 'per_page' );
		$paged           = $request->get_param( 'paged' );
		$city            = $request->get_param( 'city' );
		$search          = $request->get_param( 'search' );
		$agent           = $request->get_param( 'agent' );

		$status          = ! empty( $status ) ? $status : 'all';
		$ticket_category = ! empty( $ticket_category ) ? $ticket_category : 'all';
		$ticket_priority = ! empty( $ticket_priority ) ? $ticket_priority : 'all';
		$city            = ! empty( $city ) ? $city : 'all';
		$per_page        = ! empty( $per_page ) ? absint( $per_page ) : 20;
		$paged           = ! empty( $paged ) ? absint( $paged ) : 1;

		$supportTicket = new SupportTicket;

		if ( ! empty( $search ) ) {
			$items = $supportTicket->search( [
				'search'          => $search,
				'ticket_category' => $ticket_category
			] );
		} else {
			$items = $supportTicket->find( [
				'paged'           => $paged,
				'per_page'        => $per_page,
				'ticket_status'   => $status,
				'ticket_category' => $ticket_category,
				'ticket_priority' => $ticket_priority,
				'city'            => $city,
				'agent'           => $agent,
			] );
		}
		$counts = $supportTicket->count_records();

		$pagination = $supportTicket->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );

		$response = [ 'items' => $items, 'counts' => $counts, 'pagination' => $pagination ];

		return $this->respondOK( $response );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 */
	public function create_item( $request ) {
		if ( ! current_user_can( 'create_tickets' ) ) {
			return $this->respondUnauthorized();
		}

		$params             = $request->get_params();
		$customer_name      = $request->get_param( 'customer_name' );
		$customer_email     = $request->get_param( 'customer_email' );
		$ticket_subject     = $request->get_param( 'ticket_subject' );
		$ticket_content     = $request->get_param( 'ticket_content' );
		$ticket_priority    = $request->get_param( 'ticket_priority' );
		$ticket_category    = $request->get_param( 'ticket_category' );
		$ticket_attachments = $request->get_param( 'ticket_attachments' );

		if ( empty( $ticket_subject ) || empty( $ticket_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket subject and content is required.' );
		}

		$user = wp_get_current_user();

		if ( ! filter_var( $customer_email, FILTER_VALIDATE_EMAIL ) ) {
			$customer_email = $user->user_email;
		}

		if ( empty( $customer_name ) ) {
			$customer_name = $user->display_name;
		}

		$data = [
			'ticket_subject'   => $ticket_subject,
			'customer_name'    => $customer_name,
			'customer_email'   => $customer_email,
			'user_type'        => 'guest',
			'ticket_status'    => get_option( 'wpsc_default_ticket_status' ),
			'ticket_category'  => get_option( 'wpsc_default_ticket_category' ),
			'ticket_priority'  => get_option( 'wpsc_default_ticket_priority' ),
			'ip_address'       => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'agent_created'    => $user->ID,
			'ticket_auth_code' => bin2hex( random_bytes( 5 ) ),
			'active'           => 1
		];

		if ( is_numeric( $ticket_priority ) ) {
			$data['ticket_priority'] = $ticket_priority;
		}

		if ( is_numeric( $ticket_category ) ) {
			$data['ticket_category'] = $ticket_category;
		}

		$SupportTicket = new SupportTicket;
		$ticket_id     = $SupportTicket->create( $data );
		if ( $ticket_id ) {
			$SupportTicket->update_metadata( $ticket_id, 'assigned_agent', '0' );
			( new TicketThread() )->create( [
				'ticket_id'      => $ticket_id,
				'post_content'   => $ticket_content,
				'customer_name'  => $customer_name,
				'customer_email' => $customer_email,
				'thread_type'    => 'report',
				'attachments'    => is_array( $ticket_attachments ) ? $ticket_attachments : [],
			] );

			return $this->respondCreated( [ 'ticket_id' => $ticket_id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 */
	public function create_support_item( $request ) {
		$customer_name   = $request->get_param( 'customer_name' );
		$customer_email  = $request->get_param( 'customer_email' );
		$ticket_subject  = $request->get_param( 'ticket_subject' );
		$ticket_content  = $request->get_param( 'ticket_content' );
		$ticket_category = $request->get_param( 'ticket_category' );
		$phone_number    = $request->get_param( 'phone_number' );

		if ( empty( $customer_email ) || empty( $customer_name ) ) {
			return $this->respondUnprocessableEntity( null, 'Customer name and email are required.' );
		}

		if ( empty( $ticket_subject ) || empty( $ticket_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket subject and content are required.' );
		}

		$category = get_option( 'wpsc_default_contact_form_ticket_category' );

		$data = [
			'ticket_subject'   => $ticket_subject,
			'customer_name'    => $customer_name,
			'customer_email'   => $customer_email,
			'ticket_category'  => $ticket_category,
			'user_type'        => get_current_user_id() ? 'user' : 'guest',
			'ticket_status'    => get_option( 'wpsc_default_ticket_status' ),
			'ticket_priority'  => get_option( 'wpsc_default_ticket_priority' ),
			'ip_address'       => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'agent_created'    => 0,
			'ticket_auth_code' => bin2hex( random_bytes( 5 ) ),
			'active'           => 1
		];

		$SupportTicket = new SupportTicket;
		$ticket_id     = $SupportTicket->create( $data );

		ob_start(); ?>
        <table class="table--support-order">
            <tr>
                <td>Name:</td>
                <td><strong><?php echo $customer_name ?></strong></td>
            </tr>
            <tr>
                <td>Phone:</td>
                <td><strong><?php echo $phone_number ?></strong></td>
            </tr>
            <tr>
                <td>Content:</td>
                <td><strong><?php echo $ticket_content; ?></strong></td>
            </tr>
        </table>
		<?php
		$html = ob_get_clean();

		if ( $ticket_id ) {
			$SupportTicket->update_metadata( $ticket_id, 'assigned_agent', '0' );
			( new TicketThread() )->create( [
				'ticket_id'      => $ticket_id,
				'post_content'   => $html,
				'customer_name'  => $customer_name,
				'customer_email' => $customer_email,
				'thread_type'    => 'report',
			] );

			return $this->respondCreated( [ 'ticket_id' => $ticket_id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 */
	public function get_item( $request ) {
		if ( ! current_user_can( 'read_tickets' ) ) {
			return $this->respondUnauthorized();
		}

		$id = (int) $request->get_param( 'id' );

		$supportTicket = ( new SupportTicket )->find_by_id( $id );
		if ( ! $supportTicket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		$ticket     = $supportTicket->to_array();
		$threads    = $supportTicket->get_ticket_threads();
		$pagination = $supportTicket->find_pre_and_next( $id );

		$response = [ 'ticket' => $ticket, 'threads' => $threads, 'navigation' => $pagination ];

		global $wpdb;
		$sql      = $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = '_support_ticket_id' AND meta_value = %d", $id );
		$result   = $wpdb->get_row( $sql, ARRAY_A );
		$order_id = isset( $result['post_id'] ) ? intval( $result['post_id'] ) : 0;
		if ( $order_id ) {
			$order           = wc_get_order( $order_id );
			$order_url       = add_query_arg( [
				'post'   => $order->get_id(),
				'action' => 'edit'
			], admin_url( 'post.php' ) );
			$payment_page_id = Settings::get_payment_page_id();
			$page_url        = get_permalink( $payment_page_id );
			$payment_url     = add_query_arg( [
				'order' => $order->get_id(),
				'token' => $order->get_meta( '_reschedule_hash', true ),
			], $page_url );
			$_paid_date      = get_post_meta( $order_id, '_paid_date', true );
			$link_sms_sent   = get_post_meta( $order_id, '_payment_link_sms_sent', true );
			$link_email_sent = get_post_meta( $order_id, '_payment_link_email_sent', true );
			$payment_status  = 'repairing';
			if ( ! empty( $_paid_date ) ) {
				$payment_status = 'complete';
			} elseif ( ! empty( $link_sms_sent ) || ! empty( $link_email_sent ) ) {
				$payment_status = 'processing';
			}

			$_custom_amount = get_post_meta( $order_id, '_custom_payment_amount', true );
			$custom_amount  = [];
			if ( is_array( $_custom_amount ) && count( $_custom_amount ) ) {
				foreach ( $_custom_amount as $index => $item ) {
					if ( empty( $item['amount'] ) ) {
						continue;
					}

					$custom_payment_url = add_query_arg( [
						'order' => $order->get_id(),
						'token' => $order->get_meta( '_reschedule_hash', true ),
						'_type' => 'custom',
						'_hash' => $item['hash'],
					], $page_url );

					$custom_amount[ $index ]                = $item;
					$custom_amount[ $index ]['payment_url'] = Utils::shorten_url( $custom_payment_url );
				}
			}

			$response['order'] = [
				'id'                  => $order->get_id(),
				'order_total'         => $order->get_formatted_order_total(),
				'status'              => 'wc-' . $order->get_status(),
				'order_edit_url'      => $order_url,
				'address'             => $order->get_formatted_billing_address(),
				'latitude_longitude'  => GoogleMap::get_customer_latitude_longitude_from_order( $order ),
				'payment_status'      => $payment_status,
				'payment_url'         => $payment_url,
				'custom_amount_items' => $custom_amount,
			];
		}

		// Map Data
		if ( $supportTicket->created_via() == 'map' ) {
			$response['map'] = ( new Map() )->find_by_id( $supportTicket->belongs_to_id() );
		}

		// Map Data
		if ( $supportTicket->created_via() == 'checkout_analysis' ) {
			$checkout_analysis = ( new CheckoutAnalysis() )->find_by_id( $supportTicket->belongs_to_id() );

			$response['checkout_analysis'] = $checkout_analysis->get_rest_response_data();
		}

		return $this->respondOK( $response );
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$id = (int) $request->get_param( 'id' );

		$supportTicket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $supportTicket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$data = $request->get_params();

		if ( ( new SupportTicket() )->update( $data ) ) {
			return $this->respondOK();
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		$id     = $request->get_param( 'id' );
		$action = $request->get_param( 'action' );

		$id     = ! empty( $id ) ? absint( $id ) : 0;
		$action = ! empty( $action ) ? sanitize_text_field( $action ) : '';

		if ( ! in_array( $action, [ 'trash', 'restore', 'delete' ] ) ) {
			return $this->respondUnauthorized();
		}

		$class  = new SupportTicket();
		$survey = $class->find_by_id( $id );

		if ( ! $survey instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'delete_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		if ( 'trash' == $action ) {
			$class->trash( $id );
		}
		if ( 'restore' == $action ) {
			$class->restore( $id );
		}
		if ( 'delete' == $action ) {
			$class->delete( $id );
		}

		return $this->respondOK( "#{$id} Support ticket has been deleted" );
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_items( $request ) {
		$ids    = $request->get_param( 'ids' );
		$ids    = is_array( $ids ) ? array_map( 'intval', $ids ) : [];
		$action = $request->get_param( 'action' );
		$action = ! empty( $action ) ? sanitize_text_field( $action ) : '';

		if ( ! in_array( $action, [ 'trash', 'restore', 'delete' ] ) ) {
			return $this->respondUnprocessableEntity();
		}

		$class = new SupportTicket();

		foreach ( $ids as $id ) {

			if ( ! current_user_can( 'delete_ticket', $id ) ) {
				continue;
			}

			if ( 'trash' == $action ) {
				$class->trash( $id );
			}
			if ( 'restore' == $action ) {
				$class->restore( $id );
			}
			if ( 'delete' == $action ) {
				$class->delete( $id );
			}
		}

		return $this->respondOK( "Support tickets has been deleted" );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_thread( $request ) {
		$id                 = (int) $request->get_param( 'id' );
		$thread_type        = $request->get_param( 'thread_type' );
		$thread_content     = $request->get_param( 'thread_content' );
		$ticket_attachments = $request->get_param( 'ticket_attachments' );
		$attachments        = is_array( $ticket_attachments ) ? $ticket_attachments : [];

		if ( empty( $id ) || empty( $thread_type ) || empty( $thread_content ) ) {
			return $this->respondUnprocessableEntity( null, 'Ticket ID, thread type and thread content is required.' );
		}

		if ( ! in_array( $thread_type, [ 'note', 'reply' ] ) ) {
			return $this->respondUnprocessableEntity( null, 'Only note and reply are supported.' );
		}

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$user = wp_get_current_user();

		$support_ticket->add_ticket_info( $id, [
			'thread_type'    => $thread_type,
			'customer_name'  => $user->display_name,
			'customer_email' => $user->user_email,
			'post_content'   => $thread_content,
			'agent_created'  => $user->ID,
		], $attachments );

		return $this->respondCreated();
	}

	/**
	 * Update thread content
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_thread( $request ) {
		$id           = (int) $request->get_param( 'id' );
		$thread_id    = (int) $request->get_param( 'thread_id' );
		$post_content = $request->get_param( 'post_content' );

		if ( empty( $id ) || empty( $thread_id ) || empty( $post_content ) ) {
			return $this->respondUnprocessableEntity();
		}

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

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

		$response = wp_update_post( [
			'ID'           => $thread_id,
			'post_content' => $post_content,
		] );

		if ( ! $response instanceof WP_Error ) {
			return $this->respondOK( $post_content );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_agent( $request ) {
		$id    = (int) $request->get_param( 'id' );
		$agent = $request->get_param( 'agents_ids' );

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'edit_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		$support_ticket->update_agent( $agent );

		return $this->respondOK();
	}

	/**
	 * Deletes multiple items from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_thread( $request ) {
		$id        = (int) $request->get_param( 'id' );
		$thread_id = (int) $request->get_param( 'thread_id' );

		$support_ticket = ( new SupportTicket )->find_by_id( $id );

		if ( ! $support_ticket instanceof SupportTicket ) {
			return $this->respondNotFound();
		}

		if ( ! current_user_can( 'delete_ticket', $id ) ) {
			return $this->respondUnauthorized();
		}

		if ( $support_ticket->delete_thread( $thread_id ) ) {
			return $this->respondOK( [ $id, $thread_id ] );
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

		$appointment = ( new Appointment() )->find_by_id( $belongs_to_id );

		if ( ! $appointment instanceof Appointment ) {
			return $this->respondNotFound( null, 'No appointment found.' );
		}

		try {
			$order_id = LeadSupportTicketToOrder::process( $appointment, $id );
			if ( $order_id ) {
				return $this->respondCreated( $appointment );
			}
		} catch ( Exception $e ) {
			Logger::log( $e->getMessage() );
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
