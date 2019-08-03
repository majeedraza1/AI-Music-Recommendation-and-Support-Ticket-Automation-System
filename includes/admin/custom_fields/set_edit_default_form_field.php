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

$field_label = isset( $_POST ) && isset( $_POST['field_label'] ) ? sanitize_text_field( $_POST['field_label'] ) : '';
if ( ! $field_label ) {
	exit;
}

$extra_info = isset( $_POST ) && isset( $_POST['extra_info'] ) ? sanitize_text_field( $_POST['extra_info'] ) : '';
$width      = isset( $_POST ) && isset( $_POST['width'] ) ? sanitize_text_field( $_POST['width'] ) : '1';
$status     = isset( $_POST ) && isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '1';

update_term_meta( $field_id, 'wpsc_tf_label', $field_label );
update_term_meta( $field_id, 'wpsc_tf_extra_info', $extra_info );
update_term_meta( $field_id, 'wpsc_tf_width', $width );
update_term_meta( $field_id, 'wpsc_tf_status', $status );

do_action( 'set_edit_default_form_field', $field_id );

echo '{ "sucess_status":"1","messege":"' . __( 'updated successfully.', 'supportcandy' ) . '" }';
