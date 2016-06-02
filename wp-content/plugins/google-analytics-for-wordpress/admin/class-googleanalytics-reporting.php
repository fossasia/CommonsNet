<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Class Yoast_Googleanalytics_Reporting
 */
class Yoast_Googleanalytics_Reporting {

	/**
	 * Store this instance
	 *
	 * @var null
	 */
	private static $instance = null;

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
	 * Format a response
	 *
	 * @param array  $raw_data
	 * @param string $store_as
	 * @param string $start_date
	 * @param string $end_date
	 *
	 * @return array
	 */
	public function parse_response( $raw_data, $store_as, $start_date, $end_date ) {
		$data = array();

		if ( $store_as == 'datelist' ) {
			$data_tmp = $this->date_range( strtotime( $start_date ), strtotime( $end_date ) );
			$data     = array_keys( $data_tmp );
		}

		if ( isset( $raw_data['body']['rows'] ) && is_array( $raw_data['body']['rows'] ) ) {
			foreach ( $raw_data['body']['rows'] as $key => $item ) {
				if ( $store_as == 'datelist' ) {
					$data[ (int) $this->format_ga_date( $item[0] ) ] = $this->parse_row( $item );
				}
				else {
					$data[] = $this->parse_data_row( $item );
				}
			}
		}

		if ( $store_as == 'datelist' ) {
			$data = $this->check_validity_data( $data );
		}

		return $data;
	}

	/**
	 * Check the key on valid unix timestamps and remove invalid keys
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function check_validity_data( $data = array() ) {
		foreach ( $data as $key => $value ) {
			if ( strlen( $key ) <= 5 ) {
				unset( $data[ $key ] );
			}
		}

		return $data;
	}

	/**
	 * Format the GA date value
	 *
	 * @param string $date
	 *
	 * @return int
	 */
	private function format_ga_date( $date ) {
		$year  = substr( $date, 0, 4 );
		$month = substr( $date, 4, 2 );
		$day   = substr( $date, 6, 2 );

		return strtotime( $year . '-' . $month . '-' . $day );
	}

	/**
	 * Parse a row and return an array with the correct data rows
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	private function parse_row( $item ) {
		if ( isset( $item[2] ) ) {
			return array(
				'date'  => (int) $this->format_ga_date( $item[0] ),
				'value' => (string) $item[1],
				'total' => (int) $item[2],
			);
		}

		return (int) $item[1];
	}

	/**
	 * Parse a row for the list storage type
	 *
	 * @param array $item
	 *
	 * @return array
	 */
	private function parse_data_row( $item ) {
		return array(
			'name'  => (string) $item[0],
			'value' => (int) $item[1],
		);
	}

	/**
	 * Calculate the date range between 2 dates
	 *
	 * @param string $current
	 * @param string $last
	 * @param string $step
	 * @param string $format
	 *
	 * @return array
	 */
	private function date_range( $current, $last, $step = '+1 day', $format = 'Y-m-d' ) {
		$dates   = array();

		while ( $current <= $last ) {
			$dates[] = date( $format, $current );
			$current = strtotime( $step, $current );
		}

		return $dates;
	}

}
