<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpscfunction;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}

$term_id = isset( $_POST ) && isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
if ( ! $term_id ) {
	die();
}

$term = get_term_by( 'id', $term_id, 'support_ticket_notification' );

$agent_role         = get_option( 'support_ticket_agent_roles' );
$notification_types = $wpscfunction->get_email_notification_types();
?>
<h4 style="margin-bottom:20px;"><?php _e( 'Edit email notification', 'supportcandy' ); ?></h4>

<form id="wpsc_frm_general_settings" method="post" action="javascript:wpsc_set_edit_ticket_notification();">

    <div class="form-group">
        <label for="support_ticket_notification_title"><?php _e( 'Title', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Title to show in notification list. Please make sure title you are entering is not already available in other notifications.', 'supportcandy' ); ?></p>
        <input type="text" class="form-control" name="support_ticket_notification_title" id="support_ticket_notification_title"
               value="<?php echo $term->name ?>"/>
    </div>

	<?php $type = get_term_meta( $term_id, 'type', true ) ?>
    <div class="form-group">
        <label for="support_ticket_notification_type"><?php _e( 'Type', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Select event to send this email.', 'supportcandy' ); ?></p>
        <select class="form-control" name="support_ticket_notification_type" id="support_ticket_notification_type">
			<?php foreach ( $notification_types as $key => $value ) : ?>
                <option <?php echo $key == $type ? 'selected="selected"' : '' ?>
                        value="<?php echo $key ?>"><?php echo htmlentities( $value ) ?></option>
			<?php endforeach; ?>
        </select>
    </div>

	<?php $subject = get_term_meta( $term_id, 'subject', true );
	?>
    <div class="form-group">
        <label for="support_ticket_notification_subject"><?php _e( 'Email Subject', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Subject for email to send.', 'supportcandy' ); ?></p>
        <input type="text" class="form-control" name="support_ticket_notification_subject" id="support_ticket_notification_subject"
               value="<?php echo htmlentities( stripcslashes( $subject ) ) ?>"/>
    </div>

	<?php $body = get_term_meta( $term_id, 'body', true ) ?>
    <div class="form-group">
        <label for="support_ticket_notification_body"><?php _e( 'Email Body', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Body for email to send. Use macros for ticket specific details. Macros will get replaced by its value while sending an email.', 'supportcandy' ); ?></p>
        <textarea type="text" class="form-control" name="support_ticket_notification_body"
                  id="support_ticket_notification_body"><?php echo htmlentities( $body ) ?></textarea>
        <div class="row attachment_link">
            <span onclick="wpsc_get_templates(); "><?php _e( 'Insert Macros', 'supportcandy' ) ?></span>
        </div>
    </div>

	<?php $recipients = get_term_meta( $term_id, 'recipients', true ) ?>
    <div class="form-group">
        <label for=""><?php _e( 'Recipients', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( 'Select roles who will receive email notifications. Assigned Agent will be none if type is New Ticket. If you want to automate assign agent for new ticket, you can purchase our <strong>Assign Agent Rules</strong> add-on.', 'supportcandy' ); ?></p>
        <div class="row">
            <div class="col-sm-4" style="margin-bottom:10px; display:flex;">
                <div style="width:25px;"><input
                            type="checkbox" <?php echo in_array( 'customer', $recipients ) ? 'checked="checked"' : '' ?>
                            name="support_ticket_notification_recipients[]" value="customer"/></div>
                <div style="padding-top:3px;"><?php _e( 'Customer', 'supportcandy' ) ?></div>
            </div>
            <div class="col-sm-4" style="margin-bottom:10px; display:flex;">
                <div style="width:25px;"><input
                            type="checkbox" <?php echo in_array( 'assigned_agent', $recipients ) ? 'checked="checked"' : '' ?>
                            name="support_ticket_notification_recipients[]" value="assigned_agent"/></div>
                <div style="padding-top:3px;"><?php _e( 'Assigned Agent', 'supportcandy' ) ?></div>
            </div>
			<?php foreach ( $agent_role as $key => $role ) : ?>
                <div class="col-sm-4" style="margin-bottom:10px; display:flex;">
                    <div style="width:25px;"><input
                                type="checkbox" <?php echo in_array( $key, $recipients ) ? 'checked="checked"' : '' ?>
                                name="support_ticket_notification_recipients[]" value="<?php echo $key ?>"/></div>
                    <div style="padding-top:3px;"><?php echo $role['label'] . ' ' . __( '(all agents)', 'supportcandy' ) ?></div>
                </div>
			<?php endforeach; ?>

			<?php do_action( 'wpsp_en_after_edit_recipients', $recipients ); ?>

        </div>
    </div>

	<?php $additional_recipients = get_term_meta( $term_id, 'extra_recipients', true ) ?>
    <div class="form-group">
        <label for="support_ticket_notification_extra_recipients"><?php _e( 'Additional Recipients', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( '(Optional) Enter additional recipient email address. One email per line.', 'supportcandy' ); ?></p>
        <textarea style="height:100px !important" class="form-control" name="support_ticket_notification_extra_recipients"
                  id="support_ticket_notification_extra_recipients"><?php echo stripcslashes( implode( '\n', $additional_recipients ) ) ?></textarea>
    </div>

	<?php $conditions = get_term_meta( $term_id, 'conditions', true ) ?>
    <div class="form-group">
        <label for=""><?php _e( 'Conditions', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( '(Optional) Email will only send when all condition matches.', 'supportcandy' ); ?></p>
		<?php $wpscfunction->load_conditions_ui( 'wpsc_edit_en_conditions', $conditions ); ?>
    </div>

	<?php do_action( 'wpsc_get_edit_ticket_notification', $term_id ); ?>

    <button type="submit" class="btn btn-success"><?php _e( 'Save Changes', 'supportcandy' ); ?></button>
    <img class="wpsc_submit_wait" style="display:none;"
         src="<?php echo WPSC_PLUGIN_URL . 'asset/images/ajax-loader@2x.gif'; ?>">
    <input type="hidden" name="action" value="wpsc_email_notifications"/>
    <input type="hidden" name="setting_action" value="set_edit_ticket_notification"/>
    <input type="hidden" name="term_id" value="<?php echo htmlentities( $term_id ) ?>"/>

</form>

<script>
    tinymce.remove();
    tinymce.init({
        selector: '#support_ticket_notification_body',
        body_id: 'email_body',
        menubar: false,
        statusbar: false,
        height: '200',
        plugins: [
            'lists link image directionality'
        ],
        image_advtab: true,
        toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image',
        branding: false,
        autoresize_bottom_margin: 20,
        browser_spellcheck: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        setup: function (editor) {
        }
    });
</script>