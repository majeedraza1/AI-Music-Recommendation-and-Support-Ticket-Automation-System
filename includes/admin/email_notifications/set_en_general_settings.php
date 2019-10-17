<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

// From Name
$from_name = isset( $_POST ) && isset( $_POST['support_ticket_notification_from_name'] ) ? sanitize_text_field( $_POST['support_ticket_notification_from_name'] ) : '';
update_option( 'support_ticket_notification_from_name', $from_name );

// From Email
$from_email = isset( $_POST ) && isset( $_POST['support_ticket_notification_from_email'] ) ? sanitize_text_field( $_POST['support_ticket_notification_from_email'] ) : '';
update_option( 'support_ticket_notification_from_email', $from_email );

// Reply To
$reply_to = isset( $_POST ) && isset( $_POST['support_ticket_notification_reply_to'] ) ? sanitize_text_field( $_POST['support_ticket_notification_reply_to'] ) : '';
update_option( 'support_ticket_notification_reply_to', $reply_to );

$ignore_emails = isset( $_POST ) && isset( $_POST['support_ticket_notification_ignore_emails'] ) ? explode( "\n", $_POST['support_ticket_notification_ignore_emails'] ) : array();
$ignore_emails = $wpscfunction->sanitize_array( $ignore_emails );
update_option( 'support_ticket_notification_ignore_emails', $ignore_emails );

do_action( 'wpsc_set_en_gerneral_settings' );

echo '{ "sucess_status":"1","messege":"' . __( 'Settings saved.', 'supportcandy' ) . '" }';
