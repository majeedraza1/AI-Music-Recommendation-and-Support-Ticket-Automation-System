<?php

namespace StackonetSupportTicket\Integrations\MuslimZone;

use MuslimZone\Modules\PushNotification\BackgroundPushPublisher;
use MuslimZone\Modules\PushNotification\Models\Device;
use MuslimZone\Modules\PushNotification\Models\PushTemplate;
use Stackonet\WP\Framework\Supports\Validate;
use StackonetSupportTicket\Models\SupportTicket;

defined( 'ABSPATH' ) || exit;

class PushNotification {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			if ( ! static::is_enabled() ) {
				return self::$instance;
			}

			add_filter( 'push_notification/placeholders/group', [ self::$instance, 'add_group' ] );
			add_action( 'stackonet_support_ticket/v3/thread_created', [ self::$instance, 'send_push' ], 10, 3 );
			add_filter( 'stackonet_support_ticket/settings/panels', [ self::$instance, 'add_settings_panels' ] );
			add_filter( 'stackonet_support_ticket/settings/sections', [ self::$instance, 'add_settings_sections' ] );
			add_filter( 'stackonet_support_ticket/settings/fields', [ self::$instance, 'add_settings_fields' ] );
		}

		return self::$instance;
	}

	/**
	 * Check if push notification enabled
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return class_exists( BackgroundPushPublisher::class ) &&
		       class_exists( Device::class ) &&
		       class_exists( PushTemplate::class );
	}

	/**
	 * Add setting panels
	 *
	 * @param array $panels
	 *
	 * @return array
	 */
	public function add_settings_panels( array $panels ) {
		$panels[] = [
			'id'       => 'panel_push_notification',
			'title'    => __( 'Push Notification', 'stackonet-support-ticket' ),
			'priority' => 20,
		];

		return $panels;
	}

	/**
	 * Add setting sections
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function add_settings_sections( array $sections ) {
		$sections[] = [
			'id'       => 'section_push_notification',
			'title'    => __( 'Push Notification Settings', 'stackonet-support-ticket' ),
			'panel'    => 'panel_push_notification',
			'priority' => 10,
		];

		return $sections;
	}

	/**
	 * Add setting fields
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_settings_fields( array $fields ) {
		$fields[] = [
			'section'           => 'section_push_notification',
			'id'                => 'push_template_id_for_admin_reply',
			'type'              => 'select',
			'title'             => __( 'Push Template for admin reply' ),
			'description'       => __( 'Select push notification template for admin reply on ticket thread.' ),
			'priority'          => 10,
			'sanitize_callback' => 'intval',
			'options'           => static::get_templates_options(),
		];

		return $fields;
	}

	/**
	 * Get template options
	 *
	 * @return array
	 */
	private static function get_templates_options() {
		$templates = PushTemplate::get_manual_templates();
		$options   = [];
		foreach ( $templates as $template ) {
			$options[ $template->get_id() ] = $template->get_title();
		}

		return $options;
	}

	/**
	 * Add new push notification group
	 *
	 * @param array $groups
	 *
	 * @return mixed
	 */
	public function add_group( array $groups ) {
		$placeholders             = [ '{{id}}', '{{title}}', '{{thread_id}}', '{{thread_content}}' ];
		$groups['support_ticket'] = [
			'key'          => 'support_ticket',
			'label'        => 'Support Ticket',
			'placeholders' => $placeholders,
			'parser'       => SupportTicketParser::class,
		];

		return $groups;
	}

	/**
	 * Sent push notification
	 *
	 * @param int   $id
	 * @param int   $thread_id
	 * @param array $params
	 */
	public function send_push( int $id, int $thread_id, array $params ) {
		if ( ! ( isset( $params['send_push_notification'] ) && Validate::checked( $params['send_push_notification'] ) ) ) {
			return;
		}

		$ticket      = ( new SupportTicket )->find_by_id( $id );
		$created_by  = $ticket->get_created_by();
		$type        = $ticket->get( 'user_type' );
		$template_id = (int) get_option( 'push_template_id_for_admin_reply' );
		$devices     = ( new Device )->find_by_user_id( $created_by );
		if ( ! ( $created_by > 0 && $template_id > 0 && $type == 'user' && count( $devices ) < 1 ) ) {
			return;
		}

		$parser   = new SupportTicketParser( $id, $thread_id );
		$template = new PushTemplate( $template_id );
		$template->set_parser( $parser );

		foreach ( $devices as $device ) {
			BackgroundPushPublisher::send_push_notification( $device, $template );
		}
	}
}
