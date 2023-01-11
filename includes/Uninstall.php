<?php

namespace StackonetSupportTicket;

use StackonetSupportTicket\Models\SupportAgent;
use StackonetSupportTicket\Models\TicketCategory;
use StackonetSupportTicket\Models\TicketPriority;
use StackonetSupportTicket\Models\TicketStatus;

class Uninstall {

	public static function run() {
		$self = new self();
		$self->delete_category_status_priority();
		$self->delete_options();
		$self->delete_roles_and_capabilities();
		$self->drop_tables();
	}

	/**
	 * Drop all tables
	 *
	 * @return void
	 */
	public function drop_tables() {
		global $wpdb;

		$constant_name = self::get_foreign_key_constant_name( 'support_ticketmeta', 'support_ticket' );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}support_ticketmeta DROP FOREIGN KEY {$constant_name}" );
		$constant_name = self::get_foreign_key_constant_name( 'support_ticket_thread', 'support_ticket' );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}support_ticket_thread DROP FOREIGN KEY {$constant_name}" );
		$constant_name = self::get_foreign_key_constant_name( 'support_ticket_threadmeta', 'support_ticket_thread' );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}support_ticket_threadmeta DROP FOREIGN KEY {$constant_name}" );

		self::print_ajax( 'Dropping support ticket tables foreign key...' );

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}support_ticket" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}support_ticketmeta" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}support_ticket_thread" );
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}support_ticket_threadmeta" );

		self::print_ajax( 'Dropping support ticket tables...' );
	}

	public function delete_options() {
		global $wpdb;
		// delete all options started with 'support_ticket_'
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'support_ticket_%'" );

		// delete option used for storing the version of the tables
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%support_ticket%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%support_ticketmeta%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%support_ticket_thread%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%support_ticket_threadmeta%'" );

		delete_option( 'ticket_extra_fields_labels' );
		delete_option( 'ticket_user_extra_fields' );
		self::print_ajax( 'Options deleted' );
	}

	public function delete_roles_and_capabilities() {
		RoleAndCapability::uninstall();
		self::print_ajax( 'Roles and capabilities deleted successfully' );
	}

	public function delete_category_status_priority() {
		// Delete all categories
		$categories = TicketCategory::get_all();
		foreach ( $categories as $category ) {
			static::print_ajax( 'Deleting category: ' . $category->get_id() );
			TicketCategory::delete( $category->get_id() );
		}

		// Delete all priorities
		$priorities = TicketPriority::get_all();
		foreach ( $priorities as $priority ) {
			static::print_ajax( 'Deleting priority: ' . $priority->get_id() );
			TicketPriority::delete( $priority->get_id() );
		}

		// Delete all statuses
		$statuses = TicketStatus::get_all();
		foreach ( $statuses as $status ) {
			static::print_ajax( 'Deleting status: ' . $status->get_id() );
			TicketStatus::delete( $status->get_id() );
		}

		// Delete all agents
		$agents = SupportAgent::get_all();
		foreach ( $agents as $agent ) {
			static::print_ajax( 'Deleting agent: ' . $agent->get_id() );
			SupportAgent::delete( $agent->get_id() );
		}
	}

	private static function print_ajax( string $line ) {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			echo $line . '<br>';
		}
	}

	/**
	 * Get foreign key constant name
	 *
	 * @param  string  $table1
	 * @param  string  $table2
	 *
	 * @return string
	 */
	private static function get_foreign_key_constant_name( string $table1, string $table2 ): string {
		global $wpdb;
		$tables = [
			str_replace( $wpdb->prefix, '', $table1 ),
			str_replace( $wpdb->prefix, '', $table2 ),
		];
		asort( $tables );

		return substr( sprintf( 'fk_%s__%s', $tables[0], $tables[1] ), 0, 64 );
	}
}