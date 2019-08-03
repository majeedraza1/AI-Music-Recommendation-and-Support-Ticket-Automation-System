<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$field_id = isset( $_POST ) && isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;
if ( ! $field_id ) {
	exit;
}

$custom_field = get_term_by( 'id', $field_id, 'wpsc_ticket_custom_fields' );

$field_label = isset( $_POST ) && isset( $_POST['field_label'] ) ? sanitize_text_field( $_POST['field_label'] ) : '';
if ( ! $field_label ) {
	exit;
}

$extra_info = isset( $_POST ) && isset( $_POST['extra_info'] ) ? sanitize_text_field( $_POST['extra_info'] ) : '';

$personal_info = isset( $_POST ) && isset( $_POST['personal_info'] ) ? intval( $_POST['personal_info'] ) : '1';

$field_type = isset( $_POST ) && isset( $_POST['field_type'] ) ? intval( $_POST['field_type'] ) : '';
if ( ! $field_type ) {
	exit;
}

$field_options = isset( $_POST ) && isset( $_POST['field_options'] ) ? explode( "\n", $_POST['field_options'] ) : array();
$field_types   = $wpscfunction->get_custom_field_types();
if ( $field_types[ $field_type ]['has_options'] == 1 && ! $field_options ) {
	exit;
}

foreach ( $field_options as $key => $value ) {
	$field_options[ $key ] = trim( sanitize_text_field( $value ) );
}

$required = isset( $_POST ) && isset( $_POST['required'] ) ? intval( $_POST['required'] ) : '1';

$width = isset( $_POST ) && isset( $_POST['width'] ) ? sanitize_text_field( $_POST['width'] ) : '1';

$visibility = isset( $_POST ) && isset( $_POST['visibility'] ) ? $_POST['visibility'] : array();
foreach ( $visibility as $key => $value ) {
	$visibility[ $key ] = sanitize_text_field( $value );
}

$old_tf_type = get_term_meta( $field_id, 'wpsc_tf_type', true );

update_term_meta( $field_id, 'wpsc_tf_label', $field_label );
update_term_meta( $field_id, 'wpsc_tf_extra_info', $extra_info );
update_term_meta( $field_id, 'wpsc_tf_type', $field_type );
update_term_meta( $field_id, 'wpsc_tf_options', $field_options );
update_term_meta( $field_id, 'wpsc_tf_required', $required );
update_term_meta( $field_id, 'wpsc_tf_width', $width );
update_term_meta( $field_id, 'wpsc_tf_visibility', $visibility );
update_term_meta( $field_id, 'wpsc_tf_personal_info', $personal_info );

if ( $old_tf_type != $field_type ) {
	if ( $field_types[ $field_type ]['allow_ticket_list'] ) {
		update_term_meta( $field_id, 'wpsc_allow_ticket_list', '1' );
		update_term_meta( $field_id, 'wpsc_customer_ticket_list_status', '0' );
		update_term_meta( $field_id, 'wpsc_agent_ticket_list_status', '0' );
	} else {
		update_term_meta( $field_id, 'wpsc_allow_ticket_list', '0' );
	}
	if ( $field_types[ $field_type ]['allow_ticket_filter'] ) {
		update_term_meta( $field_id, 'wpsc_allow_ticket_filter', '1' );
		update_term_meta( $field_id, 'wpsc_ticket_filter_type', $field_types[ $field_type ]['ticket_filter_type'] );
		update_term_meta( $field_id, 'wpsc_customer_ticket_filter_status', '0', '0' );
		update_term_meta( $field_id, 'wpsc_agent_ticket_filter_status', '0' );
	} else {
		update_term_meta( $field_id, 'wpsc_allow_ticket_filter', '0' );
	}
	if ( $field_types[ $field_type ]['allow_orderby'] ) {
		update_term_meta( $field_id, 'wpsc_allow_orderby', '1' );
	} else {
		update_term_meta( $field_id, 'wpsc_allow_orderby', '0' );
		$agent_orderby = get_option( 'wpsc_tl_agent_orderby' );
		if ( $custom_field->slug == $agent_orderby ) {
			update_option( 'wpsc_tl_agent_orderby', 'date_updated' );
			update_option( 'wpsc_tl_agent_orderby_order', 'DESC' );
		}
		$customer_orderby = get_option( 'wpsc_tl_customer_orderby' );
		if ( $custom_field->slug == $customer_orderby ) {
			update_option( 'wpsc_tl_customer_orderby', 'date_updated' );
			update_option( 'wpsc_tl_customer_orderby_order', 'DESC' );
		}
	}
}

do_action( 'wpsc_set_edit_form_field', $field_id );

echo '{ "sucess_status":"1","messege":"' . __( 'updated successfully.', 'supportcandy' ) . '" }';
