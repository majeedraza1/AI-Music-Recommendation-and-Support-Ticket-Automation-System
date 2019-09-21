<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Utilities\Logger;
use WP_Role;

defined( 'ABSPATH' ) || exit;

class RoleAndCapability {

	/**
	 * Support Ticket capabilities
	 *
	 * @var array
	 */
	protected static $ticket_capabilities = [
		'delete_others_tickets',
		'delete_tickets',
		'edit_others_tickets',
		'edit_tickets',
		'create_tickets',
		'read_others_tickets',
		'read_tickets',
	];

	/**
	 * Method to run on plugin activation
	 */
	public static function activation() {
		self::add_manager_role();
		self::add_agent_role();
		self::add_support_ticket_capabilities();
	}

	/**
	 * Add manager role
	 */
	public static function add_manager_role() {
		if ( ! get_role( 'manager' ) ) {
			add_role( 'manager', 'Manager', [ 'read' => true ] );
		}
	}

	/**
	 * Add agent role
	 */
	public static function add_agent_role() {
		if ( ! get_role( 'agent' ) ) {
			add_role( 'agent', 'Agent', [ 'read' => true ] );
		}
	}

	/**
	 * Add support ticket capabilities
	 */
	public static function add_support_ticket_capabilities() {
		self::add_capabilities_to_roles( [ 'administrator', 'editor', 'manager' ],
			array_fill_keys( self::$ticket_capabilities, true )
		);
		self::add_capabilities_to_roles( [ 'agent' ], [
				'delete_tickets' => true,
				'edit_tickets'   => true,
				'create_tickets' => true,
				'read_tickets'   => true,
			]
		);
	}

	/**
	 * Add capabilities to roles
	 *
	 * @param array $roles
	 * @param array $capabilities
	 */
	public static function add_capabilities_to_roles( array $roles, array $capabilities ) {
		foreach ( $roles as $roleName ) {
			$role = get_role( $roleName );
			if ( ! $role instanceof WP_Role ) {
				continue;
			}

			foreach ( $capabilities as $cap => $grant ) {
				if ( ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap, (bool) $grant );
				}
			}
		}
	}
}
