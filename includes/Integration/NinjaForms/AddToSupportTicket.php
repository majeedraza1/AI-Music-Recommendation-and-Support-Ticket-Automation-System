<?php

namespace StackonetSupportTicket\Integration\NinjaForms;

use NF_Abstracts_Action;
use Stackonet\WP\Framework\Supports\Logger;

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

		$this->_nicename = esc_html__( 'Stackonet: Add to Support Ticket', 'stackonet-support-ticket' );

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
		$fields_mapping = $action_settings['fields_mapping'] ?? [];
		$fields_by_key  = $data['fields_by_key'] ?? [];

		$form_fields_to_ticket_fields = [];
		foreach ( $fields_mapping as $field ) {
			$form_fields_to_ticket_fields[ $field['support_ticket_field'] ] = $fields_by_key[ $field['form_field'] ]['value'];
		}
		Logger::log( $form_fields_to_ticket_fields );
		// TODO: Add to support ticket

		return $data;
	}

	/**
	 * Get action settings
	 *
	 * @return array[]
	 */
	private function get_action_settings(): array {
		$label = esc_html__( 'Field Mapping', 'stackonet-support-ticket' );
		$label .= ' <a href="#" class="nf-add-new">' . esc_html__( 'Add New', 'stackonet-support-ticket' ) . '</a>';

		return [
			'fields_mapping' => [
				'name'     => 'fields_mapping',
				'type'     => 'option-repeater',
				'label'    => $label,
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
				'label' => 'Subject',
				'value' => 'subject',
			],
			[
				'label' => 'Content',
				'value' => 'content',
			],
			[
				'label' => 'Name',
				'value' => 'name',
			],
			[
				'label' => 'Email',
				'value' => 'email',
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
			[
				'label' => 'attachments',
				'value' => 'attachments',
			],
		];
	}
}
