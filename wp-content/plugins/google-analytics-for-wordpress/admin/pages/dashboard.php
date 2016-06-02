<?php
/**
 * @package GoogleAnalytics\Admin
 */

global $yoast_ga_admin;

$options_class = Yoast_GA_Options::instance();
$options       = $options_class->get_options();
$tracking_code = $options_class->get_tracking_code();

echo $yoast_ga_admin->content_head();
?>
	<h2 id="yoast_ga_title"><?php echo __( 'Google Analytics by MonsterInsights: ', 'google-analytics-for-wordpress' ) . __( 'Dashboard', 'google-analytics-for-wordpress' ); ?> <?php do_action( 'yst_ga_dashboard_title' ); ?></h2>

	<h2 class="nav-tab-wrapper" id="ga-tabs">
		<a class="nav-tab" id="general-tab" href="#top#general"><?php _e( 'Overview', 'google-analytics-for-wordpress' ); ?></a>
		<a class="nav-tab" id="dimensions-tab" href="#top#dimensions"><?php _e( 'Reports', 'google-analytics-for-wordpress' ); ?></a>
		<a class="nav-tab" id="customdimensions-tab" href="#top#customdimensions"><?php _e( 'Custom dimension reports', 'google-analytics-for-wordpress' ); ?></a>
		<a class="nav-tab" id="systeminfo-tab" href="#top#systeminfo"><?php _e( 'System Info', 'google-analytics-for-wordpress' ); ?></a>
	</h2>

	<script type="text/javascript">
		var yoast_ga_dashboard_nonce = '<?php echo wp_create_nonce( 'yoast-ga-dashboard-nonce' ); ?>';

		jQuery(function () {
			jQuery.each(
				jQuery('select[data-rel=toggle_dimensions]'),
				function (num, element) {
					dimension_switch(element);
				}
			);
		});
	</script>

	<div class="tabwrapper">
		<div id="general" class="wpseotab gatab active">
			<div class="yoast-graphs">
				<?php
				if ( $tracking_code !== '' ) {
					if ( empty( $options['analytics_profile'] ) ) {
						echo '<div class="ga-promote"><p>';
						echo sprintf(
							__( 'We need you to authenticate with Google Analytics to use this functionality. If you set your UA-code manually, this won\'t work. You can %sauthenticate your Google Analytics profile here%s to enable dashboards.', 'google-analytics-for-wordpress' ),
							'<a href=" ' . admin_url( 'admin.php?page=yst_ga_settings#top#general' ) . '">',
							'</a>'
						);
						echo '</p></div>';
					}
					else if ( ! Yoast_Google_Analytics::get_instance()->has_refresh_token() ) {
						echo '<div class="ga-promote"><p>';
						echo sprintf(
							__( 'Because we\'ve switched to a newer version of the Google Analytics API, you\'ll need to re-authenticate with Google Analytics. We\'re sorry for the inconvenience. You can %sre-authenticate your Google Analytics profile here%s.', 'google-analytics-for-wordpress' ),
							'<a href=" ' . admin_url( 'admin.php?page=yst_ga_settings#top#general' ) . '">',
							'</a>'
						);
						echo '</p></div>';
					}
					else {
						Yoast_GA_Dashboards_Display::get_instance()->display( 'general' );
					}
				}
				else {
					echo '<div class="ga-promote"><p>';
					echo sprintf(
						__( 'You have not yet finished setting up Google Analytics for Wordpress by MonsterInsights. Please %sadd your Analytics profile here%s to enable tracking.', 'google-analytics-for-wordpress' ),
						'<a href=" ' . admin_url( 'admin.php?page=yst_ga_settings#top#general' ) . '">',
						'</a>'
					);
					echo '</p></div>';
				}
				?>
			</div>
		</div>

		<div id="dimensions" class="wpseotab gatab">
			<?php

			if ( $tracking_code !== '' ) {
				if ( empty( $options['analytics_profile'] ) ) {
					echo '<div class="ga-promote"><p>';
					echo sprintf(
						__( 'We need you to authenticate with Google Analytics to use this functionality. If you set your UA-code manually, this won\'t work. You can %sauthenticate your Google Analytics profile here%s to enable dashboards.', 'google-analytics-for-wordpress' ),
						'<a href=" ' . admin_url( 'admin.php?page=yst_ga_settings#top#general' ) . '">',
						'</a>'
					);
					echo '</p></div>';
				}
				else if ( ! Yoast_Google_Analytics::get_instance()->has_refresh_token() ) {
					echo '<div class="ga-promote"><p>';
					echo sprintf(
						__( 'Because we\'ve switched to a newer version of the Google Analytics API, you\'ll need to re-authenticate with Google Analytics. We\'re sorry for the inconvenience. You can %sre-authenticate your Google Analytics profile here%s.', 'google-analytics-for-wordpress' ),
						'<a href=" ' . admin_url( 'admin.php?page=yst_ga_settings#top#general' ) . '">',
						'</a>'
					);
					echo '</p></div>';
				}
				else {
					?>
					<div class="ga-form ga-form-input">
						<label class="ga-form ga-form-checkbox-label ga-form-label-left"><?php echo __( 'Select a dimension', 'google-analytics-for-wordpress' ); ?></label>
					</div>
					<select data-rel='toggle_dimensions' id="toggle_dimensions" style="width: 350px"></select>

					<?php
					Yoast_GA_Dashboards_Display::get_instance()->display( 'dimensions' );
				}
			}
			else {
				echo '<div class="ga-promote"><p>';
				echo sprintf(
					__( 'You have not yet finished setting up Google Analytics for Wordpress by MonsterInsights. Please %sadd your Analytics profile here%s to enable tracking.', 'google-analytics-for-wordpress' ),
					'<a href=" ' . admin_url( 'admin.php?page=yst_ga_settings#top#general' ) . '">',
					'</a>'
				);
				echo '</p></div>';
			}
			?>
		</div>

		<div id="customdimensions" class="wpseotab gatab">
			<?php
			do_action( 'yst_ga_custom_dimension_add-dashboards-tab' );
			?>
		</div>

		<div id="systeminfo" class="wpseotab gatab">
			<form action="<?php echo esc_url( admin_url( 'admin.php?page=yst_ga_dashboard#top#systeminfo' ) ); ?>" method="post" dir="ltr">
			<textarea id="ga-debug-info" style="width: 800px; height: 400px; font-family: Menlo, Monaco, monospace; background: none; white-space: pre; overflow: auto; display: block;" class="postbox" readonly="readonly" onclick="this.focus(); this.select()" name="monsterinsights-sysinfo" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac)."><?php
				global $wpdb;
				ob_start();
				print_r( $this->options );
				$options = ob_get_contents();
				ob_end_clean();
				// Get theme info
				$theme_data = wp_get_theme();
				$theme      = $theme_data->Name . ' ' . $theme_data->Version;

				$return  = '### Begin System Info ###' . "\n\n";
				// Start with the basics...
				$return .= '-- Site Info' . "\n\n";
				$return .= 'Site URL:                 ' . site_url() . "\n";
				$return .= 'Home URL:                 ' . home_url() . "\n";
				$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";
				
				// WordPress configuration
				$return .= "\n" . '-- WordPress Configuration' . "\n\n";
				$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
				$return .= 'Language:                 ' . ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ) . "\n";
				$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
				$return .= 'Active Theme:             ' . $theme . "\n";
				$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";
				// Only show page specs if frontpage is set to 'page'
				if( get_option( 'show_on_front' ) == 'page' ) {
					$front_page_id = get_option( 'page_on_front' );
					$blog_page_id = get_option( 'page_for_posts' );
					$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . "\n";
					$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . "\n";
				}
				$return .= 'ABSPATH:                  ' . ABSPATH . "\n";
				// Make sure wp_remote_post() is working
				$request['cmd'] = '_notify-validate';
				$params = array(
					'sslverify'     => false,
					'timeout'       => 60,
					'user-agent'    => 'MI/' . GAWP_VERSION,
					'body'          => $request
				);
				$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );
				if( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
					$WP_REMOTE_POST = 'wp_remote_post() works';
				} else {
					$WP_REMOTE_POST = 'wp_remote_post() does not work';
				}
				$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
				$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";

				$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
				$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";

				$return .= "\n" . '-- MI Version' . "\n";
				$return .= GAWP_VERSION;

				// MI configuration
				$return .= "\n\n" . '-- MI Configuration' . "\n";
				$return .= $options;


				// Get plugins that have an update
				$updates = get_plugin_updates();
				// Must-use plugins
				// NOTE: MU plugins can't show updates!
				$muplugins = get_mu_plugins();
				if( count( $muplugins > 0 ) ) {
					$return .= "\n" . '-- Must-Use Plugins' . "\n\n";
					foreach( $muplugins as $plugin => $plugin_data ) {
						$return .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
					}

				}
				// WordPress active plugins
				$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";
				$plugins = get_plugins();
				$active_plugins = get_option( 'active_plugins', array() );
				foreach( $plugins as $plugin_path => $plugin ) {
					if( !in_array( $plugin_path, $active_plugins ) )
						continue;
					$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
					$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
				}

				// WordPress inactive plugins
				$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";
				foreach( $plugins as $plugin_path => $plugin ) {
					if( in_array( $plugin_path, $active_plugins ) )
						continue;
					$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
					$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
				}

				if( is_multisite() ) {
					// WordPress Multisite active plugins
					$return .= "\n" . '-- Network Active Plugins' . "\n\n";
					$plugins = wp_get_active_network_plugins();
					$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
					foreach( $plugins as $plugin_path ) {
						$plugin_base = plugin_basename( $plugin_path );
						if( !array_key_exists( $plugin_base, $active_plugins ) )
							continue;
						$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[$plugin_path]->update->new_version . ')' : '';
						$plugin  = get_plugin_data( $plugin_path );
						$return .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
					}
				}
				// Server configuration (really just versioning)
				$return .= "\n" . '-- Webserver Configuration' . "\n\n";
				$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
				$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
				$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

				// PHP configs... now we're getting to the important stuff
				$return .= "\n" . '-- PHP Configuration' . "\n\n";
				$return .= 'Safe Mode:                ' . ( ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled' . "\n" );
				$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
				$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
				$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
				$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
				$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
				$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
				$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";

				// PHP extensions and such
				$return .= "\n" . '-- PHP Extensions' . "\n\n";
				$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
				$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
				$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
				$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

				$return .= "\n" . '### End System Info ###';
				echo $return;
			?>
			</textarea>
			<p class="submit">
				<input type="hidden" name="monsterinsights-action" value="download_sysinfo" />
				<?php submit_button( 'Download System Info File', 'primary', 'monsterinsights-download-sysinfo', false ); ?>
			</p>
			</form>
		</div>
	</div>


<?php
echo $yoast_ga_admin->content_footer();
?>