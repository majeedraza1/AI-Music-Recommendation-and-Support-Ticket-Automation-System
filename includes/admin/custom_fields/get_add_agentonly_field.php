<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$field_types = $wpscfunction->get_custom_field_types();

$conditional_types = array();
foreach ( $field_types as $key => $field ) {
	if ( $field['has_options'] ) {
		$conditional_types[] = $key;
	}
}

ob_start();
?>
    <div class="form-group">
        <label for="wpsc_tf_label"><?php _e( 'Field Label', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Insert field label. Please make sure label you are entering should not already exist in agent only as well as ticket form fields.', 'supportcandy' ); ?></p>
        <input id="wpsc_tf_label" class="form-control" name="wpsc_tf_label" value=""/>
    </div>
    <div class="form-group">
        <label for="wpsc_tf_extra_info"><?php _e( 'Extra Information', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Extra information about the field. Useful if you want to give instructions or information about the field for agent. Keep this empty if not needed.', 'supportcandy' ); ?></p>
        <input id="wpsc_tf_extra_info" class="form-control" name="wpsc_tf_extra_info" value=""/>
    </div>
    <div class="form-group">
        <label for="wpsc_tf_personal_info"><?php _e( 'Personal Information', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Enable or disable personal information in ticket form.', 'supportcandy' ); ?></p>
        <select id="wpsc_tf_personal_info" class="form-control" name="wpsc_tf_personal_info">
            <option value="0"><?php _e( 'No', 'supportcandy' ); ?></option>
            <option value="1"><?php _e( 'Yes', 'supportcandy' ); ?></option>
        </select>
    </div>
    <div class="form-group">
        <label for="wpsc_tf_type"><?php _e( 'Field Type', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Select field type.', 'supportcandy' ); ?></p>
        <select id="wpsc_tf_type" class="form-control" name="wpsc_tf_type">
			<?php foreach ( $field_types as $key => $field ) : ?>
                <option data-options="<?php echo $field['has_options'] ?>"
                        value="<?php echo $key ?>"><?php echo $field['label'] ?></option>
			<?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" id="wpsc_tf_options_container" style="display:none;">
        <label for="wpsc_tf_options"><?php _e( 'Field Options', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Insert field options. New option should begin on new line.', 'supportcandy' ); ?></p>
        <textarea style="height:80px !important;" id="wpsc_tf_options" class="form-control"
                  name="wpsc_tf_options"></textarea>
    </div>
    <script>
        jQuery('#wpsc_tf_type').change(function () {
            var has_options = jQuery('option:selected', this).data('options');
            if (has_options == '1') {
                jQuery('#wpsc_tf_options_container').show();
            } else {
                jQuery('#wpsc_tf_options_container').hide();
            }
        });
    </script>
<?php
$body = ob_get_clean();
ob_start();
?>
    <button type="button" class="btn wpsc_popup_close"
            onclick="wpsc_modal_close();"><?php _e( 'Close', 'supportcandy' ); ?></button>
    <button type="button" class="btn wpsc_popup_action"
            onclick="wpsc_set_add_agentonly_field();"><?php _e( 'Submit', 'supportcandy' ); ?></button>
<?php
$footer = ob_get_clean();

$output = array(
	'body'   => $body,
	'footer' => $footer
);

echo json_encode( $output );
