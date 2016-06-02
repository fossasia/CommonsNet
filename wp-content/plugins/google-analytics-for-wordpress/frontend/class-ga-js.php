<?php
/**
 * @package GoogleAnalytics\Frontend
 */

/**
 * The frontend JS class
 */
class Yoast_GA_JS extends Yoast_GA_Tracking {

	/**
	 * Function to output the GA Tracking code in the wp_head()
	 *
	 * @param bool $return_array
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
				$domain = null; // Default domain value
			}

			if ( ! isset( $this->options['allowanchor'] ) ) {
				$this->options['allowanchor'] = false;
			}

			$ua_code = $this->get_tracking_code();
			if ( is_null( $ua_code ) && $return_array == false ) {
				return null;
			}

			$gaq_push[] = "'_setAccount', '" . $ua_code . "'";

			if ( ! is_null( $domain ) ) {
				$gaq_push[] = "'_setDomainName', '" . $domain . "'";
			}

			if ( isset( $this->options['allowanchor'] ) && $this->options['allowanchor'] ) {
				$gaq_push[] = "'_setAllowAnchor', true";
			}

			if ( $this->options['add_allow_linker'] ) {
				$gaq_push[] = "'_setAllowLinker', true";
			}

			// @todo, check for AllowLinker in GA.js? Universal only?

			// SSL data
			$gaq_push[] = "'_gat._forceSSL'";

			if ( ! empty( $this->options['custom_code'] ) ) {
				// Add custom code to the view
				$gaq_push[] = array(
					'type'  => 'custom_code',
					'value' => stripslashes( $this->options['custom_code'] ),
				);
			}

			// Anonymous data
			if ( $this->options['anonymize_ips'] == 1 ) {
				$gaq_push[] = "'_gat._anonymizeIp'";
			}

			if ( isset( $this->options['allowhash'] ) && $this->options['allowhash'] ) {
				$gaq_push[] = "'_gat._anonymizeIp',true";
			}

			if ( is_404() ) {
				$gaq_push[] = "'_trackPageview','/404.html?page=' + document.location.pathname + document.location.search + '&from=' + document.referrer";
			}
			else {
				if ( $wp_query->is_search ) {
					$pushstr = "'_trackPageview','/?s=";
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
					$gaq_push[] = "'_trackPageview'";
				}
			}

			/**
			 * Filter: 'yoast-ga-push-array-ga-js' - Allows filtering of the commands to push
			 *
			 * @api array $gaq_push
			 */
			if ( true == $return_array ) {
				return $gaq_push;
			}

			$gaq_push = apply_filters( 'yoast-ga-push-array-ga-js', $gaq_push );

			$ga_settings = $this->options; // Assign the settings to the javascript include view


			// Include the tracking view
			if ( ! $this->debug_mode() ) {
				require( 'views/tracking-ga-js.php' );
			}
		}
		else {
			$this->disabled_usergroup();
		}
	}

	/**
	 * Get tracking prefix
	 *
	 * @return string
	 */
	public function get_tracking_prefix() {
		return ( empty( $this->options['trackprefix'] ) ) ? '/yoast-ga/' : $this->options['trackprefix'];
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
					$onclick = "_gaq.push(['_trackPageview','download/" . esc_js( $full_url ) . "']);";
				}
				else {
					$onclick = "_gaq.push(['_trackEvent','download','" . esc_js( $full_url ) . "']);";
				}

				break;
			case 'email':
				$onclick = "_gaq.push(['_trackEvent','mailto','" . esc_js( $link['original_url'] ) . "']);";

				break;
			case 'internal-as-outbound':
				$label = $this->sanitize_internal_label();

				$onclick = "_gaq.push(['_trackEvent', '" . esc_js( $link['category'] ) . '-' . esc_js( $label ) . "', '" . esc_js( $full_url ) . "', '" . esc_js( strip_tags( $link['link_text'] ) ) . "']);";

				break;
			case 'outbound':
				$onclick = "_gaq.push(['_trackEvent', '" . esc_js( $link['category'] ) . "', '" . esc_js( $full_url ) . "', '" . esc_js( strip_tags( $link['link_text'] ) ) . "']);";

				break;
		}

		$link['link_attributes'] = $this->output_add_onclick( $link['link_attributes'], $onclick );

		return '<a href="' . $full_url . '" ' . $link['link_attributes'] . '>' . $link['link_text'] . '</a>';
	}

}

