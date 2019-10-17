<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;

if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$ticket_widget_id = isset( $_POST ) && isset( $_POST['ticket_widget_id'] ) ? sanitize_text_field( $_POST['ticket_widget_id'] ) : '';
if ( ! $ticket_widget_id ) {
	exit;
}

$ticket_widget_name = isset( $_POST ) && isset( $_POST['ticket_widget_name'] ) ? sanitize_text_field( $_POST['ticket_widget_name'] ) : '';
if ( ! $ticket_widget_name ) {
	exit;
}

update_term_meta( $ticket_widget_id, 'wpsc_label', $ticket_widget_name );

update_term_meta( $ticket_widget_id, 'support_ticket_widget_type', sanitize_text_field( $_POST['ticket_widget_type'] ) );

$support_ticket_widget_role = isset( $_POST ) && isset( $_POST['ticket_widget_role'] ) ? $wpscfunction->sanitize_array( $_POST['ticket_widget_role'] ) : array();
update_term_meta( $ticket_widget_id, 'support_ticket_widget_role', $support_ticket_widget_role );

do_action( 'wpsc_set_edit_ticket_widget', $ticket_widget_id );

echo '{ "success_status":"1","messege":"' . __( 'Settings saved.', 'supportcandy' ) . '" }';