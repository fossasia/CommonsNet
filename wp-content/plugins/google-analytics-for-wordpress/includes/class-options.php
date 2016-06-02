<?php
/**
 * @package GoogleAnalytics\Includes
 */

/**
 * Options class.
 */
class Yoast_GA_Options {

	/** @var array  */
	public $options;

	/**
	 * Holds the settings for the GA plugin and possible subplugins
	 *
	 * @var string
	 */
	public $option_name = 'yst_ga';

	/**
	 * Holds the prefix we use within the option to save settings
	 *
	 * @var string
	 */
	public $option_prefix = 'ga_general';

	/**
	 * Holds the path to the main plugin file
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Holds the URL to the main plugin directory
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Saving instance of it's own in this static var
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Getting instance of this object. If instance doesn't exists it will be created.
	 *
	 * @return object|Yoast_GA_Options
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new Yoast_GA_Options();
		}

		return self::$instance;

	}

	/**
	 * Constructor for the options
	 */
	public function __construct() {
		$this->options = $this->get_options();
		$this->options = $this->check_options( $this->options );

		$this->plugin_path = plugin_dir_path( GAWP_FILE );
		$this->plugin_url  = trailingslashit( plugin_dir_url( GAWP_FILE ) );

		if ( false == $this->options ) {
			add_option( $this->option_name, $this->default_ga_values() );
			$this->options = $this->get_options();
		}

		if ( ! isset( $this->options['version'] ) || $this->options['version'] < GAWP_VERSION ) {
			$this->upgrade();
		}

		// If instance is null, create it. Prevent creating multiple instances of this class
		if ( is_null( self::$instance ) ) {
			self::$instance = $this;
		}

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Updates the GA option within the current option_prefix
	 *
	 * @param array $val
	 *
	 * @return bool
	 */
	public function update_option( $val ) {
		$options                         = get_option( $this->option_name );
		$options[ $this->option_prefix ] = $val;

		return update_option( $this->option_name, $options );
	}

	/**
	 * Return the Google Analytics options
	 *
	 * @return mixed|void
	 */
	public function get_options() {
		$options = get_option( $this->option_name );

		return $options[ $this->option_prefix ];
	}

	/**
	 * Check if all the options are set, to prevent a notice if debugging is enabled
	 * When we have new changes, the settings are saved to the options class
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function check_options( $options ) {

		$changes = 0;
		foreach ( $this->default_ga_values() as $key => $value ) {
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
				$changes ++;
			}
		}

		if ( $changes >= 1 ) {
			$this->update_option( $options );
		}

		return $options;
	}

	/**
	 * Get the Google Analytics tracking code for this website
	 *
	 * @return null
	 */
	public function get_tracking_code() {
		$tracking_code = null;
		$this->options = $this->get_options();

		if ( ! empty( $this->options['analytics_profile'] ) && ! empty( $this->options['analytics_profile_code'] ) ) {
			$tracking_code = $this->options['analytics_profile_code'];
		}
		elseif ( ! empty( $this->options['analytics_profile'] ) && empty( $this->options['analytics_profile_code'] ) ) {
			// Analytics profile is still holding the UA code
			$tracking_code = $this->options['analytics_profile'];
		}

		if ( ! empty( $this->options['manual_ua_code_field'] ) && ! empty( $this->options['manual_ua_code'] ) ) {
			$tracking_code = $this->options['manual_ua_code_field'];
		}

		return $tracking_code;
	}

	/**
	 * Convert a option value to a bool
	 *
	 * @param string $option_name
	 *
	 * @return bool
	 */
	public function option_value_to_bool( $option_name ) {
		$this->options = $this->get_options();

		if ( isset( $this->options[ $option_name ] ) && $this->options[ $option_name ] == 1 ) {
			return true;
		}

		return false;
	}

	/**
	 * Upgrade the settings when settings are changed.
	 *
	 * @since 5.0.1
	 */
	private function upgrade() {
		if ( ! isset( $this->options['version'] ) && is_null( $this->get_tracking_code() ) ) {
			$old_options = get_option( 'Yoast_Google_Analytics' );
			if ( isset( $old_options ) && is_array( $old_options ) ) {
				if ( isset( $old_options['uastring'] ) && '' !== trim( $old_options['uastring'] ) ) {
					// Save UA as manual UA, instead of saving all the old GA crap
					$this->options['manual_ua_code']       = 1;
					$this->options['manual_ua_code_field'] = $old_options['uastring'];
				}
				// Other settings
				$this->options['allow_anchor']               = $old_options['allowanchor'];
				$this->options['add_allow_linker']           = $old_options['allowlinker'];
				$this->options['anonymous_data']             = $old_options['anonymizeip'];
				$this->options['track_outbound']             = $old_options['trackoutbound'];
				$this->options['track_internal_as_outbound'] = $old_options['internallink'];
				$this->options['track_internal_as_label']    = $old_options['internallinklabel'];
				$this->options['extensions_of_files']        = $old_options['dlextensions'];
			}
			delete_option( 'Yoast_Google_Analytics' );
		}
		// 5.0.0 to 5.0.1 fix of ignore users array
		if ( ! isset( $this->options['version'] ) || version_compare( $this->options['version'], '5.0.1', '<' ) ) {
			if ( isset( $this->options['ignore_users'] ) && ! is_array( $this->options['ignore_users'] ) ) {
				$this->options['ignore_users'] = (array) $this->options['ignore_users'];
			}
		}
		// 5.1.2+ Remove firebug_lite from options, if set
		if ( ! isset ( $this->options['version'] ) || version_compare( $this->options['version'], '5.1.2', '<' ) ) {
			if ( isset( $this->options['firebug_lite'] ) ) {
				unset( $this->options['firebug_lite'] );
			}
		}
		// 5.2.8+ Add disabled dashboards option
		if ( ! isset ( $this->options['dashboards_disabled'] ) || version_compare( $this->options['version'], '5.2.8', '>' ) ) {
			$this->options['dashboards_disabled'] = 0;
		}
		// Check is API option already exists - if not add it
		$yst_ga_api = get_option( 'yst_ga_api' );
		if ( $yst_ga_api === false ) {
			add_option( 'yst_ga_api', array(), '', 'no' );
		}
		// Fallback to make sure every default option has a value
		$defaults = $this->default_ga_values();
		if ( is_array( $defaults ) ) {
			foreach ( $defaults[ $this->option_prefix ] as $key => $value ) {
				if ( ! isset( $this->options[ $key ] ) ) {
					$this->options[ $key ] = $value;
				}
			}
		}
		// Set to the current version now that we've done all needed upgrades
		$this->options['version'] = GAWP_VERSION;
		$this->update_option( $this->options );
	}

	/**
	 * Set the default GA settings here
	 * @return array
	 */
	public function default_ga_values() {
		$options = array(
			$this->option_prefix => array(
				'analytics_profile'          => null,
				'analytics_profile_code'     => null,
				'manual_ua_code'             => 0,
				'manual_ua_code_field'       => null,
				'track_internal_as_outbound' => null,
				'track_internal_as_label'    => null,
				'track_outbound'             => 0,
				'anonymous_data'             => 0,
				'enable_universal'           => 1,
				'demographics'               => 0,
				'ignore_users'               => array( 'administrator', 'editor' ),
				'dashboards_disabled'        => 0,
				'anonymize_ips'              => 0,
				'track_download_as'          => 'event',
				'extensions_of_files'        => 'doc,exe,js,pdf,ppt,tgz,zip,xls',
				'track_full_url'             => 'domain',
				'subdomain_tracking'         => null,
				'tag_links_in_rss'           => 0,
				'allow_anchor'               => 0,
				'add_allow_linker'           => 0,
				'enhanced_link_attribution'  => 0,
				'custom_code'                => null,
				'debug_mode'                 => 0,
			)
		);
		$options = apply_filters( 'yst_ga_default-ga-values', $options, $this->option_prefix );

		return $options;
	}

	/**
	 * Load plugin textdomain
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'google-analytics-for-wordpress', false, dirname( plugin_basename( GAWP_FILE ) ) . '/languages/' );
	}

}
