<script type="text/javascript">

jQuery(function($){
	$('#blc-tabs').tabs();
	
	//Refresh the "Status" box every 10 seconds
	function blcUpdateStatus(){
		$.getJSON(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_full_status',
				'random' : Math.random()
			},
			function (data, textStatus){
				if ( data && ( typeof(data['text']) != 'undefined' ) ){
					$('#wsblc_full_status').html(data.text);
				} else {
					$('#wsblc_full_status').html('<?php _e('[ Network error ]', 'broken-link-checker'); ?>');
				}
				
				setTimeout(blcUpdateStatus, 10000);							
			}
		);
	}
	blcUpdateStatus();
	
	//Refresh the avg. load display every 10 seconds
	function blcUpdateLoad(){
		$.get(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_current_load'
			},
			function (data, textStatus){
				$('#wsblc_current_load').html(data);
				
				setTimeout(blcUpdateLoad, 10000); //...update every 10 seconds							
			}
		);
	}
	//Only do it if load limiting is available on this server, though.
	if ( $('#wsblc_current_load').length > 0 ){
		blcUpdateLoad();
	}
	
	
	var toggleButton = $('#blc-debug-info-toggle'); 
	
	toggleButton.click(function(){
		
		var box = $('#blc-debug-info'); 
		box.toggle();
		if( box.is(':visible') ){
			toggleButton.text('<?php _e('Hide debug info', 'broken-link-checker'); ?>');
		} else {
			toggleButton.text('<?php _e('Show debug info', 'broken-link-checker'); ?>');
		}
		
	});
	
	$('#toggle-broken-link-css-editor').click(function(){
		var box = $('#broken-link-css-wrap').toggleClass('hidden');
		
		$.cookie(
			box.attr('id'),
			box.hasClass('hidden')?'0':'1',
			{
				expires : 365
			}
		);
		
		return false;
	});
	
	$('#toggle-removed-link-css-editor').click(function(){
		var box = $('#removed-link-css-wrap').toggleClass('hidden');
		
		$.cookie(
			box.attr('id'),
			box.hasClass('hidden')?'0':'1',
			{
				expires : 365
			}
		);
		
		return false;
	});
	
	//Show/hide per-module settings
	$('.toggle-module-settings').click(function(){
		var settingsBox = $(this).parent().find('.module-extra-settings');
		if ( settingsBox.length > 0 ){
			settingsBox.toggleClass('hidden');
			$.cookie(
				settingsBox.attr('id'),
				settingsBox.hasClass('hidden')?'0':'1',
				{
					expires : 365
				}
			);
		}
		return false;
	});
	
	//When the user ticks the "Custom fields" box, display the field list input
	//so that they notice that they need to enter the field names. 
	$('#module-checkbox-custom_field').click(function(){
		var box = $(this);
		var fieldList = $('#blc_custom_fields');
		if ( box.is(':checked') && ( $.trim(fieldList.val()) == '' ) ){
			$('#module-extra-settings-custom_field').removeClass('hidden');
		}
	});
	
	//Handle the "Recheck" button
	$('#start-recheck').click(function(){
		$('#recheck').val('1'); //populate the hidden field
		$('#link_checker_options input[name="submit"]').click(); //.submit() didn't work for some reason	
	});

	//Enable/disable log-related options depending on whether "Enable logging" is on.
	function blcToggleLogOptions() {
		$('#blc-logging-options')
			.find('input')
			.prop('disabled', ! $('#logging_enabled').is(':checked'));
	}

	blcToggleLogOptions();
	$('#logging_enabled').change(blcToggleLogOptions);

	//
	$('#target_resource_usage').change(function() {
		$('#target_resource_usage_percent').text($(this).val() + '%')
	});
});

</script>