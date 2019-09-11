<?php

namespace StackonetSupportTicket\Models;

defined( 'ABSPATH' ) or exit;

class AgentRole {

	/**
	 * @return array
	 */
	public static function get_all() {
		$agent_role = get_option( 'wpsc_agent_role' );

		return $agent_role;
	}
}