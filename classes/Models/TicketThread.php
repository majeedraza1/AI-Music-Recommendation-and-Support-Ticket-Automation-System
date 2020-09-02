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
	public static function get_thread_types() {
		return apply_filters( 'ticket_thread_types', self::$valid_thread_types );
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		$human_time = human_time_diff( strtotime( $this->get_created_at() ), current_time( 'timestamp' ) );

		return [
			'thread_id'           => $this->get_id(),
			'thread_content'      => $this->get_thread_content(),
			'thread_date'         => $this->get_created_at(),
			'human_time'          => $human_time,
			'thread_type'         => $this->get_thread_type(),
			'customer_name'       => $this->get( 'user_name' ),
			'customer_email'      => $this->get( 'user_email' ),
			'customer_avatar_url' => $this->get_avatar_url(),
			'attachments'         => $this->get_attachments(),
		];
	}

	/**
	 * thread id
	 *
	 * @return int
	 */
	public function get_id() {
		return intval( $this->get( 'id' ) );
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function get_thread_content() {
		return $this->get( 'thread_content', '' );
	}

	/**
	 * Get created by id
	 *
	 * @return int
	 */
	public function get_created_by() {
		return (int) $this->get( 'created_by' );
	}

	/**
	 * Get customer avatar url
	 *
	 * @return string
	 */
	public function get_avatar_url() {
		if ( empty( $this->avatar_url ) ) {
			$id_or_email      = $this->get_created_by() ? $this->get_created_by() : $this->get( 'customer_email' );
			$this->avatar_url = Utils::get_avatar_url( $id_or_email );
		}

		return $this->avatar_url;
	}

	/**
	 * Get thread type
	 *
	 * @return string
	 */
	public function get_thread_type() {
		return $this->get( 'thread_type' );
	}

	/**
	 * Get thread type
	 *
	 * @return string
	 */
	public function get_user_type() {
		return $this->get( 'user_type', 'user' );
	}

	/**
	 * Get created at
	 *
	 * @return string
	 */
	public function get_created_at() {
		return $this->get( 'created_at' );
	}

	/**
	 * Get attachments
	 *
	 * @return array
	 */
	public function get_attachments_ids() {
		return $this->get( 'attachments', [] );
	}

	/**
	 * Get attachment data
	 */
	public function get_attachments() {
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
	 * @param int $ticket_id
	 *
	 * @return array
	 */
	public function find_by_ticket_id( int $ticket_id ) {
		global $wpdb;
		$table = $this->get_table_name();

		$sql      = $wpdb->prepare( "SELECT * FROM {$table} WHERE ticket_id = %d", $ticket_id );
		$sql      .= " ORDER BY id DESC";
		$_threads = $wpdb->get_results( $sql, ARRAY_A );

		$threads = [];
		foreach ( $_threads as $thread ) {
			$threads[] = new static( $thread );
		}

		return $threads;
	}

	/**
	 * Create table
	 */
	public static function create_table() {
		global $wpdb;
		$table      = $wpdb->prefix . 'support_ticket_thread';
		$meta_table = $wpdb->prefix . 'support_ticket_threadmeta';
		$fk_table   = $wpdb->prefix . 'support_ticket';
		$collate    = $wpdb->get_charset_collate();

		$tables = "CREATE TABLE IF NOT EXISTS {$table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ticket_id BIGINT(20) UNSIGNED NOT NULL,
			thread_type VARCHAR(30) NULL DEFAULT NULL,
			thread_content LONGTEXT NULL DEFAULT NULL,
			attachments TEXT NULL DEFAULT NULL,
			user_type VARCHAR(30) NULL DEFAULT NULL COMMENT 'agent or user',
			user_name VARCHAR(100) NULL DEFAULT NULL,
			user_email VARCHAR(100) NULL DEFAULT NULL,
			user_phone VARCHAR(20) NULL DEFAULT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NULL DEFAULT NULL,
			updated_at DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (id)
		) $collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $tables );

		$meta_table_schema = "CREATE TABLE IF NOT EXISTS {$meta_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			thread_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key varchar(255) NULL DEFAULT NULL,
			meta_value LONGTEXT NULL DEFAULT NULL,
			PRIMARY KEY  (id)
		) $collate;";
		dbDelta( $meta_table_schema );

		$version = get_option( $table . '-version' );
		if ( false === $version ) {
			$sql = "ALTER TABLE `{$table}` ADD CONSTRAINT `fk_{$fk_table}_{$table}` FOREIGN KEY (`ticket_id`)";
			$sql .= " REFERENCES `{$fk_table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
			$wpdb->query( $sql );

			$sql = "ALTER TABLE `{$meta_table}` ADD CONSTRAINT `fk_{$table}_{$meta_table}` FOREIGN KEY (`thread_id`)";
			$sql .= " REFERENCES `{$table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
			$wpdb->query( $sql );

			update_option( $table . '-version', '1.0.0', false );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function count_records() {
		return [];
	}
}
