<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Models\AgentRole;
use StackonetSupportTicket\Models\SupportAgent;
use StackonetSupportTicket\Models\TicketCategory;
use StackonetSupportTicket\Models\TicketPriority;
use StackonetSupportTicket\Models\TicketStatus;
use WP_User;

defined( 'ABSPATH' ) || exit;

class Install {

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

			RoleAndCapability::activation();

			self::create_support_table();
			self::create_thread_table();

			self::add_default_roles();
			self::add_support_ticket_agents();
			self::add_default_data();
			self::add_default_options();
		}

		return self::$instance;
	}

	/**
	 * Get foreign key constant name
	 *
	 * @param  string  $table1
	 * @param  string  $table2
	 *
	 * @return string
	 */
	public static function get_foreign_key_constant_name( string $table1, string $table2 ): string {
		global $wpdb;
		$tables = [
			str_replace( $wpdb->prefix, '', $table1 ),
			str_replace( $wpdb->prefix, '', $table2 ),
		];
		asort( $tables );

		return substr( sprintf( 'fk_%s__%s', $tables[0], $tables[1] ), 0, 64 );
	}

	/**
	 * Add default roles
	 */
	public static function add_default_roles() {
		$valid_caps = AgentRole::valid_capabilities();
		AgentRole::add_role(
			'administrator',
			__( 'Support Admin', 'stackonet-support-ticket' ),
			array_fill_keys( array_keys( $valid_caps ), true )
		);
		AgentRole::add_role(
			'agent',
			__( 'Support Agent', 'stackonet-support-ticket' ),
			[
				'view_unassigned'                      => true,
				'view_assigned_me'                     => true,
				'assign_unassigned'                    => true,
				'assign_assigned_me'                   => true,
				'change_ticket_status_assigned_me'     => true,
				'change_ticket_field_assigned_me'      => true,
				'change_ticket_agent_only_assigned_me' => true,
				'reply_assigned_me'                    => true,
			]
		);
	}

	/**
	 * Add default support ticket agents
	 */
	public static function add_support_ticket_agents() {
		/** @var WP_User[] $admins */
		$admins = get_users( array( 'role' => 'administrator' ) );
		foreach ( $admins as $admin ) {
			SupportAgent::create( $admin->ID, 'administrator' );
		}
	}

	/**
	 * Create meta table
	 */
	private static function create_support_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'support_ticket';
		$meta_table_name = $wpdb->prefix . 'support_ticketmeta';
		$collate         = $wpdb->get_charset_collate();

		$tables = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			customer_name varchar(100) NULL DEFAULT NULL,
			customer_email varchar(100) NULL DEFAULT NULL,
			customer_phone varchar(20) NULL DEFAULT NULL,
			ticket_subject TEXT NULL DEFAULT NULL,
			city varchar(100) NULL DEFAULT NULL,
			user_type varchar(30) NULL DEFAULT NULL,
			ticket_category BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ticket_priority BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ticket_status BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			active tinyint(1) NOT NULL DEFAULT 1,
			ip_address VARCHAR(30) NULL DEFAULT NULL,
			ticket_auth_code varchar(255) NULL DEFAULT NULL,
			agent_created BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			admin_unread_threads_count tinyint UNSIGNED NOT NULL DEFAULT 0,
			user_unread_threads_count tinyint UNSIGNED NOT NULL DEFAULT 0,
			date_created datetime NULL DEFAULT NULL,
			date_updated datetime NULL DEFAULT NULL,
			PRIMARY KEY (id),
    		INDEX `ticket_category` (`ticket_category`),
    		INDEX `ticket_priority` (`ticket_priority`),
    		INDEX `ticket_status` (`ticket_status`),
    		INDEX `agent_created` (`agent_created`)
		) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $tables );

		$version = get_option( $table_name . '-version' );


		$tables = "CREATE TABLE IF NOT EXISTS {$meta_table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ticket_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key varchar(255) NULL DEFAULT NULL,
			meta_value LONGTEXT NULL DEFAULT NULL,
			PRIMARY KEY  (id)
		) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $tables );

		$version = get_option( $meta_table_name . '-version' );
		if ( false === $version ) {
			$constant_name = self::get_foreign_key_constant_name( $meta_table_name, $table_name );
			$sql           = "ALTER TABLE `{$meta_table_name}` ADD CONSTRAINT $constant_name FOREIGN KEY (`ticket_id`)";
			$sql           .= " REFERENCES `{$table_name}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
			$wpdb->query( $sql );
			update_option( $meta_table_name . '-version', '1.0.0', false );
		}
	}


	/**
	 * Create table
	 */
	public static function create_thread_table() {
		global $wpdb;
		$self       = new self();
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

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
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
			$constant_name = $self->get_foreign_key_constant_name( $fk_table, $table );
			$sql           = "ALTER TABLE `{$table}` ADD CONSTRAINT $constant_name FOREIGN KEY (`ticket_id`)";
			$sql           .= " REFERENCES `{$fk_table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
			$wpdb->query( $sql );

			$constant_name2 = $self->get_foreign_key_constant_name( $meta_table, $table );
			$sql            = "ALTER TABLE `{$meta_table}` ADD CONSTRAINT $constant_name2 FOREIGN KEY (`thread_id`)";
			$sql            .= " REFERENCES `{$table}`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
			$wpdb->query( $sql );

			update_option( $table . '-version', '1.0.0', false );
		}
	}

	/**
	 * Add plugin default data
	 */
	private static function add_default_data() {
		// Category Items
		$term_id = TicketCategory::create( __( 'General', 'stackonet-support-ticket' ), [ 'slug' => 'general' ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_default_category', $term_id );
		}

		// Status Items
		$term_id = TicketStatus::create( __( 'Open', 'stackonet-support-ticket' ), [ 'color' => '#d9534f' ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_default_status', $term_id );
		}
		$term_id = TicketStatus::create(
			__( 'Awaiting customer reply', 'stackonet-support-ticket' ),
			[ 'color' => '#000000' ]
		);
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_status_after_agent_reply', $term_id );
		}
		$term_id = TicketStatus::create(
			__( 'Awaiting agent reply', 'stackonet-support-ticket' ),
			[ 'color' => '#f0ad4e' ]
		);
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_status_after_customer_reply', $term_id );
		}
		$term_id = TicketStatus::create( __( 'Closed', 'stackonet-support-ticket' ), [ 'color' => '#5cb85c' ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_close_ticket_status', $term_id );
		}

		// Priority Items
		$term_id = TicketPriority::create( __( 'Low', 'stackonet-support-ticket' ), [ 'color' => '#5bc0de' ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_default_priority', $term_id );
		}
		$term_id = TicketPriority::create( __( 'Medium', 'stackonet-support-ticket' ), [ 'color' => '#f0ad4e' ] );
		$term_id = TicketPriority::create( __( 'High', 'stackonet-support-ticket' ), [ 'color' => '#d9534f' ] );
	}

	/**
	 * Add default options
	 */
	private static function add_default_options() {
		update_option( 'support_ticket_allow_customer_close_ticket', 'yes' );

		$support_ticket_thankyou_html = sprintf(
			__( '<p>Thank you for contacting us! We will get back to you as soon as possible. <a href="%s">Click here to track your ticket.</a></p>',
				'stackonet-support-ticket' ),
			get_option( 'customer_support_list_page_url' )
		);
		update_option( 'support_ticket_thankyou_html', $support_ticket_thankyou_html );
		update_option( 'support_ticket_thankyou_url', '' );

		update_option( 'support_terms_and_conditions', '0' );

		$support_ticket_gdpr_html = __(
			'I understand my personal information like Name, Email address, IP address etc will be stored in database.',
			'stackonet-support-ticket'
		);
		update_option( 'support_ticket_gdpr_html', $support_ticket_gdpr_html );

		update_option( 'support_ticket_allow_tinymce_in_guest_ticket', '1' );
	}
}
