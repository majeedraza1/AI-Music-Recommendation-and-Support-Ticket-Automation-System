<?php

namespace StackonetSupportTicket;

defined( 'ABSPATH' ) || exit;

class Frontend {

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

			add_shortcode( 'support_ticket', array( self::$instance, 'support_ticket' ) );
			add_shortcode( 'create_ticket', array( self::$instance, 'create_ticket' ) );
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'load_frontend_scripts' ) );
		}

		return self::$instance;
	}

	/**
	 * Load frontend scripts
	 */
	public function load_frontend_scripts() {
		wp_enqueue_style( STACKONET_SUPPORT_TICKET . '-frontend' );
		wp_enqueue_script( STACKONET_SUPPORT_TICKET . '-frontend' );
	}

	/**
	 * Support Ticket frontend shortcode
	 */
	public function support_ticket() {
		if ( ! is_user_logged_in() ) {
			return $this->support_ticket_login();
		}

		return 'Welcome to support';
	}

	/**
	 * Support Ticket frontend shortcode
	 */
	public function create_ticket() {
		if ( ! is_user_logged_in() ) {
			return $this->support_ticket_login();
		}

		return 'Welcome to create support ticket';
	}

	/**
	 * Support ticket login form
	 *
	 * @return string
	 */
	public function support_ticket_login() {
		return '<div id="stackonet_support_ticket_login"></div>';
	}
}
