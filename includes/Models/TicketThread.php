<?php

namespace StackonetSupportTicket\Models;

use Stackonet\WP\Framework\Abstracts\DatabaseModel;
use StackonetSupportTicket\Supports\Utils;

defined( 'ABSPATH' ) or exit;

class TicketThread extends DatabaseModel {

	/**
	 * @inheridoc
	 */
	protected $table = 'support_ticket_thread';

	protected $meta_table = 'support_ticket_threadmeta';

	/**
	 * Available thread types
	 *
	 * @var array
	 */
	protected static $valid_thread_types = [ 'report', 'log', 'reply', 'note', 'sms', 'email' ];

	/**
	 * Thread attachments
	 *
	 * @var array
	 */
	private $attachments = [];

	/**
	 * @var bool
	 */
	protected $attachments_read = false;

	/**
	 * Avatar URL
	 *
	 * @var string
	 */
	protected $avatar_url = '';

	/**
	 * @return array
	 */
	public static function get_thread_types(): array {
		return apply_filters( 'ticket_thread_types', self::$valid_thread_types );
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array(): array {
		$human_time = human_time_diff( strtotime( $this->get_created_at() ), current_time( 'timestamp' ) );

		return [
			'thread_id'           => $this->get_id(),
			'thread_content'      => $this->get_thread_content(),
			'thread_date'         => $this->get_created_at(),
			'human_time'          => $human_time,
			'thread_type'         => $this->get_thread_type(),
			'customer_name'       => $this->get_prop( 'user_name' ),
			'customer_email'      => $this->get_prop( 'user_email' ),
			'user_type'           => $this->get_user_type(),
			'customer_avatar_url' => $this->get_avatar_url(),
			'attachments'         => $this->get_attachments(),
		];
	}

	/**
	 * thread id
	 *
	 * @return int
	 */
	public function get_id(): int {
		return intval( $this->get_prop( 'id' ) );
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function get_thread_content(): string {
		return $this->get_prop( 'thread_content' );
	}

	/**
	 * Get created by id
	 *
	 * @return int
	 */
	public function get_created_by(): int {
		return (int) $this->get_prop( 'created_by' );
	}

	/**
	 * Get customer avatar url
	 *
	 * @return string
	 */
	public function get_avatar_url(): string {
		if ( empty( $this->avatar_url ) ) {
			$id_or_email      = $this->get_created_by() ? $this->get_created_by() : $this->get_prop( 'customer_email' );
			$this->avatar_url = Utils::get_avatar_url( $id_or_email );
		}

		return $this->avatar_url;
	}

	/**
	 * Get thread type
	 *
	 * @return string
	 */
	public function get_thread_type(): string {
		return $this->get_prop( 'thread_type' );
	}

	/**
	 * Get thread type
	 *
	 * @return string
	 */
	public function get_user_type(): string {
		return $this->get_prop( 'user_type', 'user' );
	}

	/**
	 * Get created at
	 *
	 * @return string
	 */
	public function get_created_at(): string {
		return $this->get_prop( 'created_at' );
	}

	/**
	 * Get attachments
	 *
	 * @return array
	 */
	public function get_attachments_ids(): array {
		return $this->get_prop( 'attachments', [] );
	}

	/**
	 * Get attachment data
	 */
	public function get_attachments(): array {
		if ( $this->attachments_read ) {
			return $this->attachments;
		}

		foreach ( $this->get_attachments_ids() as $id ) {
			$this->attachments[] = [
				'title'        => get_the_title( $id ),
				'download_url' => wp_get_attachment_url( $id ),
			];
		}
		$this->attachments_read = true;

		return $this->attachments;
	}

	/**
	 * Get all threads by ticket id
	 *
	 * @param  int  $ticket_id
	 *
	 * @return array
	 */
	public function find_by_ticket_id( int $ticket_id ): array {
		global $wpdb;
		$table = $this->get_table_name();

		$sql      = $wpdb->prepare( "SELECT * FROM {$table} WHERE ticket_id = %d", $ticket_id );
		$sql      .= ' ORDER BY id DESC';
		$_threads = $wpdb->get_results( $sql, ARRAY_A );

		$threads = [];
		foreach ( $_threads as $thread ) {
			$threads[] = new static( $thread );
		}

		return $threads;
	}
}
