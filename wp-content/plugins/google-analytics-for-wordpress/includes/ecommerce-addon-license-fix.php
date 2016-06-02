<?php
if ( ! class_exists( 'MI_Product_GA_eCommerce', false ) && class_exists( 'MI_Product' ) ){

	/**
	 * Class MI_Product_GA_eCommerce
	 *
	 * @since 3.0
	 */
	class MI_Product_GA_eCommerce extends MI_Product {

		/**
		 * Contains the license manager object
		 *
		 * @var object MI_Plugin_License_Manager
		 */
		protected $license_manager;


		public function __construct() {
			parent::__construct(
				'https://www.monsterinsights.com',
				'eCommerce Addon',
				plugin_basename( Yoast_GA_eCommerce_Tracking::PLUGIN_FILE ),
				Yoast_GA_eCommerce_Tracking::VERSION,
				'https://www.monsterinsights.com/pricing/',
				'admin.php?page=yst_ga_extensions#top#licenses',
				'yoast-ga-ecommerce',
				'MonsterInsights'
			);

			$this->setup_license_manager();
		}

		/**
		 * Setting up the license manager
		 *
		 * @since 3.0
		 */
		protected function setup_license_manager() {

			$license_manager = new MI_Plugin_License_Manager( $this );
			$license_manager->setup_hooks();

			add_filter( 'yst_ga_extension_status', array( $this, 'filter_extension_is_active' ), 10, 1 );
			add_action( 'yst_ga_show_license_form', array( $this, 'action_show_license_form' ) );

			$this->license_manager = $license_manager;
		}

		/**
		 * If extension is active, it should be check if its license is valid
		 *
		 * @since 3.0
		 *
		 * @param $extensions
		 *
		 * @return mixed
		 */
		public function filter_extension_is_active( $extensions ) {
			if ( $this->license_manager->license_is_valid() ) {
				$extensions['ecommerce']->status = 'active';
			} else {
				$extensions['ecommerce']->status = 'inactive';
			}

			return $extensions;
		}

		/**
		 * This method will echo the license form for the extension
		 *
		 * @since 3.0
		 */
		public function action_show_license_form() {
			echo $this->license_manager->show_license_form( false );
		}

	}
	// Setting up the license manager
	if ( defined( 'GAWP_ECOMMERCE_PATH' ) && defined( 'Yoast_GA_eCommerce_Tracking::VERSION' ) && Yoast_GA_eCommerce_Tracking::VERSION === '5.4.8' ) {
		new MI_Product_GA_eCommerce();
	}
}
