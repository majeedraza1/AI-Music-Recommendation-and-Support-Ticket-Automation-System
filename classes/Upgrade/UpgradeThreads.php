<?php

namespace StackonetSupportTicket\Upgrade;

use Stackonet\WP\Framework\Abstracts\BackgroundProcess;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class UpgradeThreads extends BackgroundProcess {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'clone_thread_background_process';

	/**
	 * @var string
	 */
	protected static $old_post_type_name = 'wpsc_ticket_thread';

	/**
	 * @var string
	 */
	protected static $new_post_type_name = 'ticket_thread';

	/**
	 * @var string
	 */
	protected static $status_option_name = 'clone_thread_background_process_status';

	/**
	 * @param int $old_post_id
	 * @param int $new_post_id
	 */
	public static function clone_metadata( $old_post_id, $new_post_id ) {
		$post_meta_data = get_post_meta( $old_post_id );
		// Loop over returned metadata, and re-assign them to the new post_type
		if ( $post_meta_data ) {
			foreach ( $post_meta_data as $meta_key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $meta_value => $meta_text ) {
						if ( is_serialized( $meta_text ) ) {
							update_post_meta( $new_post_id, $meta_key, unserialize( $meta_text ) );
						} else {
							update_post_meta( $new_post_id, $meta_key, $meta_text );
						}
					}
				} else {
					update_post_meta( $new_post_id, $meta_key, $value );
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function task( $item ) {
		$items_to_clone = count( $item );

		$threads = get_posts( [
			'post_type'      => static::$old_post_type_name,
			'include'        => $item,
			'posts_per_page' => $items_to_clone,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		] );

		foreach ( $threads as $thread ) {
			static::clone_thread( $thread );
		}

		$status                   = static::get_status();
		$status['items_complete'] = $status['items_complete'] + $items_to_clone;

		if ( $status['items_complete'] < $status['total_items'] ) {
			update_option( static::$status_option_name, $status );
		} else {
			delete_option( static::$status_option_name );
		}

		return false;
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	public function complete() {
		parent::complete();

		// Upgrade ticket Categories
		UpgradeCategories::clone_categories();

		// Upgrade ticket Priorities
		UpgradePriorities::clone_priorities();

		// Upgrade ticket Statuses
		UpgradeStatus::clone_statuses();
	}

	/**
	 * Clone ticket
	 *
	 * @param WP_Post $post
	 * @param string|null $new_post_type
	 * @param int $new_ticket_id
	 *
	 * @return int|\WP_Error
	 */
	public static function clone_thread( WP_Post $post, $new_post_type = null, $new_ticket_id = 0 ) {
		if ( empty( $new_post_type ) ) {
			$new_post_type = static::$new_post_type_name;
		}
		// Create post object
		$my_post = array(
			'post_title'   => $post->post_type,
			'post_content' => $post->post_content,
			'post_status'  => $post->post_status,
			'post_author'  => $post->post_author,
			'post_type'    => $new_post_type,
		);

		// Insert the post into the database
		$new_post_id = wp_insert_post( $my_post );
		if ( is_wp_error( $new_post_id ) ) {
			return 0;
		}

		self::clone_metadata( $post->ID, $new_post_id );

		if ( $new_ticket_id ) {
			update_post_meta( $new_post_id, 'ticket_id', $new_ticket_id );
		}

		return $new_post_id;
	}

	/**
	 * Clone Threads
	 */
	public static function clone_threads() {
		global $wpdb;

		// Get all threads ids
		$ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID DESC",
				static::$old_post_type_name
			),
			ARRAY_A
		);
		$ids = count( $ids ) ? wp_list_pluck( $ids, 'ID' ) : [];
		$ids = count( $ids ) ? array_map( 'intval', $ids ) : [];

		$chunks = array_chunk( $ids, 30 );

		$background_process = stackonet_support_ticket()->clone_thread_background_process();
		foreach ( $chunks as $chunk_ids ) {
			$background_process->push_to_queue( $chunk_ids );
		}

		add_action( 'shutdown', function () use ( $background_process ) {
			$background_process->save()->dispatch();
		}, 100 );

		update_option( static::$status_option_name, [ 'total_items' => count( $ids ), 'items_complete' => 0 ] );
	}

	/**
	 * Get status data
	 *
	 * @return array
	 */
	public static function get_status() {
		$default = [ 'total_items' => 0, 'items_complete' => 0, ];
		$status  = get_option( static::$status_option_name, $default );
		$status  = is_array( $status ) ? $status : $default;

		return $status;
	}

	/**
	 * @return string
	 */
	public static function get_admin_notice_text() {
		$status = static::get_status();

		if ( $status['items_complete'] < $status['total_items'] ) {
			return "Stackonet Support Ticket: " . $status['items_complete'] . " items complete out of " . $status['total_items'] . " items";
		}

		return '';
	}
}