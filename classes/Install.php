<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Models\AgentRole;
use StackonetSupportTicket\Models\SupportAgent;
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
}