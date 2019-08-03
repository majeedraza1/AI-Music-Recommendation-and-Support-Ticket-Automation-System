<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

// Title
$title = isset( $_POST ) && isset( $_POST['wpsc_en_title'] ) ? sanitize_text_field( $_POST['wpsc_en_title'] ) : '';
$term  = wp_insert_term( $title, 'wpsc_en' );
if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {

	$type = isset( $_POST ) && isset( $_POST['wpsc_en_type'] ) ? sanitize_text_field( $_POST['wpsc_en_type'] ) : '';
	add_term_meta( $term['term_id'], 'type', $type );

	$subject = isset( $_POST ) && isset( $_POST['wpsc_en_subject'] ) ? sanitize_text_field( $_POST['wpsc_en_subject'] ) : '';
	add_term_meta( $term['term_id'], 'subject', $subject );

	$body = isset( $_POST ) && isset( $_POST['wpsc_en_body'] ) ? wp_kses_post( $_POST['wpsc_en_body'] ) : '';
	add_term_meta( $term['term_id'], 'body', $body );

	$recipients = isset( $_POST ) && isset( $_POST['wpsc_en_recipients'] ) ? $wpscfunction->sanitize_array( $_POST['wpsc_en_recipients'] ) : array();
	add_term_meta( $term['term_id'], 'recipients', $recipients );

	$extra_recipients = isset( $_POST ) && isset( $_POST['wpsc_en_extra_recipients'] ) ? explode( "\n", $_POST['wpsc_en_extra_recipients'] ) : array();
	$extra_recipients = $wpscfunction->sanitize_array( $extra_recipients );
	add_term_meta( $term['term_id'], 'extra_recipients', $extra_recipients );

	$conditions = isset( $_POST ) && isset( $_POST['conditions'] ) && $_POST['conditions'] != '[]' ? sanitize_text_field( $_POST['conditions'] ) : '';
	add_term_meta( $term['term_id'], 'conditions', $conditions );

	do_action( 'wpsc_set_add_ticket_notification', $term );
	echo '{ "sucess_status":"1","messege":"' . __( 'Email Notification added successfully.', 'supportcandy' ) . '" }';

} else {
	echo '{ "sucess_status":"0","messege":"' . __( 'An error occured while creating email notification.', 'supportcandy' ) . '" }';
}
