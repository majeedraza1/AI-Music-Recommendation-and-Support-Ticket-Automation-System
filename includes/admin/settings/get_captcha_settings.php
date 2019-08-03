<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $current_user, $wpdb;
if ( ! ( $current_user->ID && $current_user->has_cap( 'manage_options' ) ) ) {
	exit;
}
?>

<form id="wpsc_frm_captcha_settings" method="post" action="javascript:wpsc_set_captcha_settings();">
    <div class="form-group">
        <label for="wpsc_captcha"><?php _e( 'Allow Captcha', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Enable or disable Captcha in ticket form.", "wpsc" ); ?></p>
        <select class="form-control" name="wpsc_captcha" id="wpsc_captcha">
			<?php
			$wpsc_captcha = get_option( 'wpsc_captcha' );
			$selected     = $wpsc_captcha == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'Enable', 'supportcandy' ) . '</option>';
			$selected = $wpsc_captcha == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Disable', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group">
        <label for="wpsc_recaptcha_type"><?php _e( 'Recaptcha Type', 'supportcandy' ); ?></label>
        <p class="help-block"><?php _e( "Select the captcha type for Supportcandy on create ticket page.", "supportcandy" ); ?>
			<?php echo sprintf( __( '%1$s for steps to get Site Key and Secret Key for Google Recaptcha.', 'supportcandy' ), '<a href="https://supportcandy.net/knowledgebase/captcha/" target="_blank">Click here</a>' ) ?></p>

        <select class="form-control" name="wpsc_recaptcha_type" id="wpsc_recaptcha_type">
			<?php
			$wpsc_recaptcha_type = get_option( 'wpsc_recaptcha_type' );
			$selected            = $wpsc_recaptcha_type == '1' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="1">' . __( 'SupportCandy Captcha', 'supportcandy' ) . '</option>';
			$selected = $wpsc_recaptcha_type == '0' ? 'selected="selected"' : '';
			echo '<option ' . $selected . ' value="0">' . __( 'Google reCaptcha', 'supportcandy' ) . '</option>';
			?>
        </select>
    </div>

    <div class="form-group" id="wpsc_get_site_key" style="display:none;">
        <label for="wpsc_recaptcha_type"><?php _e( 'Enter Site Key', 'supportcandy' ); ?></label>
        <input type="text" class="form-control" name="wpsc_get_site_key" id="wpsc_get_site_key"
               value="<?php echo get_option( 'wpsc_get_site_key' ); ?>">
    </div>

    <div class="form-group" id="wpsc_get_secret_key" style="display:none;">
        <label for="wpsc_get_secret_key"><?php _e( 'Enter Secret Key', 'supportcandy' ); ?></label>
        <input type="text" class="form-control" name="wpsc_get_secret_key" id="wpsc_get_secret_key"
               value="<?php echo get_option( 'wpsc_get_secret_key' ); ?>">
    </div>

    <button type="submit" class="btn btn-success"><?php _e( 'Save Changes', 'supportcandy' ); ?></button>
    <img class="wpsc_submit_wait" style="display:none;"
         src="<?php echo WPSC_PLUGIN_URL . 'asset/images/ajax-loader@2x.gif'; ?>">
    <input type="hidden" name="action" value="wpsc_settings"/>
    <input type="hidden" name="setting_action" value="set_captcha_settings"/>
</form>
<script>

    jQuery(document).ready(function () {
		<?php
		if( $wpsc_recaptcha_type == '0' ){
		?>
        jQuery('#wpsc_get_site_key').show();
        jQuery('#wpsc_get_secret_key').show();
		<?php
		}
		?>
        jQuery('#wpsc_recaptcha_type').change(function () {
            if (this.value == 0) {
                jQuery('#wpsc_get_site_key').show();
                jQuery('#wpsc_get_secret_key').show();
            } else {
                jQuery('#wpsc_get_site_key').hide();
                jQuery('#wpsc_get_secret_key').hide();
            }
        });
    });

    function wpsc_set_captcha_settings() {

        if (jQuery("#wpsc_recaptcha_type").val() == '0') {
            if (!jQuery("input[name=wpsc_get_site_key]").val()) {
                alert("<?php _e( 'Enter site key', 'supportcandy' )?>");
                return;
            }
            if (!jQuery("input[name=wpsc_get_secret_key]").val()) {
                alert("<?php _e( 'Enter secret key', 'supportcandy' )?>");
                return;
            }
        }
        jQuery('.wpsc_submit_wait').show();
        var dataform = new FormData(jQuery('#wpsc_frm_captcha_settings')[0]);
        jQuery.ajax({
            url: wpsc_admin.ajax_url,
            type: 'POST',
            data: dataform,
            processData: false,
            contentType: false
        })
            .done(function (response_str) {
                var response = JSON.parse(response_str);
                jQuery('.wpsc_submit_wait').hide();
                if (response.sucess_status == '1') {
                    jQuery('#wpsc_alert_success .wpsc_alert_text').text(response.messege);
                }
                jQuery('#wpsc_alert_success').slideDown('fast', function () {
                });
                setTimeout(function () {
                    jQuery('#wpsc_alert_success').slideUp('fast', function () {
                    });
                }, 3000);
            });
    }
</script>