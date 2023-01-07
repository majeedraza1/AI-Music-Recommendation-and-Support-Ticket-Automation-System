<?php

namespace StackonetSupportTicket\Models;

use ArrayObject;
use DateTime;
use Exception;
use Stackonet\WP\Framework\Abstracts\DatabaseModel;
use StackonetSupportTicket\Supports\Utils;
use WP_Post;
use WP_Term;
use WP_Term_Query;
use WP_User;

defined( 'ABSPATH' ) or exit;

class SupportTicket extends DatabaseModel {

	/**
	 * Get current user support agent id
	 *
	 * @var int
	 */
	private static $current_user_agent_id = 0;

	/**
	 * Post type name
	 *
	 * @var string
	 */
	protected $table = 'support_ticket';

	/**
	 * @var string
	 */
	protected $meta_table = 'support_ticketmeta';

	/**
	 * @var string
	 */
	protected $cache_group = 'support_ticket';

	/**
	 * Object data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $created_at = 'date_created';

	/**
	 * @var string
	 */
	protected $updated_at = 'date_updated';

	/**
	 * @var string
	 */
	protected $created_by = 'agent_created';

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_data = [
		'id'               => 0,
		'ticket_status'    => 0,
		'customer_name'    => '',
		'customer_email'   => '',
		'customer_phone'   => '',
		'city'             => '',
		'ticket_subject'   => '',
		'user_type'        => '',
		'ticket_category'  => '',
		'ticket_priority'  => '',
		'date_created'     => '',
		'date_updated'     => '',
		'ip_address'       => '',
		'agent_created'    => 0,
		'ticket_auth_code' => '',
		'active'           => 1,
	];

	/**
	 * Default data
	 * Must contain all table columns name in (key => value) format
	 *
	 * @var array
	 */
	protected $default_metadata = [
		'ticket_id'      => 0,
		'thread_type'    => '',
		'customer_name'  => '',
		'customer_email' => '',
		'attachments'    => [],
	];

	/**
	 * @var array
	 */
	protected $valid_thread_types = [ 'report', 'log', 'reply' ];

	/**
	 * @var bool
	 */
	protected $assigned_agent_read = false;

	/**
	 * @var array
	 */
	protected $assigned_agents_ids = [];

	/**
	 * @var array
	 */
	protected $assigned_agents = [];

	/**
	 * @var array
	 */
	private $ticket_threads = [];

	/**
	 * @var false|mixed|string
	 */
	protected $avatar_url;

	/**
	 * Model constructor.
	 *
	 * @param  array  $data
	 */
	public function __construct( $data = [] ) {
		if ( $data ) {
			if ( isset( $data['ticket_id'] ) && is_numeric( $data['ticket_id'] ) ) {
				$data['id'] = intval( $data['ticket_id'] );
				unset( $data['ticket_id'] );

				if ( isset( $data['meta_key'] ) ) {
					unset( $data['meta_key'] );
				}
				if ( isset( $data['meta_value'] ) ) {
					unset( $data['meta_value'] );
				}
			}
		}
		parent::__construct( $data );
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 * @throws Exception
	 */
	public function to_array(): array {
		$data                       = parent::to_array();
		$data['customer_url']       = get_avatar_url( $this->get( 'customer_email' ) );
		$data['status']             = $this->get_ticket_status();
		$data['category']           = $this->get_ticket_category();
		$data['priority']           = $this->get_ticket_priority();
		$data['created_by']         = $this->get_agent_created();
		$data['assigned_agents']    = $this->get_assigned_agents();
		$data['updated']            = $this->update_at();
		$data['updated_human_time'] = $this->updated_human_time();
		$data['created_via']        = $this->created_via();
		$data['belongs_to_id']      = $this->belongs_to_id();
		$data['last_note_diff']     = $this->get_last_note_diff();
		$data['called_to_customer'] = $this->called_to_customer();

		return $data;
	}

	/**
	 * @return int
	 */
	public function get_ticket_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * Get ticket subject
	 *
	 * @return string
	 */
	public function get_ticket_subject() {
		return $this->get( 'ticket_subject' );
	}

	/**
	 * Get created via
	 *
	 * @return string|null
	 */
	public function created_via() {
		$created_via = $this->get_metadata( $this->get_ticket_id(), 'created_via' );

		return isset( $created_via[0] ) ? $created_via[0] : null;
	}

	/**
	 * Get belongs to id
	 *
	 * @return int
	 */
	public function belongs_to_id() {
		$belongs_to_id = ( new SupportTicket() )->get_metadata( $this->get_ticket_id(), 'belongs_to_id' );

		return isset( $belongs_to_id[0] ) ? intval( $belongs_to_id[0] ) : 0;
	}

	/**
	 * Called to customer
	 *
	 * @return string
	 */
	public function called_to_customer() {
		$called = ( new static() )->get_metadata( $this->get_ticket_id(), '_called_to_customer' );

		return isset( $called[0] ) && ( 'yes' == $called[0] ) ? 'yes' : 'no';
	}

	/**
	 * Get ticket threads
	 *
	 * @return TicketThread[]
	 */
	public function get_ticket_threads() {
		if ( empty( $this->ticket_threads ) ) {
			$this->ticket_threads = ( new TicketThread() )->find_by_ticket_id( $this->get_ticket_id() );
		}

		return $this->ticket_threads;
	}

	/**
	 * Get all metadata
	 *
	 * @return array
	 */
	public function get_all_metadata(): array {
		global $wpdb;
		$table = $wpdb->prefix . $this->meta_table;

		$ticket_meta = [];
		$sql         = "SELECT meta_key,meta_value FROM {$table} WHERE ticket_id= %d";
		$results     = $wpdb->get_results( $wpdb->prepare( $sql, $this->get_id() ), ARRAY_A );
		if ( $results ) {
			foreach ( $results as $result ) {
				$ticket_meta[ $result['meta_key'] ] = stripslashes( $result['meta_value'] );
			}
		}

		return $ticket_meta;
	}

	/**
	 * Get ticket notes
	 *
	 * @return array
	 */
	public function get_ticket_notes() {
		$_threads = $this->get_ticket_threads();
		$threads  = [];
		foreach ( $_threads as $thread ) {
			if ( 'note' == $thread->get_thread_type() ) {
				$threads[] = $thread;
			}
		}

		return $threads;
	}

	/**
	 * Get last note
	 *
	 * @return array|TicketThread
	 */
	public function get_last_ticket_note() {
		$notes = $this->get_ticket_notes();

		return isset( $notes[0] ) && ( $notes[0] instanceof TicketThread ) ? $notes[0] : [];
	}

	/**
	 * @throws Exception
	 */
	public function get_last_note_diff() {
		$note = $this->get_last_ticket_note();
		if ( $note instanceof TicketThread ) {
			$dateCreated = new DateTime( $note->get_created_at() );
			$now         = new DateTime( 'now' );
			$diff        = $now->diff( $dateCreated );

			return $diff->m + ( $diff->h * 60 ) + ( $diff->days * 24 * 60 );
		}

		return 0;
	}

	/**
	 */
	public function update_at() {
		$date_updated = $this->get( 'date_updated' );
		try {
			$dateTime = new DateTime( $date_updated );

			return $dateTime->format( 'Y-m-d\TH:i:s' );
		} catch ( Exception $e ) {
		}

		return $date_updated;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function updated_human_time() {
		$date_updated = $this->get( 'date_updated' );
		$dateTime     = new DateTime( $date_updated );

		return human_time_diff( $dateTime->getTimestamp(), current_time( 'timestamp' ) ) . ' ago';
	}

	/**
	 * Get ticket raised user id
	 *
	 * @return int
	 */
	public function get_created_by() {
		return (int) $this->get( $this->created_by );
	}

	/**
	 * @return string
	 */
	public function get_agent_created() {
		$agent_created = $this->get( 'agent_created' );

		if ( is_numeric( $agent_created ) ) {
			$user = get_user_by( 'id', $agent_created );
			if ( $user instanceof WP_User ) {
				return $user->display_name;
			}
		}

		return 'None';
	}

	/**
	 * Get ticket status
	 *
	 * @return array|ArrayObject
	 */
	public function get_ticket_status() {
		$ticket_status = $this->get( 'ticket_status' );
		$terms         = get_term_by( 'id', $ticket_status, 'ticket_status' );

		if ( $terms instanceof WP_Term ) {
			return static::format_term_for_response( $terms );
		}

		return new ArrayObject();
	}

	/**
	 * Get ticket category
	 *
	 * @return array|ArrayObject
	 */
	public function get_ticket_category() {
		$ticket_status = $this->get( 'ticket_category' );
		$terms         = get_term_by( 'id', $ticket_status, 'ticket_category' );
		if ( $terms instanceof WP_Term ) {
			return static::format_term_for_response( $terms );
		}

		return new ArrayObject();
	}

	/**
	 * Get ticket priority
	 *
	 * @return array|ArrayObject
	 */
	public function get_ticket_priority() {
		$ticket_status = $this->get( 'ticket_priority' );
		$terms         = get_term_by( 'id', $ticket_status, 'ticket_priority' );

		if ( $terms instanceof WP_Term ) {
			return static::format_term_for_response( $terms );
		}

		return new ArrayObject();
	}

	/**
	 * Get customer avatar url
	 *
	 * @return string
	 */
	public function get_avatar_url() {
		if ( empty( $this->avatar_url ) ) {
			$created_by       = (int) $this->get( 'agent_created' );
			$id_or_email      = $created_by ? $created_by : $this->get( 'customer_email' );
			$this->avatar_url = Utils::get_avatar_url( $id_or_email );
		}

		return $this->avatar_url;
	}

	/**
	 * @param  WP_Term  $term
	 *
	 * @return array
	 */
	public static function format_term_for_response( WP_Term $term ) {
		$color = get_term_meta( $term->term_id, '_color', true );

		return [
			'id'    => $term->term_id,
			'name'  => $term->name,
			'slug'  => $term->slug,
			'color' => ! empty( $color ) ? $color : '',
		];
	}

	/**
	 * Get assigned agents
	 *
	 * @return array
	 */
	public function get_assigned_agents() {
		$ids = $this->get_assigned_agents_ids();
		if ( ! count( $ids ) ) {
			return [];
		}

		if ( empty( $this->assigned_agents ) ) {
			foreach ( $ids as $id ) {
				$user = get_user_by( 'id', $id );
				if ( ! $user instanceof WP_User ) {
					continue;
				}

				$this->assigned_agents[] = [
					'id'           => $user->ID,
					'email'        => $user->user_email,
					'display_name' => $user->display_name,
					'avatar_url'   => get_avatar_url( $user->user_email ),
				];
			}
		}

		return $this->assigned_agents;
	}

	/**
	 * Get assigned agents ids
	 *
	 * @return array
	 */
	public function get_assigned_agents_ids() {
		if ( ! $this->assigned_agent_read ) {
			$ticket_id = $this->get( 'id' );
			$terms_ids = $this->get_metadata( $ticket_id, 'assigned_agent' );
			if ( empty( $terms_ids ) ) {
				return [];
			}

			foreach ( $terms_ids as $terms_id ) {
				$user_id                     = get_term_meta( $terms_id, 'user_id', true );
				$this->assigned_agents_ids[] = is_numeric( $user_id ) ? intval( $user_id ) : 0;
			}

			$this->assigned_agent_read = true;
		}

		return $this->assigned_agents_ids;
	}

	/**
	 * Create support ticket
	 *
	 * @param  array  $data
	 * @param  string  $content
	 * @param  string  $thread_type
	 *
	 * @return int
	 * @throws Exception
	 */
	public function create_support_ticket( array $data, $content = '', $thread_type = 'report' ) {
		$data = wp_parse_args(
			$data,
			[
				'ticket_subject'   => '',
				'customer_name'    => '',
				'customer_email'   => '',
				'user_type'        => get_current_user_id() ? 'user' : 'guest',
				'ticket_status'    => get_option( 'support_ticket_default_status' ),
				'ticket_category'  => get_option( 'support_ticket_default_category' ),
				'ticket_priority'  => get_option( 'support_ticket_default_priority' ),
				'ip_address'       => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
				'agent_created'    => 0,
				'ticket_auth_code' => bin2hex( random_bytes( 5 ) ),
				'active'           => 1,
			]
		);

		$ticket_id = $this->create( $data );

		$this->update_metadata( $ticket_id, 'assigned_agent', '0' );

		self::add_thread(
			$ticket_id,
			[
				'thread_type'    => $thread_type,
				'customer_name'  => $data['customer_name'],
				'customer_email' => $data['customer_email'],
				'post_content'   => $content,
				'agent_created'  => $data['agent_created'],
			]
		);

		return $ticket_id;
	}

	/**
	 * @param  int  $ticket_id
	 * @param  array  $data
	 * @param  array  $attachments
	 *
	 * @return int
	 */
	public static function add_thread( int $ticket_id, array $data, $attachments = [] ) {
		$data = wp_parse_args(
			$data,
			[
				'thread_type'    => 'report',
				'customer_name'  => '',
				'customer_email' => '',
				'post_content'   => '',
				'agent_created'  => 0,
				'user_type'      => 'user',
			]
		);

		$_data = [
			'ticket_id'      => $ticket_id,
			'thread_type'    => $data['thread_type'],
			'thread_content' => wp_filter_post_kses( nl2br( $data['post_content'], false ) ),
			'user_type'      => $data['user_type'],
			'user_name'      => $data['customer_name'],
			'user_email'     => $data['customer_email'],
			'created_by'     => $data['agent_created'],
			'attachments'    => $attachments,
		];

		$thread_id = TicketThread::create( $_data );

		if ( is_wp_error( $thread_id ) ) {
			return 0;
		}

		( new static() )->update(
			[
				'id'           => $ticket_id,
				'date_updated' => current_time( 'mysql' ),
			]
		);

		return $thread_id;
	}

	/**
	 * Add support ticket note
	 *
	 * @param  int  $ticket_id
	 * @param  string  $note
	 * @param  string  $type
	 */
	public function add_note( $ticket_id, $note, $type = 'note' ) {
		$user = wp_get_current_user();
		self::add_thread(
			$ticket_id,
			[
				'thread_type'    => $type,
				'customer_name'  => $user->display_name,
				'customer_email' => $user->user_email,
				'agent_created'  => $user->ID,
				'post_content'   => $note,
			]
		);
	}

	/**
	 * Get ticket meta
	 *
	 * @param  int  $ticket_id
	 * @param  string  $meta_key
	 *
	 * @return array
	 */
	public function get_metadata( $ticket_id, $meta_key ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->meta_table;

		$ticket_meta = array();
		$sql         = $wpdb->prepare(
			"SELECT meta_value FROM {$table} WHERE ticket_id= %d AND meta_key = %s",
			$ticket_id,
			$meta_key
		);
		$results     = $wpdb->get_results( $sql );
		if ( $results ) {
			foreach ( $results as $result ) {
				$ticket_meta[] = stripslashes( $result->meta_value );
			}
		}

		return $ticket_meta;
	}

	/**
	 * Update ticket metadata
	 *
	 * @param  int  $ticket_id
	 * @param  string  $meta_key
	 * @param  mixed  $meta_value
	 * @param  int  $meta_id
	 *
	 * @return int
	 */
	public function update_metadata( $ticket_id, $meta_key, $meta_value, $meta_id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->meta_table;
		$data  = [
			'ticket_id'  => $ticket_id,
			'meta_key'   => $meta_key,
			'meta_value' => $meta_value,
		];
		if ( $meta_id ) {
			$data['id'] = $meta_id;
		}
		$wpdb->insert( $table, $data );

		return $meta_id ? $meta_id : $wpdb->insert_id;
	}

	/**
	 * Get current user agent id
	 *
	 * @param  int  $user_id
	 *
	 * @return int|mixed
	 */
	public static function get_current_user_agent_id( $user_id = 0 ) {
		$current_user_agent_id = 0;
		$agents                = SupportAgent::get_all();
		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		foreach ( $agents as $agent ) {
			if ( $agent->get_user()->ID == $user_id ) {
				$current_user_agent_id = $agent->get( 'term_id' );
			}
		}

		return $current_user_agent_id;
	}

	/**
	 * Find multiple records from database
	 *
	 * @param  array  $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$per_page     = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : $this->perPage;
		$paged        = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
		$current_page = $paged < 1 ? 1 : $paged;
		$offset       = ( $current_page - 1 ) * $per_page;
		$orderby      = $this->primary_key;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order         = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';
		$ticket_status = $args['ticket_status'] ?? 'all';
		$agent         = isset( $args['agent'] ) && is_numeric( $args['agent'] ) ? intval( $args['agent'] ) : 0;
		$active        = isset( $args['active'] ) && is_bool( $args['active'] ) && $args['active'];

		global $wpdb;
		$table      = $wpdb->prefix . $this->table;
		$meta_table = $wpdb->prefix . $this->meta_table;

		$query = "SELECT * FROM {$table}";

		if ( ! current_user_can( 'read_others_tickets' ) || $agent > 0 ) {
			$query .= " LEFT JOIN {$meta_table} as mt ON {$table}.id = mt.ticket_id";
		}

		$query .= ' WHERE 1=1';

		if ( ! current_user_can( 'read_others_tickets' ) || $agent > 0 ) {
			if ( $agent > 0 ) {
				$user_id = $agent;
			} else {
				$user_id = get_current_user_id();
			}
			$agent_id = self::get_current_user_agent_id( $user_id );
			$query    .= ' AND(';
			if ( ! empty( $agent_id ) ) {
				$query .= $wpdb->prepare(
					' (mt.meta_key = %s AND mt.meta_value = %d) OR',
					'assigned_agent',
					$agent_id
				);
			}
			$query .= $wpdb->prepare( ' agent_created = %d', $user_id );
			$query .= ' )';
		}

		if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
			$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
		}

		if ( is_numeric( $ticket_status ) ) {
			$query .= $wpdb->prepare( ' AND ticket_status = %d', intval( $ticket_status ) );
		}

		if ( isset( $args['ticket_category'] ) && is_numeric( $args['ticket_category'] ) ) {
			$query .= $wpdb->prepare( ' AND ticket_category = %d', intval( $args['ticket_category'] ) );
		}

		if ( isset( $args['ticket_priority'] ) && is_numeric( $args['ticket_priority'] ) ) {
			$query .= $wpdb->prepare( ' AND ticket_priority = %d', intval( $args['ticket_priority'] ) );
		}

		if ( isset( $args['city'] ) && ! empty( $args['city'] ) && $args['city'] !== 'all' ) {
			$query .= $wpdb->prepare( ' AND city = %s', $args['city'] );
		}

		if ( ! $active ) {
			$query .= ' AND active = 0';
		} else {
			$query .= ' AND active = 1';
		}

		$query   .= " GROUP BY {$table}.{$this->primary_key}";
		$query   .= " ORDER BY {$table}.{$orderby} {$order}";
		$query   .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );

		$data = [];
		foreach ( $results as $result ) {
			$data[] = new self( $result );
		}

		return $data;
	}

	public static function _find_for_user_sql( array $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'search'          => '',
				'ticket_status'   => 0,
				'ticket_category' => 0,
				'ticket_priority' => 0,
				'agent_created'   => 0,
			]
		);

		$self = new static();
		global $wpdb;
		$table = $self->get_table_name();

		$query = "SELECT * FROM {$table} WHERE 1=1";
		$query .= $wpdb->prepare( ' AND `agent_created` = %d', intval( $args['agent_created'] ) );

		foreach ( [ 'ticket_category', 'ticket_priority', 'ticket_status' ] as $columnName ) {
			if ( $args[ $columnName ] ) {
				$query .= $wpdb->prepare( " AND `{$columnName}` = %d", intval( $args[ $columnName ] ) );
			}
		}

		if ( ! empty( $args['search'] ) ) {
			$query .= $wpdb->prepare( ' AND `ticket_subject` LIKE %s', '%' . $args['search'] . '%' );
		}

		return $query;
	}

	public static function find_for_user( array $args = [] ) {
		$self = ( new static() )->get_data_store();
		global $wpdb;
		$cache_key = $self->get_cache_key_for_collection( $args );
		$items     = $self->get_cache( $cache_key );
		if ( false === $items ) {
			list( $per_page, $offset ) = $self->get_pagination_and_order_data( $args );
			$order_by = $self->get_order_by( $args );

			$query = static::_find_for_user_sql( $args );
			if ( $order_by ) {
				$query .= " ORDER BY {$order_by}";
			}

			if ( $per_page > 0 ) {
				$query .= $wpdb->prepare( ' LIMIT %d', $per_page );
			}
			if ( $per_page > 0 && $offset >= 0 ) {
				$query .= $wpdb->prepare( ' OFFSET %d', $offset );
			}
			$items = $wpdb->get_results( $query, ARRAY_A );

			// Set cache for one day
			$self->set_cache( $cache_key, $items, DAY_IN_SECONDS );
		}

		$data = [];
		foreach ( $items as $result ) {
			$data[] = new self( $result );
		}

		return $data;
	}

	public static function count_for_user( array $args = [] ) {
		global $wpdb;
		$query = static::_find_for_user_sql( $args );
		$query = str_replace( 'SELECT *', 'SELECT COUNT( * ) AS num_entries', $query );

		$count = $wpdb->get_row( $query, ARRAY_A );

		return isset( $count['num_entries'] ) ? intval( $count['num_entries'] ) : 0;
	}

	/**
	 * Metadata for user
	 *
	 * @param  array  $args
	 *
	 * @return array
	 */
	public static function metadata_for_user( array $args ) {
		$self  = new static();
		$table = $self->get_table_name();
		global $wpdb;
		$columns = [ 'ticket_category', 'ticket_priority', 'ticket_status' ];
		$queries = [];

		foreach ( $columns as $column ) {
			$queries[] = $wpdb->prepare(
				"(SELECT `{$column}` AS term_id FROM {$table} WHERE `agent_created` = %d GROUP BY `{$column}`)",
				intval( $args['agent_created'] )
			);
		}

		$sql      = implode( ' UNION ', $queries );
		$results  = $wpdb->get_results( $sql, ARRAY_A );
		$term_ids = array_unique( array_map( 'intval', wp_list_pluck( $results, 'term_id' ) ) );

		$data               = [];
		$data['categories'] = TicketCategory::get_all( [ 'include' => $term_ids ] );
		$data['priorities'] = TicketPriority::get_all( [ 'include' => $term_ids ] );
		$data['statuses']   = TicketStatus::get_all( [ 'include' => $term_ids ] );

		return $data;
	}

	/**
	 * Search phone
	 *
	 * @param  array  $args
	 * @param  array  $fields
	 *
	 * @return array
	 */
	public function search( $args, $fields = [] ) {
		global $wpdb;
		$table           = $wpdb->prefix . $this->table;
		$meta_table      = $wpdb->prefix . $this->meta_table;
		$string          = isset( $args['search'] ) ? esc_sql( $args['search'] ) : '';
		$fields          = empty( $fields ) ? array_keys( $this->default_data ) : $fields;
		$ticket_status   = ! empty( $args['ticket_status'] ) ? $args['ticket_status'] : 'all';
		$ticket_category = ! empty( $args['ticket_category'] ) ? $args['ticket_category'] : 'all';
		$orderby         = $this->primary_key;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';

		$cache_key = sprintf( 'support_ticket_search_%s', md5( json_encode( $args ) ) );
		$tickets   = wp_cache_get( $cache_key, $this->cache_group );
		if ( false === $tickets ) {
			$tickets = [];

			$terms_ids = $this->search_terms( $string );

			$query = "SELECT * FROM {$table}";

			if ( ! current_user_can( 'read_others_tickets' ) ) {
				$query .= " LEFT JOIN {$meta_table} as mt ON {$table}.id = mt.ticket_id";
			}

			$query .= ' WHERE 1=1';

			if ( ! current_user_can( 'read_others_tickets' ) ) {
				$user_id  = get_current_user_id();
				$agent_id = self::get_current_user_agent_id( $user_id );
				$query    .= ' AND(';
				if ( ! empty( $agent_id ) ) {
					$query .= $wpdb->prepare(
						' (mt.meta_key = %s AND mt.meta_value = %d) OR',
						'assigned_agent',
						$agent_id
					);
				}
				$query .= $wpdb->prepare( ' agent_created = %d', $user_id );
				$query .= ' )';
			}

			if ( isset( $args[ $this->created_by ] ) && is_numeric( $args[ $this->created_by ] ) ) {
				$query .= $wpdb->prepare( " AND {$this->created_by} = %d", intval( $args[ $this->created_by ] ) );
			}

			if ( 'trash' == $ticket_status ) {
				$query .= ' AND active = 0';
			} else {
				$query .= ' AND active = 1';
			}

			$total_fields = count( $fields );
			foreach ( $fields as $index => $field ) {
				if ( 0 === $index ) {
					$query .= " AND ({$field} LIKE '%{$string}%'";
				} elseif ( ( $total_fields - 1 ) === $index ) {
					$query .= " OR {$field} LIKE '%{$string}%')";
				} else {
					$query .= " OR {$field} LIKE '%{$string}%'";
				}
			}

			if ( is_numeric( $ticket_category ) && $ticket_category > 1 ) {
				$query .= $wpdb->prepare( ' AND ticket_category = %d', intval( $ticket_category ) );
			} else {
				if ( count( $terms_ids ) ) {
					$terms_fields = [ 'ticket_status', 'ticket_category', 'ticket_priority' ];

					foreach ( $terms_fields as $index => $field ) {
						foreach ( $terms_ids as $term_id ) {
							$query .= $wpdb->prepare( " OR {$field} = %d", $term_id );
						}
					}
				}
			}

			$query .= " ORDER BY {$table}.{$orderby} {$order}";

			$items = $wpdb->get_results( $query, ARRAY_A );
			if ( $items ) {
				foreach ( $items as $item ) {
					$tickets[] = new self( $item );
				}
			}
			wp_cache_add( $cache_key, $tickets, $this->cache_group );
		}

		return $tickets;
	}

	/**
	 * @param  string  $query
	 * @param  bool  $object
	 *
	 * @return array|WP_Term[]
	 */
	public function search_terms( $query = '', $object = false ) {
		if ( empty( $query ) ) {
			return [];
		}
		// WP_Term_Query arguments
		$args = array(
			'taxonomy'   => array( 'ticket_category', 'ticket_priority', 'ticket_status' ),
			'hide_empty' => false,
			'name__like' => $query,
		);

		// The Term Query
		$term_query = new WP_Term_Query( $args );

		$terms = $term_query->get_terms();

		if ( count( $terms ) && ! $object ) {
			return wp_list_pluck( $terms, 'term_id' );
		}

		return $terms;
	}

	/**
	 * Get all unique cities from support ticket
	 *
	 * @return array
	 */
	public function find_all_cities() {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT DISTINCT city FROM {$table};";

		$results = $wpdb->get_results( $query, ARRAY_A );

		$cities = [];
		if ( is_array( $results ) && count( $results ) ) {
			$_cities = wp_list_pluck( $results, 'city' );
			foreach ( $_cities as $city ) {
				if ( is_numeric( $city ) || empty( $city ) || preg_match( '/[0-9]/', $city ) ) {
					continue;
				}

				$cities[] = $city;
			}
		}

		return $cities;
	}

	/**
	 * Find record by id
	 *
	 * @param  int  $id
	 *
	 * @return false|self
	 */
	public function find_by_id( $id ) {
		$item = parent::find_by_id( $id );

		if ( $item ) {
			return new self( $item );
		}

		return false;
	}

	/**
	 * Get previous and next item
	 *
	 * @param  int  $id
	 *
	 * @return array
	 */
	public function find_pre_and_next( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$id    = intval( $id );
		$items = [
			'pre'  => null,
			'next' => null,
		];

		$sql = "select * from {$table} where ( id = IFNULL((select min(id) from {$table} where id > {$id}),0)
			or  id = IFNULL((select max(id) from {$table} where id < {$id}),0) )";

		$results = $wpdb->get_results( $sql, ARRAY_A );
		if ( $results ) {
			foreach ( $results as $result ) {
				if ( $result[ $this->primary_key ] > $id ) {
					$items['next'] = new self( $result );
				}
				if ( $result[ $this->primary_key ] < $id ) {
					$items['pre'] = new self( $result );
				}
			}
		}

		return $items;
	}

	/**
	 * Update support ticket agents
	 *
	 * @param  array  $agents_ids  List of WP_User[] ID
	 */
	public function update_agent( array $agents_ids ) {
		global $wpdb;
		$table     = $wpdb->prefix . $this->meta_table;
		$meta_key  = 'assigned_agent';
		$ticket_id = $this->get( 'id' );

		$sql     = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE ticket_id= %d AND meta_key = %s",
			$ticket_id,
			$meta_key
		);
		$results = $wpdb->get_results( $sql );
		if ( count( $results ) ) {
			foreach ( $results as $result ) {
				$wpdb->delete( $table, [ 'id' => $result->id ], '%d' );
			}
		}

		$_agents = SupportAgent::get_all();
		$agents  = [];
		foreach ( $_agents as $agent ) {
			foreach ( $agents_ids as $agents_id ) {
				if ( $agent->get_user()->ID == $agents_id ) {
					$agents[] = $agent;
					$this->update_metadata( $ticket_id, $meta_key, $agent->get( 'term_id' ) );
				}
			}
		}

		/**
		 * @param  SupportTicket  $this
		 * @param  SupportAgent[]  $agents
		 */
		do_action( 'save_support_ticket_agent', $this, $agents );
	}

	/**
	 * Delete data
	 *
	 * @param  int  $thread_id
	 *
	 * @return bool
	 */
	public function delete_thread( $thread_id = 0 ) {
		return wp_delete_post( $thread_id ) instanceof WP_Post;
	}

	/**
	 * Send an item to trash
	 *
	 * @param  int  $id
	 *
	 * @return bool
	 */
	public function trash( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$query = $wpdb->update( $table, [ 'active' => 0 ], [ $this->primary_key => $id ] );

		return ( false !== $query );
	}

	/**
	 * Restore an item from trash
	 *
	 * @param  int  $id
	 *
	 * @return bool
	 */
	public function restore( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$query = $wpdb->update( $table, [ 'active' => 1 ], [ $this->primary_key => $id ] );

		return ( false !== $query );
	}

	/**
	 * Delete data
	 *
	 * @param  int  $id
	 *
	 * @return bool
	 */
	public function delete( $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		// Delete all threads first
		$threads = $item->get_ticket_threads();
		foreach ( $threads as $thread ) {
			wp_delete_post( $thread->get_id(), true );
		}

		return ( false !== $wpdb->delete( $table, [ $this->primary_key => $id ], $this->primary_key_type ) );
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_ticket_statuses_terms() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'ticket_status',
				'hide_empty' => false,
			)
		);

		return $terms;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_categories_terms() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'ticket_category',
				'hide_empty' => false,
			)
		);

		return $terms;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_priorities_terms() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'ticket_priority',
				'hide_empty' => false,
			)
		);

		return $terms;
	}

	/**
	 * Get ticket statuses term
	 *
	 * @return WP_Term[]
	 */
	public function get_agents_terms() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'support_agent',
				'hide_empty' => false,
			)
		);

		return $terms;
	}

	/**
	 * Cont trash records
	 *
	 * @return int
	 */
	public function count_inactive_records() {
		global $wpdb;
		$table   = $wpdb->prefix . $this->table;
		$query   = "SELECT COUNT( * ) AS num_entries FROM {$table} WHERE active = 0";
		$results = $wpdb->get_row( $query, ARRAY_A );

		return intval( $results['num_entries'] );
	}

	/**
	 * Cont trash records
	 *
	 * @return int
	 */
	public function count_active_records() {
		global $wpdb;
		$table   = $wpdb->prefix . $this->table;
		$query   = "SELECT COUNT( * ) AS num_entries FROM {$table} WHERE active = 1";
		$results = $wpdb->get_row( $query, ARRAY_A );

		return intval( $results['num_entries'] );
	}

	/**
	 * Count number of tickets by status
	 *
	 * @param  WP_Term[]  $terms
	 * @param  string  $column_name
	 *
	 * @return array
	 */
	public static function tickets_count_by_terms( $terms, $column_name ) {
		$counts  = array_fill_keys( wp_list_pluck( $terms, 'term_id' ), 0 );
		$results = static::count_column_unique_values( $column_name );
		foreach ( $results as $row ) {
			$counts[ $row['_key'] ] = intval( $row['_value'] );
		}
		$counts['all'] = array_sum( $counts );

		return $counts;
	}

	/**
	 * @param  string  $column_name  table column name
	 *
	 * @return array
	 */
	public static function count_column_unique_values( $column_name ) {
		global $wpdb;
		$self = ( new static() );

		$table      = $wpdb->prefix . $self->table;
		$meta_table = $wpdb->prefix . $self->meta_table;
		$counts     = wp_cache_get( $column_name . '_counts', $self->cache_group );
		if ( false === $counts ) {
			$query = "SELECT {$column_name} as _key, COUNT( * ) AS _value FROM {$table}";

			if ( ! current_user_can( 'read_others_tickets' ) ) {
				$query .= " LEFT JOIN {$meta_table} as mt ON {$table}.id = mt.ticket_id";
			}

			$query .= ' WHERE active = 1';

			if ( ! current_user_can( 'read_others_tickets' ) ) {
				$user_id  = get_current_user_id();
				$agent_id = self::get_current_user_agent_id( $user_id );
				$query    .= ' AND(';
				if ( ! empty( $agent_id ) ) {
					$query .= $wpdb->prepare(
						' (mt.meta_key = %s AND mt.meta_value = %d) OR',
						'assigned_agent',
						$agent_id
					);
				}
				$query .= $wpdb->prepare( ' agent_created = %d', $user_id );
				$query .= ' )';
			}

			$query  .= " GROUP BY {$column_name}";
			$counts = $wpdb->get_results( $query, ARRAY_A );

			wp_cache_set( $column_name . '_counts', $counts, $self->cache_group );
		}

		return $counts;
	}

	/**
	 * Get tickets count by agents
	 *
	 * @return array
	 */
	public static function count_tickets_by_agents() {
		global $wpdb;
		$self       = ( new static() );
		$table      = $wpdb->prefix . $self->table;
		$meta_table = $wpdb->prefix . $self->meta_table;

		$query   = "SELECT mt.meta_value as _key, COUNT(mt.ticket_id ) AS _value FROM {$table}";
		$query   .= " RIGHT JOIN {$meta_table} as mt ON {$table}.id = mt.ticket_id";
		$query   .= " WHERE {$table}.active = 1 AND mt.meta_key = 'assigned_agent'";
		$query   .= ' GROUP BY meta_value';
		$results = $wpdb->get_results( $query, ARRAY_A );

		$counts = [];
		foreach ( $results as $row ) {
			$counts[ $row['_key'] ] = intval( $row['_value'] );
		}

		return $counts;
	}

	/**
	 * Map meta capability for support ticket
	 *
	 * @param  array  $caps
	 * @param  string  $cap
	 * @param  int  $user_id
	 * @param  array  $args
	 *
	 * @return array
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		/* If editing, deleting, or reading a movie, get the post and post type object. */
		if ( 'edit_ticket' == $cap || 'delete_ticket' == $cap || 'read_ticket' == $cap ) {

			$object_id = $args[0] ?? 0;
			$ticket    = ( new static() )->find_by_id( $object_id );
			if ( ! $ticket instanceof static ) {
				return $caps;
			}

			$is_same_user = false;
			if ( $user_id == $ticket->get( 'agent_created' ) || in_array(
					$user_id,
					$ticket->get_assigned_agents_ids()
				) ) {
				$is_same_user = true;
			}

			$caps = [];

			if ( 'edit_ticket' == $cap ) {
				$caps[] = $is_same_user ? 'edit_tickets' : 'edit_others_tickets';
			}

			if ( 'delete_ticket' == $cap ) {
				$caps[] = $is_same_user ? 'delete_tickets' : 'delete_others_tickets';
			}

			if ( 'read_ticket' == $cap ) {
				$caps[] = $is_same_user ? 'read_tickets' : 'read_others_tickets';
			}
		}

		return $caps;
	}
}
