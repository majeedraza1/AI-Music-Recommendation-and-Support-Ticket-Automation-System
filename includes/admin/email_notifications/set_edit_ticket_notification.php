<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$term_id = isset( $_POST ) && isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
if ( ! $term_id ) {
	die();
}

// Title
$title = isset( $_POST ) && isset( $_POST['support_ticket_notification_title'] ) ? sanitize_text_field( $_POST['support_ticket_notification_title'] ) : '';
wp_update_term( $term_id, 'support_ticket_notification', array( 'name' => $title ) );

$type = isset( $_POST ) && isset( $_POST['support_ticket_notification_type'] ) ? sanitize_text_field( $_POST['support_ticket_notification_type'] ) : '';
update_term_meta( $term_id, 'type', $type );

$subject = isset( $_POST ) && isset( $_POST['support_ticket_notification_subject'] ) ? sanitize_text_field( $_POST['support_ticket_notification_subject'] ) : '';
update_term_meta( $term_id, 'subject', $subject );

$body = isset( $_POST ) && isset( $_POST['support_ticket_notification_body'] ) ? wp_kses_post( $_POST['support_ticket_notification_body'] ) : '';
update_term_meta( $term_id, 'body', $body );

$recipients = isset( $_POST ) && isset( $_POST['support_ticket_notification_recipients'] ) ? $wpscfunction->sanitize_array( $_POST['support_ticket_notification_recipients'] ) : array();
update_term_meta( $term_id, 'recipients', $recipients );

$extra_recipients = isset( $_POST ) && isset( $_POST['support_ticket_notification_extra_recipients'] ) ? explode( "\n", $_POST['support_ticket_notification_extra_recipients'] ) : array();
$extra_recipients = $wpscfunction->sanitize_array( $extra_recipients );
update_term_meta( $term_id, 'extra_recipients', $extra_recipients );

$conditions = isset( $_POST ) && isset( $_POST['conditions'] ) && $_POST['conditions'] != '[]' ? sanitize_text_field( $_POST['conditions'] ) : '';
update_term_meta( $term_id, 'conditions', $conditions );

do_action( 'wpsc_set_edit_ticket_notification', $term_id );
echo '{ "sucess_status":"1","messege":"' . __( 'Email Notification updated successfully.', 'supportcandy' ) . '" }';
