<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * This class is for the backend, extendable for all child classes
 */
class Yoast_GA_Admin extends Yoast_GA_Options {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'plugins_loaded', array( $this, 'init_ga' ) );

		// Only run admin_init when there is a cron jon executed.
		$current_page = filter_input( INPUT_GET, 'page' );

		// Only when current page is not 'wpseo'.
		if ( strpos( $current_page, 'wpseo' ) !== 0 ) {
			if ( ( $this->is_running_cron() || $this->is_running_ajax() ) || strpos( $current_page, 'yst_ga' ) === 0 ) {
				add_action( 'admin_init', array( $this, 'init_settings' ) );
			}
		}

		add_action( 'admin_init', array( $this, 'system_info' ) );

	}

	/**
	 * Init function when the plugin is loaded
	 */
	public function init_ga() {

		new Yoast_GA_Admin_Menu( $this );

		add_filter( 'plugin_action_links_' . plugin_basename( GAWP_FILE ), array( $this, 'add_action_links' ) );

	}

	/**
	 * Init function for the settings of GA
	 */
	public function init_settings() {
		$this->options = $this->get_options();

		try {
			// Loading Google Api Libs with minimal version 2.0.
			new MI_Api_Libs( '2.0' );
		}
		catch( Exception $exception ) {
			if ( $exception->getMessage() === 'required_version' ) {
				add_action( 'admin_notices', array( $this, 'set_api_libs_error' ) );
			}
		}

		$dashboards = Yoast_GA_Dashboards::get_instance();

		// Listener for reconnecting with google analytics
		$this->google_analytics_listener();

		if ( is_null( $this->get_tracking_code() ) && $this->show_admin_warning() ) {
			add_action( 'admin_notices', array( 'Yoast_Google_Analytics_Notice', 'config_warning' ) );
		}

		// Check if something has went wrong with GA-api calls
		$has_tracking_code = ( ! is_null( $this->get_tracking_code() ) && empty( $this->options['manual_ua_code_field'] ) );
		if ( $has_tracking_code && $this->show_admin_dashboard_warning() ) {
			Yoast_Google_Analytics::get_instance()->check_for_ga_issues();
		}


		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$this->handle_ga_post_request( $dashboards );
		}

		/**
		 * Show the notifications if we have one
		 */
		$this->show_notification( 'ga_notifications' );

		// Load the Google Analytics Dashboards functionality
		$dashboards->init_dashboards( $this->get_current_profile() );
	}

	/**
	 * There is an error with the API libs. So show a notice.
	 */
	public function set_api_libs_error() {
		echo '<div class="error notice"><p>' . __( 'MonsterInsights plugins share some code between them to make your site faster. As a result of that, we need all MonsterInsights plugins to be up to date. We\'ve detected this isn\'t the case, so please update the MonsterInsights plugins that aren\'t up to date yet.', 'google-analytics-for-wordpress' ) . '</p></div>';
	}

	/**
	 * This function saves the settings in the option field and returns a wp success message on success
	 *
	 * @param array $data
	 */
	public function save_settings( $data ) {

		unset( $data['google_auth_code'] );

		foreach ( $data as $key => $value ) {
			if ( $key != 'return_tab' ) {
				if ( is_string( $value ) ) {
					if ( $key === 'custom_code' && ! current_user_can( 'unfiltered_html' ) ) {
						continue;
					}
					else {
						$value = strip_tags( $value );
					}
				}

				$this->options[ $key ] = $value;
			}
		}

		// Check checkboxes, on a uncheck they won't be posted to this function
		$defaults = $this->default_ga_values();
		foreach ( $defaults[ $this->option_prefix ] as $option_name => $value ) {
			$this->handle_default_setting( $data, $option_name, $value );
		}

		if ( ! empty( $this->options['analytics_profile'] ) ) {
			$this->options['analytics_profile_code'] = $this->get_ua_code_from_profile( $this->options['analytics_profile'] );
		}

		$this->do_validation( $data['return_tab'] );

		if ( $this->update_option( $this->options ) ) {
			// Success, add a new notification
			$this->add_notification( 'ga_notifications', array(
				'type'        => 'success',
				'description' => __( 'Settings saved.', 'google-analytics-for-wordpress' ),
			) );
		}
		else {
			// Fail, add a new notification
			$this->add_notification( 'ga_notifications', array(
				'type'        => 'error',
				'description' => __( 'There were no changes to save, please try again.', 'google-analytics-for-wordpress' ),
			) );
		}

		// redirect
		wp_redirect( admin_url( 'admin.php' ) . '?page=yst_ga_settings#top#' . $data['return_tab'], 301 );
		exit;
	}

	/**
	 * Redirect to settings with a validation error if there are validation errors
	 *
	 * @param string $return_tab The tab to return to when there is a validation error.
	 */
	protected function do_validation( $return_tab ) {
		$validation = $this->validate_settings();
		if ( is_wp_error( $validation ) ) {
			$this->add_notification( 'ga_notifications', array(
				'type' => 'error',
				'description' => $validation->get_error_message(),
			) );

			wp_redirect( admin_url( 'admin.php' ) . '?page=yst_ga_settings#top#' . $return_tab, 301 );
			exit;
		}
	}

	/**
	 * Validates the settings in the `options` attribute, returns an WP_Error object on error
	 *
	 * @return true|WP_Error true or an error object.
	 */
	protected function validate_settings() {

		if ( ! empty( $this->options['manual_ua_code_field'] ) ) {
			$this->options['manual_ua_code_field'] = trim( $this->options['manual_ua_code_field'] );
			// en dash to minus, prevents issue with code copied from web with "fancy" dash
			$this->options['manual_ua_code_field'] = str_replace( '–', '-', $this->options['manual_ua_code_field'] );

			// Regex to tests if a valid UA code has been set. Valid codes follow: "UA-[4 digits]-[at least 1 digit]".
			if ( ! preg_match( '|^UA-\d{4,}-\d+$|', $this->options['manual_ua_code_field'] ) ) {

				return new WP_Error(
					'ua-code-format',
					__( 'The UA code needs to follow UA-XXXXXXXX-X format.', 'google-analytics-for-wordpress' )
				);
			}
		}

		/**
		 * Filters the validation for the admin options
		 *
		 * @param true|WP_Error true if the validation is successful, WP_Error on error.
		 * @param array $this->options The options that are being saved.
		 */
		return apply_filters( 'yst_ga_admin_validate_settings', true, $this->options );
	}

	/**
	 * Run a this deactivation hook on deactivation of GA. When this happens we'll
	 * remove the options for the profiles and the refresh token.
	 */
	public static function ga_deactivation_hook() {
		// Remove the refresh token and other API settings
		self::analytics_api_clean_up();
	}

	/**
	 * Handle a default setting in GA
	 *
	 * @param array  $data
	 * @param string $option_name
	 * @param mixed  $value
	 */
	private function handle_default_setting( $data, $option_name, $value ) {
		if ( ! isset( $data[ $option_name ] ) ) {
			// If no data was passed in, set it to the default.
			if ( $value === 1 ) {
				// Disable the checkbox for now, use value 0
				$this->options[ $option_name ] = 0;
			}
			else {
				$this->options[ $option_name ] = $value;
			}
		}
	}

	/**
	 * Handle the post requests in the admin form of the GA plugin
	 *
	 * @param Yoast_GA_Dashboards $dashboards
	 */
	private function handle_ga_post_request( $dashboards ) {
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}

		if ( isset( $_POST['ga-form-settings'] ) && wp_verify_nonce( $_POST['yoast_ga_nonce'], 'save_settings' ) ) {
			if ( ! isset ( $_POST['ignore_users'] ) ) {
				$_POST['ignore_users'] = array();
			}

			$dashboards_disabled = Yoast_GA_Settings::get_instance()->dashboards_disabled();

			if ( ( $dashboards_disabled == false && isset( $_POST['dashboards_disabled'] ) ) || $this->ga_profile_changed( $_POST ) ) {
				$dashboards->reset_dashboards_data();
			}

			// Post submitted and verified with our nonce
			$this->save_settings( $_POST );
		}
	}

	/**
	 * Is there selected an other property in the settings post? Returns true or false.
	 *
	 * @param array $post
	 *
	 * @return bool
	 */
	private function ga_profile_changed( $post ) {
		if ( isset( $post['analytics_profile'] ) && isset( $this->options['analytics_profile'] ) ) {
			if ( $post['analytics_profile'] != $this->options['analytics_profile'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Are we allowed to show a warning message? returns true if it's allowed
	 *
	 * @return bool
	 */
	private function show_admin_warning() {
		return ( current_user_can( 'manage_options' ) && ( ! isset( $_GET['page'] ) || ( isset( $_GET['page'] ) && $_GET['page'] !== 'yst_ga_settings' ) ) );
	}

	/**
	 * Are we allowed to show a warning message? returns true if it's allowed ( this is meant to be only for dashboard )
	 *
	 * @return bool
	 */
	private function show_admin_dashboard_warning() {
		return ( current_user_can( 'manage_options' ) && isset( $_GET['page'] ) && $_GET['page'] === 'yst_ga_dashboard' );
	}

	/**
	 * Transform the Profile ID into an helpful UA code
	 *
	 * @param integer $profile_id
	 *
	 * @return null
	 */
	private function get_ua_code_from_profile( $profile_id ) {
		$profiles = $this->get_profiles();
		$ua_code  = null;

		foreach ( $profiles as $account ) {
			foreach ( $account['items'] as $profile ) {
				foreach ( $profile['items'] as $subprofile ) {
					if ( isset( $subprofile['id'] ) && $subprofile['id'] === $profile_id ) {
						return $subprofile['ua_code'];
					}
				}
			}
		}

		return $ua_code;
	}

	/**
	 * Add a link to the settings page to the plugins list
	 *
	 * @param array $links array of links for the plugins, adapted when the current plugin is found.
	 *
	 * @return array $links
	 */
	public function add_action_links( $links ) {
		// add link to knowledgebase
		// @todo UTM link fix
		$faq_link = '<a title="MonsterInsights Knowledge Base" href="http://www.monsterinsights.com/docs/">' . __( 'FAQ', 'google-analytics-for-wordpress' ) . '</a>';
		array_unshift( $links, $faq_link );

		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=yst_ga_settings' ) ) . '">' . __( 'Settings', 'google-analytics-for-wordpress' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Adds some promo text for the premium plugin on the custom dimensions tab.
	 */
	public function premium_promo() {
		echo '<div class="ga-promote">';
		echo '<p>';
		printf( __( 'If you want to track custom dimensions like page views per author or post type, you should upgrade to the %1$spremium version of Google Analytics by MonsterInsights%2$s.', 'google-analytics-for-wordpress' ), '<a href="https://www.monsterinsights.com/pricing/#utm_medium=text-link&utm_source=gawp-config&utm_campaign=wpgaplugin&utm_content=custom_dimensions_tab">', '</a>' );
		echo ' ';
		_e( 'This will also give you access to the support team at MonsterInsights, who will provide support on the plugin 24/7.', 'google-analytics-for-wordpress' );
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Load the page of a menu item in the GA plugin
	 */
	public function load_page() {

		if ( ! has_action( 'yst_ga_custom_dimensions_tab-content' ) ) {
			add_action( 'yst_ga_custom_dimensions_tab-content', array( $this, 'premium_promo' ) );
		}

		if ( ! has_action( 'yst_ga_custom_dimension_add-dashboards-tab' ) ) {
			add_action( 'yst_ga_custom_dimension_add-dashboards-tab', array( $this, 'premium_promo' ) );
		}

		switch ( filter_input( INPUT_GET, 'page' ) ) {
			case 'yst_ga_settings':
				require_once( $this->plugin_path . 'admin/pages/settings.php' );
				break;
			case 'yst_ga_extensions':
				require_once( $this->plugin_path . 'admin/pages/extensions.php' );
				break;
			case 'yst_ga_dashboard':
			default:
				require_once( $this->plugin_path . 'admin/pages/dashboard.php' );
				break;
		}
	}


	/**
	 * Get the Google Analytics profiles which are in this google account
	 *
	 * @return array
	 */
	public function get_profiles() {
		$return = Yoast_Google_Analytics::get_instance()->get_profiles();

		return $return;
	}

	/**
	 * Checks if there is a callback to get token from Google Analytics API
	 */
	private function google_analytics_listener() {
		$google_auth_code = filter_input( INPUT_POST, 'google_auth_code' );
		if ( $google_auth_code && current_user_can( 'manage_options' ) && wp_verify_nonce( filter_input( INPUT_POST, 'yoast_ga_nonce' ), 'save_settings' ) ) {
			self::analytics_api_clean_up();

			Yoast_Google_Analytics::get_instance()->authenticate( trim( $google_auth_code ) );
		}
	}

	/**
	 * Clean up the Analytics API settings
	 */
	public static function analytics_api_clean_up() {
		delete_option( 'yoast-ga-refresh_token' );
		delete_option( 'yst_ga_api_call_fail' );
		delete_option( 'yst_ga_last_wp_run' );
		delete_option( 'yst_ga_api' );
	}

	/**
	 * Get the current GA profile
	 *
	 * @return null
	 */
	private function get_current_profile() {
		if ( ! empty( $this->options['analytics_profile'] ) ) {
			return $this->options['analytics_profile'];
		}

		return null;
	}

	/**
	 * Get the user roles of this WordPress blog
	 *
	 * @return array
	 */
	public function get_userroles() {
		global $wp_roles;

		$all_roles = $wp_roles->roles;
		$roles     = array();

		/**
		 * Filter: 'editable_roles' - Allows filtering of the roles shown within the plugin (and elsewhere in WP as it's a WP filter)
		 *
		 * @api array $all_roles
		 */
		$editable_roles = apply_filters( 'editable_roles', $all_roles );

		foreach ( $editable_roles as $id => $name ) {
			$roles[] = array(
				'id'   => $id,
				'name' => translate_user_role( $name['name'] ),
			);
		}

		return $roles;
	}

	/**
	 * Get types of how we can track downloads
	 *
	 * @return array
	 */
	public function track_download_types() {
		return array(
			0 => array( 'id' => 'event', 'name' => __( 'Event', 'google-analytics-for-wordpress' ) ),
			1 => array( 'id' => 'pageview', 'name' => __( 'Pageview', 'google-analytics-for-wordpress' ) ),
		);
	}

	/**
	 * Get options for the track full url or links setting
	 *
	 * @return array
	 */
	public function get_track_full_url() {
		return array(
			0 => array( 'id' => 'domain', 'name' => __( 'Just the domain', 'google-analytics-for-wordpress' ) ),
			1 => array( 'id' => 'full_links', 'name' => __( 'Full links', 'google-analytics-for-wordpress' ) ),
		);
	}

	/**
	 * Render the admin page head for the GA Plugin
	 */
	public function content_head() {
		require 'views/content_head.php';
	}

	/**
	 * Output System Info file
	 */
	public function system_info() {
		if ( ! empty( $_REQUEST['monsterinsights-action'] ) && $_REQUEST['monsterinsights-action'] === 'download_sysinfo' ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			nocache_headers();
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename="monsterinsights-system-info.txt"' );
			echo wp_strip_all_tags( $_POST['monsterinsights-sysinfo'] );
			die();
		}
	}

	/**
	 * Render the admin page footer with sidebar for the GA Plugin
	 */
	public function content_footer() {

		do_action( 'yoast_ga_admin_footer' );

		if ( class_exists( 'MI_Product_GA_Premium' ) ) {
			$license_manager = new MI_Plugin_License_Manager( new MI_Product_GA_Premium() );
			if ( $license_manager->license_is_valid() ) {
				return;
			}
		}

		$banners   = array();
		$banners[] = array(
			'url'    => 'https://www.optinmonster.com/?utm_source=monsterinsights-config&utm_medium=banner&utm_campaign=gaplugin',
			'banner' => $this->plugin_url . 'assets/img/omupsell.png',
			'title'  => 'Convert Visitors into Subscribers',
		);
		$banners[] = array(
			'url'    => 'https://www.monsterinsights.com/pricing/?utm_source=monsterinsights-config&utm_medium=banner&utm_campaign=gaplugin',
			'banner' => $this->plugin_url . 'assets/img/upgradetopro.png',
			'title'  => 'Get the premium version of Google Analytics by MonsterInsights!',
		);
		$banners[] = array(
			'url'    => 'http://www.wpbeginner.net/?utm_source=monsterinsights-config&utm_medium=banner&utm_campaign=gaplugin',
			'banner' => $this->plugin_url . 'assets/img/wpbeginnerupsell.png',
			'title'  => 'The best collection of free beginner WordPress resources!',
		);
		$banners[] = array(
			'url'    => 'https://wpforms.com/pricing/?utm_source=monsterinsights-config&utm_medium=banner&utm_campaign=gaplugin',
			'banner' => $this->plugin_url . 'assets/img/wpformsupsell.png',
			'title'  => 'Get the most beginner friendly WordPress contact form plugin in the market!',
		);

		shuffle( $banners );

		require 'views/content-footer.php';

	}

	/**
	 * Returns a list of all available extensions
	 *
	 * @return array
	 */
	public function get_extensions() {
		$extensions = array(
			'ga_premium' => (object) array(
				'url'    => 'https://www.monsterinsights.com/pricing/',
				'title'  => __( 'Google Analytics by MonsterInsights Pro', 'google-analytics-for-wordpress' ),
				'desc'   => __( 'The premium version of Google Analytics by MonsterInsights with more features and support.', 'google-analytics-for-wordpress' ),
				'status' => 'uninstalled',
			),
			'ecommerce'  => (object) array(
				'url'    => 'https://www.monsterinsights.com/pricing/',
				'title'  => __( 'Google Analytics by MonsterInsights', 'google-analytics-for-wordpress' ) . '<br />' . __( 'eCommerce tracking', 'google-analytics-for-wordpress' ),
				'desc'   => __( 'Track your eCommerce data and transactions with this eCommerce extension for Google Analytics.', 'google-analytics-for-wordpress' ),
				'status' => 'uninstalled',
			),
		);

		$extensions = apply_filters( 'yst_ga_extension_status', $extensions );

		return $extensions;
	}

	/**
	 * Add a notification to the notification transient
	 *
	 * @param string $transient_name
	 * @param array  $settings
	 */
	private function add_notification( $transient_name, $settings ) {
		set_transient( $transient_name, $settings, MINUTE_IN_SECONDS );
	}

	/**
	 * Show the notification that should be set, after showing the notification this function unset the transient
	 *
	 * @param string $transient_name The name of the transient which contains the notification
	 */
	public function show_notification( $transient_name ) {
		$transient = get_transient( $transient_name );

		if ( isset( $transient['type'] ) && isset( $transient['description'] ) ) {
			if ( $transient['type'] == 'success' ) {
				add_settings_error(
					'yoast_google_analytics',
					'yoast_google_analytics',
					$transient['description'],
					'updated'
				);
			}
			else {
				add_settings_error(
					'yoast_google_analytics',
					'yoast_google_analytics',
					$transient['description'],
					'error'
				);
			}

			delete_transient( $transient_name );
		}
	}

	/**
	 * Check if there the aggregate data cron is executed
	 * @return bool
	 */
	private function is_running_cron() {
		return doing_action( 'yst_ga_aggregate_data' ) && defined( 'DOING_CRON' ) && DOING_CRON;
	}

	/**
	 * Check if there the aggregate data cron is executed
	 * @return bool
	 */
	private function is_running_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX && strpos( filter_input( INPUT_GET, 'action' ), 'yoast_dashboard' ) === 0;
	}

}
