<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$field_id = isset( $_POST ) && isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;
if ( ! $field_id ) {
	exit;
}

$custom_field = get_term_by( 'id', $field_id, 'support_ticket_custom_fields' );

$wpsc_tf_label      = get_term_meta( $custom_field->term_id, 'wpsc_tf_label', true );
$wpsc_tf_extra_info = get_term_meta( $custom_field->term_id, 'wpsc_tf_extra_info', true );
$wpsc_tf_status     = get_term_meta( $custom_field->term_id, 'wpsc_tf_status', true );
$wpsc_tf_width      = get_term_meta( $custom_field->term_id, 'wpsc_tf_width', true );

ob_start();
?>
    <div class="form-group">
        <label for="wpsc_tf_label"><?php _e( 'Field Label', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Insert field label. Please make sure label you are entering should not already exist.', 'supportcandy' ); ?></p>
        <input id="wpsc_tf_label" class="form-control" name="wpsc_tf_label"
               value="<?php echo str_replace( '"', "&quot;", stripcslashes( $wpsc_tf_label ) ) ?>"/>
    </div>
    <div class="form-group">
        <label for="wpsc_tf_extra_info"><?php _e( 'Extra Information', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Extra information about the field. Useful if you want to give instructions or information about the field in create ticket from. Keep this empty if not needed.', 'supportcandy' ); ?></p>
        <input id="wpsc_tf_extra_info" class="form-control" name="wpsc_tf_extra_info"
               value="<?php echo str_replace( '"', "&quot;", stripcslashes( $wpsc_tf_extra_info ) ) ?>"/>
    </div>
    <div class="form-group">
        <label for="wpsc_tf_width"><?php _e( 'Width', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Please select width of the field in create ticket form.', 'supportcandy' ); ?></p>
        <select id="wpsc_tf_width" class="form-control" name="wpsc_tf_width">
            <option <?php echo $wpsc_tf_width == '1/3' ? 'selected="selected"' : '' ?>
                    value="1/3"><?php _e( '1/3 width of Row', 'supportcandy' ); ?></option>
            <option <?php echo $wpsc_tf_width == '1/2' ? 'selected="selected"' : '' ?>
                    value="1/2"><?php _e( 'Half width of Row', 'supportcandy' ); ?></option>
            <option <?php echo $wpsc_tf_width == '1' ? 'selected="selected"' : '' ?>
                    value="1"><?php _e( 'Full width of Row', 'supportcandy' ); ?></option>
        </select>
    </div>
<?php
if ( ! ( $custom_field->slug == 'customer_name' || $custom_field->slug == 'customer_email' ) ) {
	?>
    <div class="form-group">
        <label for="wpsc_tf_status"><?php _e( 'Status', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'If disabled, will not be available in create ticket form. This will disable ability of customer to insert while creating ticket but agents can edit these values and remian visible in open ticket', 'supportcandy' ); ?></p>
        <select id="wpsc_tf_status" class="form-control" name="wpsc_tf_status">
            <option <?php echo $wpsc_tf_status == '1' ? 'selected="selected"' : '' ?>
                    value="1"><?php _e( 'Enable', 'supportcandy' ); ?></option>
            <option <?php echo $wpsc_tf_status == '0' ? 'selected="selected"' : '' ?>
                    value="0"><?php _e( 'Disable', 'supportcandy' ); ?></option>
        </select>
    </div>
	<?php
} else {
	?>
    <input id="wpsc_tf_status" type="hidden" name="wpsc_tf_status" value="1">
	<?php
}

$body = ob_get_clean();
ob_start();
?>
    <button type="button" class="btn wpsc_popup_close"
            onclick="wpsc_modal_close();"><?php _e( 'Close', 'supportcandy' ); ?></button>
    <button type="button" class="btn wpsc_popup_action"
            onclick="wpsc_set_edit_default_form_field(<?php echo htmlentities( $field_id ) ?>);"><?php _e( 'Submit', 'supportcandy' ); ?></button>
<?php
$footer = ob_get_clean();

$output = array(
	'body'   => $body,
	'footer' => $footer
);

echo json_encode( $output );
