<?php

namespace StackonetSupportTicket;

use Stackonet\WP\Framework\Supports\Validate;
use StackonetSupportTicket\Models\TicketCategory;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend
 *
 * @package StackonetSupportTicket
 */
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
		if ( $this->should_load_scripts() ) {
			wp_enqueue_style( STACKONET_SUPPORT_TICKET . '-frontend' );
			wp_enqueue_script( STACKONET_SUPPORT_TICKET . '-frontend' );
		}
	}

	/**
	 * Support Ticket frontend shortcode
	 */
	public function support_ticket(): string {
		if ( ! is_user_logged_in() ) {
			return $this->support_ticket_login();
		}
		// Include icons on footer before loading the script
		add_action( 'wp_footer', function () {
			include STACKONET_SUPPORT_TICKET_PATH . '/assets/icon/icons.svg';
		}, 0 );

		// Return the root element to load the Vue/React app
		return '<div id="stackonet_support_ticket_list"></div>';
	}

	/**
	 * Support Ticket frontend shortcode
	 *
	 * @param  array  $attributes
	 *
	 * @return string
	 */
	public function create_ticket( array $attributes ): string {
		$default_category = (int) get_option( 'support_ticket_default_category' );
		$default_status   = (int) get_option( 'support_ticket_default_status' );
		$default_priority = (int) get_option( 'support_ticket_default_priority' );

		$attributes = shortcode_atts(
			[
				'need_login'       => 'no',
				'show_category'    => 'yes',
				'category'         => '',
				'default_category' => $default_category,
				'default_status'   => $default_status,
				'default_priority' => $default_priority,
			],
			$attributes
		);

		$need_login    = Validate::checked( $attributes['need_login'] );
		$show_category = Validate::checked( $attributes['show_category'] );

		$cat_options = [];
		if ( $show_category ) {
			$cat_args = [];
			if ( ! empty( $attributes['category'] ) ) {
				$category = array_map( 'intval', explode( ',', $attributes['category'] ) );
				$cat_args = [ 'include' => $category ];
			}
			$cats = TicketCategory::get_all( $cat_args );
			foreach ( $cats as $cat ) {
				$cat_options[ $cat->get_prop( 'term_id' ) ] = $cat->get_prop( 'name' );
			}
		}

		$current_user = wp_get_current_user();

		if ( $need_login && ! $current_user->exists() ) {
			return $this->support_ticket_login();
		}

		$fields = [
			'name'    => [
				'id'      => 'name',
				'type'    => 'text',
				'label'   => __( 'Name', 'stackonet-support-ticket' ),
				'default' => $current_user->exists() ? $current_user->display_name : '',
			],
			'email'   => [
				'id'      => 'email',
				'type'    => 'email',
				'label'   => __( 'Email', 'stackonet-support-ticket' ),
				'default' => $current_user->exists() ? $current_user->user_email : '',
			],
			'subject' => [
				'id'          => 'subject',
				'type'        => 'text',
				'label'       => __( 'Subject', 'stackonet-support-ticket' ),
				'description' => __( 'Short description of the ticket.', 'stackonet-support-ticket' ),
				'default'     => '',
			],
			'content' => [
				'id'          => 'content',
				'type'        => 'textarea',
				'label'       => __( 'Content', 'stackonet-support-ticket' ),
				'description' => __( 'Detailed description of the ticket', 'stackonet-support-ticket' ),
				'default'     => '',
			],
		];

		// Show category if enabled
		if ( $show_category && count( $cat_options ) ) {
			$fields['category'] = [
				'id'          => 'category',
				'type'        => 'select',
				'label'       => __( 'Category', 'stackonet-support-ticket' ),
				'description' => __( 'Please select category.', 'stackonet-support-ticket' ),
				'default'     => $attributes['default_category'],
				'options'     => $cat_options,
			];
		}

		// Support ticket category
		if ( $attributes['default_category'] != $default_category && ! isset( $fields['category'] ) ) {
			$fields['category'] = [
				'id'      => 'category',
				'type'    => 'hidden',
				'default' => $attributes['default_category'],
			];
		}

		// Support ticket priority
		if ( $attributes['default_priority'] != $default_priority ) {
			$fields['priority'] = [
				'id'      => 'priority',
				'type'    => 'hidden',
				'default' => $attributes['default_priority'],
			];
		}

		// Support ticket status
		if ( $attributes['default_status'] != $default_status ) {
			$fields['status'] = [
				'id'      => 'status',
				'type'    => 'hidden',
				'default' => $attributes['default_status'],
			];
		}

		return "<div data-form_fields='" . wp_json_encode( $fields ) . "'><div id='stackonet_support_ticket_form'></div></div>";
	}

	/**
	 * Support ticket login form
	 *
	 * @return string
	 */
	public function support_ticket_login(): string {
		if ( is_user_logged_in() ) {
			return 'You are already logged in.';
		}

		return '<div id="stackonet_support_ticket_login"></div>';
	}

	/**
	 * Check if it should load frontend scripts
	 *
	 * @return bool
	 */
	private function should_load_scripts(): bool {
		global $post;
		if ( $post instanceof WP_Post ) {
			$shortcodes = [ 'support_ticket', 'create_ticket' ];
			foreach ( $shortcodes as $shortcode ) {
				if ( has_shortcode( $post->post_content, $shortcode ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
