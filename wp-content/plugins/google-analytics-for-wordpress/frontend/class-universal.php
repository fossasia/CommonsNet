<?php
/**
 * @package GoogleAnalytics\Frontend
 */

/**
 * This is the frontend class for the GA Universal code
 */
class Yoast_GA_Universal extends Yoast_GA_Tracking {

	/**
	 * Test helper function
	 */
	public function get_options(){
		return $this->options;
	}

	/**
	 * Function to output the GA Tracking code in the wp_head()
	 *
	 * @param boolean $return_array
	 *
	 * @return null|array
	 */
	public function tracking( $return_array = false ) {
		global $wp_query;

		if ( $this->do_tracking() && ! is_preview() ) {
			$gaq_push = array();

			// Running action for adding possible code
			do_action( 'yst_tracking' );

			if ( isset( $this->options['subdomain_tracking'] ) && $this->options['subdomain_tracking'] != '' ) {
				$domain = esc_attr( $this->options['subdomain_tracking'] );
			}
			else {
				$domain = 'auto'; // Default domain value
			}

			if ( ! isset( $this->options['allowanchor'] ) ) {
				$this->options['allowanchor'] = false;
			}

			$ua_code = $this->get_tracking_code();
			if ( is_null( $ua_code ) && $return_array == false ) {
				return null;
			}

			// Set tracking code here
			if ( ! empty( $ua_code ) ) {
				if ( $this->options['add_allow_linker'] && ! $this->options['allow_anchor'] ) {
					$gaq_push[] = "'create', '" . $ua_code . "', '" . $domain . "', {'allowLinker': true}";
				}
				else {
					if ( $this->options['allow_anchor'] && ! $this->options['add_allow_linker'] ) {
						$gaq_push[] = "'create', '" . $ua_code . "', '" . $domain . "', {'allowAnchor': true}";
					}
					else {
						if ( $this->options['allow_anchor'] && $this->options['add_allow_linker'] ) {
							$gaq_push[] = "'create', '" . $ua_code . "', '" . $domain . "', {'allowAnchor': true, 'allowLinker': true}";
						}
						else {
							$gaq_push[] = "'create', '" . $ua_code . "', '" . $domain . "'";
						}
					}
				}
			}

			$gaq_push[] = "'set', 'forceSSL', true";

			if ( ! empty( $this->options['custom_code'] ) ) {
				// Add custom code to the view
				$gaq_push[] = array(
					'type'  => 'custom_code',
					'value' => stripslashes( $this->options['custom_code'] ),
				);
			}

			// Anonymous data
			if ( $this->options['anonymize_ips'] == 1 ) {
				$gaq_push[] = "'set', 'anonymizeIp', true";
			}

			/**
			* Filter: 'yst_ga_filter_push_vars' - Allow changing the $gaq_push variables before scripts are required.
			*
			* @api array
			*/
			if ( has_filter( 'yst_ga_filter_push_vars' ) && $value_to_push = apply_filters( 'yst_ga_filter_push_vars', $gaq_push ) ) {
				$gaq_push[] = $value_to_push;
			}

			// add demographics
			if ( $this->options['demographics'] ) {
				$gaq_push[] = "'require', 'displayfeatures'";
			}

			// Check for Enhanced link attribution
			if ( $this->get_enhanced_link_attribution() == 1 ) {
				$gaq_push[] = "'require', 'linkid', 'linkid.js'";
			}

			if ( is_404() ) {
				$gaq_push[] = "'send','pageview','/404.html?page=' + document.location.pathname + document.location.search + '&from=' + document.referrer";
			}
			else {
				if ( $wp_query->is_search ) {
					$pushstr = "'send','pageview','/?s=";
					if ( $wp_query->found_posts == 0 ) {
						$gaq_push[] = $pushstr . 'no-results:' . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=no-results'";
					}
					else {
						if ( $wp_query->found_posts == 1 ) {
							$gaq_push[] = $pushstr . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=1-result'";
						}
						else {
							if ( $wp_query->found_posts > 1 && $wp_query->found_posts < 6 ) {
								$gaq_push[] = $pushstr . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=2-5-results'";
							}
							else {
								$gaq_push[] = $pushstr . rawurlencode( $wp_query->query_vars['s'] ) . "&cat=plus-5-results'";
							}
						}
					}
				}
				else {
					$gaq_push[] = "'send','pageview'";
				}
			}

			/**
			 * Filter: 'yoast-ga-push-array-universal' - Allows filtering of the commands to push
			 *
			 * @api array $gaq_push
			 */
			if ( true == $return_array ) {
				return $gaq_push;
			}

			$gaq_push = apply_filters( 'yoast-ga-push-array-universal', $gaq_push );

			$ga_settings = $this->options; // Assign the settings to the javascript include view

			// Include the tracking view
			if ( ! $this->debug_mode() ) {
				require( 'views/tracking-universal.php' );
			}
		}
		else {
			$this->disabled_usergroup();
		}
	}

	/**
	 * Ouput tracking link
	 *
	 * @param string $label
	 * @param array  $matches
	 *
	 * @return mixed
	 */
	protected function output_parse_link( $label, $matches ) {
		$link = $this->get_target( $label, $matches );

		// bail early for links that we can't handle
		if ( is_null( $link['type'] ) || 'internal' === $link['type'] ) {
			return $matches[0];
		}

		$onclick  = null;
		$full_url = $this->make_full_url( $link );

		switch ( $link['type'] ) {
			case 'download':
				if ( $this->options['track_download_as'] == 'pageview' ) {
					$onclick = "__gaTracker('send', 'pageview', '" . esc_js( $full_url ) . "');";
				}
				else {
					$onclick = "__gaTracker('send', 'event', 'download', '" . esc_js( $full_url ) . "');";
				}

				break;
			case 'email':
				$onclick = "__gaTracker('send', 'event', 'mailto', '" . esc_js( $link['original_url'] ) . "');";

				break;
			case 'internal-as-outbound':
				$label = $this->sanitize_internal_label();

				$onclick = "__gaTracker('send', '" . esc_js( $this->options['track_download_as'] ) . "', '" . esc_js( $link['category'] ) . '-' . esc_js( $label ) . "', '" . esc_js( $full_url ) . "', '" . esc_js( strip_tags( $link['link_text'] ) ) . "');";

				break;
			case 'outbound':
				if ( $this->options['track_outbound'] == 1 ) {
					$onclick = "__gaTracker('send', 'event', '" . esc_js( $link['category'] ) . "', '" . esc_js( $full_url ) . "', '" . esc_js( strip_tags( $link['link_text'] ) ) . "');";
				}

				break;
		}

		$link['link_attributes'] = $this->output_add_onclick( $link['link_attributes'], $onclick );

		if ( ! empty( $link['link_attributes'] ) ) {
			return '<a href="' . $full_url . '" ' . trim( $link['link_attributes'] ) . '>' . $link['link_text'] . '</a>';
		}

		return '<a href="' . $full_url . '">' . $link['link_text'] . '</a>';
	}

}
