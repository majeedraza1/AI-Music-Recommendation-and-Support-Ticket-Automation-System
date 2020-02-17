<?php

namespace StackonetSupportTicket\Sync;

use StackonetSupportTicket\Upgrade\UpgradeCategories;
use StackonetSupportTicket\Upgrade\UpgradePriorities;
use StackonetSupportTicket\Upgrade\UpgradeStatus;
use StackonetSupportTicket\Upgrade\UpgradeThreads;
use WP_Post;
use wpdb;

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
	 * Get new ticket id from old ticket
	 *
	 * @param int $new_ticket_id
	 *
	 * @return int
	 */
	public static function get_old_ticket_id( $new_ticket_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$meta_table = $wpdb->prefix . static::$ticket_meta_table['old'];
		$sql        = $wpdb->prepare( "SELECT meta_value FROM {$meta_table} WHERE meta_key = %s and meta_value = %s",
			'_new_ticket_id', $new_ticket_id );
		$row        = $wpdb->get_row( $sql, ARRAY_A );

		return isset( $row['meta_value'] ) ? intval( $row['meta_value'] ) : 0;
	}

	/**
	 * Clone ticket
	 *
	 * @param int $new_ticket_id
	 */
	public function ticket_created( $new_ticket_id ) {
		$new_data = static::get_ticket_data( $new_ticket_id, 'new' );

		$ticket                    = $new_data['ticket'];
		$ticket['ticket_category'] = UpgradeCategories::get_old_term_id( $ticket['ticket_category'] );
		$ticket['ticket_priority'] = UpgradePriorities::get_old_term_id( $ticket['ticket_priority'] );
		$ticket['ticket_status']   = UpgradeStatus::get_old_term_id( $ticket['ticket_status'] );

		$ticket_metadata = $new_data['ticket_metadata'];

		/** @var WP_Post[] $threads */
		$threads = $new_data['threads'];

		/** @var wpdb $wpdb */
		global $wpdb;
		$table      = $wpdb->prefix . static::$ticket_table['old'];
		$meta_table = $wpdb->prefix . static::$ticket_meta_table['old'];
		$post_type  = static::$post_type['old'];

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

			static::record_ticket_id_on_both_table( $new_ticket_id, $id );

			foreach ( $threads as $new_thread ) {
				$old_thread_id = UpgradeThreads::clone_thread( $new_thread, $post_type );
				if ( $old_thread_id ) {
					update_post_meta( $new_thread->ID, '_old_thread_id', $old_thread_id );
					update_post_meta( $old_thread_id, '_new_thread_id', $new_thread->ID );
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
