<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}
// Allow ticket url Permissions
$wpsc_ticket_url_permission = isset( $_POST ) && isset( $_POST['wpsc_ticket_url_permission'] ) ? sanitize_text_field( $_POST['wpsc_ticket_url_permission'] ) : '0';
update_option( 'wpsc_ticket_url_permission', $wpsc_ticket_url_permission );

$wpsc_allow_sign_out = isset( $_POST ) && isset( $_POST['wpsc_sign_out'] ) ? sanitize_text_field( $_POST['wpsc_sign_out'] ) : '';
update_option( 'wpsc_sign_out', $wpsc_allow_sign_out );

$wpsc_guest_can_upload_files = isset( $_POST ) && isset( $_POST['wpsc_guest_can_upload_files'] ) ? sanitize_text_field( $_POST['wpsc_guest_can_upload_files'] ) : '';
update_option( 'wpsc_guest_can_upload_files', $wpsc_guest_can_upload_files );

// Public MOde
$wpsc_ticket_public_mode = isset( $_POST ) && isset( $_POST['wpsc_ticket_public_mode'] ) ? sanitize_text_field( $_POST['wpsc_ticket_public_mode'] ) : '0';
update_option( 'wpsc_ticket_public_mode', $wpsc_ticket_public_mode );

$wpsc_show_and_hide_filters = isset( $_POST ) && isset( $_POST['wpsc_show_and_hide_filters'] ) ? sanitize_text_field( $_POST['wpsc_show_and_hide_filters'] ) : '';
update_option( 'wpsc_show_and_hide_filters', $wpsc_show_and_hide_filters );

$wpsc_allow_reply_confirmation = isset( $_POST ) && isset( $_POST['wpsc_allow_reply_confirmation'] ) ? sanitize_text_field( $_POST['wpsc_allow_reply_confirmation'] ) : '0';
update_option( 'wpsc_allow_reply_confirmation', $wpsc_allow_reply_confirmation );

$wpsc_tinymce_toolbar_active = isset( $_POST ) && isset( $_POST['wpsc_tinymce_toolbar'] ) ? $wpscfunction->sanitize_array( $_POST['wpsc_tinymce_toolbar'] ) : array();
update_option( 'wpsc_tinymce_toolbar_active', $wpsc_tinymce_toolbar_active );

$wpsc_thread_date_format = isset( $_POST ) && isset( $_POST['wpsc_thread_date_format'] ) ? sanitize_text_field( $_POST['wpsc_thread_date_format'] ) : 'string';
update_option( 'wpsc_thread_date_format', $wpsc_thread_date_format );

$wpsc_thread_date_time_format = isset( $_POST ) && isset( $_POST['wpsc_thread_date_time_format'] ) ? sanitize_text_field( $_POST['wpsc_thread_date_time_format'] ) : 'string';
update_option( 'wpsc_thread_date_time_format', $wpsc_thread_date_time_format );

$wpsc_notify = isset( $_POST ) && isset( $_POST['wpsc_notify'] ) ? sanitize_text_field( $_POST['wpsc_notify'] ) : '1';
update_option( 'wpsc_do_not_notify_setting', $wpsc_notify );

$wpsc_notify_checkbox = isset( $_POST ) && isset( $_POST['wpsc_notify_checkbox'] ) ? sanitize_text_field( $_POST['wpsc_notify_checkbox'] ) : '1';
update_option( 'wpsc_default_do_not_notify_option', $wpsc_notify_checkbox );

$wpsc_allow_attach_active = isset( $_POST ) && isset( $_POST['wpsc_allow_attach'] ) ? $wpscfunction->sanitize_array( $_POST['wpsc_allow_attach'] ) : array();
update_option( 'wpsc_allow_attachment', $wpsc_allow_attach_active );

$wpsc_hide_show_priority = isset( $_POST ) && isset( $_POST['wpsc_hide_show_priority'] ) ? sanitize_text_field( $_POST['wpsc_hide_show_priority'] ) : '';
update_option( 'wpsc_hide_show_priority', $wpsc_hide_show_priority );

$wpsc_ticket_id_type = isset( $_POST ) && isset( $_POST['wpsc_ticket_id_type'] ) ? sanitize_text_field( $_POST['wpsc_ticket_id_type'] ) : '0';
update_option( 'wpsc_ticket_id_type', $wpsc_ticket_id_type );

$wpsc_view_more = isset( $_POST ) && isset( $_POST['wpsc_view_more'] ) ? sanitize_text_field( $_POST['wpsc_view_more'] ) : '';
update_option( 'wpsc_view_more', $wpsc_view_more );

$wpsc_thread_limit = isset( $_POST ) && isset( $_POST['wpsc_thread_limit'] ) ? sanitize_text_field( $_POST['wpsc_thread_limit'] ) : '';
update_option( 'wpsc_thread_limit', $wpsc_thread_limit );

do_action( 'wpsc_set_advanced_settings' );

echo '{ "sucess_status":"1","messege":"' . __( 'Settings saved.', 'supportcandy' ) . '" }';