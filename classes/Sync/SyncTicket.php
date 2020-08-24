<?php

namespace StackonetSupportTicket\Sync;

defined( 'ABSPATH' ) || exit;

class SyncTicket {

	/**
	 * @var array
	 */
	protected static $ticket_table = [
		'old' => 'wpsc_ticket',
		'new' => 'support_ticket',
	];

	/**
	 * @var array
	 */
	protected static $ticket_meta_table = [
		'old' => 'wpsc_ticketmeta',
		'new' => 'support_ticketmeta',
	];

	/**
	 * @var array
	 */
	protected static $post_type = [
		'old' => 'wpsc_ticket_thread',
		'new' => 'ticket_thread',
	];

	/**
	 * @var array
	 */
	protected static $map_old_new = [
		'terms'      => [
			'wpsc_categories' => 'ticket_category',
			'wpsc_priorities' => 'ticket_priority',
			'wpsc_statuses'   => 'ticket_status',
			'wpsc_agents'     => 'support_agent',
		],
		'terms_meta' => [
			'wpsc_category_load_order' => 'support_ticket_category_menu_order',
			'wpsc_priority_load_order' => 'support_ticket_priority_menu_order',
			'wpsc_status_load_order'   => 'support_ticket_status_menu_order',
		],
		'options'    => [
			'wpsc_default_ticket_status'                        => 'support_ticket_default_status',
			'wpsc_default_ticket_category'                      => 'support_ticket_default_category',
			'wpsc_default_ticket_priority'                      => 'support_ticket_default_priority',
			'wpsc_ticket_status_after_customer_reply'           => 'support_ticket_status_after_customer_reply',
			'wpsc_ticket_status_after_agent_reply'              => 'support_ticket_status_after_agent_reply',
			'wpsc_close_ticket_status'                          => 'support_ticket_close_ticket_status',
			'wpsc_allow_customer_close_ticket'                  => 'support_ticket_allow_customer_close_ticket',
			// Custom
			'wpsc_default_order_ticket_category'                => 'support_ticket_default_order_ticket_category',
			'wpsc_default_spot_appointment_category'            => 'support_ticket_default_spot_appointment_category',
			'carrier_store_default_category'                    => 'support_ticket_default_carrier_store_category',
			'support_ticket_default_checkout_analysis_category' => 'support_ticket_default_checkout_analysis_category',
			'support_ticket_default_map_category'               => 'support_ticket_default_map_category',
			'wpsc_default_contact_form_ticket_category'         => 'support_ticket_default_contact_form_category',
			'stackonet_ticket_search_categories'                => 'support_ticket_search_categories',
		],
	];

	/**
	 * Record ticket id on both table
	 * On v3, v1 ticket id should be recorded
	 * On v1, v3 ticket id should be recorded
	 *
	 * @param int $new_ticket_id
	 * @param int $old_ticket_id
	 */
	public static function record_ticket_id_on_both_table( $new_ticket_id, $old_ticket_id ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$data_1 = [ 'ticket_id' => $new_ticket_id, 'meta_key' => '_old_ticket_id', 'meta_value' => $old_ticket_id ];

		$new_meta_table = $wpdb->prefix . static::$ticket_meta_table['new'];
		$wpdb->insert( $new_meta_table, $data_1 );

		$data_2 = [ 'ticket_id' => $old_ticket_id, 'meta_key' => '_new_ticket_id', 'meta_value' => $new_ticket_id ];

		$old_meta_table = $wpdb->prefix . static::$ticket_meta_table['old'];
		$wpdb->insert( $old_meta_table, $data_2 );
	}

	/**
	 * @param int $ticket_id
	 * @param string $mode
	 *
	 * @return array
	 */
	protected static function get_ticket_data( $ticket_id, $mode = 'old' ) {
		global $wpdb;
		$table      = $wpdb->prefix . static::$ticket_table[ $mode ];
		$meta_table = $wpdb->prefix . static::$ticket_meta_table[ $mode ];
		$post_type  = static::$post_type[ $mode ];

		$sql    = $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $ticket_id );
		$ticket = $wpdb->get_row( $sql, ARRAY_A );

		$sql             = $wpdb->prepare( "SELECT * FROM {$meta_table} WHERE ticket_id = %d", $ticket_id );
		$ticket_metadata = $wpdb->get_results( $sql, ARRAY_A );

		$args    = [
			'post_type'      => $post_type,
			'posts_per_page' => - 1,
			'meta_key'       => 'ticket_id',
			'meta_value'     => $ticket_id,
		];
		$threads = get_posts( $args );

		return [
			'ticket'          => $ticket,
			'ticket_metadata' => $ticket_metadata,
			'threads'         => $threads
		];
	}
}