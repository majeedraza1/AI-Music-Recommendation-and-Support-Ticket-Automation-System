<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$wpsc_custom_ticket_count = get_option( 'wpsc_custom_ticket_count' );
$wpsc_thread_limit        = get_option( 'wpsc_thread_limit' );
?>

<form id="wpsc_frm_advanced_settings" method="post" action="javascript:wpsc_set_advanced_settings();">
    <div class="form-group">
        <label for="support_ticket_url_permission"><?php _e( 'Ticket URL Permissions', 'supportcandy' ); ?></label>
        <select class="form-control" name="support_ticket_url_permission" id="support_ticket_url_permission">
			<?php
			$support_ticket_url_permission = get_option( 'support_ticket_url_permission' );
			$selected                   = $support_ticket_url_permission == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Anyone can see', 'supportcandy' ) . '</option>';
			$selected = $support_ticket_url_permission == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Login required', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_sign_out"><?php _e( 'Logout button', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Enable/Disable logout button on ticket list action bar.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_sign_out" id="wpsc_sign_out">
			<?php
			$wpsc_allow_sign_out = get_option( 'wpsc_sign_out' );
			$selected            = $wpsc_allow_sign_out == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $wpsc_allow_sign_out == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_guest_can_upload_files"><?php _e( 'Guest can upload files', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Enable/Disable attachement for guest in create ticket.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_guest_can_upload_files" id="wpsc_guest_can_upload_files">
			<?php
			$wpsc_guest_can_upload_files = get_option( 'wpsc_guest_can_upload_files' );
			$selected                    = $wpsc_guest_can_upload_files == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $wpsc_guest_can_upload_files == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="support_ticket_public_mode"><?php _e( 'Public Mode', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "If you enable this setting then all tickets will be visible to all users and they can reply to each others tickets.", "supportcandy" ); ?></p>
        <select class="form-control" name="support_ticket_public_mode" id="support_ticket_public_mode">
			<?php
			$support_ticket_public_mode = get_option( 'support_ticket_public_mode' );
			$selected                = $support_ticket_public_mode == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $support_ticket_public_mode == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_show_and_hide_filters"><?php _e( 'Ticket list filters default visibility', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Default show/hide filters on ticket list on page load.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_show_and_hide_filters" id="wpsc_show_and_hide_filters">
			<?php
			$wpsc_show_and_hide_filters = get_option( 'wpsc_show_and_hide_filters' );
			$selected                   = $wpsc_show_and_hide_filters == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Show', 'supportcandy' ) . '</option>';
			$selected = $wpsc_show_and_hide_filters == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Hide', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_allow_reply_confirmation"><?php _e( 'Allow Reply Confirmation', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "If you enable this setting, it will ask confirmation before submitting reply.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_allow_reply_confirmation" id="wpsc_allow_reply_confirmation">
			<?php
			$wpsc_allow_reply_confirmation = get_option( 'wpsc_allow_reply_confirmation' );
			$selected                      = $wpsc_allow_reply_confirmation == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $wpsc_allow_reply_confirmation == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
		<?php
		$wpsc_tinymce_toolbar        = get_option( 'wpsc_tinymce_toolbar' );
		$wpsc_tinymce_toolbar_active = get_option( 'wpsc_tinymce_toolbar_active' );
		?>
        <label for="wpsc_tinymce_toolbar"><?php _e( 'Tinymce toolbar', 'supportcandy' ); ?></label>
        <div class="row">
			<?php foreach ( $wpsc_tinymce_toolbar as $key => $val ) { ?>
                <div class="col-sm-4" style="margin-bottom:10px; display:flex;">
					<?php $checked = in_array( $key, $wpsc_tinymce_toolbar_active ) ? 'checked="checked"' : ''; ?>
                    <div style="width:25px;"><input <?php echo $checked ?> type="checkbox" name="wpsc_tinymce_toolbar[]"
                                                                           value= <?php echo $key ?>/></div>
                    <div style="padding-top:3px;"><?php echo $val['name'] ?></div>
                </div>
			<?php } ?>
        </div>
    </div>

    <div class="form-group">
        <label for="wpsc_thread_date_format"><?php _e( 'Thread Date Format', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "This format will be applicable for thread log time.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_thread_date_format" id="wpsc_thread_date_format">
			<?php
			$wpsc_thread_date_format = get_option( 'wpsc_thread_date_format' );
			$selected                = $wpsc_thread_date_format == 'string' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="string">' . __( 'In words (eg. 1 min ago)', 'supportcandy' ) . '</option>';
			$selected = $wpsc_thread_date_format == 'timestamp' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="timestamp">' . __( 'Timestamp', 'supportcandy' ) . '</option>';
			?>
        </select>
		<?php $wpsc_thread_dt_format = get_option( 'wpsc_thread_date_time_format' ); ?>
        <div id="date_time_format"
             style="<?php echo $wpsc_thread_date_format == 'timestamp' ? 'display:block;' : 'display:none;' ?>">
            <label for="wpsc_thread_date_time_format"
                   style="margin-top:10px"><?php _e( 'Timestamp Format', 'supportcandy' ); ?></label>
			<?php $wpsc_thread_date_time_format = get_option( 'wpsc_thread_date_time_format' ); ?>
            <input type="text" class="form-control" id="wpsc_thread_date_time_format"
                   name="wpsc_thread_date_time_format" value="<?php echo $wpsc_thread_date_time_format ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="wpsc_notify"><?php _e( 'Do Not Notify Owner', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Enable or Disable Do Not Notify Owner checkbox in create ticket.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_notify" id="wpsc_notify">
			<?php
			$wpsc_notify = get_option( 'wpsc_do_not_notify_setting' );
			$selected    = $wpsc_notify == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $wpsc_notify == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_notify_checkbox"><?php _e( "Don't Notify Owner Checkbox", 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Checked or Unchecked Don't Notify Owner checkbox in create ticket.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_notify_checkbox" id="wpsc_notify_checkbox">
			<?php
			$wpsc_notify_checkbox = get_option( 'wpsc_default_do_not_notify_option' );
			$selected             = $wpsc_notify_checkbox == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Checked', 'supportcandy' ) . '</option>';
			$selected = $wpsc_notify_checkbox == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Unchecked', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
		<?php
		$wpsc_allow_attach_active = get_option( 'wpsc_allow_attachment' );
		?>
        <label for="wpsc_allow_attach"><?php _e( 'Allow Attachment', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Allow attachment option in create ticket and reply ticket form.", "supportcandy" ); ?></p>
        <div class="row">
            <div class="col-sm-4" style="margin-bottom:10px; display:flex;">
				<?php $checked = in_array( 'create', $wpsc_allow_attach_active ) ? 'checked="checked"' : ''; ?>
                <div style="width:25px;"><input <?php echo $checked ?> type="checkbox" name="wpsc_allow_attach[]"
                                                                       value="create"/></div>
                <div style="padding-top:3px;"><?php _e( 'Create Ticket Form', 'supportcandy' ) ?></div>
            </div>
            <div class="col-sm-4" style="margin-bottom:10px; display:flex;">
				<?php $checked = in_array( 'reply', $wpsc_allow_attach_active ) ? 'checked="checked"' : ''; ?>
                <div style="width:25px;"><input <?php echo $checked ?> type="checkbox" name="wpsc_allow_attach[]"
                                                                       value="reply"/></div>
                <div style="padding-top:3px;"><?php _e( 'Reply Form', 'supportcandy' ) ?></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="wpsc_hide_show_priority"><?php _e( 'Priority Visibility', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Show/Hide priority in individual ticket for customers.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_hide_show_priority" id="wpsc_hide_show_priority">
			<?php
			$wpsc_hide_show_priority = get_option( 'wpsc_hide_show_priority' );
			$selected                = $wpsc_hide_show_priority == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Show', 'supportcandy' ) . '</option>';
			$selected = $wpsc_hide_show_priority == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Hide', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_view_more"><?php _e( 'Ticket Thread View More', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Enable/Disable View More button in individual ticket thread. This will show full content of the thread if disabled.", "supportcandy" ); ?></p>
        <select class="form-control" name="wpsc_view_more" id="wpsc_view_more">
			<?php
			$wpsc_view_more = get_option( 'wpsc_view_more' );
			$selected       = $wpsc_view_more == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $wpsc_view_more == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_sign_out"><?php _e( 'Ticket ID', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "This is applicable for Ticket id being created for new ticket.", "supportcandy" ); ?></p>
        <select class="form-control" name="support_ticket_id_type" id="support_ticket_id_type">
			<?php
			$support_ticket_id_type = get_option( 'support_ticket_id_type' );
			$selected            = $support_ticket_id_type == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Sequential', 'supportcandy' ) . '</option>';
			$selected = $support_ticket_id_type == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Random', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_custom_ticket_number"><?php _e( 'Starting Ticket ID', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "This is applicable only for Sequential ticket id type.", "supportcandy" ); ?></p>
        <div class="row">
            <div class="col-sm-6" style="padding-left:0px;" id="wpsc_custom_ticket_start_number">
                <input type="number" class="form-control" name="wpsc_custom_ticket_count" id="wpsc_custom_ticket_count"
                       value="<?php echo htmlentities( $wpsc_custom_ticket_count ) ?>"/>
            </div>
            <div class="col-sm-6">
                <button id="support_ticket_count_btn" type="button" class="btn btn-success"
                        onclick="wpsc_custom_ticket_number();"><?php _e( 'Save', 'supportcandy' ); ?></button>
                <img class="wpsc_submit_wait_1" style="display:none;"
                     src="<?php echo WPSC_PLUGIN_URL . 'asset/images/ajax-loader@2x.gif'; ?>">
                <input type="hidden" name="action" value="wpsc_settings"/>
                <input type="hidden" name="setting_action" value="custom_start_ticket_number"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="wpsc_thread_limit"><?php _e( 'Ticket History Limit', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Number of threads for ticket history macro.", "supportcandy" ); ?></p>
        <div class="row">
            <input type="number" class="form-control" name="wpsc_thread_limit" id="wpsc_thread_limit"
                   value="<?php echo htmlentities( $wpsc_thread_limit ) ?>"/>
        </div>
    </div>

    <button type="submit" class="btn btn-success"><?php _e( 'Save Changes', 'supportcandy' ); ?></button>
    <img class="wpsc_submit_wait" style="display:none;"
         src="<?php echo WPSC_PLUGIN_URL . 'asset/images/ajax-loader@2x.gif'; ?>">
    <input type="hidden" name="action" value="wpsc_settings"/>
    <input type="hidden" name="setting_action" value="set_advanced_settings"/>
</form>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#wpsc_thread_date_format').change(function () {
            if (this.value == 'timestamp') {
                jQuery('#date_time_format').show();
            } else {
                jQuery('#date_time_format').hide();
            }
        });
    });
</script>

