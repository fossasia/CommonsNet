<?php
/**
 * Changelog
 */

$zerif_lite = wp_get_theme( 'zerif-lite' );

?>
<div class="zerif-lite-tab-pane" id="changelog">

	<div class="zerif-tab-pane-center">
	
		<h1>Zerif Lite <?php if( !empty($zerif_lite['Version']) ): ?> <sup id="zerif-lite-theme-version"><?php echo esc_attr( $zerif_lite['Version'] ); ?> </sup><?php endif; ?></h1>

	</div>

	<?php
	WP_Filesystem();
	global $wp_filesystem;
	$zerif_lite_changelog = $wp_filesystem->get_contents( get_template_directory().'/CHANGELOG.md' );
	$zerif_lite_changelog_lines = explode(PHP_EOL, $zerif_lite_changelog);
	foreach($zerif_lite_changelog_lines as $zerif_lite_changelog_line){
		if(substr( $zerif_lite_changelog_line, 0, 3 ) === "###"){
			echo '<hr /><h1>'.substr($zerif_lite_changelog_line,3).'</h1>';
		} else {
			echo $zerif_lite_changelog_line.'<br/>';
		}
	}

	?>
	
</div>
