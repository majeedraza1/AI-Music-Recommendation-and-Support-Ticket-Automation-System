<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'support_ticket_Form_Field' ) ) :

	class support_ticket_List {

		var $slug;
		var $type;
		var $label;
		var $extra_info;
		var $status;
		var $options;
		var $required;
		var $width;
		var $col_class;
		var $visibility;
		var $visibility_conditions;

		function print_field( $field ) {

			$this->slug                  = $field->slug;
			$this->type                  = get_term_meta( $field->term_id, 'wpsc_tf_type', true );
			$this->label                 = get_term_meta( $field->term_id, 'wpsc_tf_label', true );
			$this->extra_info            = get_term_meta( $field->term_id, 'wpsc_tf_extra_info', true );
			$this->status                = get_term_meta( $field->term_id, 'wpsc_tf_status', true );
			$this->options               = get_term_meta( $field->term_id, 'wpsc_tf_options', true );
			$this->required              = get_term_meta( $field->term_id, 'wpsc_tf_required', true );
			$this->width                 = get_term_meta( $field->term_id, 'wpsc_tf_width', true );
			$this->visibility            = get_term_meta( $field->term_id, 'wpsc_tf_visibility', true );
			$this->visibility_conditions = is_array( $this->visibility ) && $this->visibility ? implode( ';;', $this->visibility ) : '';
			$this->visibility_conditions = str_replace( '"', '&quot;', $this->visibility_conditions );
			$this->col_class             = 'col-sm-12';

			if ( $this->type == '0' ) {
				switch ( $field->slug ) {

					case 'customer_name':
						$this->print_customer_name( $field );
						break;

					case 'customer_email':
						$this->print_customer_email( $field );
						break;

					case 'ticket_subject':
						if ( $this->status == '1' ) {
							$this->print_ticket_subject( $field );
						}
						break;

					case 'ticket_description':
						if ( $this->status == '1' ) {
							$this->print_ticket_description( $field );
						}
						break;

					case 'ticket_category':
						if ( $this->status == '1' ) {
							$this->print_ticket_category( $field );
						}
						break;

					case 'ticket_priority':
						if ( $this->status == '1' ) {
							$this->print_ticket_priority( $field );
						}
						break;

					default:
						do_action( 'wpsc_print_edit_default_form_field', $field, $this );
						break;
				}

			} else {

				switch ( $this->type ) {

					case '1':
						$this->print_text_field( $field );
						break;

					case '2':
						$this->print_drop_down( $field );
						break;

					case '3':
						$this->print_checkbox( $field );
						break;

					case '4':
						$this->print_radio_btn( $field );
						break;

					case '5':
						$this->print_textarea( $field );
						break;

					case '6':
						$this->print_date( $field );
						break;

					case '7':
						$this->print_url( $field );
						break;

					case '8':
						$this->print_email( $field );
						break;

					case '9':
						$this->print_numberonly( $field );
						break;

					case '10':
						$this->print_file_attachment( $field );
						break;

					default:
						do_action( 'wpsc_print_edit_custom_form_field', $field, $this );
						break;
				}

			}

		}

		function print_text_field( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			?>
            <div data-fieldtype="text" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_textfield"
                       name="<?php echo $this->slug; ?>" autocomplete="off" value="<?php echo $label ?>">
            </div>
			<?php
		}

		function print_drop_down( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			?>
            <div data-fieldtype="dropdown" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <select id="<?php echo $this->slug; ?>" class="form-control wpsc_drop_down"
                        name="<?php echo $this->slug; ?>">
                    <option value=""></option>
					<?php
					foreach ( $this->options as $key => $val ) :
						$value = trim( stripcslashes( $val ) );
						$selected = $value == $label ? 'selected="selected"' : '';
						?>

                        <option <?php echo $selected ?>
                                value="<?php echo $value ?>"><?php echo stripcslashes( $val ) ?></option>

					<?php
					endforeach;
					?>
                </select>
            </div>
			<?php
		}

		function print_checkbox( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug );
			?>
            <div data-fieldtype="checkbox" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
				<?php
				foreach ( $this->options as $key => $value ) :
					$checked = in_array( $value, $label ) ? 'checked="checked"' : '';
					?>
                    <div class="row">
                        <div class="col-sm-12" style="margin-bottom:10px; display:flex;">
                            <div style="width:25px;"><input <?php echo $checked ?> type="checkbox" class="wpsc_checkbox"
                                                                                   name="<?php echo $this->slug ?>[]"
                                                                                   value="<?php echo str_replace( '"', '&quot;', $value ) ?>">
                            </div>
                            <div style="padding-top:3px;"><?php echo htmlentities( $value ) ?></div>
                        </div>
                    </div>
				<?php
				endforeach;
				?>
            </div>
			<?php
		}

		function print_radio_btn( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			?>
            <div data-fieldtype="radio" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
				<?php
				foreach ( $this->options as $key => $value ) :
					$checked = $value == $label ? 'checked="checked"' : '';
					?>
                    <div class="row">
                        <div class="col-sm-12" style="margin-bottom:10px; display:flex;">
                            <div style="width:25px;"><input <?php echo $checked ?>type="radio" class="wpsc_radio_btn"
                                                            name="<?php echo $this->slug ?>"
                                                            value="<?php echo str_replace( '"', '&quot;', $value ) ?>">
                            </div>
                            <div style="padding-top:3px;"><?php echo htmlentities( $value ) ?></div>
                        </div>
                    </div>
				<?php
				endforeach;
				?>
            </div>
			<?php
		}

		function print_textarea( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			$data      = stripslashes( $label );
			?>
            <div data-fieldtype="textarea" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <textarea id="<?php echo $this->slug; ?>" class="wpsc_textarea"
                          name="<?php echo $this->slug; ?>"><?php echo htmlentities( $data ) ?></textarea>
            </div>
			<?php
		}

		function print_date( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$value     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			$date      = '';
			if ( strlen( $value ) != 0 ) {
				$date = $wpscfunction->datetimeToCalenderFormat( $value );
			}
			?>
            <div data-fieldtype="date" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_date"
                       name="<?php echo $this->slug; ?>" autocomplete="off" value="<?php echo $date; ?>">
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery(".wpsc_date").datepicker({
                        dateFormat: '<?php echo get_option( 'wpsc_calender_date_format' )?>',
                        showAnim: 'slideDown',
                        changeMonth: true,
                        changeYear: true,
                        yearRange: "-50:+50",
                    });
                });
            </script>
			<?php
		}

		function print_url( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			?>
            <div data-fieldtype="url" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_url"
                       name="<?php echo $this->slug; ?>" autocomplete="off" value="<?php echo $label ?>">
            </div>
			<?php
		}

		function print_email( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			?>
            <div data-fieldtype="email" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_email"
                       name="<?php echo $this->slug; ?>" autocomplete="off" value="<?php echo $label ?>">
            </div>
			<?php
		}

		function print_numberonly( $field ) {
			global $wpscfunction;
			$ticket_id = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$label     = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug, true );
			?>
            <div data-fieldtype="number" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="number" id="<?php echo $this->slug; ?>" class="form-control wpsc_numberonly"
                       name="<?php echo $this->slug; ?>" autocomplete="off" value="<?php echo $label ?>">
            </div>
			<?php
		}

		function print_customer_name( $field ) {
			global $current_user;
			?>
            <div data-fieldtype="text" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?><?php echo $this->required ? '<span style="color:red;">*</span>' : ''; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_customer_name"
                       name="<?php echo $this->slug; ?>" autocomplete="off"
                       value="<?php echo is_user_logged_in() ? $current_user->display_name : '' ?>">
            </div>
			<?php
		}

		function print_customer_email( $field ) {
			global $current_user;
			?>
            <div data-fieldtype="email" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?><?php echo $this->required ? '<span style="color:red;">*</span>' : ''; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_email"
                       name="<?php echo $this->slug; ?>" autocomplete="off"
                       value="<?php echo is_user_logged_in() ? $current_user->user_email : '' ?>">
            </div>
			<?php
		}

		function print_ticket_subject( $field ) {
			?>
            <div data-fieldtype="text" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?><?php echo $this->required ? '<span style="color:red;">*</span>' : ''; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <input type="text" id="<?php echo $this->slug; ?>" class="form-control wpsc_subject"
                       name="<?php echo $this->slug; ?>" autocomplete="off" value="">
            </div>
			<?php
		}

		function print_file_attachment( $field ) {
			global $wpscfunction;
			$ticket_id        = isset( $_POST['ticket_id'] ) ? intval( $_POST['ticket_id'] ) : 0;
			$ticket_auth_code = $wpscfunction->get_ticket_fields( $ticket_id, 'ticket_auth_code' );
			$auth_id          = $ticket_auth_code;
			$attachments      = $wpscfunction->get_ticket_meta( $ticket_id, $field->slug );
			?>
            <div data-fieldtype="file_attachment" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?>
                </label>
                <div class="row attachment" style="margin-bottom:10px;">
                    <div class="row attachment_link">
                        <span onclick="support_ticket_attachment_upload('<?php echo 'attach_' . $field->term_id ?>','<?php echo $field->slug ?>');"><?php _e( 'Attach file', 'supportcandy' ) ?></span>
                    </div>
                    <div id="<?php echo 'attach_' . $field->term_id ?>" class="row attachment_container"></div>
                </div>
				<?php if ( $attachments ): ?>
                    <table class="support_ticket_attachment_tbl">
                        <tbody>
						<?php
						foreach ( $attachments

						as $attachment ):
						$attach      = array();
						$attach_meta = get_term_meta( $attachment );
						foreach ( $attach_meta as $key => $value ) {
							$attach[ $key ] = $value[0];
						}
						$upload_dir   = wp_upload_dir();
						$file_url     = $upload_dir['baseurl'] . '/wpsc/' . $attach['save_file_name'];
						$download_url = $attach['is_image'] ? $file_url : site_url( '/' ) . '?support_ticket_attachment=' . $attachment . '&tid=' . $ticket_id . '&tac=' . $auth_id;
						?>
                        <tr>
                            <td>
                                <div class="" id="<?php echo 'attach_' . $attachment; ?>">
                                    <input type="hidden" name="<?php echo $field->slug ?>[]"
                                           value="<?php echo htmlentities( $attachment ) ?>">
                                    <span style="padding: 7px;"><a href="<?php echo $download_url ?>"
                                                                   target="_blank"><?php echo $attach['filename']; ?></a></span>
                                    <span onclick="wpsc_delete_attached_files('<?php echo $attachment ?>','<?php echo $ticket_id ?>','<?php echo $field->slug ?>');"
                                          class="fa fa-trash"></span>
                                </div>
                            </td>
                        </tr>
                        <div>
							<?php endforeach; ?>
                        </tbody>
                    </table>
				<?php endif; ?>
            </div>
			<?php
		}

		function print_ticket_category( $field ) {
			?>
            <div data-fieldtype="dropdown" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?><?php echo $this->required ? '<span style="color:red;">*</span>' : ''; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <select id="<?php echo $this->slug; ?>" class="form-control wpsc_category"
                        name="<?php echo $this->slug; ?>">
                    <option value=""></option>
					<?php
					$categories = get_terms( [
						'taxonomy'   => 'ticket_category',
						'hide_empty' => false,
						'orderby'    => 'meta_value_num',
						'order'      => 'ASC',
						'meta_query' => array( 'order_clause' => array( 'key' => 'support_ticket_category_menu_order' ) ),
					] );
					foreach ( $categories as $category ) :
						echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
					endforeach;
					?>
                </select>
            </div>
			<?php
		}

		function print_ticket_priority( $field ) {
			?>
            <div data-fieldtype="dropdown" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label" for="<?php echo $this->slug; ?>">
					<?php echo $this->label; ?><?php echo $this->required ? '<span style="color:red;">*</span>' : ''; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <select id="<?php echo $this->slug; ?>" class="form-control wpsc_priority"
                        name="<?php echo $this->slug; ?>">
                    <option value=""></option>
					<?php
					$priorities = get_terms( [
						'taxonomy'   => 'ticket_priority',
						'hide_empty' => false,
						'orderby'    => 'meta_value_num',
						'order'      => 'ASC',
						'meta_query' => array( 'order_clause' => array( 'key' => 'support_ticket_priority_menu_order' ) ),
					] );
					foreach ( $priorities as $priority ) :
						echo '<option value="' . $priority->term_id . '">' . $priority->name . '</option>';
					endforeach;
					?>
                </select>
            </div>
			<?php
		}

		function print_ticket_description( $field ) {
			?>
            <div data-fieldtype="tinymce" data-visibility="<?php echo $this->visibility_conditions ?>"
                 class="<?php echo $this->col_class ?>  <?php echo $this->required ? 'wpsc_required' : '' ?> form-group wpsc_form_field <?php echo 'field_' . $field->term_id ?>">
                <label class="wpsc_ct_field_label">
					<?php echo $this->label; ?><?php echo $this->required ? '<span style="color:red;">*</span>' : ''; ?>
                </label>
				<?php if ( $this->extra_info ) { ?><p class="help-block"><?php echo $this->extra_info; ?></p><?php } ?>
                <textarea id="<?php echo $this->slug; ?>" class="form-control wpsc_description"
                          name="<?php echo $this->slug; ?>"></textarea>
                <div class="row attachment" style="margin-bottom:20px;">
                    <div class="row attachment_link">
                        <span onclick="support_ticket_attachment_upload('<?php echo 'attach_' . $field->term_id ?>','desc_attachment');"><?php _e( 'Attach file', 'supportcandy' ) ?></span>
                        <span onclick="wpsc_get_templates()"><?php _e( 'Insert Macros', 'supportcandy' ) ?></span>
                        <span>Canned Reply</span>
                    </div>
                    <div id="<?php echo 'attach_' . $field->term_id ?>" class="row attachment_container"></div>
                </div>
            </div>
            <script type="text/javascript">
                tinymce.remove();
                tinymce.init({
                    selector: '#<?php echo $this->slug;?>',
                    body_id: '<?php echo $this->slug;?>',
                    menubar: false,
                    statusbar: false,
                    height: '150',
                    plugins: [
                        'lists link image directionality'
                    ],
                    toolbar: 'bold italic underline blockquote | alignleft aligncenter alignright | bullist numlist | rtl | link image | wpsc_templates',
                    branding: false,
                    autoresize_bottom_margin: 20,
                    browser_spellcheck: true,
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true
                });
            </script>
			<?php
		}


	}


endif;