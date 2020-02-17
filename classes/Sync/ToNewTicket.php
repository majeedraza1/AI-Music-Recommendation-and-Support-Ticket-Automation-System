<?php

namespace StackonetSupportTicket\Sync;

use StackonetSupportTicket\Models\SupportTicket;
use StackonetSupportTicket\Upgrade\UpgradeCategories;
use StackonetSupportTicket\Upgrade\UpgradePriorities;
use StackonetSupportTicket\Upgrade\UpgradeStatus;
use StackonetSupportTicket\Upgrade\UpgradeThreads;
use WP_Post;
use wpdb;

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
	 * Get new ticket id from old ticket
	 *
	 * @param int $old_ticket_id
	 *
	 * @return int
	 */
	public static function get_new_ticket_id( $old_ticket_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$meta_table = $wpdb->prefix . static::$ticket_meta_table['new'];
		$sql        = $wpdb->prepare( "SELECT meta_value FROM {$meta_table} WHERE meta_key = %s and meta_value = %s",
			'_old_ticket_id', $old_ticket_id );
		$row        = $wpdb->get_row( $sql, ARRAY_A );

		return isset( $row['meta_value'] ) ? intval( $row['meta_value'] ) : 0;
	}

	/**
	 * Clone ticket
	 *
	 * @param int $old_ticket_id
	 */
	public function ticket_created( $old_ticket_id ) {
		$old_data = static::get_ticket_data( $old_ticket_id, 'old' );

		$ticket                    = $old_data['ticket'];
		$ticket['ticket_category'] = UpgradeCategories::get_new_term_id( $ticket['ticket_category'] );
		$ticket['ticket_priority'] = UpgradePriorities::get_new_term_id( $ticket['ticket_priority'] );
		$ticket['ticket_status']   = UpgradeStatus::get_new_term_id( $ticket['ticket_status'] );

		$ticket_metadata = $old_data['ticket_metadata'];

		/** @var WP_Post[] $threads */
		$threads = $old_data['threads'];

		/** @var wpdb $wpdb */
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

			static::record_ticket_id_on_both_table( $id, $old_ticket_id );

			foreach ( $threads as $thread ) {
				$new_thread_id = UpgradeThreads::clone_thread( $thread, $post_type );
				if ( $new_thread_id ) {
					update_post_meta( $new_thread_id, '_old_thread_id', $thread->ID );
					update_post_meta( $thread->ID, '_new_thread_id', $new_thread_id );
				}
			}
		}
	}

	/**
	 * Update ticket
	 *
	 * @param int $old_ticket_id
	 * @param array $data
	 */
	public function ticket_updated( $old_ticket_id, $data ) {
		$new_ticket_id = static::get_new_ticket_id( $old_ticket_id );

		$ticket = ( new SupportTicket() )->find_by_id( $new_ticket_id );

		if ( $ticket instanceof SupportTicket ) {
			$ticket->update( $data );
		}
	}

	/**
	 * Delete ticket
	 *
	 * @param $old_ticket_id
	 * @param string $action
	 */
	public function ticket_deleted( $old_ticket_id, $action ) {
		$new_ticket_id = static::get_new_ticket_id( $old_ticket_id );

		$ticket = ( new SupportTicket() )->find_by_id( $new_ticket_id );

		if ( $ticket instanceof SupportTicket ) {
			if ( 'trash' == $action ) {
				$ticket->trash( $new_ticket_id );
			}
			if ( 'restore' == $action ) {
				$ticket->restore( $new_ticket_id );
			}
			if ( 'delete' == $action ) {
				$ticket->delete( $new_ticket_id );
			}
		}
	}

	/**
	 * Clone thread
	 *
	 * @param int $ticket_id
	 * @param int $thread_id
	 */
	public function thread_created( $ticket_id, $thread_id ) {
		$post_type     = static::$post_type['new'];
		$new_ticket_id = static::get_new_ticket_id( $ticket_id );
		$thread        = get_post( $thread_id );
		$new_thread_id = UpgradeThreads::clone_thread( $thread, $post_type, $new_ticket_id );
		if ( $new_thread_id ) {
			update_post_meta( $new_thread_id, '_old_thread_id', $thread->ID );
			update_post_meta( $thread->ID, '_new_thread_id', $new_thread_id );
		}
	}

	/**
	 * Update a thread
	 *
	 * @param int $old_ticket_id
	 * @param int $old_thread_id
	 * @param string $content
	 */
	public function thread_updated( $old_ticket_id, $old_thread_id, $content ) {
		$new_ticket_id = static::get_new_ticket_id( $old_ticket_id );
		$new_thread_id = (int) get_post_meta( $old_thread_id, '_new_thread_id', true );
		if ( $new_ticket_id && $new_thread_id ) {
			$my_post = array( 'ID' => $new_thread_id, 'post_content' => $content );
			wp_update_post( $my_post );
		}
	}

	/**
	 * Delete thread
	 *
	 * @param int $old_ticket_id
	 * @param int $old_thread_id
	 */
	public function delete_thread( $old_ticket_id, $old_thread_id ) {
		$new_ticket_id = static::get_new_ticket_id( $old_ticket_id );
		if ( $new_ticket_id && $old_thread_id ) {
			$new_thread_id = (int) get_post_meta( $old_thread_id, '_new_thread_id', true );

			wp_delete_post( $new_thread_id );
		}
	}

	/**
	 * Update support agents
	 *
	 * @param int $old_ticket_id
	 * @param array $agents_ids Array of WordPress user ids
	 */
	public function update_agent( $old_ticket_id, $agents_ids ) {
		$new_ticket_id = static::get_new_ticket_id( $old_ticket_id );
		$ticket        = ( new SupportTicket() )->find_by_id( $new_ticket_id );
		if ( $ticket instanceof SupportTicket ) {
			$ticket->update_agent( $agents_ids );
		}
	}
}