<?php
//To prevent conflicts, only one version of the plugin can be activated at any given time.
if ( defined('BLC_ACTIVE') ){
	trigger_error(
		'Another version of Broken Link Checker is already active. Please deactivate it before activating this one.',
		E_USER_ERROR
	);
} else {
	
define('BLC_ACTIVE', true);

//Fail fast if the WP version is unsupported. The $wp_version variable may be obfuscated by other
//plugins, so use function detection to determine the version. get_post_stati was introduced in WP 3.0.0
if ( !function_exists('get_post_stati') ){
	trigger_error(
		'This version of Broken Link Checker requires WordPress 3.0 or later!',
		E_USER_ERROR
	);
}

/***********************************************
				Debugging stuff
************************************************/

//define('BLC_DEBUG', true);



/***********************************************
				Constants
************************************************/

/*
For performance, some internal APIs used for retrieving multiple links, instances or containers 
can take an optional "$purpose" argument. Those APIs will try to use this argument to pre-load 
any DB data required for the specified purpose ahead of time. 

For example, if you're loading a bunch of link containers for the purposes of parsing them and 
thus set $purpose to BLC_FOR_PARSING, the relevant container managers will (if applicable) precache
the parse-able fields in each returned container object. Still, setting $purpose to any particular 
value does not *guarantee* any data will be preloaded - it's only a suggestion that it should.

The currently supported values for the $purpose argument are : 
*/ 	
define('BLC_FOR_EDITING', 'edit');
define('BLC_FOR_PARSING', 'parse');
define('BLC_FOR_DISPLAY', 'display');

define('BLC_DATABASE_VERSION', 9);

/***********************************************
				Configuration
************************************************/

//Load and initialize the plugin's configuration
require BLC_DIRECTORY . '/includes/config-manager.php';

global $blc_config_manager;
$blc_config_manager = new blcConfigurationManager(
	//Save the plugin's configuration into this DB option
	'wsblc_options', 
	//Initialize default settings
	array(
        'max_execution_time' => 7*60, 	//(in seconds) How long the worker instance may run, at most.
        'check_threshold' => 72, 		//(in hours) Check each link every 72 hours.
        
        'recheck_count' => 3, 			//How many times a broken link should be re-checked. 
		'recheck_threshold' => 30*60,	//(in seconds) Re-check broken links after 30 minutes.   
		
		'run_in_dashboard' => true,		//Run the link checker algo. continuously while the Dashboard is open.
		'run_via_cron' => true,			//Run it hourly via WordPress pseudo-cron.
        
        'mark_broken_links' => true, 	//Whether to add the broken_link class to broken links in posts.
        'broken_link_css' => ".broken_link, a.broken_link {\n\ttext-decoration: line-through;\n}",
        'nofollow_broken_links' => false, //Whether to add rel="nofollow" to broken links in posts.
        
        'mark_removed_links' => false, 	//Whether to add the removed_link class when un-linking a link.
        'removed_link_css' => ".removed_link, a.removed_link {\n\ttext-decoration: line-through;\n}",
        
        'exclusion_list' => array(), 	//Links that contain a substring listed in this array won't be checked.
		
		'send_email_notifications' => true, //Whether to send the admin email notifications about broken links
		'send_authors_email_notifications' => false, //Whether to send post authors notifications about broken links in their posts.
		'notification_email_address' => '', //If set, send email notifications to this address instead of the admin.
		'notification_schedule' => 'daily', //How often (at most) notifications will be sent. Possible values : 'daily', 'weekly'
		'last_notification_sent' => 0,		//When the last email notification was sent (Unix timestamp)

		'suggestions_enabled' => true,  //Whether to suggest alternative URLs for broken links.

		'warnings_enabled' => true,		//Try to automatically detect temporary problems and false positives,
										//and report them as "Warnings" instead of broken links.

		'server_load_limit' => null,	//Stop parsing stuff & checking links if the 1-minute load average
										//goes over this value. Only works on Linux servers. 0 = no limit.
		'enable_load_limit' => true,	//Enable/disable load monitoring. 
		
        'custom_fields' => array(),		//List of custom fields that can contain URLs and should be checked.
        'enabled_post_statuses' => array('publish'), //Only check posts that match one of these statuses
        
        'autoexpand_widget' => true, 	//Autoexpand the Dashboard widget if broken links are detected
		'dashboard_widget_capability' => 'edit_others_posts', //Only display the widget to users who have this capability
		'show_link_count_bubble' => true, //Display a notification bubble in the menu when broken links are found
		
		'table_layout' => 'flexible',   //The layout of the link table. Possible values : 'classic', 'flexible'
		'table_compact' => true,   		//Compact table mode on/off 
		'table_visible_columns' => array('new-url', 'status', 'used-in', 'new-link-text',), 
		'table_links_per_page' => 30,
		'table_color_code_status' => true, //Color-code link status text
		
		'need_resynch' => false,  		//[Internal flag] True if there are unparsed items.
		'current_db_version' => 0,		//The currently set-up version of the plugin's tables
		
		'timeout' => 30,				//(in seconds) Links that take longer than this to respond will be treated as broken.
		
		'highlight_permanent_failures' => false,//Highlight links that have appear to be permanently broken (in Tools -> Broken Links).
		'failure_duration_threshold' => 3, 		//(days) Assume a link is permanently broken if it still hasn't 
												//recovered after this many days.
		'logging_enabled' => false,
		'log_file' => '',
		'custom_log_file_enabled' => false,

		'installation_complete' => false,
		'installation_flag_cleared_on' => 0,
		'installation_flag_set_on'   => 0,

		'user_has_donated' => false,   //Whether the user has donated to the plugin.
		'donation_flag_fixed' => false,

		                              //Visible link actions.
		'show_link_actions' => array('blc-deredirect-action' => false),
   )
);

/***********************************************
				Logging
************************************************/

include BLC_DIRECTORY . '/includes/logger.php';

global $blclog;
if ($blc_config_manager->get('logging_enabled', false) && is_writable($blc_config_manager->get('log_file'))) {
	$blclog = new blcFileLogger($blc_config_manager->get('log_file'));
} else {
	$blclog = new blcDummyLogger;
}

/*
if ( defined('BLC_DEBUG') && constant('BLC_DEBUG') ){
	//Load FirePHP for debug logging
	if ( !class_exists('FB') && file_exists(BLC_DIRECTORY . '/FirePHPCore/fb.php4') ) {
		require_once BLC_DIRECTORY . '/FirePHPCore/fb.php4';
	}
	//FB::setEnabled(false);
}
//to comment out all calls : (^[^\/]*)(FB::)  ->  $1\/\/$2
//to uncomment : \/\/(\s*FB::)  ->   $1
//*/

/***********************************************
				Global functions
************************************************/

/**
 * Get the configuration object used by Broken Link Checker.
 *
 * @return blcConfigurationManager
 */
function blc_get_configuration(){
	return $GLOBALS['blc_config_manager'];
}

/**
 * Notify the link checker that there are unsynched items 
 * that might contain links (e.g. a new or edited post).
 *
 * @return void
 */
function blc_got_unsynched_items(){
	$conf = blc_get_configuration();
	
	if ( !$conf->options['need_resynch'] ){
		$conf->options['need_resynch'] = true;
		$conf->save_options();
	}
}

/**
 * (Re)create synchronization records for all containers and mark them all as unparsed.
 *
 * @param bool $forced If true, the plugin will recreate all synch. records from scratch.
 * @return void
 */
function blc_resynch( $forced = false ){
	global $wpdb, $blclog; /* @var wpdb $wpdb */
	
	if ( $forced ){
		$blclog->info('... Forced resynchronization initiated');
		
		//Drop all synchronization records
		$wpdb->query("TRUNCATE {$wpdb->prefix}blc_synch");
	} else {
		$blclog->info('... Resynchronization initiated');
	}
	
	//Remove invalid DB entries
	blc_cleanup_database();
	
	//(Re)create and update synch. records for all container types.
	$blclog->info('... (Re)creating container records');
	blcContainerHelper::resynch($forced);
	
	$blclog->info('... Setting resync. flags');
	blc_got_unsynched_items();
	
	//All done.
	$blclog->info('Database resynchronization complete.');
}

/**
 * Delete synch. records, instances and links that refer to missing or invalid items.
 * 
 * @return void
 */
function blc_cleanup_database(){
	global $blclog;
	
	//Delete synch. records for container types that don't exist
	$blclog->info('... Deleting invalid container records');
	blcContainerHelper::cleanup_containers();
	
	//Delete invalid instances
	$blclog->info('... Deleting invalid link instances');
	blc_cleanup_instances();
	
	//Delete orphaned links
	$blclog->info('... Deleting orphaned links');
	blc_cleanup_links();
}

/***********************************************
				Utility hooks
************************************************/

/**
 * Add a weekly Cron schedule for email notifications
 * and a bimonthly schedule for database maintenance.
 *
 * @param array $schedules Existing Cron schedules.
 * @return array
 */
function blc_cron_schedules($schedules){
	if ( !isset($schedules['weekly']) ){
		$schedules['weekly'] = array(
	 		'interval' => 604800, //7 days
	 		'display' => __('Once Weekly')
	 	);
 	}
 	if ( !isset($schedules['bimonthly']) ){
		$schedules['bimonthly'] = array(
	 		'interval' => 15*24*2600, //15 days
	 		'display' => __('Twice a Month')
	 	);
 	}
 	
	return $schedules;
}
add_filter('cron_schedules', 'blc_cron_schedules');

/***********************************************
				Main functionality
************************************************/

//Execute the installation/upgrade script when the plugin is activated.
function blc_activation_hook(){
	require BLC_DIRECTORY . '/includes/activation.php';
}
register_activation_hook(BLC_PLUGIN_FILE, 'blc_activation_hook');

//Load the plugin if installed successfully
if ( $blc_config_manager->options['installation_complete'] ){
	function blc_init(){
		global $blc_module_manager, $blc_config_manager, $ws_link_checker;
		
		static $init_done = false;
		if ( $init_done ){
			return;
		}
		$init_done = true;
		
		//Ensure the database is up to date
		if ($blc_config_manager->options['current_db_version'] != BLC_DATABASE_VERSION) {
			require_once BLC_DIRECTORY . '/includes/admin/db-upgrade.php';
			blcDatabaseUpgrader::upgrade_database(); //Also updates the DB ver. in options['current_db_version'].
		}
		
		//Load the base classes and utilities
		require_once BLC_DIRECTORY . '/includes/links.php';
		require_once BLC_DIRECTORY . '/includes/link-query.php';
		require_once BLC_DIRECTORY . '/includes/instances.php';
		require_once BLC_DIRECTORY . '/includes/utility-class.php';

		//Load the module subsystem
		require_once BLC_DIRECTORY . '/includes/modules.php';
		
		//Load the modules that want to be executed in all contexts
		$blc_module_manager->load_modules();
		
		if ( is_admin() || defined('DOING_CRON') ){
			
			//It's an admin-side or Cron request. Load the core.
			require_once BLC_DIRECTORY . '/core/core.php';
			$ws_link_checker = new wsBrokenLinkChecker( BLC_PLUGIN_FILE, $blc_config_manager );
			
		} else {
			
			//This is user-side request, so we don't need to load the core.
			//We might need to inject the CSS for removed links, though.
			if ( $blc_config_manager->options['mark_removed_links'] && !empty($blc_config_manager->options['removed_link_css']) ){
				function blc_print_removed_link_css(){
					global $blc_config_manager;
					echo '<style type="text/css">',$blc_config_manager->options['removed_link_css'],'</style>';
				}
				add_action('wp_head', 'blc_print_removed_link_css');
			}
		}
	}
	add_action('init', 'blc_init', 2000);	
} else {
	//Display installation errors (if any) on the Dashboard.
	function blc_print_installation_errors(){
		global $blc_config_manager, $wpdb; /** @var wpdb $wpdb */
        if ( $blc_config_manager->options['installation_complete'] ) {
            return;
        }

		$messages = array(
			'<strong>' . __('Broken Link Checker installation failed. Try deactivating and then reactivating the plugin.', 'broken-link-checker') . '</strong>',
		);

		if ( is_multisite() && is_plugin_active_for_network(plugin_basename(BLC_PLUGIN_FILE)) ) {
			$messages[] = __('Please activate the plugin separately on each site. Network activation is not supported.', 'broken-link-checker');
			$messages[] = '';
		}

		if ( ! $blc_config_manager->db_option_loaded ) {
			$messages[] = sprintf(
				'<strong>Failed to load plugin settings from the "%s" option.</strong>',
				$blc_config_manager->option_name
			);
			$messages[] = '';

			$serialized_config = $wpdb->get_var(
				sprintf(
					'SELECT option_value FROM `%s` WHERE option_name = "%s"',
					$wpdb->options,
					$blc_config_manager->option_name
				)
			);

			if ( $serialized_config === null ) {
				$messages[] = "Option doesn't exist in the {$wpdb->options} table.";
			} else {
				$messages[] = "Option exists in the {$wpdb->options} table and has the following value:";
				$messages[] = '';
				$messages[] = '<textarea cols="120" rows="20">' . htmlentities($serialized_config) . '</textarea>';
			}

		} else {
			$logger = new blcCachedOptionLogger('blc_installation_log');
			$messages = array_merge(
				$messages,
				array(
					'installation_complete = ' . (isset($blc_config_manager->options['installation_complete']) ? intval($blc_config_manager->options['installation_complete']) : 'no value'),
					'installation_flag_cleared_on = ' . $blc_config_manager->options['installation_flag_cleared_on'],
					'installation_flag_set_on = ' . $blc_config_manager->options['installation_flag_set_on'],
					'',
					'<em>Installation log follows :</em>'
				),
				$logger->get_messages()
			);
		}

		echo "<div class='error'><p>", implode("<br>\n", $messages), "</p></div>";
	}
	add_action('admin_notices', 'blc_print_installation_errors');
}

}