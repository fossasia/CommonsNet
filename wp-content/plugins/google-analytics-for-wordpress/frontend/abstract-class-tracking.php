<?php
/**
 * @package GoogleAnalytics\Frontend
 */

/**
 * The basic frontend tracking class for the GA plugin, extendable for the children
 */
abstract class Yoast_GA_Tracking {

	/**
	 * Regular expression for Ga.js and universal tracking to detect links
	 * @var string
	 */
	protected $link_regex = '/<a([^>]*)\shref=([\'\"])([a-zA-Z]+):(?:\/){2}?(.*)\2([^>]*)>(.*)<\/a>/isU';

	/**
	 * Storage for the currently set options
	 * @var mixed|void
	 */
	public $options;

	/**
	 * @var boolean $do_tracking Should the tracking code be added
	 */
	protected $do_tracking = null;

	/**
	 * Function to output the GA Tracking code in the wp_head()
	 *
	 * @param bool $return_array
	 *
	 * @return mixed
	 */
	abstract public function tracking( $return_array = false );

	/**
	 * Output tracking link
	 *
	 * @param string $label
	 * @param array  $matches
	 *
	 * @return mixed
	 */
	abstract protected function output_parse_link( $label, $matches );

	/**
	 * Class constructor
	 */
	public function __construct() {
		$options_class = $this->get_options_class();
		$this->options = $options_class->options;

		add_action( 'wp_head', array( $this, 'tracking' ), 8 );

		if ( $this->options['track_outbound'] == 1 ) {
			$this->track_outbound_filters();
		}
	}

	/**
	 * Get the options class
	 *
	 * @return object|Yoast_GA_Options
	 */
	protected function get_options_class() {
		return Yoast_GA_Options::instance();
	}

	/**
	 * Delegates `get_tracking_code` to the options class
	 *
	 * @return null
	 */
	public function get_tracking_code() {
		return $this->get_options_class()->get_tracking_code();
	}

	/**
	 * Get 1 or 0 if we need to do enhanced link attribution
	 *
	 * @return mixed
	 */
	public function get_enhanced_link_attribution() {
		return $this->options['enhanced_link_attribution'];
	}

	/**
	 * Parse article link
	 *
	 * @param array $matches
	 *
	 * @return mixed
	 */
	public function parse_article_link( $matches ) {
		return $this->output_parse_link( 'outbound-article', $matches );
	}

	/**
	 * Parse comment link
	 *
	 * @param array $matches
	 *
	 * @return mixed
	 */
	public function parse_comment_link( $matches ) {
		return $this->output_parse_link( 'outbound-comment', $matches );
	}

	/**
	 * Parse widget link
	 *
	 * @param array $matches
	 *
	 * @return mixed
	 */
	public function parse_widget_link( $matches ) {
		return $this->output_parse_link( 'outbound-widget', $matches );
	}

	/**
	 * Parse menu link
	 *
	 * @param array $matches
	 *
	 * @return mixed
	 */
	public function parse_nav_menu( $matches ) {
		return $this->output_parse_link( 'outbound-menu', $matches );
	}

	/**
	 * Parse the_content or the_excerpt for links
	 *
	 * @param string $text
	 *
	 * @return mixed
	 */
	public function the_content( $text ) {
		if ( false === $this->do_tracking() ) {
			return $text;
		}

		if ( ! is_feed() ) {
			$text = preg_replace_callback( $this->link_regex, array( $this, 'parse_article_link' ), $text );
		}

		return $text;
	}

	/**
	 * Parse the widget content for links
	 *
	 * @param string $text
	 *
	 * @return mixed
	 */
	public function widget_content( $text ) {
		if ( ! $this->do_tracking() ) {
			return $text;
		}
		$text = preg_replace_callback( $this->link_regex, array( $this, 'parse_widget_link' ), $text );

		return $text;
	}

	/**
	 * Parse the nav menu for links
	 *
	 * @param string $text
	 *
	 * @return mixed
	 */
	public function nav_menu( $text ) {
		if ( ! $this->do_tracking() ) {
			return $text;
		}

		if ( ! is_feed() ) {
			$text = preg_replace_callback( $this->link_regex, array( $this, 'parse_nav_menu' ), $text );
		}

		return $text;
	}

	/**
	 * Parse comment text for links
	 *
	 * @param string $text
	 *
	 * @return mixed
	 */
	public function comment_text( $text ) {
		if ( ! $this->do_tracking() ) {
			return $text;
		}

		if ( ! is_feed() ) {
			$text = preg_replace_callback( $this->link_regex, array( $this, 'parse_comment_link' ), $text );
		}

		return $text;
	}

	/**
	 * Parse the domain
	 *
	 * @param string $uri
	 *
	 * @return array|bool
	 */
	public function yoast_ga_get_domain( $uri ) {
		$hostPattern     = '/^(https?:\/\/)?([^\/]+)/i';
		$domainPatternUS = '/[^\.\/]+\.[^\.\/]+$/';
		$domainPatternUK = '/[^\.\/]+\.[^\.\/]+\.[^\.\/]+$/';

		$matching = preg_match( $hostPattern, $uri, $matches );
		if ( $matching ) {
			$host = $matches[2];
			if ( preg_match( '/.*\..*\..*\..*$/', $host ) ) {
				preg_match( $domainPatternUK, $host, $matches );
			}
			else {
				preg_match( $domainPatternUS, $host, $matches );
			}

			if ( isset( $matches[0] ) ) {
				return array( 'domain' => $matches[0], 'host' => $host );
			}
		}

		return false;
	}

	/**
	 * Merge the existing onclick with a new one and append it
	 *
	 * @param string $link_attribute
	 * @param string $onclick
	 *
	 * @return string
	 */
	public function output_add_onclick( $link_attribute, $onclick ) {
		if ( preg_match( '/onclick=[\'\"](.*?;)[\'\"]/i', $link_attribute, $matches ) > 0 ) {
			$js_snippet_single = 'onclick=\'' . $matches[1] . ' ' . $onclick . '\'';
			$js_snippet_double = 'onclick="' . $matches[1] . ' ' . $onclick . '"';

			$link_attribute = str_replace( 'onclick="' . $matches[1] . '"', $js_snippet_double, $link_attribute );
			$link_attribute = str_replace( "onclick='" . $matches[1] . "'", $js_snippet_single, $link_attribute );

			return $link_attribute;
		}
		else {
			if ( ! is_null( $onclick ) ) {
				return 'onclick="' . $onclick . '" ' . $link_attribute;
			}
			else {
				return $link_attribute;
			}
		}
	}

	/**
	 * Generate the full URL
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public function make_full_url( $link ) {
		switch ( $link['type'] ) {
			case 'download':
			case 'internal':
			case 'internal-as-outbound':
			case 'outbound':
				return $link['protocol'] . '://' . $link['original_url'];
				break;
			case 'email':
				return 'mailto:' . $link['original_url'];
				break;
		}
	}

	/**
	 * Setting the filters for tracking outbound links
	 *
	 */
	protected function track_outbound_filters() {
		add_filter( 'the_content', array( $this, 'the_content' ), 99 );
		add_filter( 'widget_text', array( $this, 'widget_content' ), 99 );
		add_filter( 'wp_list_bookmarks', array( $this, 'widget_content' ), 99 );
		add_filter( 'wp_nav_menu', array( $this, 'widget_content' ), 99 );
		add_filter( 'the_excerpt', array( $this, 'the_content' ), 99 );
		add_filter( 'comment_text', array( $this, 'comment_text' ), 99 );
	}

	/**
	 * Check if we need to show an actual tracking code
	 *
	 * @return bool
	 */
	public function do_tracking() {
		if ( $this->do_tracking === null ) {
			$user = wp_get_current_user();
			$this->do_tracking = true;

			if ( 0 != $user->ID && isset( $this->options['ignore_users'] ) ) {
				if ( ! empty( $user->roles ) && in_array( $user->roles[0], $this->options['ignore_users'] ) ) {
					$this->do_tracking = false;
				}
			}

			/**
			 * Filter: 'yst_ga_track_super_admin' - Allows filtering if the Super admin should be tracked in a multi-site setup
			 *
			 * @api array $all_roles
			 */
			$track_super_admin = apply_filters( 'yst_ga_track_super_admin', true );
			if ( $track_super_admin === false && is_super_admin() ) {
				$this->do_tracking = false;
			}
		}

		return $this->do_tracking;
	}

	/**
	 * Return the target with a lot of parameters
	 *
	 * @param string $category
	 * @param array  $matches
	 *
	 * @return array
	 */
	protected function get_target( $category, $matches ) {
		$protocol     = $matches[3];
		$original_url = $matches[4];
		$domain       = $this->yoast_ga_get_domain( $matches[4] );
		$http_host    = empty( $_SERVER['HTTP_HOST'] ) ? '' : $_SERVER['HTTP_HOST'];
		$origin       = $this->yoast_ga_get_domain( $http_host );
		$extension    = substr( strrchr( $original_url, '.' ), 1 );
		$type         = $this->get_target_type( $extension, $domain, $origin, $matches );

		return array(
			'category'        => $category,
			'type'            => $type,
			'protocol'        => $protocol,
			'domain'          => $domain['domain'],
			'host'            => $domain['host'],
			'origin_domain'   => $origin['domain'],
			'origin_host'     => $origin['host'],
			'extension'       => $extension,
			'link_attributes' => trim( $matches[1] . ' ' . $matches[5] ),
			'link_text'       => $matches[6],
			'original_url'    => $original_url,
		);
	}

	/**
	 * Getting the type for current target
	 *
	 * @param string $extension
	 * @param array  $domain
	 * @param array  $origin
	 * @param array  $matches
	 *
	 * @return null|string
	 */
	protected function get_target_type( $extension, $domain, $origin, $matches ) {
		$download_extensions = explode( ',', str_replace( '.', '', $this->options['extensions_of_files'] ) );
		$download_extensions = array_map( 'trim', $download_extensions );
		$protocol            = $matches[3];
		$original_url        = $matches[4];

		// Break out immediately if the link is not an http or https link.
		$type = null;
		if ( $protocol !== 'http' && $protocol !== 'https' && $protocol !== 'mailto' ) {
			$type = null;
		}
		else {
			if ( ( $protocol == 'mailto' ) ) {
				$type = 'email';
			}
			elseif ( in_array( $extension, $download_extensions ) ) {
				$type = 'download';
			}
			else {
				$type = $this->parse_outbound_type( $domain, $origin, $original_url );
			}
		}

		return $type;
	}

	/**
	 * Parse the type for outbound links
	 *
	 * @param array  $domain
	 * @param array  $origin
	 * @param string $original_url
	 *
	 * @return string
	 */
	protected function parse_outbound_type( $domain, $origin, $original_url ) {
		$type = null;

		if ( $domain['domain'] == $origin['domain'] ) {
			$out_links = explode( ',', $this->options['track_internal_as_outbound'] );
			$out_links = array_unique( array_map( 'trim', $out_links ) );

			if ( ! empty( $original_url ) && ! empty( $domain['domain'] ) && count( $out_links ) >= 1 ) {
				foreach ( $out_links as $out ) {
					if ( ! empty( $out ) && strpos( $original_url, $domain['domain'] . $out ) !== false ) {
						$type = 'internal-as-outbound';
					}
				}
			}

			if ( ! isset( $type ) ) {
				$type = 'internal';
			}
		}
		elseif ( $domain['domain'] != $origin['domain'] ) {
			$type = 'outbound';
		}

		return $type;
	}

	/**
	 * Trims the track_internal_as_label option to prevent commas and whitespaces
	 *
	 * @return string
	 */
	protected function sanitize_internal_label() {
		if ( ! is_null( $this->options['track_internal_as_label'] ) && ! empty( $this->options['track_internal_as_label'] ) ) {
			$label = $this->options['track_internal_as_label'];
			$label = trim( $label, ',' );
			$label = trim( $label );
		}

		// Be sure label isn't empty, if so, set value with in
		if ( empty( $label ) ) {
			$label = 'int';
		}

		return $label;
	}

	/**
	 * When a usergroup is disabled, show a message in the source to notify the user they are in a disabled user group.
	 */
	protected function disabled_usergroup() {
		/* translators %1$s is the product name 'Google Analytics by MonsterInsights'. %2$s displays the plugin version the website uses and a link to the plugin on MonsterInsights.com */
		echo '<!-- ' . sprintf( __( 'This site uses the %1$s plugin version %2$s', 'google-analytics-for-wordpress' ), 'Google Analytics by MonsterInsights', GAWP_VERSION . ' - https://www.monsterinsights.com/' ) . ' -->';

		if ( current_user_can( 'manage_options' ) ) {
			echo '<!-- ' . __( '@Webmaster, normally you will find the Google Analytics tracking code here, but you are in the disabled user groups. To change this, navigate to Analytics -> Settings (Ignore usergroups)', 'google-analytics-for-wordpress' ) . ' -->';
		}
		else {
			echo '<!-- ' . __( 'Normally you will find the Google Analytics tracking code here, but the webmaster disabled your user group.', 'google-analytics-for-wordpress' ) . ' -->';
		}

		// Do not make this translatable, as this is the product name.
		echo '<!-- / Google Analytics by MonsterInsights -->';
	}

	/**
	 * When the debug mode is enabled, display a message in the source.
	 *
	 * @return bool
	 */
	protected function debug_mode() {
		if ( $this->options['debug_mode'] === 1 ) {
			/* translators %1$s is the product name 'Google Analytics by MonsterInsights'. %2$s displays the plugin version the website uses and a link to the plugin on MonsterInsights.com */
			echo '<!-- ' . sprintf( __( 'This site uses the %1$s plugin version %2$s', 'google-analytics-for-wordpress' ), 'Google Analytics by MonsterInsights', GAWP_VERSION . ' - https://www.monsterinsights.com/' ) . ' -->';

			if ( current_user_can( 'manage_options' ) ) {
				echo '<!-- ' . __( '@Webmaster, normally you will find the Google Analytics tracking code here, but the Debug Mode is enabled. To change this, navigate to Analytics -> Settings -> (Tab) Debug Mode and disable Debug Mode to enable tracking of your site.', 'google-analytics-for-wordpress' ) . ' -->';
			}
			else {
				echo '<!-- ' . __( 'Normally you will find the Google Analytics tracking code here, but the webmaster has enabled the Debug Mode.', 'google-analytics-for-wordpress' ) . ' -->';
			}

			// Do not make this translatable, as this is the product name.
			echo '<!-- / Google Analytics by MonsterInsights -->';

			return true;
		}
		return false;
	}

}
