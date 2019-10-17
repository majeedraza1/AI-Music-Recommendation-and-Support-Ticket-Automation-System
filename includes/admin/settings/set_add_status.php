<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$status_name = isset( $_POST ) && isset( $_POST['status_name'] ) ? sanitize_text_field( $_POST['status_name'] ) : '';
if ( ! $status_name ) {
	exit;
}

$status_color = isset( $_POST ) && isset( $_POST['status_color'] ) ? sanitize_text_field( $_POST['status_color'] ) : '';
if ( ! $status_color ) {
	exit;
}

$status_bg_color = isset( $_POST ) && isset( $_POST['status_bg_color'] ) ? sanitize_text_field( $_POST['status_bg_color'] ) : '';
if ( ! $status_bg_color ) {
	exit;
}

if ( $status_color == $status_bg_color ) {
	echo '{ "sucess_status":"0","messege":"' . __( 'Status color and background color should not be same.', 'supportcandy' ) . '" }';
	die();
}

$term = wp_insert_term( $status_name, 'ticket_status' );
if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
	$load_order = $wpdb->get_var( "select max(meta_value) as load_order from {$wpdb->prefix}termmeta WHERE meta_key='support_ticket_status_menu_order'" );
	add_term_meta( $term['term_id'], 'support_ticket_status_menu_order', ++ $load_order );
	add_term_meta( $term['term_id'], 'wpsc_status_color', $status_color );
	add_term_meta( $term['term_id'], 'wpsc_status_background_color', $status_bg_color );
	do_action( 'wpsc_set_add_status', $term['term_id'] );
	echo '{ "sucess_status":"1","messege":"' . __( 'Status added successfully.', 'supportcandy' ) . '" }';
} else {
	echo '{ "sucess_status":"0","messege":"' . __( 'An error occured while creating status.', 'supportcandy' ) . '" }';
}
