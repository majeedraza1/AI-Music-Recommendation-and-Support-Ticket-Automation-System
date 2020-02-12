<?php

namespace StackonetSupportTicket\Sync;

use StackonetSupportTicket\Upgrade\UpgradeCategories;
use StackonetSupportTicket\Upgrade\UpgradePriorities;
use StackonetSupportTicket\Upgrade\UpgradeStatus;
use StackonetSupportTicket\Upgrade\UpgradeThreads;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class ToNewTicket extends SyncTicket {

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
			add_action( 'stackonet_support_ticket/v1/ticket_created', [ self::$instance, 'ticket_created' ], 10, 1 );
			add_action( 'stackonet_support_ticket/v1/ticket_updated', [ self::$instance, 'ticket_updated' ], 10, 2 );
			add_action( 'stackonet_support_ticket/v1/ticket_deleted', [ self::$instance, 'ticket_deleted' ], 10, 2 );

			// Thread
			add_action( 'stackonet_support_ticket/v1/thread_created', [ self::$instance, 'thread_created' ], 10, 2 );
			add_action( 'stackonet_support_ticket/v1/thread_updated', [ self::$instance, 'thread_updated' ], 10, 3 );
			add_action( 'stackonet_support_ticket/v1/delete_thread', [ self::$instance, 'delete_thread' ], 10, 2 );

			// Ticket agents
			add_action( 'stackonet_support_ticket/v1/update_ticket_agent', [ self::$instance, 'update_agent' ], 10, 2 );
		}

		return self::$instance;
	}

	/**
	 * Clone ticket
	 *
	 * @param int $ticket_id
	 */
	public function ticket_created( $ticket_id ) {
		$old_data = static::get_ticket_data( $ticket_id, 'old' );

		$ticket                    = $old_data['ticket'];
		$ticket['ticket_category'] = UpgradeCategories::get_new_term_id( $ticket['ticket_category'] );
		$ticket['ticket_priority'] = UpgradePriorities::get_new_term_id( $ticket['ticket_priority'] );
		$ticket['ticket_status']   = UpgradeStatus::get_new_term_id( $ticket['ticket_status'] );

		$ticket_metadata = $old_data['ticket_metadata'];

		/** @var WP_Post[] $threads */
		$threads = $old_data['threads'];

		/** @var \wpdb $wpdb */
		global $wpdb;
		$table      = $wpdb->prefix . static::$ticket_table['new'];
		$meta_table = $wpdb->prefix . static::$ticket_meta_table['new'];
		$post_type  = static::$post_type['new'];

		// Add ticket
		$wpdb->insert( $table, $ticket );
		$id = $wpdb->insert_id;

		if ( $id ) {
			foreach ( $ticket_metadata as $metadata ) {
				$wpdb->insert( $meta_table, [
					'ticket_id'  => $id,
					'meta_key'   => $metadata['meta_key'],
					'meta_value' => $metadata['meta_value']
				] );
			}

			$wpdb->insert( $meta_table, [
				'ticket_id'  => $id,
				'meta_key'   => '_old_thicket_id',
				'meta_value' => $ticket_id
			] );

			foreach ( $threads as $thread ) {
				$new_thread_id = UpgradeThreads::clone_thread( $thread, $post_type );
				if ( $new_thread_id ) {
					update_post_meta( $new_thread_id, '_old_thread_id', $thread->ID );
					update_post_meta( $thread->ID, '_new_thread_id', $new_thread_id );
				}
			}
		}
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