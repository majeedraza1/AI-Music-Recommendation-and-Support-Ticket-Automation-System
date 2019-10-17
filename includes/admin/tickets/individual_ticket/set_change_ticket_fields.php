<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction, $wpdb;

$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
if ( ! $ticket_id ) {
	die();
}

$fields = get_terms( [
	'taxonomy'   => 'support_ticket_custom_fields',
	'hide_empty' => false,
	'orderby'    => 'meta_value_num',
	'meta_key'   => 'wpsc_tf_visibility',
	'order'      => 'ASC',
	'meta_query' => array(
		array(
			'key'     => 'agentonly',
			'value'   => '0',
			'compare' => '='
		)
	),
] );

foreach ( $fields as $field ) {
	$wpsc_tf_type = get_term_meta( $field->term_id, 'wpsc_tf_type', true );
	if ( $wpsc_tf_type == 3 || $wpsc_tf_type == 10 ) {
		$value = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug );
		if ( $wpscfunction->has_permission( 'change_ticket_fields', $ticket_id ) && isset( $_POST[ $field->slug ] ) ) {

			if ( $value != $_POST[ $field->slug ] ) {
				$wpscfunction->change_field( $ticket_id, $field->slug, $_POST[ $field->slug ] );
			}

			if ( $wpsc_tf_type == 10 ) {
				$attachment  = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug );
				$attachments = array();
				foreach ( $attachment as $key => $value ) {
					$attachment_id = intval( $value );
					$attachments[] = $attachment_id;
					update_term_meta( $attachment_id, 'active', '1' );
				}
			}


		} else if ( $wpscfunction->has_permission( 'change_ticket_fields', $ticket_id ) && ! isset( $_POST[ $field->slug ] ) && $value ) {
			$wpscfunction->delete_ticket_meta( $ticket_id, $field->slug );
			$log_str = sprintf( __( '%1$s removed %2$s', 'supportcandy' ), '<strong>' . $current_user->display_name . '</strong>', '<strong>' . $field->name . '</strong>' );
			$args    = array(
				'ticket_id'   => $ticket_id,
				'reply_body'  => $log_str,
				'thread_type' => 'log'
			);
			$args    = apply_filters( 'wpsc_thread_args', $args );
			$wpscfunction->submit_ticket_thread( $args );

		}
	} else if ( $wpsc_tf_type == 6 ) {
		$value             = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
		$prev_fields_value = $wpscfunction->datetimeToCalenderFormat( $value );
		if ( $wpscfunction->has_permission( 'change_ticket_fields', $ticket_id ) && isset( $_POST[ $field->slug ] ) && $_POST[ $field->slug ] ) {
			$fields_value_new = $wpscfunction->calenderDateFormatToDateTime( $_POST[ $field->slug ] );
			if ( ! $value ) {
				$wpscfunction->add_ticket_meta( $ticket_id, $field->slug, $fields_value_new );
			}
			if ( $value != $fields_value_new ) {
				$wpscfunction->update_ticket_meta( $ticket_id, $field->slug, array( 'meta_value' => $fields_value_new ) );
			}
			if ( $prev_fields_value ) {
				$log_str = sprintf( __( '%1$s changed %2$s from %3$s to %4$s', 'supportcandy' ), '<strong>' . $current_user->display_name . '</strong>', '<strong>' . $field->name . '</strong>', '<strong>' . $prev_fields_value . '</strong>', '<strong>' . $_POST[ $field->slug ] . '</strong>' );
			} else {
				$log_str = sprintf( __( '%1$s changed %2$s to %3$s', 'supportcandy' ), '<strong>' . $current_user->display_name . '</strong>', '<strong>' . $field->name . '</strong>', '<strong>' . $_POST[ $field->slug ] . '</strong>' );
			}

			$args = array(
				'ticket_id'   => $ticket_id,
				'reply_body'  => $log_str,
				'thread_type' => 'log'
			);
			$args = apply_filters( 'wpsc_thread_args', $args );

			$wpscfunction->submit_ticket_thread( $args );

		} else if ( $wpscfunction->has_permission( 'change_ticket_fields', $ticket_id ) && $value ) {
			$wpscfunction->delete_ticket_meta( $ticket_id, $field->slug );
			$log_str = sprintf( __( '%1$s removed %2$s', 'supportcandy' ), '<strong>' . $current_user->display_name . '</strong>', '<strong>' . $field->name . '</strong>' );
			$args    = array(
				'ticket_id'   => $ticket_id,
				'reply_body'  => $log_str,
				'thread_type' => 'log'
			);
			$args    = apply_filters( 'wpsc_thread_args', $args );
			$wpscfunction->submit_ticket_thread( $args );
		}
	} else {
		$value = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
		if ( $wpscfunction->has_permission( 'change_ticket_fields', $ticket_id ) && isset( $_POST[ $field->slug ] ) && $_POST[ $field->slug ] ) {

			if ( $value != $_POST[ $field->slug ] ) {
				$wpscfunction->change_field( $ticket_id, $field->slug, $_POST[ $field->slug ] );
			}
		} else if ( $wpscfunction->has_permission( 'change_ticket_fields', $ticket_id ) && $value ) {
			$wpscfunction->delete_ticket_meta( $ticket_id, $field->slug );
			$log_str = sprintf( __( '%1$s removed %2$s', 'supportcandy' ), '<strong>' . $current_user->display_name . '</strong>', '<strong>' . $field->name . '</strong>' );
			$args    = array(
				'ticket_id'   => $ticket_id,
				'reply_body'  => $log_str,
				'thread_type' => 'log'
			);
			$args    = apply_filters( 'wpsc_thread_args', $args );
			$wpscfunction->submit_ticket_thread( $args );
		}
	}
	do_action( 'wpsc_set_change_ticket_field', $field, $ticket_id, $wpsc_tf_type );
}
$wpdb->update( $wpdb->prefix . 'support_ticket', array( 'date_updated' => date( "Y-m-d H:i:s" ) ), array( 'id' => $ticket_id ) );