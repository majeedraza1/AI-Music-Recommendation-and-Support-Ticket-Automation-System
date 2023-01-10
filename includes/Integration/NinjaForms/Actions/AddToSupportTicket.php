<?php

namespace StackonetSupportTicket\Integration\NinjaForms\Actions;

use NF_Abstracts_Action;
use Stackonet\WP\Framework\Supports\Logger;
use StackonetSupportTicket\REST\TicketController;

/**
 * Class Module
 *
 * @package StackonetSupportTicket\Integration\NinjaForms
 */
class AddToSupportTicket extends NF_Abstracts_Action {
	/**
	 * @var string
	 */
	protected $_name = 'stackonet_add_to_support_ticket';

	/**
	 * @var array
	 */
	protected $_tags = array();

	/**
	 * @var string
	 */
	protected $_timing = 'late';

	/**
	 * @var int
	 */
	protected $_priority = 20;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->_nicename = esc_html__( 'Support Ticket', 'stackonet-support-ticket' );

		$this->_settings = array_merge( $this->_settings, $this->get_action_settings() );
	}

	/*
	* PUBLIC METHODS
	*/

	public function save( $action_settings ) {
		if ( ! isset( $_POST['form'] ) ) {
			return;
		}
		// Get the form data from the Post variable and send it off for processing.
		$form = json_decode( stripslashes( $_POST['form'] ) );
		Logger::log( $form );
	}

	public function process( $action_settings, $form_id, $data ) {
		$fields_mapping        = $action_settings['fields_mapping'] ?? [];
		$custom_fields_mapping = $action_settings['custom_fields_mapping'] ?? [];
		if ( is_array( $custom_fields_mapping ) && count( $custom_fields_mapping ) > 0 ) {
			$custom_fields_mapping = wp_list_pluck( $custom_fields_mapping, 'field' );
		}
		$ticket_subject         = $action_settings['ticket_subject'] ?? '';
		$ticket_message         = $action_settings['ticket_message'] ?? '';
		$ticket_submitter_email = $action_settings['ticket_submitter_email'] ?? '';
		$ticket_submitter_name  = $action_settings['ticket_submitter_name'] ?? '';
		$fields_by_key          = $data['fields_by_key'] ?? [];

		$form_fields_to_ticket_fields = [
			'subject'  => $ticket_subject,
			'content'  => $ticket_message,
			'name'     => $ticket_submitter_name,
			'email'    => $ticket_submitter_email,
			'metadata' => [],
		];
		foreach ( $fields_mapping as $field ) {
			$form_fields_to_ticket_fields[ $field['support_ticket_field'] ] = $fields_by_key[ $field['form_field'] ]['value'];
		}
		foreach ( $custom_fields_mapping as $field ) {
			$_value = $fields_by_key[ $field ]['value'];
			if ( empty( $_value ) ) {
				continue;
			}
			$form_fields_to_ticket_fields['metadata'][ $field ] = $fields_by_key[ $field ]['value'];
		}

		$request = new \WP_REST_Request();
		foreach ( $form_fields_to_ticket_fields as $key => $value ) {
			$request->set_param( $key, $value );
		}
		$response = ( new TicketController() )->create_item( $request );
		if ( 201 !== $response->get_status() ) {
			$data['errors']['form']['support_ticket'] = esc_html__( 'There was an error trying to create support ticket. Please try again later',
				'stackonet-support-ticket' );;
		}

		return $data;
	}

	/**
	 * Get action settings
	 *
	 * @return array[]
	 */
	private function get_action_settings(): array {
		$field_mapping_label = esc_html__( 'Optional Field Mapping', 'stackonet-support-ticket' );
		$field_mapping_label .= ' <a href="#" class="nf-add-new">' . esc_html__( 'Add New',
				'stackonet-support-ticket' ) . '</a>';

		$extra_field_mapping_label = esc_html__( 'Custom Fields', 'stackonet-support-ticket' );
		$extra_field_mapping_label .= ' <a href="#" class="nf-add-new">' . esc_html__( 'Add New',
				'stackonet-support-ticket' ) . '</a>';

		return [
			'ticket_subject'         => [
				'name'           => 'ticket_subject',
				'type'           => 'textbox',
				'group'          => 'primary',
				'label'          => esc_html__( 'Subject', 'stackonet-suppport-ticket' ),
				'placeholder'    => esc_attr__( 'Subject Text or search for a field', 'stackonet-suppport-ticket' ),
				'value'          => esc_textarea( __( 'Ninja Forms Submission', 'stackonet-suppport-ticket' ) ),
				'use_merge_tags' => true,
			],
			'ticket_message'         => [
				'name'           => 'ticket_message',
				'type'           => 'field-select',
				'group'          => 'primary',
				'field_types'    => [
					'textarea',
				],
				'label'          => esc_html__( 'Ticket Message Field', 'stackonet-support-ticket' ),
				'use_merge_tags' => true,
			],
			'ticket_submitter_email' => [
				'name'        => 'ticket_submitter_email',
				'type'        => 'field-select',
				'options'     => [],
				'group'       => 'primary',
				'field_types' => [
					'email'
				],
				'label'       => esc_html__( 'Email Address', 'stackonet-support-ticket' ),
				'value'       => '',
				'help'        => esc_html__( 'Not require for logged in user.', 'stackonet-support-ticket' ),
			],
			'ticket_submitter_name'  => [
				'name'        => 'ticket_submitter_name',
				'type'        => 'field-select',
				'options'     => [],
				'group'       => 'primary',
				'field_types' => [
					'textbox',
					'firstname',
					'lastname'
				],
				'label'       => esc_html__( 'Full Name', 'stackonet-support-ticket' ),
				'value'       => '',
				'help'        => esc_html__( 'Not require for logged in user.', 'stackonet-support-ticket' ),
			],
			'fields_mapping'         => [
				'name'     => 'fields_mapping',
				'type'     => 'option-repeater',
				'label'    => $field_mapping_label,
				'width'    => 'full',
				'group'    => 'primary',
				'tmpl_row' => 'nf-tmpl-support-ticket-field-mapping-repeater-row',
				'value'    => [],
				'columns'  => [
					'support_ticket_field' => [
						'header'  => esc_html__( 'Ticket Field', 'stackonet-support-ticket' ),
						'options' => $this->get_ticket_fields_options(),
					],
					'form_field'           => [
						'header'  => esc_html__( 'Form Field', 'stackonet-support-ticket' ),
						'options' => [],
					],
				],
			],
			'custom_fields_mapping'  => [
				'name'     => 'custom_fields_mapping',
				'type'     => 'option-repeater',
				'label'    => $extra_field_mapping_label,
				'width'    => 'full',
				'group'    => 'primary',
				'tmpl_row' => 'nf-tmpl-save-field-repeater-row',
				'value'    => [],
				'columns'  => [
					'form_field' => [
						'header'  => esc_html__( 'Form Field', 'stackonet-support-ticket' ),
						'default' => '',
						'options' => [],
					],
				],
			],
		];
	}


	/**
	 * Get ticket fields
	 *
	 * @return array[]
	 */
	private function get_ticket_fields_options(): array {
		return [
			[
				'label' => '--',
				'value' => '',
			],
			[
				'label' => 'Phone',
				'value' => 'phone_number',
			],
			[
				'label' => 'category',
				'value' => 'category',
			],
			[
				'label' => 'status',
				'value' => 'status',
			],
			[
				'label' => 'priority',
				'value' => 'priority',
			],
		];
	}
}
