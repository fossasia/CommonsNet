<?php
/**
 * @package GoogleAnalytics
 * @subpackage Main
 */

/**
 * Plugin Name: Google Analytics by MonsterInsights
 * Plugin URI: https://www.monsterinsights.com/pricing/#utm_source=wordpress&utm_medium=plugin&utm_campaign=wpgaplugin&utm_content=v504
 * Description: This plugin makes it simple to add Google Analytics to your WordPress site, adding lots of features, e.g. error page, search result and automatic outgoing links and download tracking.
 * Author: MonsterInsights
 * Version: 5.5
 * Requires at least: 3.9
 * Author URI: https://www.monsterinsights.com/
 * License: GPL v3
 * Text Domain: google-analytics-for-wordpress
 * Domain Path: /languages
 *
 * Google Analytics for WordPress
 * Copyright (C) 2008-2016, MonsterInsights, support@monsterinsights.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// This plugin was originally based on Rich Boakes' Analytics plugin: http://boakes.org/analytics, but has since been rewritten and refactored multiple times.

define( 'GAWP_VERSION', '5.5' );

define( 'GAWP_FILE', __FILE__ );

define( 'GAWP_PATH', plugin_basename( __FILE__ ) );

define( 'GAWP_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

function monsterinsights_needs_manual_update_check(){
	require_once 'includes/ecommerce-addon-license-fix.php';
}
add_action( 'plugins_loaded', 'monsterinsights_needs_manual_update_check');

function welcome_to_monsterinsights_admin_notice() {
	$user = get_current_user_id();
	if ( !current_user_can('manage_options' ) ) {
		return;
	}
	if ( ! get_option( 'monsterinsights_welcome_' . $user, false ) ) { ?>
		<div class="updated notice is-dismissible">
			<p><?php echo sprintf(__( 'Google Analytics By Yoast is now MonsterInsights. Read about this transition %s here %s', 'google-analytics-for-wordpress'), '<a href="https://monsterinsights.com/welcome-to-monsterinsights">', '</a>' ); ?></p>
		</div>
	<?php 
	update_option( 'monsterinsights_welcome_' . $user, true );
	}

}
add_action( 'admin_notices', 'welcome_to_monsterinsights_admin_notice' );

if ( file_exists( dirname( GAWP_FILE ) . '/vendor/autoload_52.php' ) ) {
	require dirname( GAWP_FILE ) . '/vendor/autoload_52.php';
}

// Only require the needed classes
if ( is_admin() ) {
	global $yoast_ga_admin;
	$yoast_ga_admin = new Yoast_GA_Admin;

}
else {
	global $yoast_ga_frontend;
	$yoast_ga_frontend = new Yoast_GA_Frontend;
}

/* ***************************** BOOTSTRAP / HOOK INTO WP *************************** */
$spl_autoload_exists = function_exists( 'spl_autoload_register' );
$filter_input_exists = function_exists( 'filter_input' );
if ( ! $spl_autoload_exists ) {
	add_action( 'admin_init', 'yoast_wpseo_self_deactivate_spl', 1 );
}
if ( ! $filter_input_exists ) {
	add_action( 'admin_init', 'yoast_wpseo_self_deactivate_filter_input', 1 );
}

/**
 * Throw an error if the PHP SPL extension is disabled (prevent white screens) and self-deactivate plugin
 *
 * @since 5.3.3
 */
function yoast_ga_self_deactivate_spl() {
	if ( is_admin() ) {
		yoast_ga_extenstion_notice(
			esc_html__( 'The Standard PHP Library (SPL) extension seem to be unavailable. Please ask your web host to enable it.', 'google-analytics-for-wordpress' )
		);
	}
}

/**
 * Throw an error if the filter extension is disabled (prevent white screens) and self-deactivate plugin
 *
 * @since 5.3.3
 */
function yoast_ga_self_deactivate_filter_input() {
	if ( is_admin() ) {
		yoast_ga_extenstion_notice(
			esc_html__( 'The (standard) PHP filter extension seem to be unavailable. Please ask your web host to enable it.', 'google-analytics-for-wordpress' )
		);
	}
}

/**
 * Show a notice in the admin
 *
 * @param string $message
 *
 * @since 5.3.3
 */
function yoast_ga_extenstion_notice( $message ) {
	add_action( 'admin_notices', create_function( $message, 'echo \'<div class="error"><p>\' . __( \'Activation failed:\', \'google-analytics-for-wordpress\' ) . \' \' . $message . \'</p></div>\';' ) );
	deactivate_plugins( plugin_basename( GAWP_FILE ) );
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

register_deactivation_hook( __FILE__, array( 'Yoast_GA_Admin', 'ga_deactivation_hook' ) );