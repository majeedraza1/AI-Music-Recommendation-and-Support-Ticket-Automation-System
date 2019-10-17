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

			self::add_default_roles();
			self::add_support_ticket_agents();
			self::create_meta_table();
			self::add_default_data();
			self::add_default_options();
		}

		return self::$instance;
	}

	/**
	 * Add default roles
	 */
	public static function add_default_roles() {
		$valid_caps = AgentRole::valid_capabilities();
		AgentRole::add_role( 'administrator', __( 'Support Admin', 'stackonet-support-ticket' ),
			array_fill_keys( array_keys( $valid_caps ), true )
		);
		AgentRole::add_role( 'agent', __( 'Support Agent', 'stackonet-support-ticket' ), [
			'view_unassigned'                      => true,
			'view_assigned_me'                     => true,
			'assign_unassigned'                    => true,
			'assign_assigned_me'                   => true,
			'change_ticket_status_assigned_me'     => true,
			'change_ticket_field_assigned_me'      => true,
			'change_ticket_agent_only_assigned_me' => true,
			'reply_assigned_me'                    => true,
		] );
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
	private static function create_meta_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'support_ticketmeta';
		$collate    = $wpdb->get_charset_collate();

		$tables = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			ticket_id bigint(20),
			meta_key LONGTEXT NULL DEFAULT NULL,
			meta_value LONGTEXT NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY ticket_id (ticket_id)
		) $collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $tables );
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
		$term_id = TicketStatus::create( __( 'Open', 'stackonet-support-ticket' ), [ 'color' => '#d9534f', ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_default_status', $term_id );
		}
		$term_id = TicketStatus::create( __( 'Awaiting customer reply', 'stackonet-support-ticket' ), [ 'color' => '#000000', ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_status_after_agent_reply', $term_id );
		}
		$term_id = TicketStatus::create( __( 'Awaiting agent reply', 'stackonet-support-ticket' ), [ 'color' => '#f0ad4e', ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_status_after_customer_reply', $term_id );
		}
		$term_id = TicketStatus::create( __( 'Closed', 'stackonet-support-ticket' ), [ 'color' => '#5cb85c', ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_close_ticket_status', $term_id );
		}

		// Priority Items
		$term_id = TicketPriority::create( __( 'Low', 'stackonet-support-ticket' ), [ 'color' => '#5bc0de', ] );
		if ( ! is_wp_error( $term_id ) ) {
			update_option( 'support_ticket_default_priority', $term_id );
		}
		$term_id = TicketPriority::create( __( 'Medium', 'stackonet-support-ticket' ), [ 'color' => '#f0ad4e', ] );
		$term_id = TicketPriority::create( __( 'High', 'stackonet-support-ticket' ), [ 'color' => '#d9534f', ] );
	}

	/**
	 * Add default options
	 */
	private static function add_default_options() {
		update_option( 'support_ticket_allow_customer_close_ticket', '1' );

		$support_ticket_thankyou_html = __( "<p>Dear {customer_name},</p><p>We have received your ticket and confirmation has been sent to your email address&nbsp;{customer_email}.</p><p>Your ticket id is #{ticket_id}. You will get email notification after we post reply in your ticket but in case email notification failed, you can check your ticket status on below link:</p><p>{ticket_url}</p>", 'supportcandy' );
		update_option( 'support_ticket_thankyou_html', $support_ticket_thankyou_html );
		update_option( 'support_ticket_thankyou_url', '' );

		update_option( 'support_terms_and_conditions', '0' );

		$support_ticket_gdpr_html = __( "I understand my personal information like Name, Email address, IP address etc will be stored in database.", 'supportcandy' );
		update_option( 'support_ticket_gdpr_html', $support_ticket_gdpr_html );

		update_option( 'support_ticket_allow_tinymce_in_guest_ticket', '1' );
	}
}