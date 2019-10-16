<?php

namespace StackonetSupportTicket\REST;

use Exception;
use StackonetSupportTicket\Models\SupportAgent;
use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\Models\TicketThread;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

defined( 'ABSPATH' ) or exit;

class TicketController extends ApiController {

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
		register_rest_route( $this->namespace, '/tickets', [
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
			],
			[
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'create_item' ],
				'args'     => $this->get_create_item_params(),
			],
		] );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
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

		$supportTicket = new SupportTicket();

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

		$statuses = $supportTicket->get_ticket_statuses_terms();
		$counts   = SupportTicket::tickets_count_by_terms( $statuses, 'ticket_status' );

		$pagination = $this->getPaginationMetadata( [
			'totalCount'  => $counts[ $status ],
			'limit'       => $per_page,
			'currentPage' => $paged,
		] );

		$response = [ 'items' => $items, 'pagination' => $pagination, 'filters' => [] ];

		$response['trash'] = [
			'key'           => 'trash',
			'name'          => __( 'Trash', 'stackonet-support-ticket' ),
			'singular_name' => __( 'Trash', 'stackonet-support-ticket' ),
			'count'         => $supportTicket->count_inactive_records(),
			'active'        => $status == 'trash'
		];

		if ( current_user_can( 'manage_options' ) ) {
			$response['filters'] = $this->get_filter_data(
				$status, $ticket_category, $ticket_priority, $agent
			);
		}

		if ( 'trash' == $status ) {
			$actions     = [
				[ 'key' => 'restore', 'label' => 'Restore' ],
				[ 'key' => 'delete', 'label' => 'Delete Permanently' ],
			];
			$bulkActions = $actions;
		} else {
			$actions     = [
				[ 'key' => 'view', 'label' => 'View' ],
				[ 'key' => 'trash', 'label' => 'Trash' ],
			];
			$bulkActions = [
				[ 'key' => 'trash', 'label' => 'Move to Trash' ],
			];
		}

		$response['meta_data'] = [ 'actions' => $actions, 'bulkActions' => $bulkActions ];

		return $this->respondOK( $response );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response Response object on success, or WP_Error object on failure.
	 * @throws Exception
	 */
	public function create_item( $request ) {
		$name           = $request->get_param( 'name' );
		$email          = $request->get_param( 'email' );
		$subject        = $request->get_param( 'subject' );
		$ticket_content = $request->get_param( 'content' );
		$phone_number   = $request->get_param( 'phone_number' );

		$ticket_category = $request->get_param( 'category' );
		$ticket_status   = $request->get_param( 'status' );
		$ticket_priority = $request->get_param( 'priority' );

		$attachments = $request->get_param( 'attachments' );
		if ( ! empty( $attachments ) ) {
			$attachments = is_array( $attachments ) ? array_map( 'intval', $attachments ) : [];
		}

		$default_category = (int) get_option( 'support_ticket_default_category' );
		$default_status   = (int) get_option( 'support_ticket_default_status' );
		$default_priority = (int) get_option( 'support_ticket_default_priority' );

		$data = [
			'ticket_subject'   => $subject,
			'customer_name'    => $name,
			'customer_email'   => $email,
			'user_type'        => get_current_user_id() ? 'user' : 'guest',
			'ticket_category'  => ! empty( $ticket_category ) ? $ticket_category : $default_category,
			'ticket_status'    => ! empty( $ticket_status ) ? $ticket_status : $default_status,
			'ticket_priority'  => ! empty( $ticket_priority ) ? $ticket_priority : $default_priority,
			'ip_address'       => self::get_remote_ip(),
			'agent_created'    => get_current_user_id(),
			'ticket_auth_code' => bin2hex( random_bytes( 5 ) ),
			'active'           => 1
		];

		$ticket_id = ( new SupportTicket )->create( $data );

		if ( ! empty( $ticket_id ) ) {
			$html = $this->get_ticket_content( $name, $phone_number, $ticket_content );

			$thread_data = [
				'ticket_id'      => $ticket_id,
				'post_content'   => $html,
				'customer_name'  => $name,
				'customer_email' => $email,
				'thread_type'    => 'report',
				'attachments'    => $attachments,
			];

			$thread_id = ( new TicketThread )->create( $thread_data );

			return $this->respondCreated( [ 'ticket_id' => $ticket_id, 'thread_id' => $thread_id ] );
		}

		return $this->respondInternalServerError();
	}

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_create_item_params() {
		return array(
			'name'         => array(
				'description'       => __( 'User full name.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'email'        => array(
				'description'       => __( 'User email address.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_email',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'phone_number' => array(
				'description'       => __( 'User phone number.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'subject'      => array(
				'description'       => __( 'Ticket subject.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'content'      => array(
				'description'       => __( 'Ticket content.', 'stackonet-support-ticker' ),
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'category'     => array(
				'description'       => __( 'Ticket category.', 'stackonet-support-ticker' ),
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'intval',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'status'       => array(
				'description'       => __( 'Ticket status.', 'stackonet-support-ticker' ),
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'intval',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'priority'     => array(
				'description'       => __( 'Ticket priority.', 'stackonet-support-ticker' ),
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'intval',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'attachments'  => array(
				'description'       => __( 'Array of WordPress media ID.', 'stackonet-support-ticker' ),
				'type'              => 'array',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * Get user IP address
	 *
	 * @return string
	 */
	public static function get_remote_ip() {
		$server_ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $server_ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				return $_SERVER[ $key ];
			}
		}

		// Fallback local ip.
		return '';
	}

	/**
	 * @param string $name
	 * @param string $phone
	 * @param string $content
	 *
	 * @return false|string
	 */
	public function get_ticket_content( $name, $phone, $content ) {
		ob_start(); ?>
        <table class="table--support-ticket">
            <tr>
                <td>Name:</td>
                <td><strong><?php echo $name ?></strong></td>
            </tr>
            <tr>
                <td>Phone:</td>
                <td><strong><?php echo $phone ?></strong></td>
            </tr>
            <tr>
                <td>Content:</td>
                <td><strong><?php echo $content; ?></strong></td>
            </tr>
        </table>
		<?php
		$html = ob_get_clean();

		return $html;
	}

	/**
	 *
	 * Get filter data
	 *
	 * @param int $status
	 * @param int $category
	 * @param int $priority
	 * @param int $agent
	 *
	 * @return array
	 */
	public function get_filter_data( $status, $category = null, $priority = null, $agent = null ) {
		$_categories = ( new SupportTicket() )->get_categories_terms();
		$counts      = SupportTicket::tickets_count_by_terms( $_categories, 'ticket_category' );
		$categories  = [];
		foreach ( $_categories as $_category ) {
			$categories[] = [
				'value'  => $_category->term_id,
				'label'  => $_category->name,
				'count'  => isset( $counts[ $_category->term_id ] ) ? $counts[ $_category->term_id ] : 0,
				'active' => $category == $_category->term_id
			];
		}

		$_priorities = ( new SupportTicket() )->get_priorities_terms();
		$counts      = SupportTicket::tickets_count_by_terms( $_priorities, 'ticket_priority' );
		$priorities  = [];
		foreach ( $_priorities as $_priority ) {
			$priorities[] = [
				'value'  => $_priority->term_id,
				'label'  => $_priority->name,
				'count'  => isset( $counts[ $_priority->term_id ] ) ? $counts[ $_priority->term_id ] : 0,
				'active' => $priority == $_priority->term_id
			];
		}

		$_statuses = ( new SupportTicket )->get_ticket_statuses_terms();
		$counts    = SupportTicket::tickets_count_by_terms( $_statuses, 'ticket_status' );
		$statuses  = [];
		foreach ( $_statuses as $_status ) {
			$statuses[] = [
				'value'  => $_status->term_id,
				'label'  => $_status->name,
				'count'  => isset( $counts[ $_status->term_id ] ) ? $counts[ $_status->term_id ] : 0,
				'active' => $status == $_status->term_id
			];
		}

		$_agents = SupportAgent::get_all();
		$counts  = SupportTicket::count_tickets_by_agents();
		$agents  = [];
		foreach ( $_agents as $_agent ) {
			$agents[] = [
				'value'  => $_agent->get_user_id(),
				'label'  => $_agent->get_user()->display_name,
				'count'  => isset( $counts[ $_agent->get( 'term_id' ) ] ) ? $counts[ $_agent->get( 'term_id' ) ] : 0,
				'active' => $agent == $_agent->get_user_id()
			];
		}

		$cities = ( new SupportTicket() )->find_all_cities();

		return [
			[
				'id'            => 'status',
				'name'          => __( 'Statuses', 'stackonet-support-ticket' ),
				'singular_name' => __( 'Status', 'stackonet-support-ticket' ),
				'options'       => $statuses,
			],
			[
				'id'            => 'priority',
				'name'          => __( 'Priorities', 'stackonet-support-ticket' ),
				'singular_name' => __( 'Priority', 'stackonet-support-ticket' ),
				'options'       => $priorities
			],
			[
				'id'            => 'agent',
				'name'          => __( 'Agents', 'stackonet-support-ticket' ),
				'singular_name' => __( 'Agent', 'stackonet-support-ticket' ),
				'options'       => $agents
			],
			[
				'id'            => 'category',
				'name'          => __( 'Categories', 'stackonet-support-ticket' ),
				'singular_name' => __( 'Category', 'stackonet-support-ticket' ),
				'options'       => $categories
			],
			[
				'id'            => 'city',
				'name'          => __( 'Cities', 'stackonet-support-ticket' ),
				'singular_name' => __( 'City', 'stackonet-support-ticket' ),
				'options'       => $cities
			],
		];
	}
}