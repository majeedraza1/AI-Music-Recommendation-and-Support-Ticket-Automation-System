<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb, $wpscfunction;
if ( ! ( $current_user->ID ) ) {
	exit;
}

$term       = isset( $_REQUEST ) && isset( $_REQUEST['term'] ) ? sanitize_text_field( $_REQUEST['term'] ) : '';
$field_slug = isset( $_REQUEST ) && isset( $_REQUEST['field'] ) ? sanitize_text_field( $_REQUEST['field'] ) : '';
if ( $field_slug == 'ticket_id' ) {
	$field_slug = 'id';
}
$output = array();

switch ( $field_slug ) {

	case 'ticket_status':

		$statuses = get_terms( [
			'taxonomy'   => 'wpsc_statuses',
			'hide_empty' => false,
			'search'     => $term,
		] );
		foreach ( $statuses as $status ) {
			$output[] = array(
				'label'    => $status->name,
				'value'    => '',
				'flag_val' => $status->term_id,
				'slug'     => $field_slug,
			);
		}
		break;

	case 'ticket_category':

		$categories = get_terms( [
			'taxonomy'   => 'wpsc_categories',
			'hide_empty' => false,
			'search'     => $term,
		] );
		foreach ( $categories as $category ) {
			$output[] = array(
				'label'    => $category->name,
				'value'    => '',
				'flag_val' => $category->term_id,
				'slug'     => $field_slug,
			);
		}
		break;

	case 'ticket_priority':

		$priorities = get_terms( [
			'taxonomy'   => 'wpsc_priorities',
			'hide_empty' => false,
			'search'     => $term,
		] );
		foreach ( $priorities as $priority ) {
			$output[] = array(
				'label'    => $priority->name,
				'value'    => '',
				'flag_val' => $priority->term_id,
				'slug'     => $field_slug,
			);
		}
		break;

	case 'assigned_agent':
	case 'agent_created':

		$agents = get_terms( [
			'taxonomy'   => 'wpsc_agents',
			'hide_empty' => false,
			'number'     => 5,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => 'label',
					'value'   => $term,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'first_name',
					'value'   => $term,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'last_name',
					'value'   => $term,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'nicename',
					'value'   => $term,
					'compare' => 'LIKE'
				),
				array(
					'key'     => 'email',
					'value'   => $term,
					'compare' => 'LIKE'
				)
			),
		] );

		foreach ( $agents as $agent ) {
			$agent_name = get_term_meta( $agent->term_id, 'label', true );
			$output[]   = array(
				'label'    => $agent_name,
				'value'    => '',
				'flag_val' => $agent->term_id,
				'slug'     => $field_slug,
			);
		}
		break;

	default:

		$output = apply_filters( 'wpsc_filter_autocomplete', $output, $term, $field_slug );
		if ( ! $output ) {

			$get_all_meta_keys = $wpscfunction->get_all_meta_keys();

			$sql = "SELECT DISTINCT t.*  FROM " . $wpdb->prefix . "wpsc_ticket t ";

			$join_str = '';

			$join = array();

			$where = '';

			if ( in_array( $field_slug, $get_all_meta_keys ) ) {

				if ( $term ) {

					$join[] = $field_slug;
					$alice  = str_replace( '-', '_', $field_slug );
					$where  .= " WHERE " . $alice . ".meta_value LIKE '$term%'";

				}

			} else {

				if ( $term ) {

					$where .= " WHERE t.$field_slug LIKE '$term%' ";
				}
			}

			$limit = "  LIMIT " . '5' . "  OFFSET " . '0';

			foreach ( $join as $slug ) {
				$alice    = str_replace( '-', '_', $slug );
				$join_str = "JOIN {$wpdb->prefix}wpsc_ticketmeta " . $alice . " ON t.id = " . $alice . ".ticket_id AND " . $alice . ".meta_key = '" . $slug . "' ";
			}

			//combining query
			$sql = $sql . $join_str . $where . $limit;

			$ticket_data = $wpdb->get_results( $sql );

			$tickets = json_decode( json_encode( $ticket_data ), true );

			foreach ( $tickets as $ticket ) {
				if ( in_array( $field_slug, $get_all_meta_keys ) ) {
					$result = $wpscfunction->get_ticket_meta( $ticket['id'], $field_slug, true );
				} else {
					$result = $wpscfunction->get_ticket_fields( $ticket['id'], $field_slug );
				}
				if ( ! in_array( $result, $output ) ) {
					$output[] = array(
						'label'    => $result,
						'value'    => '',
						'flag_val' => $result,
						'slug'     => $field_slug,
					);
				}
			}
		}

		if ( $field_slug == 'customer_name' ) {
			$users = get_users( array( 'search' => '*' . $term . '*', 'number' => 5 ) );
			foreach ( $users as $user ) {
				$output[] = array(
					'label'    => $user->display_name,
					'value'    => '',
					'flag_val' => $user->display_name,
					'slug'     => $field_slug,
				);
			}
		} else if ( $field_slug == 'customer_email' ) {
			$users = get_users( array( 'search' => '*' . $term . '*', 'number' => 5 ) );
			foreach ( $users as $user ) {
				$output[] = array(
					'label'    => $user->user_email,
					'value'    => '',
					'flag_val' => $user->user_email,
					'slug'     => $field_slug,
				);
			}
		}

		break;
}
if ( ! $output ) {
	$output[] = array(
		'label' => __( 'No matching data', 'supportcandy' ),
		'value' => '',
		'slug'  => '',
	);
}

if ( $output ) {
	$output = array_unique( $output, SORT_REGULAR );
}
echo json_encode( $output );
