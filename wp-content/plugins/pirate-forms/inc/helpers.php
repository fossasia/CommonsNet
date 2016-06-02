<?php

/**************************************************/
/**************** Show errors *********************/
/**************************************************/

if ( ! function_exists( 'pirate_forms_display_errors' ) ) {

	function pirate_forms_display_errors( $errs ) {

		$output = '';

		if( !empty($errs) ):

			$output .= '<div class="col-sm-12 col-lg-12 pirate_forms_error_box">';
				$output .= '<p>'.__( 'Sorry, an error occured.','pirate-forms' ).'</p>';
			$output .= '</div>';
			foreach ( $errs as $err ) :
				$output .= '<div class="col-sm-12 col-lg-12 pirate_forms_error_box">';
					$output .= "<p>$err</p>";
				$output .= '</div>';
			endforeach;

		endif;

		return $output;
	}
}

/***************************************************************************/
/******** Get blacklist IPs and emails from the Discussion settings ********/
/***************************************************************************/

if ( ! function_exists( 'pirate_forms_get_blacklist' ) ) {

	function pirate_forms_get_blacklist() {

		$final_blocked_arr = array();

		$blocked = get_option( 'blacklist_keys' );
		$blocked = str_replace( "\r", "\n", $blocked );

		$blocked_arr = explode( "\n", $blocked );
		$blocked_arr = array_map( 'trim', $blocked_arr );

		foreach ( $blocked_arr as $ip_or_email ) {
			$ip_or_email = trim( $ip_or_email );
			if (
					filter_var( $ip_or_email, FILTER_VALIDATE_IP ) ||
					filter_var( $ip_or_email, FILTER_VALIDATE_EMAIL )
			) {
				$final_blocked_arr[] = $ip_or_email;
			}
		}

		return $final_blocked_arr;
	}
}