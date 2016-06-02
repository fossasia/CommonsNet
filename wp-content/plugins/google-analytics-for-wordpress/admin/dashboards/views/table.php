<?php
/**
 * @package GoogleAnalytics\Admin
 */

?>
<div class='yoast-dashboard yoast-data-table' id="table-<?php echo $dashboard; ?>" data-label="<?php echo $settings['title']; ?>" data-dimension="<?php echo ( ! empty( $settings['custom-dimension-id'] ) ) ? $settings['custom-dimension-id'] : ''; ?>">
	<h3>
		<span class='alignleft'><?php echo $settings['title']; ?></span>
		<?php
		if ( ! empty( $settings['help'] ) ) {
			echo Yoast_GA_Admin_Form::show_help( 'graph-' . $dashboard, $settings['help'] );
		}
		?>
		<span class='alignright period'><?php _e( 'Last month', 'google-analytics-for-wordpress' ); ?></span>
	</h3>

	<div>
		<table class="widefat fixed stripe">
			<thead>
				<th><?php echo esc_html( $settings['title'] ); ?></th>
				<?php foreach ( $settings['columns'] as $columns ) { ?>
				<th><?php echo esc_html( $columns ); ?></th>
				<?php } ?>
			</thead>
		</table>
	</div>
</div>