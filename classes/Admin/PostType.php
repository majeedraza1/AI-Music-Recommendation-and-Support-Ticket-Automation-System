<?php

namespace StackonetSupportTicket\Admin;

defined( 'ABSPATH' ) || exit;

class PostType {

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

			add_action( 'init', array( self::$instance, 'register_post_type' ), 99 );
			add_action( 'init', array( self::$instance, 'register_taxonomy' ), 99 );
		}

		return self::$instance;
	}

	/**
	 * Register post types
	 */
	public function register_post_type() {
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_post_type( 'support_ticket', $args );

		// Threads post
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_post_type( 'ticket_thread', $args );
	}

	/**
	 * Register post types and taxonomies
	 */
	public function register_taxonomy() {
		// Register categories taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_taxonomy( 'ticket_category', 'support_ticket', $args );

		// Register status taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_taxonomy( 'ticket_status', 'support_ticket', $args );

		// Register priorities taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_taxonomy( 'ticket_priority', 'support_ticket', $args );

		// Register form field taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_taxonomy( 'support_ticket_custom_fields', 'support_ticket', $args );

		// Register agent taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);

		register_taxonomy( 'support_ticket_widget', 'support_ticket', $args );
		// Register form field taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);

		register_taxonomy( 'support_agent', 'support_ticket', $args );

		// Register attachment taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_taxonomy( 'support_ticket_attachment', 'support_ticket', $args );

		// Register ticket notifications taxonomy
		$args = array(
			'public'  => false,
			'rewrite' => false
		);
		register_taxonomy( 'support_ticket_notification', 'support_ticket', $args );
	}
}
