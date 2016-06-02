<?php
/**
 * @package GoogleAnalytics\Admin
 */

global $yoast_ga_admin;

echo $yoast_ga_admin->content_head();

$has_extensions = false;

$extensions = $yoast_ga_admin->get_extensions();
?>
	<h2 id="yoast_ga_title"><?php echo __( 'Google Analytics by MonsterInsights: ', 'google-analytics-for-wordpress' ) . __( 'Extensions', 'google-analytics-for-wordpress' ); ?></h2>

	<h2 class="nav-tab-wrapper" id="ga-tabs">
		<a class="nav-tab active" id="extensions-tab" href="#top#extensions"><?php _e( 'Extensions', 'google-analytics-for-wordpress' ); ?></a>
		<a class="nav-tab" id="licenses-tab" href="#top#licenses"><?php _e( 'Licenses', 'google-analytics-for-wordpress' ); ?></a>
	</h2>
	<div class="tabwrapper">
		<div id="extensions" class="wpseotab gatab">
		<?php
		foreach ( $extensions as $name => $extension ) :
			if ( 'uninstalled' !== $extension->status ) {
				$has_extensions = true;
			}
			?>
			<div class="extension <?php echo $name; ?>">
				<a target="_blank" href="<?php echo $extension->url; ?>#utm_medium=banner&utm_source=gawp-config&utm_campaign=extension-page-banners">
					<h3><?php echo $extension->title; ?></h3>
				</a>

				<p><?php echo $extension->desc; ?></p>
					<p>
						<?php if ( 'uninstalled' == $extension->status ) : ?>
							<a target="_blank" href="<?php echo $extension->url; ?>#utm_medium=banner&utm_source=gawp-config&utm_campaign=extension-page-banners" class="button-primary"><?php echo __( 'Get this extension', 'google-analytics-for-wordpress' ); ?></a>
						<?php else : ?>
							<?php if ( 'inactive' == $extension->status ) : ?>
								<a href="#top#licenses" class="activate-link button-primary"><?php echo __( 'Activate License', 'google-analytics-for-wordpress' ); ?></a>
							<?php else : ?>
								<button class="button-primary installed"><?php echo __( 'Installed', 'google-analytics-for-wordpress' ); ?></button>
							<?php endif; ?>
						<?php endif; ?>
					</p>
				</div>
		<?php endforeach; ?>
		</div>
		<div id="licenses" class="wpseotab gatab">
		<?php
		if ( ! $has_extensions ) {
			echo '<p>' . __( 'You have not installed any extensions for Google Analytics by MonsterInsights, so there are no licenses to activate.', 'google-analytics-for-wordpress' ) . '</p>';
		}
		else {
			do_action( 'yst_ga_show_license_form' );
		}
		?>
		</div>
	</div>
	<div class="clear"></div>

<?php
echo $yoast_ga_admin->content_footer();
?>