<?php
/**
 * @package GoogleAnalytics\Admin
 */

?>
<div id="graph-<?php echo $dashboard; ?>" class="yoast-dashboard yoast-graph" data-label="<?php echo $settings['title']; ?>" data-percent="<?php echo ! empty( $settings['data-percent'] ); ?>">
	<h3>
		<span class='alignleft'><?php echo $settings['title']; ?></span>
		<?php
		if ( ! empty( $settings['help'] ) ) {
			echo Yoast_GA_Admin_Form::show_help( 'graph-' . $dashboard, $settings['help'] );
		}
		?>
		<span class='alignright period'><?php echo __( 'Last month', 'google-analytics-for-wordpress' ); ?></span>
	</h3>

	<?php
	if ( empty( $settings['hide_y_axis'] ) ) {
		echo "<div class='yoast-graph-yaxis'></div>";
	} ?>
	<div class="yoast-graph-holder"></div>

	<?php
	if ( empty( $settings['hide_x_axis'] ) ) {
		echo "<div class='yoast-graph-xaxis'></div>";
	} ?>
</div>