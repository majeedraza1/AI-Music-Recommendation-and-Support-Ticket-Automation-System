<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}
?>

<form id="wpsc_frm_general_settings" method="post" action="javascript:wpsc_set_en_general_settings();">

    <div class="form-group">
        <label for="support_ticket_notification_from_name"><?php _e( 'From Name', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Emails to send by this name.', 'supportcandy' ); ?></p>
        <input type="text" class="form-control" name="support_ticket_notification_from_name" id="support_ticket_notification_from_name"
               value="<?php echo get_option( 'support_ticket_notification_from_name', '' ); ?>"/>
    </div>

    <div class="form-group">
        <label for="support_ticket_notification_from_email"><?php _e( 'From Email', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Emails to send from this email.', 'supportcandy' ); ?></p>
        <input type="text" class="form-control" name="support_ticket_notification_from_email" id="support_ticket_notification_from_email"
               value="<?php echo get_option( 'support_ticket_notification_from_email', '' ); ?>"/>
    </div>

    <div class="form-group">
        <label for="support_ticket_notification_reply_to"><?php _e( 'Reply to', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( '(Optional) When recipients reply to the notification from their inbox, reply will be sent to this email address.', 'supportcandy' ); ?></p>
        <input type="text" class="form-control" name="support_ticket_notification_reply_to" id="support_ticket_notification_reply_to"
               value="<?php echo get_option( 'support_ticket_notification_reply_to', '' ); ?>"/>
    </div>

    <div class="form-group">
        <label for="support_ticket_notification_ignore_emails"><?php _e( 'Ignore Emails', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Emails will not be sent to these email addresses. New email should begin on new line.', 'supportcandy' ); ?></p>
		<?php
		$ignore_emails = get_option( 'support_ticket_notification_ignore_emails', array() );
		?>
        <textarea class="form-control" style="height:100px !important;" name="support_ticket_notification_ignore_emails"
                  id="support_ticket_notification_ignore_emails"><?php echo stripcslashes( implode( '\n', $ignore_emails ) ) ?></textarea>
    </div>

	<?php do_action( 'wpsc_get_gerneral_settings' ); ?>

    <button type="submit" class="btn btn-success"><?php _e( 'Save Changes', 'supportcandy' ); ?></button>
    <img class="wpsc_submit_wait" style="display:none;"
         src="<?php echo WPSC_PLUGIN_URL . 'asset/images/ajax-loader@2x.gif'; ?>">
    <input type="hidden" name="action" value="wpsc_email_notifications"/>
    <input type="hidden" name="setting_action" value="set_en_general_settings"/>

</form>
