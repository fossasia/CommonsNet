<?php
/**
 * @package GoogleAnalytics\Includes
 */

/**
 * Utilities class.
 */
class Yoast_GA_Utils {

	/**
	 * Check if WordPress SEO or WordPress SEO Premium is active
	 *
	 * @return bool
	 */
	public static function wp_seo_active() {
		$wp_seo_active = false;

		// Makes sure is_plugin_active is available when called from front end
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
			$wp_seo_active = true;
		}

		return $wp_seo_active;
	}

	/**
	 * Calculate the date difference, return the amount of hours between the two dates
	 *
	 * @param integer $last_run datetime
	 * @param integer $now      datetime
	 *
	 * @return int
	 */
	public static function hours_between( $last_run, $now ) {
		$seconds = max( ( $now - $last_run ), 1 );
		$hours   = ( $seconds / 3600 );

		return floor( $hours );
	}

}
