<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$priority_id = isset( $_POST ) && isset( $_POST['priority_id'] ) ? intval( $_POST['priority_id'] ) : 0;
if ( ! $priority_id ) {
	exit;
}

$wpsc_default_ticket_priority = get_option( 'wpsc_default_ticket_priority' );
if ( $wpsc_default_ticket_priority == $priority_id ) {
	echo '{ "sucess_status":"0","messege":"' . __( 'Default ticket priority can not be deleted.', 'supportcandy' ) . '" }';
	die();
}

wp_delete_term( $priority_id, 'wpsc_priorities' );

do_action( 'wpsc_delete_priority', $cat_id );

echo '{ "sucess_status":"1","messege":"' . __( 'Priority deleted successfully.', 'supportcandy' ) . '" }';
