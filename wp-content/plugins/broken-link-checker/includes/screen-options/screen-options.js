jQuery(function($){
	function performAutosave(){
		var panel = $(this).parents('div.custom-options-panel');
		var params = panel.find('input, select, textarea').serialize();
		params = params + '&action=save_settings-' + panel.attr('id');
		$.post(
			'admin-ajax.php',
			params
		);
	}
	
	$('#screen-options-wrap div.requires-autosave').find('input, select, textarea').change(performAutosave);
});