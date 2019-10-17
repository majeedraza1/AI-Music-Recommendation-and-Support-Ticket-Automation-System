<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$field        = get_term_by( 'id', $field_id, 'support_ticket_custom_fields' );
$wpsc_tf_type = get_term_meta( $field_id, 'wpsc_tf_type', true );

if ( ! $wpsc_tf_type ) {

	switch ( $field->slug ) {

		case 'ticket_status':
			$status = get_term_by( 'id', $val, 'ticket_status' );
			$val    = $status->name;
			break;

		case 'ticket_priority':
			$priority = get_term_by( 'id', $val, 'ticket_priority' );
			$val      = $priority->name;
			break;

		case 'ticket_category':
			$category = get_term_by( 'id', $val, 'ticket_category' );
			$val      = $category->name;
			break;

	}

}