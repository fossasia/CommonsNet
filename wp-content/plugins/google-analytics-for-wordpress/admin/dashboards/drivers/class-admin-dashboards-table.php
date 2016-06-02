<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Dashboards table inherited class.
 */
class Yoast_GA_Dashboards_Table extends Yoast_GA_Dashboards_Driver {

	/**
	 * @var string The type of dashboard wherefore this object is created
	 */
	protected $dashboard_type = 'table';

	/**
	 * @var string The hook for ajax, when this is called the hook will be executed
	 */
	protected $ajax_hook = 'wp_ajax_yoast_dashboard_tabledata';

	/**
	 * The object that handles the response and generates the content for dashboard
	 *
	 * @return Yoast_GA_Dashboards_Table_Generate
	 */
	protected function get_dashboard_generate_object() {
		return new Yoast_GA_Dashboards_Table_Generate();
	}

}
