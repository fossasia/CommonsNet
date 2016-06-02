<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * This class is used to store and get the data of the dashboards. The data is aggregated by
 * the class-admin-dashboards-collector.php and saved with Yoast_GA_Dashboards_Data::set().
 *
 * You can retrieve the data by using the function Yoast_GA_Dashboards_Data::get() in this
 * class.
 */
class Yoast_GA_Dashboards_Data {

	/**
	 * Get a data object
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get( $type ) {
		$option = get_option( 'yst_ga_' . $type );

		if ( false === $option ) {
			// Option does not exist, abort
			return array();
		}

		// @TODO loop through transient to get the correct date range

		return $option;
	}

	/**
	 * Save a data object
	 *
	 * @param string $type
	 * @param array  $value
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $store_as
	 *
	 * @return bool
	 */
	public static function set( $type, $value, $start_date, $end_date, $store_as ) {
		$store = array(
			'store_as'   => $store_as,
			'type'       => $type,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'value'      => $value,
		);

		return update_option( 'yst_ga_' . $type, $store );
	}

	/**
	 * Reset an option of the GA dashboards storage engine
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public static function reset( $type ) {
		return update_option( 'yst_ga_' . $type, array() );
	}
}