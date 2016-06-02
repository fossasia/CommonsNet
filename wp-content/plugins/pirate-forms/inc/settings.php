<?php
function pirate_forms_is_localhost() {
	$server_name = strtolower( $_SERVER['SERVER_NAME'] );
	return in_array( $server_name, array( 'localhost', '127.0.0.1' ) );
}
function pirate_forms_from_email() {

	$admin_email = get_option( 'admin_email' );
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );

	if ( pirate_forms_is_localhost() ) {
		return $admin_email;
	}

	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}

	if ( strpbrk( $admin_email, '@' ) == '@' . $sitename ) {
		return $admin_email;
	}

	return 'wordpress@' . $sitename;
}

/*
 *
 * OPTIONS
 * @since 1.0.0
 * name; id; desc; type; default; options
 *
 */
function pirate_forms_plugin_options() {

	/*********************************************************/
	/************  Default values from Zerif Lite ************/
	/*********************************************************/

	$zerif_contactus_sitekey = get_theme_mod('zerif_contactus_sitekey');

	if( !empty($zerif_contactus_sitekey) ):
		$pirate_forms_contactus_sitekey = $zerif_contactus_sitekey;
	else:
		$pirate_forms_contactus_sitekey = '';
	endif;

	$zerif_contactus_secretkey = get_theme_mod('zerif_contactus_secretkey');
	if( !empty($zerif_contactus_secretkey) ):
		$pirate_forms_contactus_secretkey = $zerif_contactus_secretkey;
	else:
		$pirate_forms_contactus_secretkey = '';
	endif;

	$zerif_contactus_recaptcha_show = get_theme_mod('zerif_contactus_recaptcha_show');

	if( isset($zerif_contactus_recaptcha_show) && ($zerif_contactus_recaptcha_show == '1') ):
		$pirate_forms_contactus_recaptcha_show = '';
	else:
		$pirate_forms_contactus_recaptcha_show = 'yes';
	endif;

	$zerif_contactus_button_label = get_theme_mod('zerif_contactus_button_label',__('Send Message','zerif-lite'));
	if( !empty($zerif_contactus_button_label) ):
		$pirate_forms_contactus_button_label = $zerif_contactus_button_label;
	else:
		$pirate_forms_contactus_button_label = __( 'Send Message','pirate-forms' );
	endif;

	$zerif_contactus_email = get_theme_mod('zerif_contactus_email');
	$zerif_email = get_theme_mod('zerif_email');

	$pirate_forms_contactus_email = '';
	if( !empty($zerif_contactus_email) ):
		$pirate_forms_contactus_email = $zerif_contactus_email;
	elseif( !empty($zerif_email) ):
		$pirate_forms_contactus_email = $zerif_email;
	else:
		$pirate_forms_contactus_email = get_bloginfo( 'admin_email' );
	endif;

	return array(
		'fourth_tab' => array(
			'header_options' => array(
				__( 'Form processing options','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_email' => array(
				__( 'Contact notification sender email','pirate-forms' ),
				'<strong>'.__( "Insert [email] to use the contact form submitter's email.","pirate-forms" ).'</strong><br>'.__( "Email to use for the sender of the contact form emails both to the recipients below and the contact form submitter (if this is activated below). The domain for this email address should match your site's domain.","pirate-forms" ),
				'text',
				pirate_forms_from_email()
			),
			'pirateformsopt_email_recipients' => array(
				__( 'Contact submission recipients','pirate-forms' ),
				__( 'Email address(es) to receive contact submission notifications. You can separate multiple emails with a comma.','pirate-forms' ),
				'text',
				pirate_forms_get_key( 'pirateformsopt_email' ) ? pirate_forms_get_key( 'pirateformsopt_email' ) : $pirate_forms_contactus_email
			),
			'pirateformsopt_store' => array(
				__( 'Store submissions in the database','pirate-forms' ),
				__( 'Should the submissions be stored in the admin area? If chosen, contact form submissions will be saved in Contacts on the left (appears after this option is activated).','pirate-forms' ),
				'checkbox',
				'',
			),
			'pirateformsopt_blacklist' => array(
				__( 'Use the comments blacklist to restrict submissions','pirate-forms' ),
				__( 'Should form submission IP and email addresses be compared against the Comment Blacklist, found in','pirate-forms').'<strong>'.__('wp-admin > Settings > Discussion > Comment Blacklist?','pirate-forms').'</strong>',
				'checkbox',
				'yes',
			),
			'pirateformsopt_confirm_email' => array(
				__( 'Send email confirmation to form submitter','pirate-forms' ),
				__( 'Adding text here will send an email to the form submitter. The email uses the "Text to show when form is submitted..." field below as the subject line. Plain text only here, no HTML.','pirate-forms' ),
				'textarea',
				'',
			)
		),
		'first_tab' => array(
			'header_fields' => array(
				__( 'Fields Settings','pirate-forms' ),
				'',
				'title',
				'',
			),
			/* Name */
			'pirateformsopt_name_field' => array(
				__( 'Name','pirate-forms' ),
				__( 'Do you want the name field to be displayed?','pirate-forms' ),
				'select',
				'req',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Email */
			'pirateformsopt_email_field' => array(
				__( 'Email address','pirate-forms' ),
				__( 'Do you want the email address field be displayed?','pirate-forms' ),
				'select',
				'req',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Subject */
			'pirateformsopt_subject_field' => array(
				__( 'Subject','pirate-forms' ),
				__( 'Do you want the subject field be displayed?','pirate-forms' ),
				'select',
				'req',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Message */
			'pirateformsopt_message_field' => array(
				__( 'Message','pirate-forms' ),
				'',
				'select',
				'req',
				array(
					''    => __( 'None','pirate-forms' ),
					'yes' => __( 'Yes but not required','pirate-forms' ),
					'req' => __( 'Required','pirate-forms' ),
				),
			),
			/* Recaptcha */
			'pirateformsopt_recaptcha_field' => array(
				__( 'Add a reCAPTCHA','pirate-forms' ),
				'',
				'checkbox',
				$pirate_forms_contactus_recaptcha_show,
			),
			/* Site key */
			'pirateformsopt_recaptcha_sitekey' => array(
				__( 'Site key','pirate-forms' ),
				'<a href="https://www.google.com/recaptcha/admin#list" target="_blank">'.__( 'Create an account here ','pirate-forms' ).'</a>'.__( 'to get the Site key and the Secret key for the reCaptcha.','pirate-forms' ),
				'text',
				$pirate_forms_contactus_sitekey,
			),
			/* Secret key */
			'pirateformsopt_recaptcha_secretkey' => array(
				__( 'Secret key','pirate-forms' ),
				'',
				'text',
				$pirate_forms_contactus_secretkey,
			),

		),
		'second_tab' => array(
			'header_labels' => array(
				__( 'Fields Labels','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_label_name' => array(
				__( 'Name','pirate-forms' ),
				'',
				'text',
				__( 'Your Name','pirate-forms' ),
			),
			'pirateformsopt_label_email' => array(
				__( 'Email','pirate-forms' ),
				'',
				'text',
				__( 'Your Email','pirate-forms' )
			),
			'pirateformsopt_label_subject' => array(
				__( 'Subject','pirate-forms' ),
				'',
				'text',
				__( 'Subject','pirate-forms' )
			),
			'pirateformsopt_label_message' => array(
				__( 'Message','pirate-forms' ),
				'',
				'text',
				__( 'Your message','pirate-forms' )
			),
			'pirateformsopt_label_submit_btn' => array(
				__( 'Submit button','pirate-forms' ),
				'',
				'text',
				$pirate_forms_contactus_button_label
			)
		),
		'third_tab' => array(
			'header_messages' => array(
				__( 'Alert Messages','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_label_err_name' => array(
				__( 'Name required and missing','pirate-forms' ),
				'',
				'text',
				__( 'Enter your name','pirate-forms' )
			),
			'pirateformsopt_label_err_email' => array(
				__( 'E-mail required and missing','pirate-forms' ),
				'',
				'text',
				__( 'Enter a valid email','pirate-forms' )
			),
			'pirateformsopt_label_err_subject' => array(
				__( 'Subject required and missing','pirate-forms' ),
				'',
				'text',
				__( 'Please enter a subject','pirate-forms' )
			),
			'pirateformsopt_label_err_no_content' => array(
				__( 'Question/comment is missing','pirate-forms' ),
				'',
				'text',
				__( 'Enter your question or comment','pirate-forms' )
			),
			'pirateformsopt_label_submit' => array(
				__( 'Successful form submission text','pirate-forms' ),
				__( 'This text is used on the page if no "Thank You" URL is set above. This is also used as the confirmation email title, if one is set to send out.','pirate-forms' ),
				'text',
				__( 'Thanks, your email was sent successfully!','pirate-forms' )
			)
		),
		'fifth_tab' => array(
			'header_smtp' => array(
				__( 'SMTP Options','pirate-forms' ),
				'',
				'title',
				'',
			),
			'pirateformsopt_use_smtp' => array(
				__( 'Use SMTP to send emails?','pirate-forms' ),
				__( 'Instead of PHP mail function','pirate-forms' ),
				'checkbox',
				'',
			),
			'pirateformsopt_smtp_host' => array(
				__( 'SMTP Host','pirate-forms' ),
				'',
				'text',
				'',
			),
			'pirateformsopt_smtp_port' => array(
				__( 'SMTP Port','pirate-forms' ),
				'',
				'text',
				'',
			),
			'pirateformsopt_use_smtp_authentication' => array(
				__( 'Use SMTP Authentication?','pirate-forms' ),
				__( 'If you check this box, make sure the SMTP Username and SMTP Password are completed.','pirate-forms' ),
				'checkbox',
				'yes',
			),
			'pirateformsopt_smtp_username' => array(
				__( 'SMTP Username','pirate-forms' ),
				'',
				'text',
				'',
			),
			'pirateformsopt_smtp_password' => array(
				__( 'SMTP Password','pirate-forms' ),
				'',
				'text',
				'',
			)
		)
	);
}

/*
 *
 *  Add page to the dashbord menu
 *  @since 1.0.0
 */
function pirate_forms_add_to_admin() {

	add_submenu_page(
		'options-general.php',
		__( 'Pirate Forms settings', 'pirate-forms' ),
		__( 'Pirate Forms', 'pirate-forms' ),
		'manage_options',
		'pirate-forms-admin',
		'pirate_forms_admin' );

}
add_action( 'admin_menu', 'pirate_forms_add_to_admin' );

/*
 *
 *  Save forms via Ajax
 *  @since 1.0.0
 *
 */
add_action('wp_ajax_pirate_forms_save', 'pirate_forms_save_callback');
add_action('wp_ajax_nopriv_pirate_forms_save', 'pirate_forms_save_callback');

function pirate_forms_save_callback() {

	if( isset($_POST['dataSent']) ):
		$dataSent = $_POST['dataSent'];

		$params = array();

		if( !empty($dataSent) ):
			parse_str( $dataSent, $params );
		endif;

		if( !empty($params) ):

			update_option( 'pirate_forms_settings_array', $params );

			$pirate_forms_zerif_lite_mods = get_option('theme_mods_zerif-lite');

			if( empty($pirate_forms_zerif_lite_mods) ):
				$pirate_forms_zerif_lite_mods = array();
			endif;

				if( isset($params['pirateformsopt_label_submit_btn']) ):
					$pirate_forms_zerif_lite_mods['zerif_contactus_button_label'] = $params['pirateformsopt_label_submit_btn'];
				endif;

				if( isset($params['pirateformsopt_email']) ):
					$pirate_forms_zerif_lite_mods['zerif_contactus_email'] = $params['pirateformsopt_email'];
				endif;

				if( isset($params['pirateformsopt_email_recipients']) ):
					$pirate_forms_zerif_lite_mods['zerif_contactus_email'] = $params['pirateformsopt_email_recipients'];
				endif;

				if( isset($params['pirateformsopt_recaptcha_field']) && ($params['pirateformsopt_recaptcha_field'] == 'yes') ):
					$pirate_forms_zerif_lite_mods['zerif_contactus_recaptcha_show'] = 0;
				else:
					$pirate_forms_zerif_lite_mods['zerif_contactus_recaptcha_show'] = 1;
				endif;

				if( isset($params['pirateformsopt_recaptcha_sitekey']) ):
					$pirate_forms_zerif_lite_mods['zerif_contactus_sitekey'] = $params['pirateformsopt_recaptcha_sitekey'];
				endif;

				if( isset($params['pirateformsopt_recaptcha_secretkey']) ):
					$pirate_forms_zerif_lite_mods['zerif_contactus_secretkey'] = $params['pirateformsopt_recaptcha_secretkey'];
				endif;

				update_option('theme_mods_zerif-lite', $pirate_forms_zerif_lite_mods);



		endif;

	endif;

	die();

}

/*
 *  Admin area setting page for the plugin
 * @since 1.0.0
 *
 */
function pirate_forms_admin() {

	global $current_user;

	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	$plugin_options = pirate_forms_plugin_options();
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Pirate Forms','pirate-forms' ); ?></h1>


		<div class="pirate-options">
			<ul class="pirate-forms-nav-tabs" role="tablist">
				<li role="presentation" class="active"><a href="#0" aria-controls="how_to_use" role="tab" data-toggle="tab"><?php esc_html_e( 'How to use','pirate-forms'); ?></a></li>
				<li role="presentation"><a href="#1" aria-controls="options" role="tab" data-toggle="tab"><?php esc_html_e( 'Options','pirate-forms'); ?></a></li>
				<li role="presentation"><a href="#2" aria-controls="fields" role="tab" data-toggle="tab"><?php esc_html_e( 'Fields Settings','pirate-forms'); ?></a></li>
				<li role="presentation"><a href="#3" aria-controls="labels" role="tab" data-toggle="tab"><?php esc_html_e( 'Fields Labels','pirate-forms'); ?></a></li>
				<li role="presentation"><a href="#4" aria-controls="messages" role="tab" data-toggle="tab"><?php esc_html_e( 'Alert Messages','pirate-forms'); ?></a></li>
				<li role="presentation"><a href="#5" aria-controls="smtp" role="tab" data-toggle="tab"><?php esc_html_e( 'SMTP','pirate-forms'); ?></a></li>
			</ul>

			<div class="pirate-forms-tab-content">

				<div id="0" class="pirate-forms-tab-pane active">

					<h2 class="pirate_forms_welcome_text"><?php esc_html_e( 'Welcome to Pirate Forms!','pirate-forms' ); ?></h2>
					<p class= "pirate_forms_subheading"><?php esc_html_e( 'To get started, just ','pirate-forms'); ?><b><?php esc_html_e( 'configure all the options ','pirate-forms'); ?></b><?php  esc_html_e( 'you need, hit save and start using the created form.','pirate-forms' ); ?></p>

					<hr>

					<img class="pirate_forms_preview" src="<?php echo plugins_url( '../img/preview.png', __FILE__ ) ?>">

					<p><?php esc_html_e( 'There are 3 ways of using the newly created form:','pirate-forms' ); ?></p>
					<ol>
						<li><?php esc_html_e( 'Add a ','pirate-forms' ); ?><strong><a href="<?php echo admin_url( 'widgets.php' ); ?>"><?php esc_html_e( 'widget','pirate-forms' ); ?></a></strong></li>
						<li><?php esc_html_e( 'Use the shortcode ','pirate-forms' ); ?><strong><code>[pirate_forms]</code></strong><?php esc_html_e( ' in any page or post.','pirate-forms' ); ?></li>
						<li><?php esc_html_e( 'Use the shortcode ','pirate-forms' ); ?><strong><code>&lt;?php echo do_shortcode( '[pirate_forms]' ) ?&gt;</code></strong><?php esc_html_e( ' in the theme\'s files.','pirate-forms' ); ?></li>
					</ol>

					<hr>

					<div class="rate_plugin_invite">

						<h4><?php esc_html_e( 'Are you enjoying Pirate Forms?', 'pirate-forms' ); ?></h4>

						<p class="review-link"><?php echo sprintf( esc_html__( 'Rate our plugin on %sWordPress.org%s. We\'d really appreciate it!', 'pirate-forms' ), '<a href="https://wordpress.org/support/view/plugin-reviews/pirate-forms" target="_blank" rel="nofollow"> ', '</a>' ); ?></p>

						<p><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></p>

						<p><small><?php echo sprintf( esc_html__( 'If you want a more complex Contact Form Plugin please check %sthis link%s.', 'pirate-forms' ),  '<a href="http://www.codeinwp.com/blog/best-contact-form-plugins-wordpress/" target="_blank" >', '</a>'); ?></small></p>
					</div>


				</div>

				<?php

				$pirate_forms_nr_tabs = 1;

				foreach ( $plugin_options as $plugin_options_tab ) :

					echo '<div id="'.$pirate_forms_nr_tabs.'" class="pirate-forms-tab-pane">';

					?>
					<form method="post" class="pirate_forms_contact_settings">

						<?php
						$pirate_forms_nr_tabs++;
						foreach ( $plugin_options_tab as $key => $value ) :

							/* Label */
							if( !empty($value[0]) ):
								$opt_name = $value[0];
							endif;

							/* ID */
							$opt_id = $key;

							/* Description */
							if( !empty($value[1]) ):
								$opt_desc = $value[1];
							else:
								$opt_desc = '';
							endif;

							/* Input type */
							if( !empty($value[2]) ):
								$opt_type = $value[2];
							else:
								$opt_type = '';
							endif;

							/* Default value */
							if( !empty($value[3]) ):
								$opt_default = $value[3];
							else:
								$opt_default = '';
							endif;

							/* Value */
							$opt_val = isset( $pirate_forms_options[$opt_id] ) ? $pirate_forms_options[$opt_id] : $opt_default;

							/* Options if checkbox, select, or radio */
							$opt_options = empty( $value[4] ) ? array() : $value[4];

							switch ($opt_type) {
								case "title":

									if( !empty($opt_name) ):
										echo '<h3 class="title">'.$opt_name.'</h3><hr />';
									endif;

									break;

								case "text":
									?>

									<div class="pirate-forms-grouped">

										<label for="<?php echo $opt_id ?>"><?php echo $opt_name;

											if(!empty($opt_desc)) {

												if( ($opt_id == "pirateformsopt_email") || ($opt_id == "pirateformsopt_email_recipients") || ($opt_id == "pirateformsopt_confirm_email") ) {

													echo '<span class="dashicons dashicons-editor-help"></span>';

												}

												echo '<div class="pirate_forms_option_description">'.$opt_desc.'</div>'; } ?>

										</label>

										<input name="<?php echo $opt_id; ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type; ?>" value="<?php echo stripslashes( $opt_val ); ?>" class="widefat">
									</div>

									<?php
									break;

								case "textarea":
									?>

									<div class="pirate-forms-grouped">

										<label for="<?php echo $opt_id ?>"><?php echo $opt_name;

											if(!empty($opt_desc)) {

												if( ($opt_id == "pirateformsopt_confirm_email") ) {

													echo '<span class="dashicons dashicons-editor-help"></span>';

												}

												echo '<div class="pirate_forms_option_description">'.$opt_desc.'</div>'; } ?>

										</label>

										<textarea name="<?php echo $opt_id; ?>" id="<?php echo $opt_id ?>" type="<?php echo $opt_type; ?>" value="<?php echo stripslashes( $opt_val ); ?>" rows="5" cols="30"></textarea>
									</div>

									<?php
									break;

								case "select":
									?>
									<div class="pirate-forms-grouped">

										<label for="<?php echo $opt_id ?>"><?php echo $opt_name;

											if(!empty($opt_desc)) {

												echo '<div class="pirate_forms_option_description">'.$opt_desc.'</div>'; } ?>

										</label>

										<select name="<?php echo $opt_id ?>" id="<?php echo $opt_id; ?>">
											<?php
											foreach ( $opt_options as $key => $val ) :

												$selected = '';
												if ( $opt_val == $key )
													$selected = 'selected';
												?>
												<option value="<?php echo $key ?>" <?php echo $selected; ?>><?php echo $val; ?></option>
											<?php endforeach; ?>
										</select>


									</div>

								<?php
									break;
								case "checkbox":
									?>
									<div class="pirate-forms-grouped">

										<label for="<?php echo $opt_id ?>"><?php echo $opt_name;

											if(!empty($opt_desc)) {

												if( ($opt_id == "pirateformsopt_store") || ($opt_id == "pirateformsopt_blacklist") ) {

													echo '<span class="dashicons dashicons-editor-help"></span>';

												}

												echo '<div class="pirate_forms_option_description">'.$opt_desc.'</div>'; } ?>

										</label>

										<?php

											$checked = '';
											if (  array_key_exists( $opt_id,$pirate_forms_options ) ) {
												$checked = 'checked';
											}
											?>

											<input type="checkbox" value="yes" name="<?php echo $opt_id; ?>" id="<?php echo $opt_id; ?>" <?php echo $checked; ?>>Yes

									</div>


								<?php
									break;
							}

						endforeach;
						?>
						<input name="save" type="submit" value="<?php _e( 'Save changes', 'pirate-forms' ) ?>" class="button-primary pirate-forms-save-button">
						<input type="hidden" name="action" value="save">
						<input type="hidden" name="proper_nonce" value="<?php echo wp_create_nonce( $current_user->user_email ) ?>">

					</form><!-- .pirate_forms_contact_settings -->
					<div class="ajaxAnimation"></div>
				</div><!-- .pirate-forms-tab-pane -->

				<?php endforeach; ?>

			</div><!-- .pirate-forms-tab-content -->
		</div><!-- .pirate-options -->

		<div class="pirate-subscribe postbox card">
			<h3 class="title"><?php esc_html_e( 'Get Our Free Email Course', 'islemag' )?></h3>
			<div class="pirate-forms-subscribe-content">
				<?php
				      if(!empty($_POST["pirate_forms_mail"])){
				        require( PIRATE_FORMS_PATH . 'mailin.php' );
				        $user_info = get_userdata(1);
				    		$mailin = new Mailin("https://api.sendinblue.com/v2.0","cHW5sxZnzE7mhaYb");
				    		$data = array( "email" => $_POST["pirate_forms_mail"],
				    			"attributes" => array("NAME"=>$user_info->first_name, "SURNAME"=>$user_info->last_name),
				    			"blacklisted" => 0,
				    			"listid" => array(51),
				    			"blacklisted_sms" => 0
				    		);
				    		$status =  $mailin->create_update_user($data);
				    		if($status['code'] == 'success'){
				    				update_option( 'pirate_forms_subscribe', true);
				        }
				      }
				      $was_submited = get_option( 'pirate_forms_subscribe', false);
				      if( $was_submited == false ){
				        echo sprintf( '<p> %s </p><form class="pirate-forms-submit-mail" method="post"><input name="pirate_forms_mail" type="email" value="'.get_option( 'admin_email' ) .'" /><input class="button" type="submit" value="Submit"></form>', esc_html__('Ready to learn how to reduce your website loading times by half? Come and join the 1st lesson here!', 'pirate-forms' ) );
				      } else {
				        echo sprintf( '<p> %s </p>', esc_html__( 'Thank you for subscribing! You have been added to the mailing list and will receive the next email information in the coming weeks. If you ever wish to unsubscribe, simply use the "Unsubscribe" link included in each newsletter.', 'pirate-forms' ) );
				      } ?>
			</div>
		</div>



	</div><!-- .wrap -->

	<?php
}

/***********************************************************/
/*********** Save default options if none exist ***********/
/**********************************************************/

function pirate_forms_settings_init() {

	if ( ! get_option( 'pirate_forms_settings_array' ) ) {

		$new_opt = array();
		foreach ( pirate_forms_plugin_options() as $temparr ) {
			foreach ($temparr as $key => $opt) {
				$new_opt[$key] = $opt[3];
			}
		}

		update_option( 'pirate_forms_settings_array', $new_opt );

	}
}

add_action( 'admin_head', 'pirate_forms_settings_init' );
