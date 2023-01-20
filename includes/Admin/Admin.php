<?php

namespace StackonetSupportTicket\Admin;

use StackonetSupportTicket\Plugin;

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

			add_action( 'admin_menu', [ self::$instance, 'add_admin_menu' ] );
		}

		return self::$instance;
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		global $submenu;
		$capability = 'manage_options';
		$slug       = 'stackonet-support-ticket';
		$hook       = add_menu_page(
			__( 'Support - beta', 'stackonet-toolkit' ),
			__( 'Support - beta', 'stackonet-toolkit' ),
			$capability,
			$slug,
			[ self::$instance, 'menu_page_callback' ],
			'dashicons-format-chat',
			6
		);

		$menus = [
			[
				'title' => __( 'Tickets', 'stackonet-support-ticket' ),
				'slug'  => '#/',
			],
			[
				'title' => __( 'Settings', 'stackonet-support-ticket' ),
				'slug'  => '#/settings',
			],
		];

		if ( current_user_can( $capability ) ) {
			foreach ( $menus as $menu ) {
				// phpcs:ignore
				$submenu[ $slug ][] = [ $menu['title'], $capability, 'admin.php?page=' . $slug . $menu['slug'] ];
			}
		}

		add_action( 'load-' . $hook, [ self::$instance, 'init_support_tickets_hooks' ] );
	}

	/**
	 * Menu page callback
	 */
	public function menu_page_callback() {
		echo '<div class="wrap"><div id="stackonet_support_ticket_list"></div></div>';
		include Plugin::init()->get_plugin_path() . '/assets/icon/icons.svg';
	}

	/**
	 * Load required styles and scripts
	 */
	public static function init_support_tickets_hooks() {
		wp_enqueue_style( Plugin::init()->get_directory_name() . '-admin' );
		wp_enqueue_script( Plugin::init()->get_directory_name() . '-admin' );
	}
}
