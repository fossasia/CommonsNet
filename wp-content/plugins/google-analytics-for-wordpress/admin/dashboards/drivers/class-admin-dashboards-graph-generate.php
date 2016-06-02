<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Graph inherited class.
 */
class Yoast_GA_Dashboards_Graph_Generate extends Yoast_GA_Dashboards_Driver_Generate {

	/**
	 * The field that will be used for displaying on x-axis. Mostly it will be the j (day in month)
	 *
	 * See: http://nl3.php.net/manual/en/function.date.php under 'format'
	 *
	 * @var string
	 */
	private $date_field = 'j';

	/**
	 * @var array - Storage for $data
	 */
	private $data = array();

	/**
	 * Storage for mapping
	 *
	 * @var array
	 */
	private $mapping = array(
		'x'     => array(),
		'hover' => array(),
	);

	/**
	 * Construct will set all values and generate the date for response
	 */
	public function __construct() {
		parent::__construct();

		$this->set_date_field();

		$this->generate();
	}

	/**
	 * Putting $this->data and $this->mapping and give them back as a json encoded string
	 *
	 * @return string
	 */
	public function get_json() {
		$return = array(
			'data'    => $this->data,
			'mapping' => $this->mapping,
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

		foreach ( $google_data['value'] as $unix_timestamp => $value ) {
			if ( $this->is_date_in_period( $unix_timestamp ) ) {
				$return[ $unix_timestamp ] = $value;
			}
		}

		return $return;
	}

	/**
	 * Which field should be taken from timestamp. Most cases J will be good
	 */
	private function set_date_field() {
		switch ( $this->period ) {
			default:
			case 'lastweek' :
			case 'lastmonth' :
				$date_field = 'j';
				break;
		}

		$this->date_field = $date_field;
	}

	/**
	 * Generate the data for the frontend based on the $google_data
	 */
	private function generate() {
		$google_data = $this->get_google_data();

		foreach ( $google_data as $timestamp => $value ) {
			$timestamp = esc_html( $timestamp );

			$this->add_data( $value );
			$this->add_x_mapping( $timestamp );
			$this->add_hover_mapping( $timestamp, $value );
		}

		$this->mapping['x'] = array_filter( $this->mapping['x'] );
	}

	/**
	 * Adding value to data property
	 *
	 * x is position on x-axis, always starting from 0
	 * y is the value of that point.
	 *
	 * @param integer $value
	 */
	private function add_data( $value ) {
		static $current_x = 0;

		$this->data[] = array(
			'x' => $current_x,
			'y' => $value,
		);

		$current_x ++;
	}

	/**
	 * Add date field to the x-mapping
	 *
	 * Key will be auto numbered by PHP, starting with 0, the key will always point to the the x in x-axis
	 * The value will be always the value that should be displayed.
	 *
	 * @param integer $timestamp
	 */
	private function add_x_mapping( $timestamp ) {

		$is_monday            = ( 'Mon' === date( 'D', $timestamp ) );
		$this->mapping['x'][] = ( $is_monday ) ? date( $this->date_field . ' M', $timestamp ) : null;
	}

	/**
	 * Add date field to the hover-mapping
	 *
	 * @param integer $timestamp
	 * @param integer $value
	 */
	private function add_hover_mapping( $timestamp, $value ) {
		$this->mapping['hover'][] = esc_html( date_i18n( 'l ' . $this->date_field . ' M', $timestamp ) . ': ' . number_format_i18n( $value, 0 ) );
	}

}

