<!-- Advertising -->
<?php
$configuration = blc_get_configuration();
if ( !$configuration->get('user_has_donated') ):
?>
	<div id="managewp-ad" class="postbox">
		<div class="inside">
			<a href="http://managewp.com/?utm_source=broken_link_checker&utm_medium=Banner&utm_content=mwp250_2&utm_campaign=Plugins" title="ManageWP">
				<img src="<?php echo plugins_url('images/mwp250_2.png', BLC_PLUGIN_FILE) ?>" width="250" height="250" alt="ManageWP">
			</a>
		</div>
	</div>
<?php
endif; ?>