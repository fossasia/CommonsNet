<?php
/**
 * @package GoogleAnalytics\Admin
 */

/**
 * This class is for the backend, extendable for all child classes
 */
class Yoast_GA_Admin_Menu {

	/**
	 * @var object $target_object The property used for storing target object (class admin)
	 */
	private $target_object;

	/**
	 * @var boolean $dashboard_disabled The dashboards disabled bool
	 */
	private $dashboards_disabled;

	/**
	 * The parent slug for the submenu items based on if the dashboards are disabled or not.
	 *
	 * @var string
	 */
	private $parent_slug;

	/**
	 * Setting the target_object and adding actions
	 *
	 * @param object $target_object
	 */
	public function __construct( $target_object ) {

		$this->target_object = $target_object;

		add_action( 'admin_menu', array( $this, 'create_admin_menu' ), 10 );
		add_action('admin_head', array( $this, 'mi_add_styles_for_menu' ) );

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active_for_network( GAWP_PATH ) ) {
			add_action( 'network_admin_menu', array( $this, 'create_admin_menu' ), 5 );
		}

		$this->dashboards_disabled = Yoast_GA_Settings::get_instance()->dashboards_disabled();
		$this->parent_slug         = ( ( $this->dashboards_disabled ) ? 'yst_ga_settings' : 'yst_ga_dashboard' );
	}

	public function mi_add_styles_for_menu() {
		?>
		<style type="text/css">
			@font-face {
				font-family: 'MonsterInsights';
				src: url('data:application/octet-stream;base64,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') format('woff'),
			   url('data:application/octet-stream;base64,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') format('truetype');
				font-weight: normal;
				font-style: normal;
			}

			#toplevel_page_yst_ga_dashboard .wp-menu-image:before,
			.yst_ga_dashboard-menu-icon:before {
			
				font-family: "MonsterInsights" !important;
				content: '\61';
				font-style: normal !important;
				font-weight: normal !important;
				font-variant: normal !important;
				text-transform: none !important;
				speak: none;
				line-height: 1;
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				color: #fff;
			}
			#toplevel_page_yst_ga_dashboard .wp-menu-image:before {
				font-size: 1.5em;
				padding-top: 4px;
				padding-bottom: 2px;
				font-size: 22px;
			}
		</style>
		<?php
	}

	/**
	 * Create the admin menu
	 */
	public function create_admin_menu() {
		$menu_name = is_network_admin() ? 'extensions' : 'dashboard';

		if ( $this->dashboards_disabled ) {
			$menu_name = 'settings';
		}

		// Add main page
		add_menu_page(
			__( 'Google Analytics by MonsterInsights:', 'google-analytics-for-wordpress' ) . ' ' . __( 'General settings', 'google-analytics-for-wordpress' ), __( 'Insights', 'google-analytics-for-wordpress' ), 'manage_options', 'yst_ga_' . $menu_name,
			array(
				$this->target_object,
				'load_page',
			),
			'',
			'100.00013467543'
		);


		$this->add_submenu_pages();
	}

	/**
	 * Prepares an array that can be used to add a submenu page to the Google Analytics for Wordpress menu
	 *
	 * @param string $submenu_name
	 * @param string $submenu_slug
	 * @param string $font_color
	 *
	 * @return array
	 */
	private function prepare_submenu_page( $submenu_name, $submenu_slug, $font_color = '' ) {
		return array(
			'parent_slug'      => $this->parent_slug,
			'page_title'       => __( 'Google Analytics by MonsterInsights:', 'google-analytics-for-wordpress' ) . ' ' . $submenu_name,
			'menu_title'       => $this->parse_menu_title( $submenu_name, $font_color ),
			'capability'       => 'manage_options',
			'menu_slug'        => 'yst_ga_' . $submenu_slug,
			'submenu_function' => array( $this->target_object, 'load_page' ),
		);
	}

	/**
	 * Parsing the menutitle
	 *
	 * @param string $menu_title
	 * @param string $font_color
	 *
	 * @return string
	 */
	private function parse_menu_title( $menu_title, $font_color ) {
		if ( ! empty( $font_color ) ) {
			$menu_title = '<span style="color:' . $font_color . '">' . $menu_title . '</span>';
		}

		return $menu_title;
	}

	/**
	 * Adds a submenu page to the Google Analytics for WordPress menu
	 *
	 * @param array $submenu_page
	 */
	private function add_submenu_page( $submenu_page ) {
		$page         = add_submenu_page( $submenu_page['parent_slug'], $submenu_page['page_title'], $submenu_page['menu_title'], $submenu_page['capability'], $submenu_page['menu_slug'], $submenu_page['submenu_function'] );
		$is_dashboard = ( 'yst_ga_dashboard' === $submenu_page['menu_slug'] );
		$this->add_assets( $page, $is_dashboard );
	}

	/**
	 * Adding stylesheets and based on $is_not_dashboard maybe some more styles and scripts.
	 *
	 * @param string  $page
	 * @param boolean $is_dashboard
	 */
	private function add_assets( $page, $is_dashboard ) {
		add_action( 'admin_print_styles-' . $page, array( 'Yoast_GA_Admin_Assets', 'enqueue_styles' ) );
		add_action( 'admin_print_styles-' . $page, array( 'Yoast_GA_Admin_Assets', 'enqueue_settings_styles' ) );
		add_action( 'admin_print_scripts-' . $page, array( 'Yoast_GA_Admin_Assets', 'enqueue_scripts' ) );
		if ( ! $is_dashboard && filter_input( INPUT_GET, 'page' ) === 'yst_ga_dashboard' ) {
			Yoast_GA_Admin_Assets::enqueue_dashboard_assets();
		}
	}

	/**
	 * Prepares and adds submenu pages to the Google Analytics for Wordpress menu:
	 * - Dashboard
	 * - Settings
	 * - Extensions
	 *
	 * @return void
	 */
	private function add_submenu_pages() {
		foreach ( $this->get_submenu_types() as $submenu ) {
			if ( isset( $submenu['color'] ) ) {
				$submenu_page = $this->prepare_submenu_page( $submenu['label'], $submenu['slug'], $submenu['color'] );
			}
			else {
				$submenu_page = $this->prepare_submenu_page( $submenu['label'], $submenu['slug'] );
			}
			$this->add_submenu_page( $submenu_page );
		}
	}

	/**
	 * Determine which submenu types should be added as a submenu page.
	 *
	 * Dashboard can be disables by user
	 *
	 * Dashboard and settings are disables in network admin
	 *
	 * @return array
	 */
	private function get_submenu_types() {
		/**
		 * Array structure:
		 *
		 * array(
		 *   $submenu_name => array(
		 *        'color' => $font_color,
		 *        'label' => __( 'text-label', 'google-analytics-for-wordpress' ),
		 * 		  'slug'  => $menu_slug,
		 *        ),
		 *   ..,
		 * )
		 *
		 * $font_color can be left empty.
		 *
		 */
		$submenu_types = array();

		if ( ! is_network_admin() ) {

			if ( ! $this->dashboards_disabled ) {
				$submenu_types['dashboard'] = array(
					'label' => __( 'Dashboard', 'google-analytics-for-wordpress' ),
					'slug'  => 'dashboard',
				);
			}

			$submenu_types['settings'] = array(
				'label' => __( 'Settings', 'google-analytics-for-wordpress' ),
				'slug'  => 'settings',
			);
		}

		$submenu_types['extensions'] = array(
			'color' => '#f18500',
			'label' => __( 'Extensions', 'google-analytics-for-wordpress' ),
			'slug'  => 'extensions',
		);

		return $submenu_types;
	}
}