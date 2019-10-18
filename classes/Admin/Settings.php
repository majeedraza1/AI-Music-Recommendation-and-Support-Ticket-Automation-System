<?php

namespace StackonetSupportTicket\Admin;

use StackonetSupportTicket\Models\TicketCategory;
use StackonetSupportTicket\Models\TicketPriority;
use StackonetSupportTicket\Models\TicketStatus;
use StackonetSupportTicket\Supports\SettingHandler;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class Settings {

	/**
	 * @var Settings
	 */
	private static $instance;

	/**
	 * @return Settings
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_loaded', [ self::$instance, 'settings' ] );

			add_action( 'admin_head', [ self::$instance, 'support_ticket_colors' ] );
			add_action( 'wp_head', [ self::$instance, 'support_ticket_colors' ] );
		}

		return self::$instance;
	}

	public function support_ticket_colors() {
		$primary_color   = get_option( 'support_ticket_primary_color', '#f58730' );
		$secondary_color = get_option( 'support_ticket_secondary_color', '#9c27b0' );
		$secondary_color = ! empty( $secondary_color ) ? $secondary_color : $primary_color;

		$current_user = wp_get_current_user();
		if ( $current_user->exists() ) {
			$options         = get_user_meta( $current_user->ID, '_stackonet_support_ticket', true );
			$primary_color   = isset( $options['support_ticket_primary_color'] ) ? $options['support_ticket_primary_color'] : $primary_color;
			$secondary_color = isset( $options['support_ticket_secondary_color'] ) ? $options['support_ticket_secondary_color'] : $secondary_color;
			$secondary_color = ! empty( $secondary_color ) ? $secondary_color : $primary_color;
		}

		?>
        <style type="text/css">
            :root {
                --stackonet-ticket-primary: <?php echo $primary_color; ?>;
                --stackonet-ticket-on-primary: #ffffff;
                --stackonet-ticket-secondary: <?php echo $secondary_color; ?>;
                --stackonet-ticket-on-secondary: #ffffff;
                --stackonet-ticket-text-primary: rgba(0, 0, 0, 0.87);
                --stackonet-ticket-text-secondary: rgba(0, 0, 0, 0.54);
                --stackonet-ticket-text-icon: rgba(0, 0, 0, 0.38);
            }
        </style>
		<?php
	}

	/**
	 * Plugin settings
	 */
	public static function settings() {
		$option_page = SettingHandler::init();
		$option_page->set_option_name( 'stackonet_support_ticket' );

		$panels = array(
			array(
				'id'       => 'general_settings_panel',
				'title'    => __( 'General', 'stackonet-support-ticket' ),
				'priority' => 10,
			),
			array(
				'id'       => 'colors_panel',
				'title'    => __( 'Colors', 'stackonet-support-ticket' ),
				'priority' => 20,
			),
		);

		// Add settings page tab
		$option_page->add_panels( apply_filters( 'stackonet_support_ticket/settings/panels', $panels ) );

		$sections = array(
			array(
				'id'          => 'general_settings_section',
				'title'       => __( 'General', 'stackonet-support-ticket' ),
				'description' => __( 'Plugin general options.', 'stackonet-support-ticket' ),
				'panel'       => 'general_settings_panel',
				'priority'    => 10,
			),
			array(
				'id'          => 'colors_settings_section',
				'title'       => __( 'Colors', 'stackonet-support-ticket' ),
				'description' => __( 'Plugin color options.', 'stackonet-support-ticket' ),
				'panel'       => 'colors_panel',
				'priority'    => 20,
			),
		);

		// Add Sections
		$option_page->add_sections( apply_filters( 'stackonet_support_ticket/settings/sections', $sections ) );

		$fields = array(
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_page_id',
				'type'              => 'select',
				'title'             => __( 'Support Page', 'stackonet-support-ticket' ),
				'description'       => __( 'Select page in which shortcode is inserted. Create a page with shortcode [support_ticket] if not created yet. Fullwidth page template is recommended.', 'stackonet-support-ticket' ),
				'priority'          => 10,
				'sanitize_callback' => 'intval',
				'options'           => static::get_pages_for_options(),
			),
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_ticket_default_status',
				'type'              => 'select',
				'title'             => __( 'Default ticket status', 'stackonet-support-ticket' ),
				'description'       => __( 'This status will get applied for newly created ticket.', 'stackonet-support-ticket' ),
				'priority'          => 15,
				'sanitize_callback' => 'intval',
				'options'           => static::get_tickets_statuses_for_options(),
			),
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_ticket_default_category',
				'type'              => 'select',
				'title'             => __( 'Default ticket category', 'stackonet-support-ticket' ),
				'description'       => __( 'This category will get applied for newly created ticket.', 'stackonet-support-ticket' ),
				'priority'          => 20,
				'sanitize_callback' => 'intval',
				'options'           => static::get_tickets_categories_for_options(),
			),
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_ticket_default_priority',
				'type'              => 'select',
				'title'             => __( 'Default ticket priority', 'stackonet-support-ticket' ),
				'description'       => __( 'This priority will get applied for newly created ticket.', 'stackonet-support-ticket' ),
				'priority'          => 25,
				'sanitize_callback' => 'intval',
				'options'           => static::get_tickets_priorities_for_options(),
			),
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_ticket_status_after_customer_reply',
				'type'              => 'select',
				'title'             => __( 'Ticket status after customer reply', 'stackonet-support-ticket' ),
				'description'       => __( 'This status will be applied to the ticket if customer post reply in ticket.', 'stackonet-support-ticket' ),
				'priority'          => 30,
				'sanitize_callback' => 'intval',
				'options'           => static::get_tickets_statuses_for_options(),
			),
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_ticket_status_after_agent_reply',
				'type'              => 'select',
				'title'             => __( 'Ticket status after agent reply', 'stackonet-support-ticket' ),
				'description'       => __( 'This status will be applied to the ticket if agent or any support staff post reply in ticket.', 'stackonet-support-ticket' ),
				'priority'          => 35,
				'sanitize_callback' => 'intval',
				'options'           => static::get_tickets_statuses_for_options(),
			),
			array(
				'section'           => 'general_settings_section',
				'id'                => 'support_ticket_close_ticket_status',
				'type'              => 'select',
				'title'             => __( 'Close ticket status', 'stackonet-support-ticket' ),
				'description'       => __( 'Status to apply if \'Close Ticket\' button clicked for a ticket.', 'stackonet-support-ticket' ),
				'priority'          => 40,
				'sanitize_callback' => 'intval',
				'options'           => static::get_tickets_statuses_for_options(),
			),
			array(
				'section'     => 'general_settings_section',
				'id'          => 'support_ticket_allow_customer_close_ticket',
				'type'        => 'select',
				'title'       => __( 'Allow customer to close ticket', 'stackonet-support-ticket' ),
				'description' => __( 'Enables \'Close Ticket\' button for customer inside open ticket screen.', 'stackonet-support-ticket' ),
				'priority'    => 45,
				'options'     => [
					'yes' => __( 'Yes', 'stackonet-support-ticket' ),
					'no'  => __( 'No', 'stackonet-support-ticket' ),
				],
			),
		);

		$option_page->add_fields( $fields );

		$fields = array(
			array(
				'section'     => 'colors_settings_section',
				'id'          => 'support_ticket_primary_color',
				'type'        => 'color',
				'title'       => __( 'Primary Color', 'stackonet-support-ticket' ),
				'description' => __( 'Choose primary color.', 'stackonet-support-ticket' ),
				'priority'    => 100,
				'default'     => '#f58730',
			),
			array(
				'section'     => 'colors_settings_section',
				'id'          => 'support_ticket_secondary_color',
				'type'        => 'color',
				'title'       => __( 'Secondary Color', 'stackonet-support-ticket' ),
				'description' => __( 'Choose secondary color. Leave blank if you do not want secondary color.', 'stackonet-support-ticket' ),
				'priority'    => 105,
				'default'     => '#9c27b0',
			),
		);

		// Add Sections
		$option_page->add_fields( $fields );
	}

	public static function get_pages_for_options() {
		$_pages  = get_posts( [ 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => - 1 ] );
		$options = [];
		foreach ( $_pages as $page ) {
			$options[ $page->ID ] = get_the_title( $page );
		}

		return $options;
	}

	public static function get_tickets_statuses_for_options() {
		$statuses = TicketStatus::get_all();

		$options = [];
		foreach ( $statuses as $status ) {
			$options[ $status->get( 'term_id' ) ] = $status->get( 'name' );
		}

		return $options;
	}

	public static function get_tickets_categories_for_options() {
		$statuses = TicketCategory::get_all();

		$options = [];
		foreach ( $statuses as $status ) {
			$options[ $status->get( 'term_id' ) ] = $status->get( 'name' );
		}

		return $options;
	}

	public static function get_tickets_priorities_for_options() {
		$statuses = TicketPriority::get_all();

		$options = [];
		foreach ( $statuses as $status ) {
			$options[ $status->get( 'term_id' ) ] = $status->get( 'name' );
		}

		return $options;
	}
}
