<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Google Analytics class.
 */
class Yoast_Google_Analytics {

	/**
	 * @var string
	 */
	private $option_name = 'yst_ga_api';

	/**
	 * @var array|mixed
	 */
	private $options = array();

	/**
	 * @var null|Yoast_Google_Analytics
	 */
	private static $instance = null;

	/**
	 * @var The api client object holder
	 */
	private $client;

	/**
	 * Singleton
	 *
	 */
	protected function __construct() {

		if ( is_null( self::$instance ) ) {
			self::$instance = $this;
		}

		$this->options = $this->get_options();

		// Setting the client
		$this->set_client();
	}

	/**
	 * Getting the instance object
	 *
	 * This method will return the instance of itself, if instance not exists, becauses of it's called for the first
	 * time, the instance will be created.
	 *
	 * @return null|Yoast_Google_Analytics
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Check if something went wrong with API calls to Google Analytics
	 */
	public function check_for_ga_issues() {

		$last_run   = get_option( 'yst_ga_last_wp_run' );
		$has_failed = get_option( 'yst_ga_api_call_fail', false );

		// Show error, something went wrong
		if ( $has_failed && ( $last_run === false || Yoast_GA_Utils::hours_between( strtotime( $last_run ), time() ) >= 48 ) ) {
			$notice_type = 'warning_fetching_data';

			// Authentication has been successful, so there will be an access token
			if ( ! $this->client->getAccessToken() ) {
				$notice_type .= '_authenticate';
			}

			add_action( 'admin_notices', array( 'Yoast_Google_Analytics_Notice', $notice_type ) );
		}

	}

	/**
	 * Wrapper for authenticate the client. If authentication code is send it will get and check an access token.
	 *
	 * @param mixed $authentication_code
	 *
	 * @return boolean
	 */
	public function authenticate( $authentication_code = null ) {
		// When authentication again we should clean up some stuff
		$this->api_cleanup();

		return $this->client->authenticate_client( $authentication_code );
	}

	/**
	 * Getting the analytics profiles
	 *
	 * Doing the request to the Google analytics API and if there is a response, parses this response and return its
	 * array
	 *
	 * @return array
	 */
	public function get_profiles() {
		$accounts = $this->format_profile_call(
			$this->do_request( 'https://www.googleapis.com/analytics/v3/management/accountSummaries' )
		);

		if ( is_array( $accounts ) ) {
			$this->save_profile_response( $accounts );

			return $accounts;
		}

		return array();
	}

	/**
	 * Doing request to Google Analytics
	 *
	 * This method will do a request to google and get the response code and body from content
	 *
	 * @param string $target_request_url
	 *
	 * @return array|null
	 */
	public function do_request( $target_request_url ) {

		$response = $this->client->do_request( $target_request_url );

		if ( ! empty( $response ) ) {
			return array(
				'response' => array( 'code' => $this->client->get_http_response_code() ),
				'body'     => json_decode( $response->getResponseBody(), true ),
			);
		}

	}


	/**
	 * Check if client has a refresh token
	 * @return bool
	 */
	public function has_refresh_token() {
		return $this->client->is_authenticated();
	}

	/**
	 * Getting the options bases on $this->option_name from the database
	 *
	 * @return mixed
	 */
	public function get_options() {
		return get_option( $this->option_name );
	}

	/**
	 * Checks whether we'll ever be able to reach Google.
	 *
	 * @return bool
	 */
	public function check_google_access_from_wp() {
		$can_access_google = true;
		if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ) {
			$can_access_google = false;
			if ( defined( 'WP_ACCESSIBLE_HOSTS' ) ) {
				// Better to use the internal WP logic from this point forward.
				$can_access_google = $this->test_connection_to_google();
			}
		}

		return $can_access_google;
	}

	/**
	 * Check if we can access Google Apis from this server by making a dummy connection
	 */
	public function check_google_access() {
		return $this->test_connection_to_google();
	}

	/**
	 * Updating the options based on $this->option_name and the internal property $this->options
	 */
	protected function update_options() {
		update_option( $this->option_name, $this->options );
	}

	/**
	 * Setting the client
	 *
	 * The filter is a hook to override the configuration/
	 */
	protected function set_client() {
		// See https://developers.google.com/identity/protocols/OAuth2InstalledApp#formingtheurl for more details about these fields.
		$config = array(
			'application_name' => 'Google Analytics by Yoast',
			'client_id'        => '346753076522-21smrc6aq0hq8oij8001s57dfoo8igf5.apps.googleusercontent.com',
			'client_secret'    => '5oWaEGFgp-bSrY6vWBmdPfIF',
			'redirect_uri'     => 'urn:ietf:wg:oauth:2.0:oob',
			'scopes'           => array( 'https://www.googleapis.com/auth/analytics.readonly' ),
		);

		$config = apply_filters( 'yst-ga-filter-ga-config', $config );

		$this->client = new Yoast_Api_Google_Client( $config, 'yoast-ga', '' );
	}

	/**
	 * Gets an authentication URL
	 *
	 * @return mixed
	 */
	public function create_auth_url() {
		return $this->client->createAuthUrl();
	}

	/**
	 * Saving profile response in options
	 *
	 * @param array $accounts
	 */
	protected function save_profile_response( $accounts ) {
		$this->options['ga_api_response_accounts'] = $accounts;

		$this->update_options();
	}

	/**
	 * Test a connection to Google
	 *
	 * @return bool
	 */
	private function test_connection_to_google() {
		$wp_http = new WP_Http();
		if ( $wp_http->block_request( 'https://www.googleapis.com/analytics/v3/management/accountSummaries' ) === false ) {
			return true;
		}

		return false;
	}

	/**
	 * Format the accounts request
	 *
	 * @param array $response
	 *
	 * @return mixed
	 */
	private function format_profile_call( $response ) {

		if ( isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
			if ( ! empty( $response['body']['items'] ) && is_array( $response['body']['items'] ) ) {
				$accounts = array();

				foreach ( $response['body']['items'] as $item ) {
					// Check if webProperties is set
					if ( isset( $item['webProperties'] ) ) {
						$profiles = array();

						foreach ( $item['webProperties'] as $property_key => $property ) {
							$profiles[ $property_key ] = array(
								'id'    => $property['id'],
								'name'  => $property['name'],
								'items' => array(),
							);

							// Check if profiles is set
							if ( isset( $property['profiles'] ) ) {
								foreach ( $property['profiles'] as $key => $profile ) {
									$profiles[ $property_key ]['items'][ $key ] = array_merge(
										$profile,
										array(
											'name'    => $profile['name'] . ' (' . $property['id'] . ')',
											'ua_code' => $property['id'],
										)
									);
								}
							}
						}

						$accounts[ $item['id'] ] = array(
							'id'          => $item['id'],
							'ua_code'     => $property['id'],
							'parent_name' => $item['name'],
							'items'       => $profiles,
						);

					}
				}

				return $accounts;
			}
		}

		return false;
	}

	/**
	 * Doing some clean up when this method is called
	 */
	private function api_cleanup() {
		delete_option( 'yst_ga_api_call_fail' );
	}

}

/**
 * Notice class.
 */
class Yoast_Google_Analytics_Notice {

	/**
	 * Throw a warning if no UA code is set.
	 */
	public static function config_warning() {
		self::show_error(
			sprintf( __( 'Please configure your %sGoogle Analytics settings%s!', 'google-analytics-for-wordpress' ),
				'<a href="' . admin_url( 'admin.php?page=yst_ga_settings' ) . '">',
				'</a>'
			)
		);
	}

	/**
	 * Throw a warning when the fetching failed
	 */
	public static function warning_fetching_data_authenticate() {
		self::show_error(
			sprintf(
				__( 'It seems the authentication for the plugin has expired, please %sre-authenticate%s with Google Analytics to allow the plugin to fetch data.', 'google-analytics-for-wordpress' ),
				'<a href="' . admin_url( 'admin.php?page=yst_ga_settings' ) . '">',
				'</a>'
			)
		);
	}

	/**
	 * Throw a warning when the fetching failed
	 */
	public static function warning_fetching_data() {
		self::show_error(
			sprintf(
				__( 'Data is not up-to-date, there was an error in retrieving the data from Google Analytics. This error could be caused by several issues. If the error persists, please see %sthis page%s.', 'google-analytics-for-wordpress' ),
				'<a href="http://yoa.st/2p">',
				'</a>'
			)
		);
	}

	/**
	 * Showing the given error as an error div
	 *
	 * @param string $error_message
	 */
	private static function show_error( $error_message ) {
		echo '<div class="error"><p>' . $error_message . '</p></div>';
	}

}