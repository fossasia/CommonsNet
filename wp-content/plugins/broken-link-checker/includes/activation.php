<?php

global $blclog, $blc_config_manager, $wpdb;
$queryCnt = $wpdb->num_queries;

//Completing the installation/upgrade is required for the plugin to work, so make sure 
//the script doesn't get aborted by (for example) the browser timing out.
set_time_limit(300);  //5 minutes should be plenty, anything more would probably indicate an infinite loop or a deadlock 
ignore_user_abort(true);

//Log installation progress to a DB option
$blclog = new blcCachedOptionLogger('blc_installation_log');
register_shutdown_function(array(&$blclog, 'save')); //Make sure the log is saved even if the plugin crashes

$blclog->clear();
$blclog->info( sprintf('Plugin activated at %s.', date_i18n('Y-m-d H:i:s')) );
$activation_start = microtime(true);

//Reset the "installation_complete" flag
$blc_config_manager->options['installation_complete'] = false;
$blc_config_manager->options['installation_flag_cleared_on'] = date('c') . ' (' . microtime(true) . ')';
//Note the time of the first installation (not very accurate, but still useful)
if ( empty($blc_config_manager->options['first_installation_timestamp']) ){
	$blc_config_manager->options['first_installation_timestamp'] = time();
}
$blc_config_manager->save_options();
$blclog->info('Installation/update begins.');

//Load the base classes and utilities
require_once BLC_DIRECTORY . '/includes/links.php';
require_once BLC_DIRECTORY . '/includes/link-query.php';
require_once BLC_DIRECTORY . '/includes/instances.php';
require_once BLC_DIRECTORY . '/includes/utility-class.php';

//Load the module subsystem
require_once BLC_DIRECTORY . '/includes/modules.php';
$moduleManager = blcModuleManager::getInstance();	
       
//If upgrading, activate/deactivate custom field and comment containers based on old ver. settings
if ( isset($blc_config_manager->options['check_comment_links']) ){
	if ( !$blc_config_manager->options['check_comment_links'] ){
		$moduleManager->deactivate('comment');
	}
	unset($blc_config_manager->options['check_comment_links']);
}
if ( empty($blc_config_manager->options['custom_fields']) ){
	$moduleManager->deactivate('custom_field');
}

//Prepare the database.
$blclog->info('Upgrading the database...');
$upgrade_start = microtime(true);
require_once BLC_DIRECTORY . '/includes/admin/db-upgrade.php';
blcDatabaseUpgrader::upgrade_database();
$blclog->info(sprintf('--- Total: %.3f seconds', microtime(true) - $upgrade_start));

//Remove invalid DB entries
$blclog->info('Cleaning up the database...');
$cleanup_start = microtime(true);
blc_cleanup_database();
$blclog->info(sprintf('--- Total: %.3f seconds', microtime(true) - $cleanup_start));

//Notify modules that the plugin has been activated. This will cause container
//modules to create and update synch. records for all new/modified posts and other items.
$blclog->info('Notifying modules...');
$notification_start = microtime(true);
$moduleManager->plugin_activated();
blc_got_unsynched_items();
$blclog->info(sprintf('--- Total: %.3f seconds', microtime(true) - $notification_start));

//Turn off load limiting if it's not available on this server.
$blclog->info('Updating server load limit settings...');
$load = blcUtility::get_server_load();
if ( empty($load) ){
	$blc_config_manager->options['enable_load_limit'] = false;
	$blclog->info('Disable load limit. Cannot retrieve current load average.');
} elseif ( $blc_config_manager->options['enable_load_limit'] && !isset($blc_config_manager->options['server_load_limit']) ) {
	$fifteen_minutes = floatval(end($load));
	$default_load_limit = round(max(min($fifteen_minutes * 2, $fifteen_minutes + 2), 4));
	$blc_config_manager->options['server_load_limit'] = $default_load_limit;

	$blclog->info(sprintf(
		'Set server load limit to %.2f. Current load average is %.2f',
		$default_load_limit,
		$fifteen_minutes
	));
}

//And optimize my DB tables, too (for good measure)
$blclog->info('Optimizing the database...');
$optimize_start = microtime(true);
blcUtility::optimize_database();
$blclog->info(sprintf('--- Total: %.3f seconds', microtime(true) - $optimize_start));

$blclog->info('Completing installation...');
$blc_config_manager->options['installation_complete'] = true;
$blc_config_manager->options['installation_flag_set_on'] = date('c') . ' (' . microtime(true) . ')';
if ( $blc_config_manager->save_options() ){
    $blclog->info('Configuration saved.');
} else {
    $blclog->error('Error saving plugin configuration!');
};

$blclog->info(sprintf(
	'Installation/update completed at %s with %d queries executed.', 
	date_i18n('Y-m-d H:i:s'),
	$wpdb->num_queries - $queryCnt
));
$blclog->info(sprintf('Total time: %.3f seconds', microtime(true) - $activation_start));
$blclog->save();

