<?php

namespace StackonetSupportTicket\Upgrade;

use StackonetSupportTicket\Abstracts\BackgroundProcess;
use StackonetSupportTicket\Utilities\Logger;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class CloneThreadBackgroundTask extends BackgroundProcess {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'clone_thread_background_process';

	/**
	 * @inheritDoc
	 */
	protected function task( $item ) {
		Logger::log( $item );
		$threads = get_posts( [
			'post_type'      => 'wpsc_ticket_thread',
			'include'        => $item,
			'posts_per_page' => count( $item ),
			'orderby'        => 'ID',
			'order'          => 'ASC',
		] );

		foreach ( $threads as $thread ) {
			static::clone_thread( $thread );
		}

		return false;
	}

	/**
	 * Clone ticket
	 *
	 * @param WP_Post $post
	 */
	public static function clone_thread( WP_Post $post ) {
		// Create post object
		$my_post = array(
			'post_title'   => $post->post_type,
			'post_content' => $post->post_content,
			'post_type'    => 'ticket_thread',
			'post_status'  => $post->post_status,
			'post_author'  => $post->post_author,
		);

		// Insert the post into the database
		$new_post = wp_insert_post( $my_post );

		update_post_meta( $new_post, '_ticket_thread_clone_from', $post->ID );

		$post_meta_data = get_post_meta( $post->ID );
		// Loop over returned metadata, and re-assign them to the new post_type
		if ( $post_meta_data ) {
			foreach ( $post_meta_data as $meta_key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $meta_value => $meta_text ) {
						/*
						*	- Check for serialized data in some meta field
						*	This is really In place for EDD imports
						*	The varialble pricing field is a serialized array
						*/
						if ( is_serialized( $meta_text ) ) {
							update_post_meta( $new_post, $meta_key, unserialize( $meta_text ) );
						} else {
							update_post_meta( $new_post, $meta_key, $meta_text );
						}
					}
				} else {
					update_post_meta( $new_post, $meta_key, $value );
				}
			}
		}
	}
}