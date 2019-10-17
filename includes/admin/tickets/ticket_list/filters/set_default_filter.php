<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;

if ( ! $current_user->ID ) {
	die();
}

$label_key = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '';
if ( ! $label_key ) {
	die();
}

$labels = $wpscfunction->get_ticket_filter_labels();
if ( ! ( ( $current_user->has_cap( 'wpsc_agent' ) && $labels[ $label_key ]['visibility'] == 'agent' ) || ( ! $current_user->has_cap( 'wpsc_agent' ) && $labels[ $label_key ]['visibility'] == 'customer' ) || $labels[ $label_key ]['visibility'] == 'both' ) ) {
	die();
}

$filter = $wpscfunction->get_default_filter();

$filter['label'] = $label_key;

$query = array();

switch ( $label_key ) {
	case 'all':
	case 'deleted':
		$query = apply_filters( 'wpsc_filter_label_all', $query, $label_key );
		break;

	case 'unresolved_agent':
		$unresolved_agent = get_option( 'wpsc_tl_agent_unresolve_statuses' );
		$query[]          = array(
			'key'     => 'ticket_status',
			'value'   => $unresolved_agent,
			'compare' => 'IN'
		);
		$query            = apply_filters( 'wpsc_filter_label_unresolved_agent', $query, $label_key );
		break;

	case 'unresolved_customer':
		$unresolved_customer = get_option( 'wpsc_tl_customer_unresolve_statuses' );
		$query[]             = array(
			'key'     => 'ticket_status',
			'value'   => $unresolved_customer,
			'compare' => 'IN'
		);
		$query               = apply_filters( 'wpsc_filter_label_unresolved_customer', $query, $label_key );
		break;

	case 'unassigned':
		$query[] = array(
			'key'     => 'assigned_agent',
			'value'   => 0,
			'compare' => '='
		);
		$query   = apply_filters( 'wpsc_filter_label_unassigned', $query, $label_key );
		break;

	case 'mine':
		$agents = get_terms( [
			'taxonomy'   => 'support_agent',
			'hide_empty' => false,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'user_id',
					'value'   => $current_user->ID,
					'compare' => '='
				)
			),
		] );
		if ( $agents ) {
			$query[] = array(
				'key'     => 'assigned_agent',
				'value'   => $agents[0]->term_id,
				'compare' => '='
			);
		}
		$query = apply_filters( 'wpsc_filter_label_mine', $query, $label_key );
		break;

	case 'closed':
		$close_status = get_option( 'wpsc_close_ticket_status' );
		$query[]      = array(
			'key'     => 'ticket_status',
			'value'   => $close_status,
			'compare' => '='
		);
		$query        = apply_filters( 'wpsc_filter_label_closed', $query, $label_key );
		break;

	default:
		$query = apply_filters( 'wpsc_filter_label_default', $query, $label_key );
		break;
}

$filter['query'] = $query;

setcookie( 'support_ticket_filter', json_encode( $filter ) );
