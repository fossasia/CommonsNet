<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Base abstract class.
 */
abstract class Yoast_GA_Dashboards_Driver {

	/**
	 * Container for holding set dashboards
	 *
	 * @var array
	 */
	protected $dashboards = array();

	/**
	 * This will initialize the AJAX request
	 */
	public function __construct() {
		$this->initialize_ajax();
	}

	/**
	 * Method which will be called by AJAX
	 *
	 * Will echo json for graph
	 */
	public function get_ajax_data() {
		check_ajax_referer( 'yoast-ga-dashboard-nonce', '_ajax_nonce' );

		$generator = $this->get_dashboard_generate_object();
		$json      = $generator->get_json();

		echo $json;
		die();
	}

	/**
	 * Register a dashboard with settings.
	 *
	 * Dashboard can contain multiple dashboard-types. If so, $values shouldn't be passed and $dashboard argument
	 * should be key->value, key = dashboard and value should contain the values
	 *
	 * Given arguments will be marge with objects property dashboards
	 *
	 * @param mixed $dashboard
	 * @param mixed $values
	 */
	public function register( $dashboard, $values = false ) {

		if ( ! is_array( $dashboard ) ) {
			$dashboard = array( $dashboard => $values );
		}

		$this->dashboards = array_merge( $this->dashboards, $dashboard );
	}

	/**
	 * Giving the dashboardname to show
	 *
	 * @param string $dashboard
	 */
	public function display( $dashboard ) {
		$settings = $this->dashboards[ $dashboard ];
		require dirname( GAWP_FILE ) . '/admin/dashboards/views/' . $this->dashboard_type . '.php';
	}

	/**
	 * Setting hook for doing ajax request
	 */
	protected function initialize_ajax() {
		add_action( $this->ajax_hook, array( $this, 'get_ajax_data' ) );
	}

	/**
	 * This method should always be available
	 * @return mixed
	 */
	abstract protected function get_dashboard_generate_object();

}
