<?php

namespace StackonetSupportTicket\Sync;

class SyncTicket {

	/**
	 * @var array
	 */
	protected static $map_old_new = [
		'table'      => [
			'wpsc_ticket'     => 'support_ticket',
			'wpsc_ticketmeta' => 'support_ticketmeta'
		],
		'post_type'  => [
			'wpsc_ticket_thread' => 'ticket_thread',
		],
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
			'wpsc_default_ticket_status'              => 'support_ticket_default_status',
			'wpsc_default_ticket_category'            => 'support_ticket_default_category',
			'wpsc_default_ticket_priority'            => 'support_ticket_default_priority',
			'wpsc_ticket_status_after_customer_reply' => 'support_ticket_status_after_customer_reply',
			'wpsc_ticket_status_after_agent_reply'    => 'support_ticket_status_after_agent_reply',
			'wpsc_close_ticket_status'                => 'support_ticket_close_ticket_status',
			'wpsc_allow_customer_close_ticket'        => 'support_ticket_allow_customer_close_ticket',
		],
	];
}