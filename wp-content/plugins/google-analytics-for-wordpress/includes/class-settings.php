<?php
/**
 * @package GoogleAnalytics\Includes
 */

/**
 * Settings class.
 */
class Yoast_GA_Settings {

	/**
	 * Saving instance of it's own in this static var
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Store the options class instance
	 *
	 * @var mixed|void
	 */
	private $options_class;

	/**
	 * @var array $options The main GA options
	 */
	private $options;

	/**
	 * Set the options of Google Analytics
	 */
	protected function __construct() {
		$this->options_class = Yoast_GA_Options::instance();
		$this->options       = $this->options_class->get_options();
	}

	/**
	 * Getting instance of this object. If instance doesn't exists it will be created.
	 *
	 * @return object|Yoast_GA_Settings
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Yoast_GA_Settings();
		}

		return self::$instance;
	}

	/**
	 * Return the Dashboards disabled bool
	 *
	 * @return bool
	 */
	public function dashboards_disabled() {
		return $this->options_class->option_value_to_bool( 'dashboards_disabled' );
	}

}