<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Base abstract class.
 */
abstract class Yoast_GA_Dashboards_Driver_Generate {

	/**
	 * @var string - Which type of data should be loaded
	 */
	protected $graph_type;

	/**
	 * @var string - For which period should the data be shown
	 */
	protected $period;

	/**
	 * @var string - The end date
	 */
	protected $end_date;

	/**
	 * @var string - The start date
	 */
	protected $start_date;

	/**
	 * Construct will set all values and generate the date for response
	 */
	public function __construct() {
		$this->set_graph_type();
		$this->set_period();
		$this->set_end_date();
		$this->set_start_date();
	}

	/**
	 * Getting graph_id from post and strip HTML-prefix graph- to get the type
	 */
	protected function set_graph_type() {
		$graph_id         = filter_input( INPUT_GET, 'graph_id' );
		$graph_type       = str_replace( array( 'graph-', 'table-' ), '', $graph_id );
		$this->graph_type = $graph_type;
	}

	/**
	 * Getting the period from post
	 */
	protected function set_period() {
		$this->period = filter_input( INPUT_GET, 'period' );
	}

	/**
	 * Setting the end date
	 */
	protected function set_end_date() {
		$this->end_date = time();
	}

	/**
	 * This method will set a start_date based on $this->period
	 *
	 * The values in dropdown, that will be mapped in strtotime
	 * See: http://php.net/manual/en/datetime.formats.relative.php
	 */
	protected function set_start_date() {

		switch ( $this->period ) {
			case 'lastweek' :
				$time = '-6 days';
				break;
			default:
			case 'lastmonth' :
				$time = '-1 month';
				break;
		}

		$start_date = strtotime( $time, $this->end_date );

		$this->start_date = $start_date;
	}

	/**
	 * Getting the saved Google data
	 *
	 * @return array
	 */
	protected function get_google_data() {

		$response = Yoast_GA_Dashboards_Data::get( $this->graph_type );

		if ( $response != array() && array_key_exists( 'body', $response['value'] ) ) {
			$return = $response['value']['body'];
		}
		else {
			$return = $response;
		}

		return $this->filter_google_data( $return );
	}

	/**
	 * Check if given timestamp is in given period
	 *
	 * @param integer $timestamp
	 *
	 * @return bool
	 */
	protected function is_date_in_period( $timestamp ) {
		return ( $timestamp >= $this->start_date && $timestamp <= $this->end_date );
	}

	/**
	 * Escape the data array before output
	 *
	 * @param array $data The data array that we need to check
	 *
	 * @return array|boolean The data array which is escaped
	 */
	protected function escape_strings_array( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					foreach ( $value as $subkey => $subvar ) {
						$data[ $key ][ $subkey ] = esc_html( $subvar );
					}
				}
				else {
					$data[ $key ] = esc_html( (string) $value );
				}
			}

			return $data;
		}

		return false;
	}

	/**
	 * Should always be available
	 *
	 * @return mixed
	 */
	abstract public function get_json();

}
