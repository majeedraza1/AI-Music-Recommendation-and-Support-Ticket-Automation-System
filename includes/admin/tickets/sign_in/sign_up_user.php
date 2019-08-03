<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $current_user;
$wpsc_appearance_signup = get_option( 'wpsc_appearance_signup' );
$general_appearance     = get_option( 'wpsc_appearance_general_settings' );

?>
<div class="col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
    <h2 class="form-signup-heading"><?php echo __( 'Please Sign Up', 'supportcandy' ) ?></h2>
    <form id="wpsc_frm_signup_user" method="post">
        <div class="form-group">
            <label for="wpsc_register_user_first_name"
                   style="color:<?php echo $general_appearance['wpsc_text_color'] ?> !important;"><?php _e( 'First Name', 'supportcandy' ); ?></label>
            <input type="text" id="wpsc_user_first_name" class="form-control" name="wpsc_user_first_name"/>
            <div id="wpsc_register_username_error"></div>
        </div>

        <div class="form-group">
            <label for="wpsc_register_user_last_name"
                   style="color:<?php echo $general_appearance['wpsc_text_color'] ?> !important;"><?php _e( 'Last Name', 'supportcandy' ); ?></label>
            <input type="text" id="wpsc_user_last_name" class="form-control" name="wpsc_user_last_name"/>
            <div id="wpsc_register_username_error"></div>
        </div>

        <div class="form-group">
            <label for="wpsc_register_user_name"
                   style="color:<?php echo $general_appearance['wpsc_text_color'] ?> !important;"><?php _e( 'Username', 'supportcandy' ); ?></label>
            <input type="text" id="wpsc_user_name" class="form-control" name="wpsc_user_name"/>
            <div id="wpsc_register_username_error"></div>
        </div>

        <div class="form-group">
            <label for="wpsc_register_email"
                   style="color:<?php echo $general_appearance['wpsc_text_color'] ?> !important;"><?php _e( 'Email', 'supportcandy' ); ?></label>
            <input id="wpsc_email" class="form-control" name="wpsc_email"/>
            <div id="wpsc_register_email_error"></div>
        </div>

        <div class="form-group">
            <label for="wpsc_register_pass"
                   style="color:<?php echo $general_appearance['wpsc_text_color'] ?> !important;"><?php _e( 'Password', 'supportcandy' ); ?></label>
            <input type="password" id="wpsc_password" class="form-control" name="wpsc_password"/>
        </div>

        <div class="form-group">
            <label for="wpsc_register_confirmpass"
                   style="color:<?php echo $general_appearance['wpsc_text_color'] ?> !important;"><?php _e( 'Confirm Password', 'supportcandy' ); ?></label>
            <input type="password" id="wpsc_confirmpassword" class="form-control" name="wpsc_confirmpassword"/>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-sm" name='btnsubmit' onclick="javascript:wpsc_register_user(event);"
                    style="background-color:<?php echo $wpsc_appearance_signup['wpsc_appearance_signup_button_bg_color'] ?> !important; color:<?php echo $wpsc_appearance_signup['wpsc_appearance_signup_button_text_color'] ?> !important; border-color:<?php echo $wpsc_appearance_signup['wpsc_appearance_signup_button_border_color'] ?> !important;"><?php _e( 'Register Now', 'supportcandy' ) ?></button>
            <button type="cancel" class="btn btn-sm" name='btncancel' onclick="javascript:wpsc_sign_in();"
                    style="background-color:<?php echo $wpsc_appearance_signup['wpsc_appearance_cancel_button_bg_color'] ?> !important;color:<?php echo $wpsc_appearance_signup['wpsc_appearance_cancel_button_text_color'] ?> !important; border-color:<?php echo $wpsc_appearance_signup['wpsc_appearance_cancel_button_border_color'] ?> !important;"><?php _e( 'Cancel', 'supportcandy' ); ?></button>
        </div>
    </form>
</div>


<script type="text/javascript">

    function wpsc_register_user(event) {
        event.preventDefault();
        jQuery('#wpsc_register_email_error').html('');
        jQuery('#wpsc_register_username_error').html('');

        var username = jQuery('#wpsc_user_name').val();
        var email = jQuery('#wpsc_email').val();
        var password = jQuery('#wpsc_password').val();
        var confirmpass = jQuery('#wpsc_confirmpassword').val();
        var firstname = jQuery('#wpsc_user_first_name').val();
        var lastname = jQuery('#wpsc_user_last_name').val();

        var check_flag = true;

        if (username.trim() == "") {
            check_flag = false;
            alert('<?php _e( 'Please enter username!', 'supportcandy' )?>');
        } else if (!validateEmail(email)) {
            check_flag = false;
            alert('<?php _e( 'Please enter correct email address!', 'supportcandy' )?>');
        } else if (password.trim() == "") {
            check_flag = false;
            alert('<?php _e( 'Please enter password!', 'supportcandy' )?>');
        } else if (confirmpass.trim() == "") {
            check_flag = false;
            alert('<?php _e( 'Please confirm password!', 'supportcandy' )?>');
        } else if (password != confirmpass) {
            check_flag = false;
            alert('<?php _e( 'Password and confirm password does not match.', 'supportcandy' )?>');
        }

        if (check_flag) {
            var data = {
                action: 'wpsc_tickets',
                setting_action: 'submit_user',
                username: username,
                email: email,
                password: password,
                confirmpass: confirmpass,
                firstname: firstname,
                lastname: lastname
            };
            jQuery.post(wpsc_admin.ajax_url, data, function (response_str) {
                var response = JSON.parse(response_str);
                if (response.error == '1') {
                    jQuery('#wpsc_register_email_error').html("<?php _e( 'This email is already registered, please choose another one.', 'supportcandy' )?>");
                } else if (response.error == '2') {
                    jQuery('#wpsc_register_username_error').html("<?php _e( 'This username is already registered. Please choose another one.', 'supportcandy' )?>");
                } else {
                    jQuery('#wpsc_register_email_error').hide();
                    location.reload(true);
                }
            });
        }


    }
</script>

