<?php

namespace StackonetSupportTicket\Integration\NinjaForms;

use StackonetSupportTicket\Integration\NinjaForms\Actions\AddToSupportTicket;
use StackonetSupportTicket\Integration\NinjaForms\Fields\TicketCategory;

/**
 * Class Module
 *
 * @package StackonetSupportTicket\Integration\NinjaForms
 */
class Module {
	/**
	 * The instance of the class
	 *
	 * @var Module
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

			// add_filter( 'ninja_forms_field_type_sections', [ self::$instance, 'field_type_sections' ], 99 );
			// add_filter( 'ninja_forms_register_fields', [ self::$instance, 'register_fields' ], 99 );
			add_filter( 'ninja_forms_register_actions', [ self::$instance, 'register_actions' ], 99 );
			add_action( 'ninja_forms_builder_templates', [ self::$instance, 'load_custom_row_template' ] );
		}

		return self::$instance;
	}

	/**
	 * Register actions
	 *
	 * @param  array  $actions  Actions.
	 *
	 * @return array
	 */
	public function register_actions( array $actions ): array {
		$actions['stackonet_add_to_support_ticket'] = new AddToSupportTicket();

		return $actions;
	}

	/**
	 * Register custom fields
	 *
	 * @param  array  $fields  The fields.
	 *
	 * @return array
	 */
	public function register_fields( array $fields ): array {
		$fields['ticket_category'] = new TicketCategory();

		return $fields;
	}

	public function field_type_sections( array $sections ): array {
		$sections['support_ticket'] = [
			'id'         => 'support_ticket',
			'nicename'   => esc_html__( 'Support Ticket', 'stackonet-support-ticket' ),
			'fieldTypes' => array(),
		];

		return $sections;
	}

	public function load_custom_row_template() {
		?>
        <script id="nf-tmpl-support-ticket-field-mapping-repeater-row" type="text/template">
            <div>
                <span class="dashicons dashicons-menu handle"></span>
            </div>
            <div>
                <select data-id="support_ticket_field" class="setting">
                    <#
                    _.each( data.options.support_ticket_field, function( option ) {
                    #>
                    <option value="{{{ option.value }}}" {{{ ( data.support_ticket_field== option.value ) ?
                    'selected="selected"' : '' }}}>{{{option.label }}}</option>
                    <#
                    } );
                    #>
                </select>
            </div>

            <div>
                <# try { #>
                {{{data.renderNonSaveFieldSelect('form_field', data.form_field) }}}
                <# } catch ( err ) { #>
                <input type="text" class="setting" value="{{{ data.form_field }}}" data-id="form_field">
                <# } #>
            </div>

            <div>
                <span class="dashicons dashicons-dismiss nf-delete"></span>
            </div>
        </script>
		<?php
	}
}
