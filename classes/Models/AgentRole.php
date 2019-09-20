<?php

namespace StackonetSupportTicket\Models;

defined( 'ABSPATH' ) or exit;

class AgentRole {

	/**
	 * Option name
	 *
	 * @var string
	 */
	protected static $option_name = 'support_ticket_agent_roles';

	/**
	 * Support Ticket roles
	 *
	 * @var array
	 */
	protected static $roles = [
		"view_unassigned"      => 0,
		"view_assigned_me"     => 0,
		"view_assigned_others" => 0,

		"assign_unassigned"      => 0,
		"assign_assigned_me"     => 0,
		"assign_assigned_others" => 0,

		"reply_unassigned"      => 0,
		"reply_assigned_me"     => 0,
		"reply_assigned_others" => 0,

		"delete_unassigned"      => 0,
		"delete_assigned_me"     => 0,
		"delete_assigned_others" => 0,

		"change_ticket_status_unassigned"      => 0,
		"change_ticket_status_assigned_me"     => 0,
		"change_ticket_status_assigned_others" => 0,

		"change_ticket_field_unassigned"      => 0,
		"change_ticket_field_assigned_me"     => 0,
		"change_ticket_field_assigned_others" => 0,

		"change_ticket_agent_only_unassigned"      => 0,
		"change_ticket_agent_only_assigned_me"     => 0,
		"change_ticket_agent_only_assigned_others" => 0,

		"change_ticket_raised_by_unassigned"      => 0,
		"change_ticket_raised_by_assigned_me"     => 0,
		"change_ticket_raised_by_assigned_others" => 0,
	];

	/**
	 * Get all roles
	 *
	 * @return array
	 */
	public static function get_all() {
		$agent_role = get_option( self::$option_name );

		if ( ! is_array( $agent_role ) ) {
			$agent_role = [];
		}

		return $agent_role;
	}

	/**
	 * Create new roles
	 *
	 * @param string $name
	 * @param array $roles
	 */
	public static function create( $name, array $roles ) {
		$agent_roles = static::get_all();

		$_roles = [ 'label' => $name ];
		foreach ( self::$roles as $role_name => $active ) {
			$_roles[ $role_name ] = isset( $roles[ $role_name ] ) ? intval( $roles[ $role_name ] ) : $active;
		}

		$agent_roles[] = $_roles;

		update_option( 'support_ticket_agent_roles', $agent_roles );
	}

	/**
	 * Update agent roles
	 *
	 * @param string $name
	 * @param array $roles
	 * @param string $newName
	 */
	public static function update( $name, array $roles, $newName = null ) {
		$agent_roles = static::get_all();
		$labels      = wp_list_pluck( $agent_roles, 'label' );
		$index       = array_search( $name, $labels );
		if ( false !== $index ) {
			$_roles = [ 'label' => ! empty( $newName ) ? $newName : $name ];
			foreach ( self::$roles as $role_name => $active ) {
				$_roles[ $role_name ] = isset( $roles[ $role_name ] ) ? intval( $roles[ $role_name ] ) : $active;
			}

			$agent_roles[ $index ] = $_roles;

			update_option( 'support_ticket_agent_roles', $agent_roles );
		}
	}
}