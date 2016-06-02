<?php
/**
 * Actions required
 */
?>

<div id="actions_required" class="zerif-lite-tab-pane">

    <h1><?php esc_html_e( 'Keep up with Zerif Lite\'s latest news' ,'zerif-lite' ); ?></h1>

    <!-- NEWS -->
    <hr />

	<?php
	global $zerif_required_actions;

	if( !empty($zerif_required_actions) ):

		/* zerif_show_required_actions is an array of true/false for each required action that was dismissed */
		$zerif_show_required_actions = get_option("zerif_show_required_actions");

		foreach( $zerif_required_actions as $zerif_required_action_key => $zerif_required_action_value ):
			if(@$zerif_show_required_actions[$zerif_required_action_value['id']] === false) continue;
			if(@$zerif_required_action_value['check']) continue;
			?>
			<div class="zerif-action-required-box">
				<span class="dashicons dashicons-no-alt zerif-dismiss-required-action" id="<?php echo $zerif_required_action_value['id']; ?>"></span>
				<h4><?php echo $zerif_required_action_key + 1; ?>. <?php if( !empty($zerif_required_action_value['title']) ): echo $zerif_required_action_value['title']; endif; ?></h4>
				<p><?php if( !empty($zerif_required_action_value['description']) ): echo $zerif_required_action_value['description']; endif; ?></p>
				<?php
					if( !empty($zerif_required_action_value['plugin_slug']) ):
						?><p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin='.$zerif_required_action_value['plugin_slug'] ), 'install-plugin_'.$zerif_required_action_value['plugin_slug'] ) ); ?>" class="button button-primary"><?php if( !empty($zerif_required_action_value['title']) ): echo $zerif_required_action_value['title']; endif; ?></a></p><?php
					endif;
				?>

				<hr />
			</div>
			<?php
		endforeach;
	endif;

	$nr_actions_required = 0;

	/* get number of required actions */
	if( get_option('zerif_show_required_actions') ):
		$zerif_show_required_actions = get_option('zerif_show_required_actions');
	else:
		$zerif_show_required_actions = array();
	endif;

	if( !empty($zerif_required_actions) ):
		foreach( $zerif_required_actions as $zerif_required_action_value ):
			if(( !isset( $zerif_required_action_value['check'] ) || ( isset( $zerif_required_action_value['check'] ) && ( $zerif_required_action_value['check'] == false ) ) ) && ((isset($zerif_show_required_actions[$zerif_required_action_value['id']]) && ($zerif_show_required_actions[$zerif_required_action_value['id']] == true)) || !isset($zerif_show_required_actions[$zerif_required_action_value['id']]) )) :
				$nr_actions_required++;
			endif;
		endforeach;
	endif;

	if( $nr_actions_required == 0 ):
		echo '<p>'.__( 'Hooray! There are no required actions for you right now.','zerif-lite' ).'</p>';
	endif;
	?>

</div>