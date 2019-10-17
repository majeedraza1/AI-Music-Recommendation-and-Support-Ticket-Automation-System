<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPSC_Functions' ) ) :

	final class WPSC_Functions {

		// Return custom field types. Developers, please make sure hooked custom field type index should start from 51. 1-50 is reserved for core product.
		function get_custom_field_types() {
			$custom_field_types = array(
				1  => array(
					'label'               => __( 'Text Field', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'string'
				),
				2  => array(
					'label'               => __( 'Drop Down', 'supportcandy' ),
					'has_options'         => 1,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'string'
				),
				3  => array(
					'label'               => __( 'Checkbox', 'supportcandy' ),
					'has_options'         => 1,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'string'
				),
				4  => array(
					'label'               => __( 'Radio Button', 'supportcandy' ),
					'has_options'         => 1,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'string'
				),
				5  => array(
					'label'               => __( 'Textarea', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 0,
					'allow_ticket_filter' => 0,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'string'
				),
				6  => array(
					'label'               => __( 'Date', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'date'
				),
				7  => array(
					'label'               => __( 'URL', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 0,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 0,
					'ticket_filter_type'  => 'string'
				),
				8  => array(
					'label'               => __( 'Email', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'string'
				),
				9  => array(
					'label'               => __( 'Number Only', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 1,
					'allow_ticket_filter' => 1,
					'allow_orderby'       => 1,
					'ticket_filter_type'  => 'number'
				),
				10 => array(
					'label'               => __( 'File Attachment', 'supportcandy' ),
					'has_options'         => 0,
					'allow_ticket_list'   => 0,
					'allow_ticket_filter' => 0,
					'allow_orderby'       => 0,
					'ticket_filter_type'  => 'string'
				),
			);

			return apply_filters( 'wpsc_custom_field_types', $custom_field_types );
		}

		// Calender date format to datetime conversion so that it can be compared in query
		function calenderDateFormatToDateTime( $date ) {
			$calender_format = get_option( 'wpsc_calender_date_format' );
			$y               = '';
			$m               = '';
			$d               = '';
			$calender_format = explode( '-', $calender_format );
			$date            = explode( '-', $date );
			foreach ( $calender_format as $key => $value ) {
				switch ( $value ) {
					case 'dd':
						$d = $date[ $key ];
						break;

					case 'mm':
						$m = $date[ $key ];
						break;

					case 'yy':
						$y = $date[ $key ];
						break;
				}
			}
			if ( ! $y || ! $m || ! $d ) {
				return '';
			}

			return $y . '-' . $m . '-' . $d . ' 00:00:00';
		}

		// Calender date format to datetime conversion so that it can be compared in query
		function datetimeToCalenderFormat( $datetime ) {
			if ( ! $datetime ) {
				return '';
			}
			$calender_format = get_option( 'wpsc_calender_date_format' );
			$calender_format = explode( '-', $calender_format );
			$arr             = array();
			foreach ( $calender_format as $key => $value ) {
				switch ( $value ) {
					case 'dd':
						$arr[] = 'd';
						break;
					case 'mm':
						$arr[] = 'm';
						break;
					case 'yy':
						$arr[] = 'Y';
						break;
				}
			}
			$d_format = implode( '-', $arr );
			$date     = date_create( $datetime );

			return date_format( $date, $d_format );
		}

		// Get current agent permissions.
		function get_current_agent_permissions() {
			global $current_user;
			$role_id    = get_user_option( 'support_ticket_agent_roles' );
			$agent_role = get_option( 'support_ticket_agent_roles' );

			return $agent_role[ $role_id ];
		}

		// Get default ticket filter labels
		function get_ticket_filter_labels() {
			$labels = array(
				'all'                 => array(
					'label'      => __( 'All Tickets', 'supportcandy' ),
					'has_badge'  => 0,
					'visibility' => 'both',
				),
				'unresolved_agent'    => array(
					'label'      => __( 'Unresolved', 'supportcandy' ),
					'has_badge'  => 1,
					'visibility' => 'agent',
				),
				'unresolved_customer' => array(
					'label'      => __( 'Unresolved', 'supportcandy' ),
					'has_badge'  => 0,
					'visibility' => 'customer',
				),
				'unassigned'          => array(
					'label'      => __( 'Unassigned', 'supportcandy' ),
					'has_badge'  => 0,
					'visibility' => 'agent',
				),
				'mine'                => array(
					'label'      => __( 'Mine', 'supportcandy' ),
					'has_badge'  => 0,
					'visibility' => 'agent',
				),
				'closed'              => array(
					'label'      => __( 'Closed', 'supportcandy' ),
					'has_badge'  => 0,
					'visibility' => 'both',
				),
				'deleted'             => array(
					'label'      => __( 'Deleted', 'supportcandy' ),
					'has_badge'  => 0,
					'visibility' => 'agent',
				),
			);

			return apply_filters( 'ticket_filter_labels', $labels );
		}

		// Get default user filter
		function get_default_filter() {
			global $current_user;
			if ( $current_user->has_cap( 'wpsc_agent' ) ) {
				$order_key = get_option( 'wpsc_tl_agent_orderby' );
				$order     = get_option( 'wpsc_tl_agent_orderby_order' );
			} else {
				$order_key = get_option( 'wpsc_tl_customer_orderby' );
				$order     = get_option( 'wpsc_tl_customer_orderby_order' );
			}
			$filter = array(
				'label'         => 'all',
				'query'         => array(),
				'orderby'       => $order_key,
				'order'         => $order,
				'page'          => 1,
				'custom_filter' => array(
					's' => '',
				)
			);

			return $filter;
		}

		// Get current user filter
		function get_current_filter() {
			$filter = isset( $_COOKIE['support_ticket_filter'] ) ? $_COOKIE['support_ticket_filter'] : '';
			if ( ! $filter ) {
				$filter = $this->get_default_filter();
			} else {
				$filter = json_decode( stripslashes( $filter ), true );
			}

			return $filter;
		}

		// Get sort type for current orderby field
		function get_field_sort_type( $field_slug ) {
			$term               = get_term_by( 'slug', $field_slug, 'support_ticket_custom_fields' );
			$ticket_filter_type = get_term_meta( $term->term_id, 'support_ticket_filter_type', true );
			switch ( $ticket_filter_type ) {
				case 'number':
					$sort_type = 'meta_value_num';
					break;

				case 'date':
				case 'string':
					$sort_type = 'meta_value';
					break;
			}

			return apply_filters( 'wpsc_field_sort_type', $sort_type, $ticket_filter_type, $term );
		}

		// Get post status
		function get_post_status( $filter ) {
			$post_status = $filter['label'] == 'deleted' ? 'trash' : 'publish';

			return apply_filters( 'wpsc_post_status', $post_status, $filter );
		}

		// Array sanitization
		function sanitize_array( $array ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = $this->sanitize_array( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
			}

			return $array;
		}

		// Get label for ticket field in filter using slug & value
		function get_tf_value_filter_label( $field_slug, $val ) {
			switch ( $field_slug ) {

				case 'ticket_status':

					$term = get_term_by( 'id', $val, 'ticket_status' );

					return $term ? $term->name : '';
					break;

				case 'ticket_category':

					$term = get_term_by( 'id', $val, 'ticket_category' );

					return $term ? $term->name : '';
					break;

				case 'ticket_priority':

					$term = get_term_by( 'id', $val, 'ticket_priority' );

					return $term ? $term->name : '';
					break;

				case 'ticket_widget':

					$term = get_term_by( 'id', $val, 'support_ticket_widget' );

					return $term ? $term->name : '';
					break;

				case 'assigned_agent':
				case 'agent_created':

					if ( $val == 0 ) {
						return __( 'None' );
					}
					$term = get_term_by( 'id', $val, 'support_agent' );
					if ( $term ) {
						$label = get_term_meta( $term->term_id, 'label', true );

						return $label;
					} else {
						return '';
					}
					break;

				default:

					$val = apply_filters( 'wpsc_filter_val_label', $val, $field_slug );

					return $val;
					break;
			}
		}

		// get time ago format for datetime
		function time_elapsed_string( $datetime ) {
			$now  = new DateTime;
			$ago  = new DateTime( $datetime );
			$diff = $now->diff( $ago );

			$diff->w = floor( $diff->d / 7 );
			$diff->d -= $diff->w * 7;

			$string = array(
				'y' => __( 'years', 'supportcandy' ),
				'm' => __( 'months', 'supportcandy' ),
				'w' => __( 'weeks', 'supportcandy' ),
				'd' => __( 'days', 'supportcandy' ),
				'h' => __( 'hours', 'supportcandy' ),
				'i' => __( 'minutes', 'supportcandy' ),
				's' => __( 'seconds', 'supportcandy' ),
			);
			foreach ( $string as $k => &$v ) {
				if ( $diff->$k ) {
					$v = $diff->$k . ' ' . $v;
				} else {
					unset( $string[ $k ] );
				}
			}

			$string = array_slice( $string, 0, 1 );
			if ( $string ) {
				$string = implode( ' ', $string );
				$string = explode( ' ', $string );
				$digit  = $string[0];
				$unit   = $string[1];

				return sprintf( __( "%d %s ago", 'supportcandy' ), $digit, $unit );
			} else {
				return __( 'just now', 'supportcandy' );
			}

		}

		function time_elapsed_timestamp( $datetime ) {
			$wpsc_thread_date_time_format = get_option( 'wpsc_thread_date_time_format' );

			return date_i18n( $wpsc_thread_date_time_format, strtotime( get_date_from_gmt( $datetime, 'Y-m-d H:i:s' ) ) );
		}

		function assign_agent( $ticket_id, $agents ) {
			global $wpscfunction, $wpdb;
			$prev_assigned = $wpscfunction->get_ticket_meta( $ticket_id, 'assigned_agent' );

			$wpscfunction->delete_ticket_meta( $ticket_id, 'assigned_agent' );
			if ( $agents ) {
				foreach ( $agents as $agent ) {
					$wpscfunction->add_ticket_meta( $ticket_id, 'assigned_agent', $agent );
				}
			} else {
				$wpscfunction->add_ticket_meta( $ticket_id, 'assigned_agent', 0 );
			}
			do_action( 'wpsc_set_assign_agent', $ticket_id, $agents, $prev_assigned );
		}

		function change_status( $ticket_id, $status_id ) {
			global $wpscfunction, $wpdb;
			$prev_status = $wpscfunction->get_ticket_fields( $ticket_id, 'ticket_status' );
			$values      = array(
				'ticket_status' => $status_id
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array( 'id' => $ticket_id ) );
			do_action( 'wpsc_set_change_status', $ticket_id, $status_id, $prev_status );
		}

		function change_category( $ticket_id, $category_id ) {
			global $wpscfunction, $wpdb;
			$prev_cat = $wpscfunction->get_ticket_fields( $ticket_id, 'ticket_category' );
			$values   = array(
				'ticket_category' => $category_id
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array( 'id' => $ticket_id ) );
			do_action( 'wpsc_set_change_category', $ticket_id, $category_id, $prev_cat );
		}

		function change_priority( $ticket_id, $priority_id ) {
			global $wpscfunction, $wpdb;
			$prev_priority = $wpscfunction->get_ticket_fields( $ticket_id, 'ticket_priority' );
			$values        = array(
				'ticket_priority' => $priority_id
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array( 'id' => $ticket_id ) );
			do_action( 'wpsc_set_change_priority', $ticket_id, $priority_id, $prev_priority );
		}

		function change_raised_by( $ticket_id, $name, $email ) {
			global $wpscfunction, $wpdb;
			$prev_name = $wpscfunction->get_ticket_fields( $ticket_id, 'customer_name' );
			$values    = array(
				'customer_name'  => $name,
				'customer_email' => $email
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array(
				'customer_name' => $prev_name,
				'id'            => $ticket_id
			) );
			do_action( 'wpsc_set_change_raised_by', $ticket_id, $name, $email, $prev_name );
		}

		function change_field( $ticket_id, $fields_slug, $fields_value ) {
			global $wpscfunction, $wpdb;
			if ( is_array( $fields_value ) ) {
				$prev_fields_value = $wpscfunction->get_ticket_meta( $ticket_id, $fields_slug );
				$wpscfunction->delete_ticket_meta( $ticket_id, $fields_slug );
				foreach ( $fields_value as $value ) {
					$wpscfunction->add_ticket_meta( $ticket_id, $fields_slug, $value );
				}
			} else {
				$term              = get_term_by( 'slug', $fields_slug, 'support_ticket_custom_fields' );
				$wpsc_tf_type      = get_term_meta( $term->term_id, 'wpsc_tf_type', true );
				$prev_fields_value = $wpscfunction->get_ticket_meta( $ticket_id, $fields_slug, true );
				$values            = array(
					'meta_value' => $fields_value,
				);
				$get_ticket_field  = $wpdb->get_var( "SELECT meta_key FROM {$wpdb->prefix}support_ticketmeta WHERE id='$ticket_id' AND meta_key = '$fields_slug'" );
				if ( ! $get_ticket_field ) {
					$wpdb->insert( $wpdb->prefix . 'support_ticketmeta',
						array(
							'ticket_id'  => $ticket_id,
							'meta_key'   => $fields_slug,
							'meta_value' => $fields_value
						) );
				}
				$wpscfunction->update_ticket_meta( $ticket_id, $fields_slug, $values );
			}
			do_action( 'wpsc_set_change_fields', $ticket_id, $fields_slug, $fields_value, $prev_fields_value );
		}

		function delete_tickets( $ticket_id ) {
			global $wpdb;

			$values = array(
				'active' => '0'
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array( 'id' => $ticket_id ) );
			do_action( 'wpsc_set_delete_ticket', $ticket_id );
		}

		// Get post id for ticket id
		function get_ticket_post_id( $ticket_id ) {
			$posts       = get_posts( array(
				'post_type'   => 'support_ticket',
				'post_status' => array( 'publish', 'trash' ),
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => 'ticket_id',
						'value'   => $ticket_id,
						'compare' => '='
					),
				),
			) );
			$ticket_post = count( $posts ) ? $posts[0] : array();
			$post_id     = $ticket_post ? $ticket_post->ID : 0;

			return $post_id;
		}

		// Get status name by status id
		function get_status_name( $status_id ) {
			$status = get_term_by( 'id', $status_id, 'ticket_status' );

			return $status->name;
		}

		// Get category name by category id
		function get_category_name( $category_id ) {
			$category = get_term_by( 'id', $category_id, 'ticket_category' );

			return $category->name;
		}

		// Get priority name by category id
		function get_priority_name( $priority_id ) {
			$priority = get_term_by( 'id', $priority_id, 'ticket_priority' );

			return $priority->name;
		}

		// Get ticket_widget name by ticket_widget id
		function get_ticket_widget_name( $ticket_widget_id ) {
			$ticket_widget = get_term_by( 'id', $ticket_widget_id, 'support_ticket_widget' );

			return $ticket_widget->name;
		}

		// Get ticket description
		function get_ticket_description( $ticket_id ) {
			$threads       = get_posts( array(
				'post_type'   => 'ticket_thread',
				'post_status' => 'publish',
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'     => 'thread_type',
						'value'   => 'report',
						'compare' => '='
					),
					array(
						'key'     => 'ticket_id',
						'value'   => $ticket_id,
						'compare' => '='
					),
				),
			) );
			$description   = $threads ? $threads[0]->post_content : '';
			$thread_id     = $threads ? $threads[0]->ID : 0;
			$ticket_report = array();

			if ( $thread_id ) {
				$ticket_report = array(
					'description' => $description,
					'thread_id'   => $thread_id,
				);
			} else {
				$ticket_report = array(
					'description' => $description,
				);
			}

			return $ticket_report;
		}

		// Get last reply
		function get_last_reply( $ticket_id ) {
			$threads   = get_posts( array(
				'post_type'      => 'ticket_thread',
				'post_status'    => 'publish',
				'posts_per_page' => '1',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => 'ticket_id',
						'value'   => $ticket_id,
						'compare' => '='
					),
					array(
						'key'     => 'thread_type',
						'value'   => 'reply',
						'compare' => '='
					),
				),
			) );
			$thread_id = $threads ? $threads[0]->ID : 0;
			if ( $thread_id ) {
				$last_reply = array(
					'user_name'   => get_post_meta( $thread_id, 'customer_name', true ),
					'user_email'  => get_post_meta( $thread_id, 'customer_email', true ),
					'description' => $threads[0]->post_content,
					'thread_id'   => $thread_id,
				);
			} else {
				$last_reply = array(
					'user_name'   => '',
					'user_email'  => '',
					'description' => '',
					'thread_id'   => $thread_id
				);
			}

			return $last_reply;
		}

		// Get last note
		function get_last_note( $ticket_id ) {
			$threads   = get_posts( array(
				'post_type'      => 'ticket_thread',
				'post_status'    => 'publish',
				'posts_per_page' => '1',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => 'ticket_id',
						'value'   => $ticket_id,
						'compare' => '='
					),
					array(
						'key'     => 'thread_type',
						'value'   => 'note',
						'compare' => '='
					),
				),
			) );
			$thread_id = $threads ? $threads[0]->ID : 0;
			if ( $thread_id ) {
				$last_note = array(
					'user_name'   => get_post_meta( $thread_id, 'customer_name', true ),
					'user_email'  => get_post_meta( $thread_id, 'customer_email', true ),
					'description' => $threads[0]->post_content,
					'thread_id'   => $thread_id,
				);
			} else {
				$last_note = array(
					'user_name'   => '',
					'user_email'  => '',
					'description' => '',
					'thread_id'   => $thread_id
				);
			}

			return $last_note;
		}

		// Get Ticket History
		function get_ticket_history( $ticket_id ) {
			include WPSC_ABSPATH . 'includes/functions/get_ticket_history.php';

			return $ticket_history;
		}

		// Get assigned_agent names
		function get_assigned_agent_names( $ticket_id ) {
			global $wpscfunction;
			$assigned_agents = $wpscfunction->get_ticket_meta( $ticket_id, 'assigned_agent' );
			$agent_names     = array();
			foreach ( $assigned_agents as $agent_id ) {
				$agent_names[] = $this->get_agent_name( $agent_id );
			}

			return implode( ', ', $agent_names );
		}

		// Get assigned_agent names
		function get_assigned_agent_emails( $ticket_id ) {
			global $wpscfunction;
			$assigned_agents = $wpscfunction->get_ticket_meta( $ticket_id, 'assigned_agent' );
			$agent_emails    = array();
			foreach ( $assigned_agents as $agent_id ) {
				$user_id = get_term_meta( $agent_id, 'user_id', true );
				if ( $user_id ) {
					$user           = get_user_by( 'id', $user_id );
					$agent_emails[] = $user->user_email;
				}
			}

			return apply_filters( 'wpsc_get_assigned_agent_emails', $agent_emails, $ticket_id );
		}

		// Get agent name by id
		function get_agent_name( $agent_id ) {
			if ( $agent_id ) {
				return get_term_meta( $agent_id, 'label', true );
			} else {
				return __( 'None', 'supportcandy' );
			}
		}

		// Submit ticket
		function create_ticket( $args ) {
			include WPSC_ABSPATH . 'includes/functions/create_ticket.php';

			return $ticket_id;
		}

		// Submit ticket
		function create_ticket_reply( $args ) {
			include WPSC_ABSPATH . 'includes/functions/create_ticket_reply.php';

			return $thread_id;
		}

		// Submit clone ticket ticket
		function create_clone_ticket( $args ) {
			include WPSC_ABSPATH . 'includes/functions/create_clone_ticket.php';

			return $ticket_id;
		}

		// Submit ticket thread
		function submit_ticket_thread( $args ) {
			global $wpdb;
			$thread_id = wp_insert_post(
				array(
					'post_type'    => 'ticket_thread',
					'post_content' => $args['reply_body'],
					'post_status'  => 'publish',
				)
			);
			add_post_meta( $thread_id, 'ticket_id', $args['ticket_id'] );
			add_post_meta( $thread_id, 'thread_type', $args['thread_type'] );
			$customer_name = isset( $args['customer_name'] ) ? $args['customer_name'] : '';
			add_post_meta( $thread_id, 'customer_name', $customer_name );
			$customer_email = isset( $args['customer_email'] ) ? $args['customer_email'] : '';
			add_post_meta( $thread_id, 'customer_email', $customer_email );
			$attachments = isset( $args['attachments'] ) ? $args['attachments'] : array();
			add_post_meta( $thread_id, 'attachments', $attachments );
			$ticket_id = isset( $args['ticket_id'] ) ? $args['ticket_id'] : 0;
			$values    = array(
				'date_updated' => date( "Y-m-d H:i:s" )
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array( 'id' => $ticket_id ) );

			return $thread_id;
		}

		// Submit ticket thread
		function submit_cloned_ticket_thread( $args ) {
			global $wpdb;
			$thread_id = wp_insert_post(
				array(
					'post_type'    => 'ticket_thread',
					'post_content' => $args['reply_body'],
					'post_status'  => 'publish',
					'post_date'    => $args['create_time'],
				)
			);
			add_post_meta( $thread_id, 'ticket_id', $args['ticket_id'] );
			add_post_meta( $thread_id, 'thread_type', $args['thread_type'] );
			$customer_name = isset( $args['customer_name'] ) ? $args['customer_name'] : '';
			add_post_meta( $thread_id, 'customer_name', $customer_name );
			$customer_email = isset( $args['customer_email'] ) ? $args['customer_email'] : '';
			add_post_meta( $thread_id, 'customer_email', $customer_email );
			$attachments = isset( $args['attachments'] ) ? $args['attachments'] : array();
			add_post_meta( $thread_id, 'attachments', $attachments );
			$ticket_id = isset( $args['ticket_id'] ) ? $args['ticket_id'] : 0;
			$values    = array(
				'date_updated' => date( "Y-m-d H:i:s" )
			);
			$wpdb->update( $wpdb->prefix . 'support_ticket', $values, array( 'id' => $ticket_id ) );

			return $thread_id;
		}

		// Replace macros
		function replace_macro( $str, $ticket_id ) {
			include WPSC_ABSPATH . 'includes/functions/replace_macro.php';

			return $str;
		}

		// Get user agent id
		function get_current_user_agent_id() {
			global $current_user;
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
				return $agents[0]->term_id;
			} else {
				return 0;
			}
		}

		// Check agent permission for wpsc functionality
		function agent_has_permission( $permission, $ticket_id = 0 ) {
			global $current_user, $wpscfunction;
			$current_agent_id = $this->get_current_user_agent_id();
			if ( ! $current_agent_id ) {
				return false;
			}
			if ( ! $current_user->has_cap( 'wpsc_agent' ) ) {
				return false;
			}
			$role_id     = get_user_option( 'support_ticket_agent_roles', $current_user->ID );
			$agent_roles = get_option( 'support_ticket_agent_roles' );
			$permissions = $agent_roles[ $role_id ];
			if ( $ticket_id ) {
				$assigned_agents = $wpscfunction->get_ticket_meta( $ticket_id, 'assigned_agent' );
				switch ( $permission ) {
					case 'view_unassigned':
					case 'assign_unassigned':
					case 'reply_unassigned':
					case 'change_ticket_status_unassigned':
					case 'change_ticket_field_unassigned':
					case 'change_ticket_agent_only_unassigned':
					case 'change_ticket_raised_by_unassigned':
					case 'delete_unassigned':
						$response = ( $permissions[ $permission ] && in_array( 0, $assigned_agents ) ) ? true : false;
						break;

					case 'view_assigned_me':
					case 'assign_assigned_me':
					case 'reply_assigned_me':
					case 'change_ticket_status_assigned_me':
					case 'change_ticket_field_assigned_me':
					case 'change_ticket_agent_only_assigned_me':
					case 'change_ticket_raised_by_assigned_me':
					case 'delete_assigned_me':
						$response = ( $permissions[ $permission ] && in_array( $current_agent_id, $assigned_agents ) ) ? true : false;
						break;

					case 'view_assigned_others':
					case 'assign_assigned_others':
					case 'reply_assigned_others':
					case 'change_ticket_status_assigned_others':
					case 'change_ticket_field_assigned_others':
					case 'change_ticket_agent_only_assigned_others':
					case 'change_ticket_raised_by_assigned_others':
					case 'delete_assigned_others':
						$response = ( $permissions[ $permission ] && ! in_array( $current_agent_id, $assigned_agents ) && ! in_array( 0, $assigned_agents ) ) ? true : false;
						break;
				}
			} else {
				$response = ( $permissions[ $permission ] ) ? true : false;
			}

			return apply_filters( 'wpsc_agent_has_permission', $response, $permission, $ticket_id );
		}

		// User permissions
		function has_permission( $permission, $ticket_id = 0 ) {
			global $wpscfunction;
			$support_ticket_public_mode = get_option( 'support_ticket_public_mode' );
			global $current_user;
			if ( ! $current_user->ID ) {
				return false;
			}
			$ticket         = $wpscfunction->get_ticket( $ticket_id );
			$customer_email = $ticket_id ? $ticket['customer_email'] : '';
			switch ( $permission ) {
				case 'view_ticket':
					$response = $customer_email == $current_user->user_email || $this->agent_has_permission( 'view_unassigned', $ticket_id ) || $this->agent_has_permission( 'view_assigned_me', $ticket_id ) || $this->agent_has_permission( 'view_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'assign_agent':
					$response = $this->agent_has_permission( 'assign_unassigned', $ticket_id ) || $this->agent_has_permission( 'assign_assigned_me', $ticket_id ) || $this->agent_has_permission( 'assign_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'reply_ticket':
					$response = $customer_email == $current_user->user_email || $this->agent_has_permission( 'reply_unassigned', $ticket_id ) || $this->agent_has_permission( 'reply_assigned_me', $ticket_id ) || $this->agent_has_permission( 'reply_assigned_others', $ticket_id ) || ( ! $current_user->has_cap( 'wpsc_agent' ) && $support_ticket_public_mode ) ? true : false;
					break;
					$term = wp_insert_term( __( 'Status', 'supportcandy' ), 'support_ticket_widget' );
					if ( $term && isset( $term['term_id'] ) ) {
						add_term_meta( $term['term_id'], 'support_ticket_widget_load_order', '1' );
						update_option( 'support_ticket_widget_updated', $term['term_id'] );
					}

				case 'add_note':
					$response = $this->agent_has_permission( 'view_unassigned', $ticket_id ) || $this->agent_has_permission( 'view_assigned_me', $ticket_id ) || $this->agent_has_permission( 'view_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'change_status':
					$response = $this->agent_has_permission( 'change_ticket_status_unassigned', $ticket_id ) || $this->agent_has_permission( 'change_ticket_status_assigned_me', $ticket_id ) || $this->agent_has_permission( 'change_ticket_status_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'change_ticket_fields':
					$response = $this->agent_has_permission( 'change_ticket_field_unassigned', $ticket_id ) || $this->agent_has_permission( 'change_ticket_field_assigned_me', $ticket_id ) || $this->agent_has_permission( 'change_ticket_field_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'change_agentonly_fields':
					$response = $this->agent_has_permission( 'change_ticket_agent_only_unassigned', $ticket_id ) || $this->agent_has_permission( 'change_ticket_agent_only_assigned_me', $ticket_id ) || $this->agent_has_permission( 'change_ticket_agent_only_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'change_raised_by':
					$response = $this->agent_has_permission( 'change_ticket_raised_by_unassigned', $ticket_id ) || $this->agent_has_permission( 'change_ticket_raised_by_assigned_me', $ticket_id ) || $this->agent_has_permission( 'change_ticket_raised_by_assigned_others', $ticket_id ) ? true : false;
					break;

				case 'delete_ticket':
					$response = $this->agent_has_permission( 'delete_unassigned', $ticket_id ) || $this->agent_has_permission( 'delete_assigned_me', $ticket_id ) || $this->agent_has_permission( 'delete_assigned_others', $ticket_id ) ? true : false;
					break;
			}

			return apply_filters( 'wpsc_has_permission', $response, $ticket_id, $permission );
		}

		// Random string
		function getRandomString( $length = 8 ) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$string     = '';
			for ( $i = 0; $i < $length; $i ++ ) {
				$string .= $characters[ mt_rand( 0, strlen( $characters ) - 1 ) ];
			}

			return $string;
		}

		// Email Notification types
		function get_email_notification_types() {
			$notification_types = array(
				'new_ticket'      => __( 'New Ticket', 'supportcandy' ),
				'ticket_reply'    => __( 'Ticket Reply', 'supportcandy' ),
				'change_status'   => __( 'Change Ticket Status', 'supportcandy' ),
				'assign_agent'    => __( 'Assign Agent', 'supportcandy' ),
				'delete_ticket'   => __( 'Delete Ticket', 'supportcandy' ),
				'private_note'    => __( 'Private Note', 'supportcandy' ),
				'change_category' => __( 'Change Ticket Category', 'supportcandy' ),
				'change_priority' => __( 'Change Ticket Priority', 'supportcandy' )
			);

			return apply_filters( 'support_ticket_notification_types', $notification_types );
		}

		// Get Field value by value id
		function get_field_val_by_field_id( $field_id, $val ) {
			include WPSC_ABSPATH . 'includes/functions/get_field_val_by_valId.php';

			return $val;
		}

		// get condition field options
		function get_condition_field_options() {
			include WPSC_ABSPATH . 'includes/functions/get_condition_field_options.php';

			return $fields;
		}

		// check conditions
		function check_ticket_conditions( $conditions, $ticket_id ) {
			include WPSC_ABSPATH . 'includes/functions/check_ticket_conditions.php';

			return $flag;
		}

		// show ad banner on dashboard interface if no add-on is installed.
		function display_ad_banner() {
			include WPSC_ABSPATH . 'includes/functions/display_ad_banner.php';
		}

		function get_ticket( $ticket_id ) {
			global $wpdb;
			$ticket_data = array();
			$ticket      = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}support_ticket WHERE id='$ticket_id' " );
			if ( $ticket ) {
				$ticket_data = json_decode( json_encode( $ticket ), true );
			}

			return $ticket_data;
		}

		function get_ticket_fields( $ticket_id, $select_field ) {
			global $wpdb;
			$get_ticket_field_value = $wpdb->get_var( " SELECT $select_field FROM {$wpdb->prefix}support_ticket WHERE id='$ticket_id' " );
			$ticket_field_value     = $get_ticket_field_value ? $get_ticket_field_value : '';

			return stripslashes( $ticket_field_value );
		}

		/**
		 * Get ticket meta
		 */
		function get_ticket_meta( $ticket_id, $meta_key, $flag = false ) {

			global $wpdb;
			if ( $flag ) {

				$get_meta    = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->prefix}support_ticketmeta WHERE ticket_id= '$ticket_id' AND meta_key = '$meta_key' " );
				$ticket_meta = stripslashes( $get_meta ) ? stripslashes( $get_meta ) : '';

			} else {

				$ticket_meta = array();
				$results     = $wpdb->get_results( "SELECT meta_value FROM {$wpdb->prefix}support_ticketmeta WHERE ticket_id= '$ticket_id' AND meta_key = '$meta_key'" );
				if ( $results ) {
					foreach ( $results as $result ) {
						$ticket_meta[] = stripslashes( $result->meta_value );
					}
				}
			}

			return $ticket_meta;
		}

		function get_ticket_status( $ticket_id ) {
			global $wpdb;
			$ticket_status = $wpdb->get_var( "SELECT active FROM {$wpdb->prefix}support_ticket WHERE id='$ticket_id' " );

			return intval( $ticket_status );
		}

		/**
		 * Adds ticketmeta for ticket
		 */
		function add_ticket_meta( $ticket_id, $meta_key, $meta_value ) {
			global $wpdb;
			$wpdb->insert(
				$wpdb->prefix . 'support_ticketmeta',
				array(
					'ticket_id'  => $ticket_id,
					'meta_key'   => $meta_key,
					'meta_value' => $meta_value
				) );
		}

		/**
		 * Update ticket meta for ticket
		 */
		function update_ticket_meta( $ticket_id, $meta_key, $meta_value ) {
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'support_ticketmeta', $meta_value, array(
				'ticket_id' => $ticket_id,
				'meta_key'  => $meta_key
			) );
		}

		function delete_ticket_meta( $ticket_id, $meta_key ) {
			global $wpdb;
			$wpdb->delete( $wpdb->prefix . 'support_ticketmeta', array(
				'ticket_id' => $ticket_id,
				'meta_key'  => $meta_key
			) );
		}

		/**
		 * Get SQL Query depending on paramenters passed for ticket list
		 */
		public function get_sql_query( $select_str, $meta_query, $search = false, $orderby = false, $order = false, $no_of_tickets = false, $current_page = false ) {
			include WPSC_ABSPATH . 'includes/functions/get_sql_query.php';

			return $sql;
		}

		/**
		 * Get all field array which saved in ticketmeta table
		 */

		function get_all_meta_keys() {

			$c_fields = get_terms( [
				'taxonomy'   => 'support_ticket_custom_fields',
				'hide_empty' => false,
				'orderby'    => 'meta_value_num',
				'meta_key'   => 'wpsc_tf_load_order',
				'order'      => 'ASC',
				'meta_query' => array(
					array(
						'key'     => 'agentonly',
						'value'   => array( 0, 1 ),
						'compare' => 'IN'
					)
				),
			] );

			$meta_key = array();

			if ( $c_fields ) {

				foreach ( $c_fields as $field ) {

					$wpsc_tf_type = get_term_meta( $field->term_id, 'wpsc_tf_type', true );

					if ( ! $wpsc_tf_type ) {
						continue;
					}

					$meta_key[] = $field->slug;

				}

			}

			$meta_key[] = 'assigned_agent';

			return apply_filters( 'wpsc_get_all_meta_keys', $meta_key );

		}

		/**
		 * Create a ticket
		 */
		public function create_new_ticket( $values ) {
			global $wpdb;
			$wpdb->insert( $wpdb->prefix . 'support_ticket', $values );
			$ticket_id = $wpdb->insert_id;

			return $ticket_id;
		}

		/**
		 * Get status term ids
		 */
		function get_statuses() {
			$status_ids = array();
			$statuses   = get_terms( [
				'taxonomy'   => 'ticket_status',
				'hide_empty' => false,
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
				'meta_query' => array( 'order_clause' => array( 'key' => 'support_ticket_status_menu_order' ) ),
			] );
			foreach ( $statuses as $status ) {
				$status_ids[] = $status->term_id;
			}

			return $status_ids;
		}

		/**
		 * Get priority term ids
		 */
		function get_priorities() {
			$priority_ids = array();
			$priorities   = get_terms( [
				'taxonomy'   => 'ticket_priority',
				'hide_empty' => false,
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
				'meta_query' => array( 'order_clause' => array( 'key' => 'support_ticket_priority_menu_order' ) ),
			] );
			foreach ( $priorities as $priority ) {
				$priority_ids[] = $priority->term_id;
			}

			return $priority_ids;
		}

		/**
		 * Get category term ids
		 */
		function get_categories() {
			$category_ids = array();
			$categories   = get_terms( [
				'taxonomy'   => 'ticket_category',
				'hide_empty' => false,
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
				'meta_query' => array( 'order_clause' => array( 'key' => 'support_ticket_category_menu_order' ) ),
			] );
			foreach ( $categories as $category ) {
				$category_ids[] = $category->term_id;
			}

			return $category_ids;
		}

		/**
		 * Get Unresolved Label COUNT
		 */

		function get_label_count_unresolved() {

			global $wpdb, $current_user, $wpscfunction;

			$label_counts = array();

			// Get user meta history
			$label_count_history      = get_option( 'wpsc_label_count_history' );
			$label_count_last_history = get_user_meta( $current_user->ID, 'wpsc_' . get_current_blog_id() . '_label_count_last_history', true );

			if ( $label_count_history && $label_count_history == $label_count_last_history ) {
				$label_counts      = get_user_meta( $current_user->ID, 'wpsc_' . get_current_blog_id() . '_label_counts', true );
				$filter_count_flag = false;
			}

			$all_labels = $this->get_ticket_filter_labels();

			// Initialize meta query
			$meta_query = array(
				'relation' => 'AND',
			);

			// Initialie restrictions. Everyone should able to see their own tickets.
			$restrict_rules = array(
				'relation' => 'OR',
				array(
					'key'     => 'customer_email',
					'value'   => $current_user->user_email,
					'compare' => '='
				),
			);

			if ( $current_user->has_cap( 'wpsc_agent' ) ) {

				$agent_permissions = $this->get_current_agent_permissions();

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

				if ( ! $agents ) {
					die();
				}
				$current_agent = $agents[0];

				if ( $agent_permissions['view_unassigned'] ) {
					$restrict_rules[] = array(
						'key'     => 'assigned_agent',
						'value'   => 0,
						'compare' => '='
					);
				}

				if ( $agent_permissions['view_assigned_me'] ) {
					$restrict_rules[] = array(
						'key'     => 'assigned_agent',
						'value'   => $current_agent->term_id,
						'compare' => '='
					);
				}

				if ( $agent_permissions['view_assigned_others'] ) {
					$restrict_rules[] = array(
						'key'     => 'assigned_agent',
						'value'   => array( 0, $current_agent->term_id ),
						'compare' => 'NOT IN'
					);
				}

				$restrict_rules = apply_filters( 'wpsc_tl_agent_restrict_rules', $restrict_rules );

			} else {
				$restrict_rules = apply_filters( 'wpsc_tl_customer_restrict_rules', $restrict_rules );
			}

			$meta_query[] = $restrict_rules;

			$labels = array();

			foreach ( $all_labels as $key => $label ) {

				if ( $label['has_badge'] ) {

					switch ( $key ) {

						case 'unresolved_agent':
							$unresolved_agent       = get_option( 'wpsc_tl_agent_unresolve_statuses' );
							$unresolved_agent_rules = array(
								'relation' => 'OR',
							);
							if ( $unresolved_agent ) {
								$unresolved_agent_rules[] = array(
									'key'     => 'ticket_status',
									'value'   => $unresolved_agent,
									'compare' => 'IN'
								);
							}
							$meta_query[] = apply_filters( 'wpsc_unresolved_agent_label_count', $unresolved_agent_rules, $key );
							break;

						default:
							$meta_query = apply_filters( 'wpsc_filter_after_label_default', $meta_query, $key );
							break;

					}

					$sql            = $wpscfunction->get_sql_query( 'COUNT(DISTINCT t.id)', $meta_query );
					$ticket_count   = $wpdb->get_var( $sql );
					$labels[ $key ] = $ticket_count;
				}
			}

			if ( ! $label_count_history ) {
				$label_count_history = 1;
				update_option( 'wpsc_label_count_history', $label_count_history );
			}

			update_user_meta( $current_user->ID, 'wpsc_' . get_current_blog_id() . '_label_count_last_history', $label_count_history );
			update_user_meta( $current_user->ID, 'wpsc_' . get_current_blog_id() . '_label_counts', $labels );

			$label_counts = $labels;

		}

		/**
		 * Check string
		 */
		public function check_str_is_non_english( $string ) {
			$flag = true;
			if ( preg_match( '/[^\x00-\x7F]+/', $string ) ) {
				$flag = false;
			}

			return $flag;
		}

		/**
		 * Load conditions UI. Will be used to show conditions in setting.
		 *
		 * @param  [String] $id         (required) CSS id for the element. It must be ubique for each element.
		 * @param array $conditions (optional) Existing conditions to load
		 *
		 * @return [type]               Void
		 */
		public function load_conditions_ui( $id, $conditions = '' ) {
			include WPSC_ABSPATH . 'includes/functions/conditions/load_conditions_ui.php';
		}

		/**
		 * Print options html for a condition options for a given key
		 *
		 * @param Mixed $key key for getting options.
		 *
		 * @return [type] Void
		 */
		public function get_condition_options( $key ) {
			include WPSC_ABSPATH . 'includes/functions/conditions/get_condition_options.php';

			return $options;
		}

	}
endif;

$GLOBALS['wpscfunction'] = new WPSC_Functions();