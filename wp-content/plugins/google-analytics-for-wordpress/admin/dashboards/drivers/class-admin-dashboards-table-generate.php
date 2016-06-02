<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Dashboards table inherited class.
 */
class Yoast_GA_Dashboards_Table_Generate extends Yoast_GA_Dashboards_Driver_Generate {

	/**
	 * @var string - The ID of requested dimension
	 */
	protected $dimension_id;

	/**
	 * Construct will set all values and generate the date for response
	 */
	public function __construct() {
		parent::__construct();

		$this->set_dimension_id();

		$this->generate();
	}

	/**
	 * Putting $this->data and $this->mapping and give them back as a json encoded string
	 *
	 * @return string
	 */
	public function get_json() {
		$return = array(
			'data' => $this->escape_strings_array( $this->data ),
		);

		return json_encode( $return );
	}

	/**
	 * Filtering the current data to eliminate all values which are not in given period
	 *
	 * @param integer $google_data
	 *
	 * @return integer
	 */
	protected function filter_google_data( $google_data ) {
		return $google_data['value'];
	}

	/**
	 * Setting the dimension_id for current request. Based on dimension_id the graph_type will be set, this to
	 * handle the request correctly
	 */
	private function set_dimension_id() {
		$this->dimension_id = filter_input( INPUT_GET, 'dimension_id' );

		if ( ! empty( $this->dimension_id ) ) {
			$this->graph_type = 'ga:dimension' . $this->dimension_id;
		}
		else {
			$this->graph_type = $this->graph_type;
		}
	}

	/**
	 * Generate the data for the frontend based on the $google_data
	 */
	private function generate() {
		$google_data = $this->get_google_data();
		$this->data  = is_array( $google_data ) ? array_values( $google_data ) : array();
	}

}
