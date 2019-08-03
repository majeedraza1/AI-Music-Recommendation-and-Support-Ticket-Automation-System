<?php

namespace StackonetSupportTicket\Admin;

defined( 'ABSPATH' ) || exit;

class Admin {

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

			add_action( 'admin_menu', [ self::$instance, 'add_menu' ], 9 );
		}

		return self::$instance;
	}

	/**
	 * Add top level menu
	 */
	public function add_menu() {
		$capability = 'manage_options';
		$slug       = 'wpsc-tickets';

		$hook = add_menu_page( __( 'Support', 'stackonet-support-ticket' ), __( 'Support', 'stackonet-support-ticket' ),
			$capability, $slug, [ self::$instance, 'support_tickets_callback' ], 'dashicons-admin-post', 8 );

		add_action( 'load-' . $hook, [ self::$instance, 'init_support_tickets_hooks' ] );
	}

	/**
	 * Menu page callback
	 */
	public function support_tickets_callback() {
		add_action( 'admin_footer', [ $this, 'tinymce_script' ], 9 );
		echo '<div class="wrap"><div id="admin-stackonet-support-tickets"></div></div>';
	}

	/**
	 * Load tinymce scripts
	 */
	public function tinymce_script() {
		echo '<script type="text/javascript" src="' . includes_url( 'js/tinymce/tinymce.min.js' ) . '"></script>';
	}

	/**
	 * Load required styles and scripts
	 */
	public static function init_support_tickets_hooks() {
		wp_enqueue_style( 'stackonet-support-ticket-admin' );
		wp_enqueue_script( 'stackonet-support-ticket-admin' );
	}
}
