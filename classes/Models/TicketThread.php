<?php

namespace StackonetSupportTicket\Models;

use StackonetSupportTicket\Abstracts\PostTypeModel;
use WP_Post;

defined( 'ABSPATH' ) or exit;

class TicketThread extends PostTypeModel {

	/**
	 * Post type name
	 *
	 * @var string
	 */
	protected $post_type = 'wpsc_ticket_thread';

	/**
	 * Available thread types
	 *
	 * @var array
	 */
	protected $valid_thread_types = [ 'report', 'log', 'reply', 'note', 'sms', 'email' ];

	/**
	 * WP_Post class
	 *
	 * @var WP_Post|null
	 */
	private $post;

	/**
	 * Thread attachments
	 *
	 * @var array
	 */
	private $attachments = [];

	/**
	 * Avatar URL
	 *
	 * @var string
	 */
	protected $avatar_url = '';

	/**
	 * Class constructor.
	 *
	 * @param null|WP_Post $thread
	 */
	public function __construct( $thread = null ) {
		if ( $thread instanceof WP_Post ) {
			$this->post = $thread;

			$this->data = [
				'id'      => $thread->ID,
				'content' => $thread->post_content,
				'created' => $thread->post_date_gmt,
				'updated' => $thread->post_modified_gmt,
			];

			$this->read_metadata();
			$this->read_attachments_data();
		}
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$human_time = human_time_diff( strtotime( $this->get( 'created' ) ) );

		return [
			'thread_id'           => $this->get( 'id' ),
			'thread_content'      => $this->get( 'content' ),
			'thread_date'         => $this->get( 'created' ),
			'human_time'          => $human_time,
			'thread_type'         => $this->get_type(),
			'customer_name'       => $this->get( 'customer_name' ),
			'customer_email'      => $this->get( 'customer_email' ),
			'customer_avatar_url' => $this->get_avatar_url(),
			'attachments'         => $this->attachments,
		];
	}

	/**
	 * Get customer avatar url
	 *
	 * @return string
	 */
	public function get_avatar_url() {
		if ( empty( $this->avatar_url ) ) {
			$this->avatar_url = get_avatar_url( $this->get( 'customer_email' ) );
		}

		return $this->avatar_url;
	}

	/**
	 * Get thread type
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->get( 'type' );
	}

	/**
	 * Get created at
	 *
	 * @return string
	 */
	public function get_created() {
		return $this->get( 'created' );
	}

	/**
	 * Get all threads by ticket id
	 *
	 * @param int $ticket_id
	 *
	 * @return array
	 */
	public function find_by_ticket_id( $ticket_id ) {
		$args = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => - 1,
			'meta_query'     => [
				'relation' => 'AND',
				[ 'key' => 'ticket_id', 'value' => $ticket_id, 'compare' => '=' ],
			]
		);

		$_threads = get_posts( $args );

		$threads = [];
		foreach ( $_threads as $thread ) {
			$threads[] = new self( $thread );
		}

		return $threads;
	}

	/**
	 * Method to create a new record
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function create( array $data ) {
		$data['post_type']      = $this->post_type;
		$data['post_status']    = 'publish';
		$data['comment_status'] = 'closed';
		$data['ping_status']    = 'closed';

		$post_id = wp_insert_post( $data );

		if ( $post_id ) {
			$this->write_metadata( $post_id, $data );
		}

		return $post_id;
	}

	/**
	 * Write metadata
	 *
	 * @param int $post_id
	 * @param array $data
	 */
	private function write_metadata( $post_id, array $data ) {
		$ticket_id      = isset( $data['ticket_id'] ) ? $data['ticket_id'] : 0;
		$customer_name  = isset( $data['customer_name'] ) ? $data['customer_name'] : '';
		$customer_email = isset( $data['customer_email'] ) ? $data['customer_email'] : '';
		$thread_type    = isset( $data['thread_type'] ) ? $data['thread_type'] : '';
		$thread_type    = in_array( $thread_type, $this->valid_thread_types ) ? $thread_type : '';
		$attachments    = isset( $data['attachments'] ) && is_array( $data['attachments'] ) ? $data['attachments'] : [];

		update_post_meta( $post_id, 'ticket_id', $ticket_id );
		update_post_meta( $post_id, 'thread_type', $thread_type );
		update_post_meta( $post_id, 'customer_name', $customer_name );
		update_post_meta( $post_id, 'customer_email', $customer_email );
		update_post_meta( $post_id, 'attachments', $attachments );
	}

	/**
	 * Read metadata
	 */
	private function read_metadata() {
		$thread_id                    = $this->get( 'id' );
		$this->data['ticket_id']      = (int) get_post_meta( $thread_id, 'ticket_id', true );
		$this->data['type']           = get_post_meta( $thread_id, 'thread_type', true );
		$this->data['customer_name']  = get_post_meta( $thread_id, 'customer_name', true );
		$this->data['customer_email'] = get_post_meta( $thread_id, 'customer_email', true );
	}

	/**
	 * Read attachment data
	 */
	private function read_attachments_data() {
		$ticket_id    = $this->get( 'id' );
		$_attachments = get_post_meta( $ticket_id, 'attachments', true );

		$attachments = [];
		if ( is_array( $_attachments ) && count( $_attachments ) ) {
			foreach ( $_attachments as $attachment_id ) {
				$attachments[] = [
					'filename'       => get_the_title( $attachment_id ),
					'active'         => true,
					'is_image'       => true,
					'save_file_name' => get_the_title( $attachment_id ),
					'time_uploaded'  => get_term_meta( $attachment_id, 'time_uploaded', true ),
					'download_url'   => wp_get_attachment_url( $attachment_id ),
				];
			}
		}

		$this->attachments = $attachments;
	}

	/**
	 * Read attachment data
	 */
	private function _read_attachments_data() {
		$ticket_id    = $this->get( 'id' );
		$_attachments = get_post_meta( $ticket_id, 'attachments', true );

		$attachments = [];
		if ( is_array( $_attachments ) && count( $_attachments ) ) {
			foreach ( $_attachments as $attachment_id ) {

				$save_file_name = get_term_meta( $attachment_id, 'save_file_name', true );
				$is_image       = (bool) get_term_meta( $attachment_id, 'is_image', true );

				$upload_dir   = wp_upload_dir();
				$file_url     = $upload_dir['baseurl'] . '/wpsc/' . $save_file_name;
				$download_url = $is_image ? $file_url : site_url( '/' ) . '?wpsc_attachment=' . $attachment_id . '&tid=' . $ticket_id . '&tac=' . 0;

				$attachments[] = [
					'filename'       => get_term_meta( $attachment_id, 'filename', true ),
					'active'         => (bool) get_term_meta( $attachment_id, 'active', true ),
					'is_image'       => $is_image,
					'save_file_name' => $save_file_name,
					'time_uploaded'  => get_term_meta( $attachment_id, 'time_uploaded', true ),
					'download_url'   => $download_url,
				];
			}
		}

		$this->attachments = $attachments;
	}
}
