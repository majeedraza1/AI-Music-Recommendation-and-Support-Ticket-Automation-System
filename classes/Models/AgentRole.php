<?php

namespace StackonetSupportTicket\Models;

use JsonSerializable;

defined( 'ABSPATH' ) or exit;

class AgentRole implements JsonSerializable {

	/**
	 * Option name
	 *
	 * @var string
	 */
	protected static $option_name = 'support_ticket_agent_roles';

	/**
	 * Role slug
	 *
	 * @var string
	 */
	protected $role;

	/**
	 * Role Name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Support Ticket roles
	 *
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * @var array
	 */
	public static $reserve_roles = [ 'administrator', 'agent' ];

	/**
	 * AgentRole constructor.
	 *
	 * @param string $role
	 * @param array $data
	 */
	public function __construct( $role, $data = [] ) {
		$this->role = $role;
		if ( isset( $data['name'], $data['capabilities'] ) ) {
			$this->name         = $data['name'];
			$this->capabilities = static::format_capabilities( $data['capabilities'] );
		} else {
			$_role              = static::get_role( $role );
			$this->name         = $_role->get_role_name();
			$this->capabilities = $_role->get_capabilities();
		}
	}

	/**
	 * Array representation of the class
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'role'         => $this->get_role_slug(),
			'name'         => $this->get_role_name(),
			'capabilities' => $this->get_capabilities(),
		];
	}

	/**
	 * Get role name
	 *
	 * @return string
	 */
	public function get_role_slug() {
		return $this->role;
	}

	/**
	 * Get role display name
	 *
	 * @return string
	 */
	public function get_role_name() {
		return $this->name;
	}

	/**
	 * Get role capabilities
	 *
	 * @return array
	 */
	public function get_capabilities() {
		return $this->capabilities;
	}

	/**
	 * Get all roles
	 *
	 * @return array|self[]
	 */
	public static function get_roles() {
		$agent_role = get_option( self::$option_name );

		if ( ! is_array( $agent_role ) ) {
			$agent_role = [];
		}

		$roles = [];
		foreach ( $agent_role as $role => $value ) {
			$roles[ $role ] = new self( $role, $value );
		}

		return $roles;
	}

	/**
	 * Retrieve role object.
	 *
	 * @param string $role Role name.
	 *
	 * @return self|null WP_Role object if found, null if the role does not exist.
	 */
	public static function get_role( $role ) {
		$agent_roles = get_option( self::$option_name );
		if ( ! isset( $agent_roles[ $role ] ) ) {
			return null;
		}

		return new self( $role, $agent_roles[ $role ] );
	}

	/**
	 * Add role, if it does not exist.
	 *
	 * @param string $role Role name.
	 * @param string $display_name Display name for role.
	 * @param array $capabilities List of capabilities, e.g. array( 'edit_posts' => true, 'delete_posts' => false );
	 *
	 * @return self|null
	 */
	public static function add_role( $role, $display_name, $capabilities = [] ) {
		$agent_roles = get_option( self::$option_name );
		if ( isset( $agent_roles[ $role ] ) ) {
			return null;
		}

		$valid_caps    = static::valid_capabilities();
		$_capabilities = [];
		foreach ( $valid_caps as $cap_name => $active ) {
			$enabled                    = isset( $capabilities[ $cap_name ] ) ? $capabilities[ $cap_name ] : $active;
			$_capabilities[ $cap_name ] = static::is_checked( $enabled ) ? 1 : 0;
		}

		$agent_roles[ $role ] = [
			'name'         => $display_name,
			'capabilities' => $_capabilities,
		];

		update_option( static::$option_name, $agent_roles );

		return new self( $role, $agent_roles[ $role ] );
	}

	/**
	 * Update role, if it exist.
	 *
	 * @param string $role Role name.
	 * @param string $display_name Display name for role.
	 * @param array $capabilities List of capabilities, e.g. array( 'edit_posts' => true, 'delete_posts' => false );
	 *
	 * @return self|null
	 */
	public static function update_role( $role, $capabilities = [], $display_name = null ) {
		$agent_roles = get_option( self::$option_name );
		if ( ! isset( $agent_roles[ $role ] ) ) {
			return null;
		}

		/** @var self $current_role */
		$current_role = $agent_roles[ $role ];

		$_capabilities = [];
		foreach ( $current_role->get_capabilities() as $cap_name => $active ) {
			$enabled = isset( $capabilities[ $cap_name ] ) ? $capabilities[ $cap_name ] : $active;

			$_capabilities[ $cap_name ] = static::is_checked( $enabled ) ? 1 : 0;
		}

		$agent_roles[ $role ] = [
			'name'         => ! empty( $display_name ) ? sanitize_text_field( $display_name ) : $current_role->get_role_name(),
			'capabilities' => $_capabilities,
		];

		update_option( static::$option_name, $agent_roles );

		return new self( $role, $agent_roles[ $role ] );
	}

	/**
	 * Remove role, if it exists.
	 *
	 * @param string $role Role name.
	 *
	 * @return bool
	 */
	public static function remove_role( $role ) {
		$agent_roles = get_option( self::$option_name );
		if ( isset( $agent_roles[ $role ] ) ) {
			unset( $agent_roles[ $role ] );
			update_option( static::$option_name, $agent_roles );

			return true;
		}

		return false;
	}

	/**
	 * Support ticket valid capabilities
	 *
	 * @return array
	 */
	public static function valid_capabilities() {
		return [
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
	}

	/**
	 * Format capabilities
	 *
	 * @param $capabilities
	 *
	 * @return array
	 */
	public static function format_capabilities( $capabilities ) {
		$caps = [];
		foreach ( static::valid_capabilities() as $cap_name => $default ) {
			$enabled           = isset( $capabilities[ $cap_name ] ) ? $capabilities[ $cap_name ] : false;
			$caps[ $cap_name ] = static::is_checked( $enabled );
		}

		return $caps;
	}

	/**
	 * Check if boolean
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function is_checked( $value ) {
		return in_array( $value, [ '1', 'true', 'on', 'yes', 1, true ], true );
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @return mixed data which can be serialized by json_encode
	 * which is a value of any type other than a resource.
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}