<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Dashboards collector.
 */
class Yoast_GA_Dashboards_Collector {

	/**
	 * @var array $active_metrics Store the active metrics
	 */
	public $active_metrics;

	/**
	 * Store the dimensions
	 *
	 * @var array
	 */
	private $dimensions = array();

	/**
	 * Store the valid metrics, which should be
	 *
	 * @var array
	 */
	private $valid_metrics = array();

	/**
	 * @var integer $ga_profile_id Store the GA Profile ID
	 */
	public $ga_profile_id;

	/**
	 * Construct on the dashboards class for GA
	 *
	 * @param int   $ga_profile_id
	 * @param array $active_metrics
	 * @param array $valid_metrics
	 */
	public function __construct( $ga_profile_id, $active_metrics, $valid_metrics ) {
		$this->ga_profile_id = $ga_profile_id;

		$active_metrics       = $this->filter_metrics_to_dimensions( $active_metrics );
		$this->active_metrics = $active_metrics;

		add_filter( 'ga_dashboards_dimensions', array( $this, 'filter_dimensions' ), 10, 1 );

		$this->options = Yoast_GA_Dashboards_Api_Options::get_instance();

		$this->init_shutdown_hook();
	}

	/**
	 * Fetch the data from Google Analytics and store it
	 */
	public function aggregate_data() {
		if ( is_numeric( $this->ga_profile_id ) ) {
			// ProfileID is set

			/**
			 * Implement the metric data first
			 */
			if ( is_array( $this->active_metrics ) && count( $this->active_metrics ) >= 1 ) {
				$this->aggregate_metrics( $this->active_metrics );
			}

			/**
			 * Now implement the dimensions that are set
			 */
			if ( is_array( $this->dimensions ) && count( $this->dimensions ) >= 1 ) {
				$this->aggregate_dimensions( $this->dimensions );
			}
		}
		else {
			// Failure on authenticating, please reauthenticate
		}
	}

	/**
	 * This hook runs on the shutdown to fetch data from GA
	 */
	private function init_shutdown_hook() {
		// Hook the WP cron event
		add_action( 'wp', array( $this, 'setup_wp_cron_aggregate' ) );

		// Hook our function to the WP cron event the fetch data daily
		add_action( 'yst_ga_aggregate_data', array( $this, 'aggregate_data' ) );

		// Check if the WP cron did run on time
		if ( filter_input( INPUT_GET, 'page' ) === 'yst_ga_dashboard' ) {
			add_action( 'shutdown', array( $this, 'check_api_call_hook' ) );
		}
	}

	/**
	 * Check if we scheduled the WP cron event, if not, do so.
	 */
	public function setup_wp_cron_aggregate() {
		if ( ! wp_next_scheduled( 'yst_ga_aggregate_data' ) ) {
			// Set the next event of fetching data
			wp_schedule_event( strtotime( date( 'Y-m-d', strtotime( 'tomorrow' ) ) . ' 00:05:00 ' ), 'daily', 'yst_ga_aggregate_data' );
		}
	}

	/**
	 * Check if the WP cron did run yesterday. If not, we need to run it form here
	 */
	public function check_api_call_hook() {
		$last_run = $this->get_last_aggregate_run();


		/**
		 * Transient doesn't exists, so we need to run the
		 * hook (This function runs already on Shutdown so
		 * we can call it directly from now on) or the last run has ben more than 24 hours
		 */
		if ( $last_run === false || Yoast_GA_Utils::hours_between( strtotime( $last_run ), time() ) >= 24 ) {
			$this->aggregate_data();
		}
	}


	/**
	 * Get the datetime when the aggregate data function was succesful
	 *
	 * @return mixed
	 */
	private function get_last_aggregate_run() {
		return get_option( 'yst_ga_last_wp_run' );
	}

	/**
	 * Remove metrics and set them as a dimension if needed
	 *
	 * @param array $metrics
	 *
	 * @return mixed
	 */
	private function filter_metrics_to_dimensions( $metrics ) {
		$filter_metrics = $this->get_filter_metrics();

		foreach ( $metrics as $key => $metric_name ) {
			if ( isset( $filter_metrics[ $metric_name ] ) ) {
				// Add and set the dimension
				$dimension        = array( $filter_metrics[ $metric_name ] );
				$this->dimensions = array_merge( $this->dimensions, $dimension );

				// Remove it from the metrics after we've added it into dimensions
				unset( $metrics[ $key ] );
			}
		}

		return $metrics;
	}

	/**
	 * Get array with metrics which we need to filter as a dimension
	 *
	 * @return array
	 */
	private function get_filter_metrics() {
		return array(
			'source'        => array(
				'metric'       => 'sessions',
				'dimension'    => 'source',
				'storage_name' => 'source',
			),
			'top_pageviews' => array(
				'metric'       => 'pageViews',
				'dimension'    => 'pagePath',
				'storage_name' => 'top_pageviews',
			),
			'top_countries' => array(
				'metric'       => 'sessions',
				'dimension'    => 'country',
				'storage_name' => 'top_countries',
			),
		);
	}

	/**
	 * Filter function for adding dimensions
	 *
	 * @filter ga_dashboards_dimensions
	 *
	 * @param array $dimensions
	 *
	 * @return array
	 */
	public function filter_dimensions( $dimensions = array() ) {
		if ( is_array( $dimensions ) && count( $dimensions ) >= 1 ) {
			$dimensions       = array_merge( $this->dimensions, $dimensions );
			$this->dimensions = $dimensions;
		}

		return $this->dimensions;
	}

	/**
	 * Get the start and and date for aggregation functionality
	 *
	 * @return array
	 */
	private function get_date_range() {
		/**
		 * Filter: 'yst-ga-filter-api-end-date' - Allow people to change the end date for the dashboard
		 * data. Default: yesterday.
		 *
		 * @api string Date (Y-m-d)
		 */
		return array(
			'start' => date( 'Y-m-d', strtotime( '-1 month' ) ),
			'end'   => apply_filters( 'yst-ga-filter-api-end-date', date( 'Y-m-d', strtotime( 'yesterday' ) ) ),
		);
	}

	/**
	 * Aggregate metrics from GA. This function should be called in the shutdown function.
	 *
	 * @param array $metrics
	 */
	private function aggregate_metrics( $metrics ) {
		$dates = $this->get_date_range();

		foreach ( $metrics as $metric ) {
			$this->execute_call( $metric, $dates['start'], $dates['end'] );
		}
	}

	/**
	 * Aggregate dimensions from GA. This function should be called in the shutdown function.
	 *
	 * @param array $dimensions
	 */
	private function aggregate_dimensions( $dimensions ) {
		$dates = $this->get_date_range();

		foreach ( $dimensions as $dimension ) {
			if ( isset( $dimension['metric'] ) ) {
				if ( isset( $dimension['id'] ) ) {
					$this->execute_call( $dimension['metric'], $dates['start'], $dates['end'], 'ga:dimension' . $dimension['id'] );
				}
				elseif ( isset( $dimension['dimension'] ) ) {
					if ( isset( $dimension['storage_name'] ) ) {
						$this->execute_call( $dimension['metric'], $dates['start'], $dates['end'], 'ga:' . $dimension['dimension'], $dimension['storage_name'] );
					}
					else {
						$this->execute_call( $dimension['metric'], $dates['start'], $dates['end'], 'ga:' . $dimension['dimension'] );
					}
				}
			}
		}
	}

	/**
	 * Execute an API call to Google Analytics and store the data in the dashboards data class
	 *
	 * @param string $metric
	 * @param string $start_date   2014-10-16
	 * @param string $end_date     2014-11-20
	 * @param string $dimensions   ga:date
	 * @param string $storage_name auto
	 *
	 * @return bool
	 */
	private function execute_call( $metric, $start_date, $end_date, $dimensions = 'ga:date', $storage_name = 'auto' ) {
		$dimensions   = $this->prepare_dimensions( $dimensions, $metric );
		$params       = $this->build_params_for_call( $start_date, $end_date, $dimensions, $metric );
		$storage_type = $this->get_storage_type( $dimensions );

		$response = Yoast_Google_Analytics::get_instance()->do_request( 'https://www.googleapis.com/analytics/v3/data/ga?' . $params );

		if ( isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {

			// Delete option api_fail because there it's successful now
			delete_option( 'yst_ga_api_call_fail' );

			// Success, set a transient which stores the latest runtime
			update_option( 'yst_ga_last_wp_run', date( 'Y-m-d' ) );

			$response = Yoast_Googleanalytics_Reporting::get_instance()->parse_response( $response, $storage_type, $start_date, $end_date );
		}
		else {
			// When response is failing, we should count the number of
			$this->save_api_failure();

			return false;
		}

		if ( strpos( 'ga:date', $dimensions ) !== false ) {
			return $this->handle_response( $response, $metric, $dimensions, $start_date, $end_date, 'datelist', $storage_name );
		}
		else {
			return $this->handle_response( $response, $metric, $dimensions, $start_date, $end_date, 'table', $storage_name );
		}
	}

	/**
	 * When the API isn't able to get a successful response (code 200), we have to save that the call has failed
	 */
	private function save_api_failure() {
		update_option( 'yst_ga_api_call_fail', true );
	}

	/**
	 * Get the storage type from dimensions
	 *
	 * @param string $dimensions
	 *
	 * @return string
	 */
	private function get_storage_type( $dimensions ) {
		if ( strpos( 'ga:date', $dimensions ) !== false ) {
			return 'datelist';
		}
		else {
			return 'table';
		}
	}

	/**
	 * Prepare dimensions before adding them as a parameter in a call
	 *
	 * @param array $dimensions
	 * @param array $metric
	 *
	 * @return array
	 */
	private function prepare_dimensions( $dimensions, $metric ) {
		$filter_metrics = $this->get_filter_metrics();

		// Check if the dimensions param is an array, if so, glue it with implode to a comma separated string.
		if ( is_array( $dimensions ) ) {
			$dimensions = implode( ',', $dimensions );
		}

		if ( in_array( $metric, $this->valid_metrics ) ) {
			$dimensions = 'ga:date,' . $dimensions;
		}
		elseif ( isset( $filter_metrics[ str_replace( 'ga:', '', $dimensions ) ] ) ) {
			// Make sure we don't have a ga:date property here
			$dimensions = str_replace( 'ga:date', '', $dimensions );
		}

		return $dimensions;
	}

	/**
	 * Build the params for a call to Google Analytics, return them prepared for a http query
	 *
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $dimensions
	 * @param string $metric
	 *
	 * @return string
	 */
	private function build_params_for_call( $start_date, $end_date, $dimensions, $metric ) {
		/**
		 * Filter: 'yst-ga-filter-api-limit' - Allow people to change the max results value in the API
		 * calls. Default value is 1000 results per call.
		 *
		 * @api int 1000
		 */
		$api_call_limit = apply_filters( 'yst-ga-filter-api-limit', 1000 );

		$params = array(
			'ids'         => 'ga:' . $this->ga_profile_id,
			'start-date'  => $start_date,
			'end-date'    => $end_date,
			'dimensions'  => $dimensions,
			'metrics'     => 'ga:' . $metric,
			'max-results' => $api_call_limit,
		);

		$params = $this->add_sort_direction( $params, $dimensions, $metric );
		$params = http_build_query( $params );

		return $params;
	}

	/**
	 * Add a sort direction if we need to (Especially on dimensions which are
	 * listed in $this->get_filter_metrics())
	 *
	 * @param array  $params
	 * @param string $dimensions
	 * @param string $metric
	 *
	 * @return array
	 */
	private function add_sort_direction( $params, $dimensions, $metric ) {
		$filter_dimensions = $this->get_filter_metrics();

		foreach ( $filter_dimensions as $dimension ) {
			if ( str_replace( 'ga:', '', $dimensions ) == $dimension['dimension'] && str_replace( 'ga:', '', $metric ) == $dimension['metric'] ) {
				$params['sort'] = '-ga:' . $dimension['metric'];
			}
		}

		return $params;
	}

	/**
	 * Handle the response from the Google Analytics api.
	 *
	 * @param array|boolean $response
	 * @param string        $metric
	 * @param array         $dimensions
	 * @param string        $start_date
	 * @param string        $end_date
	 * @param string        $store_as
	 * @param string        $storage_name
	 *
	 * @return bool
	 */
	private function handle_response( $response, $metric, $dimensions, $start_date, $end_date, $store_as = 'table', $storage_name = 'auto' ) {
		if ( is_array( $response ) ) {
			// Success, store this data
			$filter_metrics = $this->get_filter_metrics();
			$extracted      = str_replace( 'ga:', '', str_replace( 'ga:date,', '', $dimensions ) );

			if ( isset( $filter_metrics[ $extracted ] ) ) {
				$name = $extracted;
			}
			else {
				$name = $metric;
			}

			if ( $dimensions !== 'ga:date' && ! isset( $filter_metrics[ $extracted ] ) ) {
				$name = str_replace( 'ga:date,', '', $dimensions );
			}

			// Overwrite the name if we have a defined one
			if ( $storage_name != 'auto' ) {
				$name = $storage_name;
			}

			return Yoast_GA_Dashboards_Data::set( $name, $response, strtotime( $start_date ), strtotime( $end_date ), $store_as );
		}
		else {
			// Failure on API call try to log it
			$this->log_error( print_r( $response, true ) );

			return false;
		}
	}

	/**
	 * Log an error while calling the Google Analytics API
	 *
	 * @param string $error
	 */
	private function log_error( $error ) {
		if ( true == WP_DEBUG ) {
			if ( function_exists( 'error_log' ) ) {
				error_log( 'Google Analytics by MonsterInsights (Dashboard API): ' . $error );
			}
		}
	}

}
