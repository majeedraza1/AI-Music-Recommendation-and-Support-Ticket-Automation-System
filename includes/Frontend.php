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

			add_shortcode( 'support_ticket', [ self::$instance, 'support_ticket' ] );
			add_shortcode( 'create_ticket', [ self::$instance, 'create_ticket' ] );
			add_action( 'wp_enqueue_scripts', [ self::$instance, 'load_frontend_scripts' ] );
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
	 * @param  array|string  $attributes
	 *
	 * @return string
	 */
	public function create_ticket( $attributes ): string {
		$default_category = (int) get_option( 'support_ticket_default_category' );
		$default_status   = (int) get_option( 'support_ticket_default_status' );
		$default_priority = (int) get_option( 'support_ticket_default_priority' );

		$attributes = shortcode_atts(
			[
				'need_login'        => 'no',
				'show_category'     => 'yes',
				'category'          => '',
				'exclude_fields'    => '',
				'default_subject'   => '',
				'thank_you_message' => '<h3>Thank you for contacting us!</h3><p>We will get back to you as soon as possible.</p>',
				'default_category'  => $default_category,
				'default_status'    => $default_status,
				'default_priority'  => $default_priority,
			],
			$attributes
		);

		$exclude_fields = array_filter(
			array_map( 'trim', explode( ',', $attributes['exclude_fields'] ) )
		);
		$show_category  = ! in_array( 'category', $exclude_fields );
		$need_login     = Validate::checked( $attributes['need_login'] );

		$cat_options = [];
		if ( $show_category ) {
			$cat_args = [];
			if ( ! empty( $attributes['category'] ) ) {
				$category = array_map( 'intval', explode( ',', $attributes['category'] ) );
				$cat_args = [ 'include' => $category ];
			}
			$cats = TicketCategory::get_all( $cat_args );
			foreach ( $cats as $cat ) {
				$cat_options[ $cat->get_id() ] = $cat->get_prop( 'name' );
			}
		}

		$current_user = wp_get_current_user();

		if ( $need_login && ! $current_user->exists() ) {
			return $this->support_ticket_login();
		}

		$default_name  = '';
		$default_email = '';
		$default_phone = '';
		if ( $current_user->exists() ) {
			$default_name  = $current_user->display_name;
			$default_email = $current_user->user_email;
			// @todo: get meta field name from settings
			$default_phone = get_user_meta( $current_user->ID, 'billing_phone', true );
		}

		$fields = [
			'name'         => [
				'id'      => 'name',
				'type'    => 'text',
				'label'   => __( 'Name', 'stackonet-support-ticket' ),
				'default' => $default_name,
			],
			'email'        => [
				'id'      => 'email',
				'type'    => 'email',
				'label'   => __( 'Email', 'stackonet-support-ticket' ),
				'default' => $default_email,
			],
			'phone_number' => [
				'id'      => 'phone_number',
				'type'    => 'tel',
				'label'   => __( 'Phone', 'stackonet-support-ticket' ),
				'default' => $default_phone,
			],
			'subject'      => [
				'id'          => 'subject',
				'type'        => 'text',
				'label'       => __( 'Subject', 'stackonet-support-ticket' ),
				'description' => __( 'Short description of the ticket.', 'stackonet-support-ticket' ),
				'default'     => $attributes['default_subject'],
			],
			'content'      => [
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

		// Hide excluded fields
		foreach ( $exclude_fields as $exclude_field ) {
			if ( array_key_exists( $exclude_field, $fields ) ) {
				$fields[ $exclude_field ]['type'] = 'hidden';
			}
		}

		$data = [
			'thank_you_message' => $attributes['thank_you_message'],
			'fields'            => $fields,
		];

		return "<div data-form_fields='" . wp_json_encode( $data ) . "'><div id='stackonet_support_ticket_form'></div></div>";
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
