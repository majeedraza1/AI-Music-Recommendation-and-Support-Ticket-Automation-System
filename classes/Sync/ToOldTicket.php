<?php

namespace StackonetSupportTicket\Sync;

defined( 'ABSPATH' ) || exit;

class ToOldTicket extends SyncTicket {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			// Ticket
			add_action( 'stackonet_support_ticket/v3/ticket_created', [ self::$instance, 'ticket_created' ], 10, 1 );
			add_action( 'stackonet_support_ticket/v3/ticket_updated', [ self::$instance, 'ticket_updated' ], 10, 2 );
			add_action( 'stackonet_support_ticket/v3/ticket_deleted', [ self::$instance, 'ticket_deleted' ], 10, 2 );

			// Thread
			add_action( 'stackonet_support_ticket/v3/thread_created', [ self::$instance, 'thread_created' ], 10, 2 );
			add_action( 'stackonet_support_ticket/v3/thread_updated', [ self::$instance, 'thread_updated' ], 10, 3 );
			add_action( 'stackonet_support_ticket/v3/delete_thread', [ self::$instance, 'delete_thread' ], 10, 2 );

			// Ticket agents
			add_action( 'stackonet_support_ticket/v3/update_ticket_agent', [ self::$instance, 'update_agent' ], 10, 2 );
		}

		return self::$instance;
	}

	/**
	 * Clone ticket
	 *
	 * @param int $ticket_id
	 */
	public function ticket_created( $ticket_id ) {

	}

	public function ticket_updated( $ticket_id, $data ) {

	}

	public function ticket_deleted( $ticket_id, $action ) {

	}

	public function thread_created( $ticket_id, $thread_id ) {

	}

	public function thread_updated( $ticket_id, $thread_id, $new_content ) {

	}

	public function delete_thread( $ticket_id, $thread_id ) {

	}

	public function update_agent( $ticket_id, $agents_ids ) {

	}
}
