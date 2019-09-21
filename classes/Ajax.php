<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Models\AgentRole;
use StackonetSupportTicket\Models\SupportAgent;

defined( 'ABSPATH' ) || exit;

class Ajax {

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

			add_action( 'wp_ajax_support_ticket_test', [ self::$instance, 'test' ] );
		}

		return self::$instance;
	}

	public function test() {
		$role = SupportAgent::get_all();
		var_dump( $role );
		die();
	}
}
