<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * Dashboards graph inherited class.
 */
class Yoast_GA_Dashboards_Graph extends Yoast_GA_Dashboards_Driver {

	/**
	 * @var string The type of dashboard wherefore this object is created
	 */
	protected $dashboard_type = 'graph';

	/**
	 * @var string The hook for ajax, when this is called the hook will be executed
	 */
	protected $ajax_hook = 'wp_ajax_yoast_dashboard_graphdata';

	/**
	 * The object that handles the response and generates the content for dashboard
	 *
	 * @return Yoast_GA_Dashboards_Graph_Generate
	 */
	protected function get_dashboard_generate_object() {
		return new Yoast_GA_Dashboards_Graph_Generate();
	}

}
