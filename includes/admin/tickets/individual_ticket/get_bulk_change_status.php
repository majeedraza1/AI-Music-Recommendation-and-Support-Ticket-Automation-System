<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'wpsc_agent' ) ) ) {
	exit;
}

$ticket_id = isset( $_POST['ticket_id'] ) ? sanitize_text_field( $_POST['ticket_id'] ) : '';

$wpsc_appearance_modal_window = get_option( 'wpsc_modal_window' );
ob_start();
?>
    <form id="frm_ticket_change_status" method="post">

        <div class="form-group">
            <label for="support_ticket_default_status"><?php _e( 'Ticket Status', 'supportcandy' ); ?></label>
            <select class="form-control" name="status">
                <option></option>
				<?php
				$statuses                   = get_terms( [
					'taxonomy'   => 'wpsc_statuses',
					'hide_empty' => false,
					'orderby'    => 'meta_value_num',
					'order'      => 'ASC',
					'meta_query' => array( 'order_clause' => array( 'key' => 'wpsc_status_load_order' ) ),
				] );
				$support_ticket_default_status = get_option( 'support_ticket_default_status' );
				foreach ( $statuses as $status ) :
					echo '<option  value="' . $status->term_id . '">' . $status->name . '</option>';
				endforeach;
				?>
            </select>
        </div>

        <div class="form-group">
            <label for="support_ticket_default_category"><?php _e( 'Ticket Category', 'supportcandy' ); ?></label>
            <select class="form-control" name="category">
                <option value=""></option>
				<?php
				$categories                   = get_terms( [
					'taxonomy'   => 'wpsc_categories',
					'hide_empty' => false,
					'orderby'    => 'meta_value_num',
					'order'      => 'ASC',
					'meta_query' => array( 'order_clause' => array( 'key' => 'support_ticket_category_menu_order' ) ),
				] );
				$support_ticket_default_category = get_option( 'support_ticket_default_category' );
				foreach ( $categories as $category ) :
					echo '<option  value="' . $category->term_id . '">' . $category->name . '</option>';
				endforeach;
				?>
            </select>
        </div>

        <div class="form-group">
            <label for="support_ticket_default_priority"><?php _e( 'Ticket priority', 'supportcandy' ); ?></label>
            <select class="form-control" name="priority">
                <option value=""></option>
				<?php
				$priorities                   = get_terms( [
					'taxonomy'   => 'wpsc_priorities',
					'hide_empty' => false,
					'orderby'    => 'meta_value_num',
					'order'      => 'ASC',
					'meta_query' => array( 'order_clause' => array( 'key' => 'wpsc_priority_load_order' ) ),
				] );
				$support_ticket_default_priority = get_option( 'support_ticket_default_priority' );
				foreach ( $priorities as $priority ) :
					echo '<option  value="' . $priority->term_id . '">' . $priority->name . '</option>';
				endforeach;
				?>
            </select>
        </div>
		<?php do_action( 'wpsc_after_bulk_change_ticket_status', $ticket_id ); ?>
        <input type="hidden" name="action" value="wpsc_tickets"/>
        <input type="hidden" name="setting_action" value="set_bulk_change_status"/>
        <input type="hidden" name="ticket_id" value="<?php echo htmlentities( $ticket_id ) ?>"/>

    </form>
<?php
$body = ob_get_clean();

ob_start();
?>
    <button type="button" class="btn wpsc_popup_close"
            style="background-color:<?php echo $wpsc_appearance_modal_window['wpsc_close_button_bg_color'] ?> !important;color:<?php echo $wpsc_appearance_modal_window['wpsc_close_button_text_color'] ?> !important;"
            onclick="wpsc_modal_close();"><?php _e( 'Close', 'supportcandy' ); ?></button>
    <button type="button" class="btn wpsc_popup_action"
            style="background-color:<?php echo $wpsc_appearance_modal_window['wpsc_action_button_bg_color'] ?> !important;color:<?php echo $wpsc_appearance_modal_window['wpsc_action_button_text_color'] ?> !important;"
            onclick="wpsc_set_bulk_change_status();"><?php _e( 'Save', 'supportcandy' ); ?></button>
<?php
$footer = ob_get_clean();

$output = array(
	'body'   => $body,
	'footer' => $footer
);

echo json_encode( $output );
