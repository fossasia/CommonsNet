<?php
/*
Plugin Name: Free & Simple Contact Form Plugin - PirateForms
Plugin URI: http://themeisle.com/plugins/pirate-forms/
Description: Easily creates a nice looking, simple contact form on your WP site.
Version: 1.0.14
Author: Themeisle
Author URI: http://themeisle.com
Text Domain: pirate-forms
Domain Path: /languages
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! function_exists( 'add_action' ) ) {
	die( 'Nothing to do...' );
}

/* Important constants */
define( 'PIRATE_FORMS_VERSION', '1.0.0' );
define( 'PIRATE_FORMS_URL', plugin_dir_url( __FILE__ ) );
define( 'PIRATE_FORMS_PATH', plugin_dir_path( __FILE__ ) );

/* Required helper functions */
include_once( dirname( __FILE__ ) . '/inc/helpers.php' );
include_once( dirname( __FILE__ ) . '/inc/settings.php' );
include_once( dirname( __FILE__ ) . '/inc/widget.php' );

add_action( 'plugins_loaded', 'pirate_forms_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function pirate_forms_load_textdomain() {
	load_plugin_textdomain( 'pirate-forms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Display the contact form or a confirmation message if submitted
 *
 * @param      $atts
 * @param null $content
 *
 * @return string
 */
add_shortcode( 'pirate_forms', 'pirate_forms_display_form' );

function pirate_forms_display_form( $atts, $content = NULL ) {

	/* thank you message */
	$pirate_forms_thankyou_message = '';

	if ( isset( $_GET['pcf'] ) && $_GET['pcf'] == 1 ) {
		$pirate_forms_thankyou_message .= '
		<div class="col-sm-12 col-lg-12 pirate_forms_thankyou_wrap">
			<p>' . sanitize_text_field( pirate_forms_get_key( 'pirateformsopt_label_submit' ) ) . '</p>
		</div>';
	}

	/*********************************/
	/********** FormBuilder **********/
	/*********************************/

	if ( !class_exists('PhpFormBuilder')) {
		require_once( dirname( __FILE__ ) . '/inc/PhpFormBuilder.php' );
	}

	$pirate_form = new PhpFormBuilder();

	$pirate_form->set_att( 'id', 'pirate_forms_' . ( get_the_id() ? get_the_id() : 1 ) );
	$pirate_form->set_att( 'class', array( 'pirate_forms' ) );
	$pirate_form->set_att( 'add_nonce', get_bloginfo( 'admin_email' ) );

	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	if( !empty($pirate_forms_options) ):

		/* Count the number of requested fields from Name, Email and Subject to add a certain class col-12, col-6 or col-4 */
		$pirate_forms_required_fields = 0;

		if( !empty($pirate_forms_options['pirateformsopt_name_field']) && !empty($pirate_forms_options['pirateformsopt_label_name']) ):

				$pirateformsopt_name_field = $pirate_forms_options['pirateformsopt_name_field'];
				$pirateformsopt_name_label = $pirate_forms_options['pirateformsopt_label_name'];

				if ( !empty($pirateformsopt_name_field) && !empty($pirateformsopt_name_label) && ($pirateformsopt_name_field != '') ):
					$pirate_forms_required_fields++;
				endif;

		endif;

		if( !empty($pirate_forms_options['pirateformsopt_email_field']) && !empty($pirate_forms_options['pirateformsopt_label_email']) ):

				$pirateformsopt_email_field = $pirate_forms_options['pirateformsopt_email_field'];
				$pirateformsopt_email_label = $pirate_forms_options['pirateformsopt_label_email'];

				if ( !empty($pirateformsopt_email_field) && !empty($pirateformsopt_email_label) && ($pirateformsopt_email_field != '') ):
					$pirate_forms_required_fields++;
				endif;

		endif;

		if( !empty($pirate_forms_options['pirateformsopt_subject_field']) && !empty($pirate_forms_options['pirateformsopt_label_subject']) ):

				$pirateformsopt_subject_field = $pirate_forms_options['pirateformsopt_subject_field'];
				$pirateformsopt_subject_label = $pirate_forms_options['pirateformsopt_label_subject'];

				if ( !empty($pirateformsopt_subject_field) && !empty($pirateformsopt_subject_label) && ($pirateformsopt_subject_field != '') ):
					$pirate_forms_required_fields++;
				endif;

		endif;

		$pirate_forms_layout_input = '';

		switch ($pirate_forms_required_fields) {
			case 1:
				$pirate_forms_layout_input = 'col-sm-12 col-lg-12';
				break;
			case 2:
				$pirate_forms_layout_input = 'col-sm-6 col-lg-6';
				break;
			case 3:
				$pirate_forms_layout_input = 'col-sm-4 col-lg-4';
				break;
			default:
				$pirate_forms_layout_input = 'col-sm-4 col-lg-4';
		}

			/******************************/
			/********  Name field *********/
			/******************************/

			if ( !empty($pirateformsopt_name_field) && !empty($pirateformsopt_name_label) ):

				$required     = $pirateformsopt_name_field === 'req' ? TRUE : FALSE;
				$wrap_classes = array( $pirate_forms_layout_input.' form_field_wrap', 'contact_name_wrap pirate_forms_three_inputs ' );

				// If this field was submitted with invalid data
				if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-name'] ) ) {
					$wrap_classes[] = 'error';
				}

				$pirate_form->add_input(
					'',
					array(
						'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_name_label) ),
						'required'   => $required,
						'wrap_class' => $wrap_classes,
					),
					'pirate-forms-contact-name'
				);

			endif;

			/********************************/
			/********  Email field **********/
			/********************************/

			if ( !empty($pirateformsopt_email_field) && !empty($pirateformsopt_email_label) ):

				$required     = $pirateformsopt_email_field === 'req' ? TRUE : FALSE;
				$wrap_classes = array( $pirate_forms_layout_input.' form_field_wrap', 'contact_email_wrap pirate_forms_three_inputs ' );

				// If this field was submitted with invalid data
				if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-email'] ) ) {
					$wrap_classes[] = 'error';
				}

				$pirate_form->add_input(
					'',
					array(
						'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_email_label) ),
						'required'   => $required,
						'type'       => 'email',
						'wrap_class' => $wrap_classes,
					),
					'pirate-forms-contact-email'
				);

			endif;

			/********************************/
			/********  Subject field ********/
			/********************************/

			if ( !empty($pirateformsopt_subject_field) && !empty($pirateformsopt_subject_label) ):

				$required     = $pirateformsopt_subject_field === 'req' ? TRUE : FALSE;
				$wrap_classes = array( $pirate_forms_layout_input.' form_field_wrap', 'contact_subject_wrap pirate_forms_three_inputs ' );

				// If this field was submitted with invalid data
				if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-subject'] ) ) {
					$wrap_classes[] = 'error';
				}

				$pirate_form->add_input(
					'',
					array(
						'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_subject_label) ),
						'required'   => $required,
						'wrap_class' => $wrap_classes,
					),
					'pirate-forms-contact-subject'
				);

			endif;

			/********************************/
			/********  Message field ********/
			/********************************/

			if( !empty($pirate_forms_options['pirateformsopt_message_field']) && !empty($pirate_forms_options['pirateformsopt_label_message']) ):

				$pirateformsopt_message_field = $pirate_forms_options['pirateformsopt_message_field'];
				$pirateformsopt_message_label = $pirate_forms_options['pirateformsopt_label_message'];

				if ( !empty($pirateformsopt_message_field) && !empty($pirateformsopt_message_label) ):


					$required     = $pirateformsopt_message_field === 'req' ? TRUE : FALSE;
					$wrap_classes = array( 'col-sm-12 col-lg-12 form_field_wrap', 'contact_message_wrap ' );

					// If this field was submitted with invalid data
					if ( isset( $_SESSION['pirate_forms_contact_errors']['contact-message'] ) ) {
						$wrap_classes[] = 'error';
					}

					$pirate_form->add_input(
						'',
						array(
							'placeholder' => stripslashes( sanitize_text_field($pirateformsopt_message_label) ),
							'required'   => $required,
							'wrap_class' => $wrap_classes,
							'type' => 'textarea'
						),
						'pirate-forms-contact-message'
					);

				endif;
			endif;

			/******************************/
			/********* ReCaptcha **********/
			/******************************/

			if( !empty($pirate_forms_options['pirateformsopt_recaptcha_secretkey']) && !empty($pirate_forms_options['pirateformsopt_recaptcha_sitekey']) && !empty($pirate_forms_options['pirateformsopt_recaptcha_field']) && ($pirate_forms_options['pirateformsopt_recaptcha_field'] == 'yes') ):

				$pirateformsopt_recaptcha_sitekey = $pirate_forms_options['pirateformsopt_recaptcha_sitekey'];
				$pirateformsopt_recaptcha_secretkey = $pirate_forms_options['pirateformsopt_recaptcha_secretkey'];

				$pirate_form->add_input(
					'',
					array(
						'value' => $pirateformsopt_recaptcha_sitekey,
						'wrap_class' => 'col-xs-12 col-sm-6 col-lg-6 form_field_wrap form_captcha_wrap',
						'type' => 'captcha',
					),
					'pirate-forms-captcha'
				);

			endif;

			/********************************/
			/********  Submit button ********/
			/********************************/

			if( !empty($pirate_forms_options['pirateformsopt_label_submit_btn']) ):

				$pirateformsopt_label_submit_btn = $pirate_forms_options['pirateformsopt_label_submit_btn'];

				if ( !empty($pirateformsopt_label_submit_btn) ):

					$wrap_classes = array();

					$pirate_form->add_input(
						'',
						array(
							'value' => stripslashes( sanitize_text_field($pirateformsopt_label_submit_btn) ),
							'wrap_class' => $wrap_classes,
							'type' => 'submit',
							'wrap_tag' => '',
							'class' => 'pirate-forms-submit-button'
						),
						'pirate-forms-contact-submit'
					);

				endif;
			endif;

	endif;

	/* Referring site or page, if any */
	if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$pirate_form->add_input(
			__( 'Contact Referrer','pirate-forms' ),
			array(
				'type'  => 'hidden',
				'value' => $_SERVER['HTTP_REFERER']
			)
		);
	}

	/* Referring page, if sent via URL query */
	if ( ! empty( $_REQUEST['src'] ) || ! empty( $_REQUEST['ref'] ) ) {
		$pirate_form->add_input(
			__( 'Referring page','pirate-forms' ),
			array(
				'type'  => 'hidden',
				'value' => ! empty( $_REQUEST['src'] ) ? $_REQUEST['src'] : $_REQUEST['ref']
			)
		);
	}

	/* Are there any submission errors? */
	$errors = '';
	if ( ! empty( $_SESSION['pirate_forms_contact_errors'] ) ) {
		$errors = pirate_forms_display_errors( $_SESSION['pirate_forms_contact_errors'] );
		unset( $_SESSION['pirate_forms_contact_errors'] );
	}

	/* Display the form */
	return $pirate_forms_thankyou_message.'
	<div class="pirate_forms_wrap">
	' . $errors . '
	' . $pirate_form->build_form( FALSE ) . '
		<div class="pirate_forms_clearfix"></div>
	</div>';

}

/**
 * Process the incoming contact form data, if any
 */
add_action( 'template_redirect', 'pirate_forms_process_contact' );
function pirate_forms_process_contact() {

	// If POST, nonce and honeypot are not set, beat it
	if ( empty( $_POST ) || empty( $_POST['wordpress-nonce'] ) || !isset( $_POST['honeypot'] )) {
		return false;
	}

	// Session variable for form errors
	$_SESSION['pirate_forms_contact_errors'] = array();

	// If nonce is not valid, beat it
	if ( ! wp_verify_nonce( $_POST['wordpress-nonce'], get_bloginfo( 'admin_email' ) ) ) {
		$_SESSION['pirate_forms_contact_errors']['nonce'] = __( 'Nonce failed!', 'pirate-forms' );
		return false;
	}

	// If the honeypot caught a bear, beat it
	if ( ! empty( $_POST['honeypot'] ) ) {
		$_SESSION['pirate_forms_contact_errors']['honeypot'] = __( 'Form submission failed!', 'pirate-forms' );
		return false;
	}

	// Start the body of the contact email
	$body = "*** " . __( 'Contact form submission from', 'pirate-forms' ) . " " .
		get_bloginfo( 'name' ) . " (" . site_url() . ") *** \n\n";


	/***********************************************/
	/*********   Sanitize and validate name *******/
	/**********************************************/

	$pirate_forms_contact_name = isset( $_POST['pirate-forms-contact-name'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-name'] ) ) : '';

	// if name is required and is missing
	if ( (pirate_forms_get_key( 'pirateformsopt_name_field' ) === 'req') && empty( $pirate_forms_contact_name ) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-name'] = pirate_forms_get_key( 'pirateformsopt_label_err_name' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_name ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_name' ) ) . ": $pirate_forms_contact_name \r";
	}


	/***********************************************/
	/*******  Sanitize and validate email **********/
	/***********************************************/

	$pirate_forms_contact_email = isset( $_POST['pirate-forms-contact-email'] ) ? sanitize_email( $_POST['pirate-forms-contact-email'] ) : '';

	// If required, is it valid?
	if ( (pirate_forms_get_key( 'pirateformsopt_email_field' ) === 'req') && ! filter_var( $pirate_forms_contact_email, FILTER_VALIDATE_EMAIL )) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-email'] = pirate_forms_get_key( 'pirateformsopt_label_err_email' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_email ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_email' ) )
				. ": $pirate_forms_contact_email \r";
	}

	/***********************************************/
	/*********   Sanitize and validate subject *****/
	/**********************************************/

	$pirate_forms_contact_subject = isset( $_POST['pirate-forms-contact-subject'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-subject'] ) ) : '';

	// if subject is required and is missing
	if ( (pirate_forms_get_key( 'pirateformsopt_subject_field' ) === 'req') && empty( $pirate_forms_contact_subject ) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-subject'] = pirate_forms_get_key( 'pirateformsopt_label_err_subject' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_subject ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_subject' ) ) . ": $pirate_forms_contact_subject \r";
	}

	/***********************************************/
	/*********   Sanitize and validate message *****/
	/**********************************************/

	$pirate_forms_contact_message = isset( $_POST['pirate-forms-contact-message'] ) ? sanitize_text_field( trim( $_POST['pirate-forms-contact-message'] ) ) : '';

	// if message is required and is missing
	if ( (pirate_forms_get_key( 'pirateformsopt_message_field' ) === 'req') && empty( $pirate_forms_contact_message ) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-contact-message'] = pirate_forms_get_key( 'pirateformsopt_label_err_message' );
	}
	// If not required and empty, leave it out
	elseif ( ! empty( $pirate_forms_contact_message ) ) {
		$body .= stripslashes( pirate_forms_get_key( 'pirateformsopt_label_message' ) ) . ": $pirate_forms_contact_message \r";
	}

	/*************************************************/
	/************* Validate reCAPTCHA ****************/
	/*************************************************/


	$pirateformsopt_recaptcha_sitekey = pirate_forms_get_key('pirateformsopt_recaptcha_sitekey');
	$pirateformsopt_recaptcha_secretkey = pirate_forms_get_key('pirateformsopt_recaptcha_secretkey');
	$pirateformsopt_recaptcha_field = pirate_forms_get_key('pirateformsopt_recaptcha_field');

	if( !empty($pirateformsopt_recaptcha_secretkey) && !empty($pirateformsopt_recaptcha_sitekey) && !empty($pirateformsopt_recaptcha_field) && ($pirateformsopt_recaptcha_field == 'yes') ):

		if( isset($_POST['g-recaptcha-response']) ){
			$captcha = $_POST['g-recaptcha-response'];
		}
		if( !$captcha ){
			$_SESSION['pirate_forms_contact_errors']['pirate-forms-captcha'] = __( 'Wrong reCAPTCHA','pirate-forms' );
		}
		$response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=".$pirateformsopt_recaptcha_secretkey."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR'] );

		if( !empty($response) ):
			$response_body = wp_remote_retrieve_body( $response );
		endif;

		if( !empty($response_body) ):
			$result = json_decode( $response_body, true );
		endif;

		if( isset($result['success']) && ($result['success'] == false) ) {
			$_SESSION['pirate_forms_contact_errors']['pirate-forms-captcha'] = __( 'Wrong reCAPTCHA','pirate-forms' );
		}
	endif;

	/************************************************/
	/********** Validate recipients email ***********/
	/************************************************/
	$site_recipients = sanitize_text_field( pirate_forms_get_key( 'pirateformsopt_email_recipients' ) );


	if ( empty($site_recipients) ) {
		$_SESSION['pirate_forms_contact_errors']['pirate-forms-recipients-email'] = __( 'Please enter one or more Contact submission recipients','pirate-forms' );
	}

	/**********************************************/
	/********   Sanitize and validate IP  *********/
	/**********************************************/

	$contact_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );

	// If valid and present, create a link to an IP search
	if ( ! empty( $contact_ip ) ) {
		$body .= __( 'IP address: ','pirate-forms' ). $contact_ip ."\r ".__( 'IP search:','pirate-forms' )." http://whatismyipaddress.com/ip/$contact_ip \n\n";
	}

	// Sanitize and prepare referrer;
	if ( ! empty( $_POST['pirate-forms-contact-referrer'] ) ) {
		$body .= __( 'Came from: ','pirate-forms' ) . sanitize_text_field( $_POST['pirate-forms-contact-referrer'] ) . " \r";
	}

	// Show the page this contact form was submitted on
	$body .= __( 'Sent from page: ','pirate-forms' ) . get_permalink( get_the_id() );

	// Check the blacklist
	$blocked = pirate_forms_get_blacklist();
	if ( ! empty( $blocked ) ) {
		if (
				in_array( $pirate_forms_contact_email, $blocked ) ||
				in_array( $contact_ip, $blocked )
		) {
			$_SESSION['pirate_forms_contact_errors']['blacklist-blocked'] = __( 'Form submission blocked!','pirate-forms' );
			return false;
		}
	}

	// No errors? Go ahead and process the contact
	if ( empty( $_SESSION['pirate_forms_contact_errors'] ) ) {

		$pirate_forms_options_tmp = get_option( 'pirate_forms_settings_array' );
		if( isset($pirate_forms_options_tmp['pirateformsopt_email']) ) {
			$site_email = $pirate_forms_options_tmp['pirateformsopt_email'];
		}
		
		if( !empty($pirate_forms_contact_name) ):
			$site_name = $pirate_forms_contact_name;
		else:
			$site_name  = htmlspecialchars_decode( get_bloginfo( 'name' ) );
		endif;

		// Notification recipients
		$site_recipients = sanitize_text_field( pirate_forms_get_key( 'pirateformsopt_email_recipients' ) );
		$site_recipients = explode(',', $site_recipients);
		$site_recipients = array_map( 'trim', $site_recipients );
		$site_recipients = array_map( 'sanitize_email', $site_recipients );
		$site_recipients = implode( ',', $site_recipients );

		// No name? Use the submitter email address, if one is present
		if ( empty( $pirate_forms_contact_name ) ) {
			$pirate_forms_contact_name = ! empty( $pirate_forms_contact_email ) ? $pirate_forms_contact_email : '[None given]';
		}

		// Need an email address for the email notification		
		if( !empty($site_email) ) {
			if( $site_email == '[email]' ) {
				if( !empty($pirate_forms_contact_email) ) {
					$send_from = $pirate_forms_contact_email;
				}
				else {
					$send_from = pirate_forms_from_email();	
				}
			}
			else {
				$send_from = $site_email;
			}
		}
		else {
			$send_from = pirate_forms_from_email();
		}
		
		$send_from_name = $site_name;


		// Sent an email notification to the correct address
		$headers   = "From: $send_from_name <$send_from>\r\nReply-To: $pirate_forms_contact_name <$pirate_forms_contact_email>";

		add_action( 'phpmailer_init', 'pirate_forms_phpmailer' );

		function pirate_forms_phpmailer( $phpmailer ) {

			$pirateformsopt_use_smtp = pirate_forms_get_key( 'pirateformsopt_use_smtp' );
			$pirateformsopt_smtp_host = pirate_forms_get_key( 'pirateformsopt_smtp_host' );
			$pirateformsopt_smtp_port = pirate_forms_get_key( 'pirateformsopt_smtp_port' );
			$pirateformsopt_smtp_username = pirate_forms_get_key( 'pirateformsopt_smtp_username' );
			$pirateformsopt_smtp_password = pirate_forms_get_key( 'pirateformsopt_smtp_password' );
			$pirateformsopt_use_smtp_authentication = pirate_forms_get_key('pirateformsopt_use_smtp_authentication');

			if( !empty($pirateformsopt_use_smtp) && ($pirateformsopt_use_smtp == 'yes') && !empty($pirateformsopt_smtp_host) && !empty($pirateformsopt_smtp_port) ):

				$phpmailer->isSMTP();
				$phpmailer->Host = $pirateformsopt_smtp_host;

				if( !empty($pirateformsopt_use_smtp_authentication) && ($pirateformsopt_use_smtp_authentication == 'yes') && !empty($pirateformsopt_smtp_username) && !empty($pirateformsopt_smtp_password) ):

					$phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
					$phpmailer->Port = $pirateformsopt_smtp_port;
					$phpmailer->Username = $pirateformsopt_smtp_username;
					$phpmailer->Password = $pirateformsopt_smtp_password;

				endif;

			endif;
		}

		wp_mail( $site_recipients, 'Contact on ' . htmlspecialchars_decode( get_bloginfo( 'name' ) ), $body, $headers );

		// Should a confirm email be sent?
		$confirm_body = stripslashes( trim( pirate_forms_get_key( 'pirateformsopt_confirm_email' ) ) );
		if ( ! empty( $confirm_body ) && ! empty( $pirate_forms_contact_email ) ) {

			// Removing entities
			$confirm_body = htmlspecialchars_decode( $confirm_body );
			$confirm_body = html_entity_decode( $confirm_body );
			$confirm_body = str_replace( '&#39;', "'", $confirm_body );

			$headers = "From: $site_name <$site_email>\r\nReply-To: $site_name <$site_email>";

			wp_mail(
				$pirate_forms_contact_email,
				pirate_forms_get_key( 'pirateformsopt_label_submit' ) . ' - ' . $site_name,
				$confirm_body,
				$headers
			);
		}

		/************************************************************/
		/*************   Store the entries in the DB ****************/
		/************************************************************/

		if ( pirate_forms_get_key( 'pirateformsopt_store' ) === 'yes' ) {
			$new_post_id = wp_insert_post(
				array(
					'post_type'    => 'pf_contact',
					'post_title'   => date( 'l, M j, Y', time() ) . ' by "' . $pirate_forms_contact_name . '"',
					'post_content' => $body,
					'post_author'  => 1,
					'post_status'  => 'private'
				)
			);

			if ( isset( $pirate_forms_contact_email ) && ! empty( $pirate_forms_contact_email ) ) {
				add_post_meta( $new_post_id, 'Contact email', $pirate_forms_contact_email );
			}
		}


		$redirect = $_SERVER["HTTP_REFERER"] . ( strpos( $_SERVER["HTTP_REFERER"], '?' ) === FALSE ? '?' : '&' ) . 'pcf=1#contact';


		wp_safe_redirect( $redirect );

	}

}

/* Get a settings value */
function pirate_forms_get_key( $id ) {
	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	return isset( $pirate_forms_options[$id] ) ? $pirate_forms_options[$id] : '';
}

/*************************************************************************/
/**************************** Scripts and Styles *************************/
/*************************************************************************/


add_action( 'wp_enqueue_scripts', 'pirate_forms_add_styles_and_scripts' );

function pirate_forms_add_styles_and_scripts() {

	/* style for frontpage contact */
	wp_enqueue_style( 'pirate_forms_front_styles', PIRATE_FORMS_URL . 'css/front.css' );

	/* recaptcha js */
	$pirate_forms_options = get_option( 'pirate_forms_settings_array' );

	if( !empty($pirate_forms_options) ):

		if( !empty($pirate_forms_options['pirateformsopt_recaptcha_secretkey']) && !empty($pirate_forms_options['pirateformsopt_recaptcha_sitekey']) && !empty($pirate_forms_options['pirateformsopt_recaptcha_field']) && ($pirate_forms_options['pirateformsopt_recaptcha_field'] == 'yes') ):

			if ( defined( 'POLYLANG_VERSION' ) && function_exists('pll_current_language') ) {
				$pirate_forms_contactus_language = pll_current_language();
			} else {
				$pirate_forms_contactus_language = get_locale();
			}
		
			wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?hl='.$pirate_forms_contactus_language.'' );

			wp_enqueue_script( 'pirate_forms_scripts', plugins_url( 'js/scripts.js', __FILE__ ), array('jquery','recaptcha') );

		endif;

	endif;

	wp_enqueue_script( 'pirate_forms_scripts_general', plugins_url( 'js/scripts-general.js', __FILE__ ), array('jquery') );

	$pirate_forms_errors = '';

	if( !empty($_SESSION['pirate_forms_contact_errors'])):
		$pirate_forms_errors = $_SESSION['pirate_forms_contact_errors'];
	endif;

	wp_localize_script( 'pirate_forms_scripts_general', 'pirateFormsObject', array(
		'errors' => $pirate_forms_errors
	) );

}

add_action( 'admin_enqueue_scripts', 'pirate_forms_admin_css' );

function pirate_forms_admin_css() {

	global $pagenow;

	if ( !empty($pagenow) && ( $pagenow == 'options-general.php' || $pagenow == 'admin.php' )
		&& isset( $_GET['page'] ) && $_GET['page'] == 'pirate-forms-admin' ) {

		wp_enqueue_style( 'pirate_forms_admin_styles', PIRATE_FORMS_URL . 'css/wp-admin.css' );

		wp_enqueue_script( 'pirate_forms_scripts_admin', plugins_url( 'js/scripts-admin.js', __FILE__ ), array('jquery') );
		wp_localize_script( 'pirate_forms_scripts_admin', 'cwp_top_ajaxload', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
}

/**************************************************************************/
/*** If submissions should be stored in the DB, create the Contacts CPT ***/
/*************************************************************************/

if ( pirate_forms_get_key( 'pirateformsopt_store' ) === 'yes' ) {

	add_action( 'init', 'pirate_forms_register_content_type' );

	function pirate_forms_register_content_type() {

		$labels = array(
			'name'               => _x( 'Contacts', 'post type general name', 'pirate-forms' ),
			'singular_name'      => _x( 'Contact', 'post type singular name', 'pirate-forms' ),
			'menu_name'          => _x( 'Contacts', 'admin menu', 'pirate-forms' ),
			'name_admin_bar'     => _x( 'Contact', 'add new on admin bar', 'pirate-forms' ),
			'add_new'            => _x( 'Add New', 'contact', 'pirate-forms' ),
			'add_new_item'       => __( 'Add New Contact', 'pirate-forms' ),
			'new_item'           => __( 'New Contact', 'pirate-forms' ),
			'edit_item'          => __( 'Edit Contact', 'pirate-forms' ),
			'view_item'          => __( 'View Contact', 'pirate-forms' ),
			'all_items'          => __( 'All Contacts', 'pirate-forms' ),
			'search_items'       => __( 'Search Contacts', 'pirate-forms' ),
			'parent_item_colon'  => __( 'Parent Contacts:', 'pirate-forms' ),
			'not_found'          => __( 'No contacts found.', 'pirate-forms' ),
			'not_found_in_trash' => __( 'No contacts found in Trash.', 'pirate-forms' )
		);
		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Contacts from Pirate Forms', 'pirate-forms' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'custom-fields' )
		);

		register_post_type( 'pf_contact', $args );
	}

}

/**
 * Add a Settings link in the plugins list for the Pirate Forms
 */
function pirate_forms_add_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=pirate-forms-admin">' . __( 'Settings','pirate-forms' ) . '</a>';
	if (function_exists('array_unshift')):
		array_unshift( $links, $settings_link );
	else:
		array_push( $links, $settings_link );
	endif;
	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'pirate_forms_add_settings_link' );

/**
 * Allow [pirate_forms] shortcode in text widget
 */
add_filter( 'widget_text', 'pirate_forms_widget_text_filter', 9 );

function pirate_forms_widget_text_filter( $content ) {
	if ( ! preg_match( '[pirate_forms]', $content ) )
		return $content;

	$content = do_shortcode( $content );

	return $content;
}
