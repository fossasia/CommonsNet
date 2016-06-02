<script type='text/javascript'>

function alterLinkCounter(factor, filterId){
	var counter;
	if (filterId) {
		counter = jQuery('.filter-' + filterId + '-link-count');
	} else {
		counter = jQuery('.current-link-count');
	}

    var cnt = parseInt(counter.eq(0).html(), 10);
    cnt = cnt + factor;
    counter.html(cnt);
    
	if ( blc_is_broken_filter ){
		//Update the broken link count displayed beside the "Broken Links" menu
		var menuBubble = jQuery('span.blc-menu-bubble');
		if ( menuBubble.length > 0 ){
			cnt = parseInt(menuBubble.eq(0).html());
			cnt = cnt + factor;
			if ( cnt > 0 ){
				menuBubble.html(cnt);
			} else {
				menuBubble.parent().hide();
			}
		}
	}    
}

function replaceLinkId(old_id, new_id){
	var master = jQuery('#blc-row-'+old_id);
	
	master.attr('id', 'blc-row-'+new_id);
	master.find('.blc-link-id').html(new_id);
	master.find('th.check-column input[type="checkbox"]').val(new_id);

	var details_row = jQuery('#link-details-'+old_id);
	details_row.attr('id', 'link-details-'+new_id);
}

function reloadDetailsRow(link_id){
	var details_row = jQuery('#link-details-'+link_id);
	
	//Load up the new link info                     (so sue me)    
	details_row.find('td').html('<center><?php echo esc_js(__('Loading...' , 'broken-link-checker')); ?></center>').load(
		"<?php echo admin_url('admin-ajax.php'); ?>",
		{
			'action' : 'blc_link_details',
			'link_id' : link_id
		}
	);
}

jQuery(function($){
	
	//The details button - display/hide detailed info about a link
    $(".blc-details-button, td.column-link-text, td.column-status, td.column-new-link-text").click(function () {
    	var master = $(this).parents('.blc-row');
    	var link_id = master.attr('id').split('-')[2];
		$('#link-details-'+link_id).toggle();
		return false;
    });

	var ajaxInProgressHtml = '<?php echo esc_js(__('Wait...', 'broken-link-checker')); ?>';
	
	//The "Not broken" button - manually mark the link as valid. The link will be checked again later.
	$(".blc-discard-button").click(function () {
		var me = $(this);
		me.html(ajaxInProgressHtml);
		
		var master = me.parents('.blc-row');
    	var link_id = master.attr('id').split('-')[2];
        
        $.post(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_discard',
				'link_id' : link_id,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_discard'));  ?>'
			},
			function (data, textStatus){
				if (data == 'OK'){
					var details = $('#link-details-'+link_id);
					
					//Remove the "Not broken" action
					me.parent().remove();
					
					//Set the displayed link status to OK
					var classNames = master.attr('class');
					classNames = classNames.replace(/(^|\s)link-status-[^\s]+(\s|$)/, ' ') + ' link-status-ok';
					master.attr('class', classNames);
					
					//Flash the main row green to indicate success, then remove it if the current view
					//is supposed to show only broken links or warnings.
					var should_hide_link = blc_is_broken_filter || (blc_current_base_filter == 'warnings');

					flashElementGreen(master, function(){
						if ( should_hide_link ){
							details.remove();
							master.remove();
						} else {
							reloadDetailsRow(link_id);
						}
					});
					
					//Update the elements displaying the number of results for the current filter.
					if( should_hide_link ){
                    	alterLinkCounter(-1);
                    }
				} else {
					me.html('<?php echo esc_js(__('Not broken' , 'broken-link-checker'));  ?>');
					alert(data);
				}
			}
		);
		
		return false;
    });

	//The "Dismiss" button - hide the link from the "Broken" and "Redirects" filters, but still apply link tweaks and so on.
	$(".blc-dismiss-button").click(function () {
		var me = $(this);
		var oldButtonHtml = me.html();
		me.html(ajaxInProgressHtml);

		var master = me.closest('.blc-row');
		var link_id = master.attr('id').split('-')[2];
		var should_hide_link = $.inArray(blc_current_base_filter, ['broken', 'redirects', 'warnings']) > -1;

		$.post(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_dismiss',
				'link_id' : link_id,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_dismiss'));  ?>'
			},
			function (data, textStatus){
				if (data == 'OK'){
					var details = $('#link-details-'+link_id);

					//Remove the "Dismiss" action
					me.parent().hide();

					//Flash the main row green to indicate success, then remove it if necessary.
					flashElementGreen(master, function(){
						if ( should_hide_link ){
							details.remove();
							master.remove();
						}
					});

					//Update the elements displaying the number of results for the current filter.
					if( should_hide_link ){
						alterLinkCounter(-1);
						alterLinkCounter(1, 'dismissed');
					}
				} else {
					me.html(oldButtonHtml);
					alert(data);
				}
			}
		);

		return false;
	});

	//The "Undismiss" button.
	$(".blc-undismiss-button").click(function () {
		var me = $(this);
		var oldButtonHtml = me.html();
		me.html(ajaxInProgressHtml);

		var master = me.closest('.blc-row');
		var link_id = master.attr('id').split('-')[2];
		var should_hide_link = (blc_current_base_filter == 'dismissed');

		$.post(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_undismiss',
				'link_id' : link_id,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_undismiss'));  ?>'
			},
			function (data, textStatus){
				if (data == 'OK'){
					var details = $('#link-details-'+link_id);

					//Remove the action.
					me.parent().hide();

					//Flash the main row green to indicate success, then remove it if necessary.
					flashElementGreen(master, function(){
						if ( should_hide_link ){
							details.remove();
							master.remove();
						}
					});

					//Update the elements displaying the number of results for the current filter.
					if( should_hide_link ){
						alterLinkCounter(-1);
					}
				} else {
					me.html(oldButtonHtml);
					alert(data);
				}
			}
		);

		return false;
	});

	//The "Recheck" button.
	$(".blc-recheck-button").click(function () {
		var me = $(this);
		var oldButtonHtml = me.html();
		me.html(ajaxInProgressHtml);

		var master = me.closest('.blc-row');
		var link_id = master.attr('id').split('-')[2];

		$.post(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_recheck',
				'link_id' : link_id,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_recheck'));  ?>'
			},
			function (response){
				me.html(oldButtonHtml);

				if (response && (typeof(response['error']) != 'undefined')){
					//An internal error occurred before the plugin could check the link (e.g. database error).
					alert(response.error);
				} else {
					//Display the new status in the link row.
					displayLinkStatus(master, response);
					reloadDetailsRow(link_id);

					//Flash the row green to indicate success
					flashElementGreen(master);
				}
			},
			'json'
		);

		return false;
	});

	//The "Fix redirect" action.
	$('.blc-deredirect-button').click(function() {
		//This action can only be used once. If it succeeds, it will no longer be applicable to the current link.
		//If it fails, something is broken and trying again probably won't help.
		var me = $(this);
		me.text(ajaxInProgressHtml);

		var master = me.closest('.blc-row');
		var linkId = master.attr('id').split('-')[2];
		var shouldHideLink = blc_current_base_filter == 'redirects';
		var details = $('#link-details-' + linkId);

		$.post(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_deredirect',
				'link_id' : linkId,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_deredirect'));  ?>'
			},
			function (response){
				me.closest('span').hide();

				if (handleEditResponse(response, master, linkId, null)) {
					if (shouldHideLink) {
						details.remove();
						master.remove();
					}
				}
			},
			'json'
		);

		return false;
	});

	function flashElementGreen(element, callback) {
		var oldColor = element.css('background-color');
		element.animate({ backgroundColor: "#E0FFB3" }, 200).animate({ backgroundColor: oldColor }, 300, callback);
	}


	/**
	 * Update status indicators for a link. This includes the contents of the "Status" column, CSS classes and so on.
	 *
	 * @param {Object} row Table row as a jQuery object.
	 * @param {Object} status
	 */
	function displayLinkStatus(row, status) {
		//Update the status code and class.
		var statusColumn = row.find('td.column-status');
		if (status.status_text) {
			statusColumn.find('.status-text').text(status.status_text);
		}
		statusColumn.find('.http-code').text(status.http_code ? status.http_code : '');

		var oldStatusClass = row.attr('class').match(/(?:^|\s)(link-status-[^\s]+)(?:\s|$)/);
		oldStatusClass = oldStatusClass ? oldStatusClass[1] : '';
		var newStatusClass = 'link-status-' + status.status_code;

		statusColumn.find('.link-status-row').removeClass(oldStatusClass).addClass(newStatusClass);
		row.removeClass(oldStatusClass).addClass(newStatusClass);

		//Last check time and failure duration are complicated to update, so we'll just hide them.
		//The user can refresh the page to get the new values.
		statusColumn.find('.link-last-checked td').html('&nbsp;');
		statusColumn.find('.link-broken-for td').html('&nbsp;');

		//The link may or may not be a redirect now.
		row.toggleClass('blc-redirect', status.redirect_count > 0);

		if (typeof status['redirect_count'] !== 'undefined') {
			var redirectColumn = row.find('td.column-redirect-url').empty();

			if (status.redirect_count > 0 && status.final_url) {
				redirectColumn.append(
					$(
						'<a></a>',
						{
							href: status.final_url,
							text: status.final_url,
							title: status.final_url,
							'class': 'blc-redirect-url',
							target: '_blank'
						}
					)
				);
			}
		}
	}


	/**
	 * Display the inline link editor.
	 *
	 * @param {Number} link_id Link ID. The link must be visible in the current view.
	 */
	function showLinkEditor(link_id) {
		var master = $('#blc-row-' + link_id),
			editorId = 'blc-edit-row-' + link_id,
			editRow;

		//Get rid of all existing inline editors.
		master.closest('table').find('tr.blc-inline-editor').each(function() {
			hideLinkEditor($(this));
		});

		//Create an inline editor for this link.
		editRow = $('#blc-inline-edit-row').clone(true).attr('id', editorId);
		editRow.toggleClass('alternate', master.hasClass('alternate'));
		master.after(editRow);

		//Populate editor fields.
		var urlElement = master.find('a.blc-link-url');
		var linkUrl = urlElement.data('editable-url') || urlElement.attr('href');
		var urlInput = editRow.find('.blc-link-url-field').val(linkUrl);

		var titleInput = editRow.find('.blc-link-text-field');
		var linkText = master.data('link-text'),
			canEditText = master.data('can-edit-text') == 1, //jQuery will convert a '1' to 1 (number) when reading a data attribute.
			canEditUrl = master.data('can-edit-url') == 1,
			noneText = '<?php echo esc_js(_x('(None)', 'link text', 'broken-link-checker')); ?>',
			multipleLinksText = '<?php echo esc_js(_x('(Multiple links)', 'link text', 'broken-link-checker')); ?>';

		titleInput.prop('readonly', !canEditText);
		urlInput.prop('readonly', !canEditUrl);

		if ( (typeof linkText !== 'undefined') && (linkText !== null) ) {
			if (linkText === '') {
				titleInput.val(canEditText ? linkText : noneText);
			} else {
				titleInput.val(linkText)
			}
			titleInput.prop('placeholder', noneText);
		} else {
			if (canEditText) {
				titleInput.val('').prop('placeholder', multipleLinksText);
			} else {
				titleInput.val(multipleLinksText)
			}
		}

		//Populate the list of URL replacement suggestions.
		if (canEditUrl && blc_suggestions_enabled && (master.hasClass('link-status-error') || master.hasClass('link-status-warning'))) {
			editRow.find('.blc-url-replacement-suggestions').show();
			var suggestionList = editRow.find('.blc-suggestion-list');
			findReplacementSuggestions(linkUrl, suggestionList);
		}

		editRow.find('.blc-update-link-button').prop('disabled', !(canEditUrl || canEditText));

		//Make the editor span the entire width of the table.
		editRow.find('td.blc-colspan-change').attr('colspan', master.closest('table').find('thead th:visible').length);

		master.hide();
		editRow.show();
		urlInput.focus();
		if (canEditUrl) {
			urlInput.select();
		}
	}

    /**
     * Hide the inline editor for a particular link.
	 *
	 * @param link_id Either a numeric link ID or a jQuery object that represents the editor row.
     */
	function hideLinkEditor(link_id) {
		var editRow = isNaN(link_id) ? link_id : $('#blc-edit-row-' + link_id);
		editRow.prev('tr.blc-row').show();
		editRow.remove();
	}

    /**
	 * Find possible replacements for a broken link and display them in a list.
     *
	 * @param {String} url The current link URL.
	 * @param suggestionList jQuery object that represents a list element.
     */
	function findReplacementSuggestions(url, suggestionList) {
		var searchingText     = '<?php echo esc_js(_x('Searching...', 'link suggestions', 'broken-link-checker')) ?>';
		var noSuggestionsText = '<?php echo esc_js(_x('No suggestions available.', 'link suggestions', 'broken-link-checker')) ?>';
		var iaSuggestionName  = '<?php echo esc_js(_x('Archived page from %s (via the Wayback Machine)', 'link suggestions', 'broken-link-checker')); ?>';

		suggestionList.empty().append('<li>' + searchingText + '</li>');

		var suggestionTemplate = $('#blc-suggestion-template').find('li').first();

		//Check the Wayback Machine for an archived version of the page.
		$.getJSON(
			'//archive.org/wayback/available?callback=?',
			{ url: url },

			function(data) {
				suggestionList.empty();

				//Check if there are any results.
				if (!data || !data.archived_snapshots || !data.archived_snapshots.closest || !data.archived_snapshots.closest.available ) {
					suggestionList.append('<li>' + noSuggestionsText + '</li>');
					return;
				}

				var snapshot = data.archived_snapshots.closest;

				//Convert the timestamp from YYYYMMDDHHMMSS to ISO 8601 date format.
				var readableTimestamp = snapshot.timestamp.substr(0, 4) +
					'-' + snapshot.timestamp.substr(4, 2) +
					'-'	+ snapshot.timestamp.substr(6, 2);
				var name = sprintf(iaSuggestionName, readableTimestamp);

				//Display the suggestion.
				var item = suggestionTemplate.clone();
				item.find('.blc-suggestion-name a').text(name).attr('href', snapshot.url);
				item.find('.blc-suggestion-url').text(snapshot.url);
				suggestionList.append(item);
			}
		);
	}

    /**
     * Call our PHP backend and tell it to edit all occurrences of particular link.
	 * Updates UI with the new link info and displays any error messages that might be generated.
	 *
	 * @param linkId Either a numeric link ID or a jQuery object representing the link row.
     * @param {String} newUrl The new link URL.
     * @param {String} newText The new link text. Optional. Set to null to leave it unchanged.
     */
	function updateLink(linkId, newUrl, newText) {
		var master, editRow;
		if ( isNaN(linkId) ){
			master = linkId;
			linkId = master.attr('id').split("-")[2]; //id="blc-row-$linkid"
		} else {
			master = $('#blc-row-' + linkId);
		}
		editRow = $('#blc-edit-row-' + linkId);

		var urlElement = master.find('a.blc-link-url');
		var progressIndicator = editRow.find('.waiting'),
			updateButton = editRow.find('.blc-update-link-button');
		progressIndicator.show();
		updateButton.prop('disabled', true);

		$.post(
			'<?php echo admin_url('admin-ajax.php'); ?>',
			{
				'action'   : 'blc_edit',
				'link_id'  : linkId,
				'new_url'  : newUrl,
				'new_text' : newText,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_edit'));  ?>'
			},
			function(response) {
				progressIndicator.hide();
				updateButton.prop('disabled', false);

				handleEditResponse(response, master, linkId, newText);

				hideLinkEditor(editRow);
			},
			'json'
		);

	}

	function handleEditResponse(response, master, linkId, newText) {
		if (response && (typeof(response['error']) != 'undefined')){
			//An internal error occurred before the link could be edited.
			alert(response.error);
			return false;
		} else if (response.errors.length > 0) {
			//Build and display an error message.
			var msg = '';

			if ( response.cnt_okay > 0 ){
				var fragment = sprintf(
					'<?php echo esc_js(__('%d instances of the link were successfully modified.', 'broken-link-checker')); ?>',
					response.cnt_okay
				);
				msg = msg + fragment + '\n';
				if ( response.cnt_error > 0 ){
					fragment = sprintf(
						'<?php echo esc_js(__("However, %d instances couldn't be edited and still point to the old URL.", 'broken-link-checker')); ?>',
						response.cnt_error
					);
					msg = msg + fragment + "\n";
				}
			} else {
				msg = msg + '<?php echo esc_js(__('The link could not be modified.', 'broken-link-checker')); ?>\n';
			}

			msg = msg + '\n<?php echo esc_js(__("The following error(s) occurred :", 'broken-link-checker')); ?>\n* ';
			msg = msg + response.errors.join('\n* ');

			alert(msg);
			return false;
		} else {
			//Everything went well. Update the link row with the new values.

			//Replace the displayed link URL with the new one.
			var urlElement = master.find('a.blc-link-url');
			urlElement
				.attr('href', response.url)
				.text(response.url)
				.data('editable-url', response.url)
				.prop('title', response.url);
			if ( typeof response['escaped_url'] != 'undefined' ) {
				urlElement.attr('href', response.escaped_url)
			}

			//Save the new ID
			replaceLinkId(linkId, response.new_link_id);
			//Load up the new link info
			reloadDetailsRow(response.new_link_id);

			//Update the link text if it was edited.
			if ((newText !== null) && (response.link_text !== null)) {
				master.data('link-text', response.link_text);
				if (response.ui_link_text !== null) {
					master.find('.column-new-link-text').html(response.ui_link_text);
				}
			}

			//Update the status code and class.
			displayLinkStatus(master, response);

			//Flash the row green to indicate success
			flashElementGreen(master);

			return true;
		}
	}

    //The "Edit URL" button - displays the inline editor
    $(".blc-edit-button").click(function () {
		var master = $(this).closest('.blc-row');
    	var link_id = master.attr('id').split('-')[2];
        showLinkEditor(link_id);
    });
    
    //Let the user use Enter and Esc as shortcuts for "Update" and "Cancel"
    $('.blc-inline-editor input[type="text"]').keypress(function (e) {
		var editRow = $(this).closest('.blc-inline-editor');
		if (e.which == 13) {
			editRow.find('.blc-update-link-button').click();
			return false;
		} else if (e.which == 27) {
			editRow.find('.blc-cancel-button').click();
			return false;
		}
		return true;
	});


	//The "Update" button in the inline editor.
	$('.blc-update-link-button').click(function() {
		var editRow = $(this).closest('tr'),
			master = editRow.prev('.blc-row');

		//Ensure the new URL is not empty.
		var urlField = editRow.find('.blc-link-url-field');
		var newUrl = urlField.val();
		if ($.trim(newUrl) == '') {
			alert('<?php echo esc_js(__('Error: Link URL must not be empty.', 'broken-link-checker')); ?>');
			urlField.focus();
			return;
		}

		var newLinkText = null,
			linkTextField = editRow.find('.blc-link-text-field'),
			oldLinkText = master.data('link-text');
		if (!linkTextField.prop('readonly')) {
			newLinkText = linkTextField.val();
			//Empty or identical to the original = leave the text unchanged.
			if ((newLinkText === '') || (newLinkText === oldLinkText)) {
				newLinkText = null;
			}
		}

		updateLink(master, newUrl, newLinkText);
	});

    //The "Cancel" in the inline editor.
    $(".blc-cancel-button").click(function () { 
		var editRow = $(this).closest('tr');
		hideLinkEditor(editRow);
    });

	//The "Use this URL" button in the inline editor replaces the link URL
	//with the selected suggestion URL.
	$('#blc-links').on('click', '.blc-use-url-button', function() {
		var button = $(this);
		var suggestionUrl = button.closest('tr').find('.blc-suggestion-name a').attr('href');
		button.closest('.blc-inline-editor').find('.blc-link-url-field').val(suggestionUrl);
	});


    //The "Unlink" button - remove the link/image from all posts, custom fields, etc.
    $(".blc-unlink-button").click(function () { 
    	var me = this;
    	var master = $(me).parents('.blc-row');
		$(me).html('<?php echo esc_js(__('Wait...' , 'broken-link-checker')); ?>');
		
		//Find the link ID
    	var link_id = master.attr('id').split('-')[2];
        
        $.post(
			"<?php echo admin_url('admin-ajax.php'); ?>",
			{
				'action' : 'blc_unlink',
				'link_id' : link_id,
				'_ajax_nonce' : '<?php echo esc_js(wp_create_nonce('blc_unlink'));  ?>'
			},
			function (data, textStatus){
				eval('data = ' + data);
				 
				if ( data && (typeof(data['error']) != 'undefined') ){
					//An internal error occurred before the link could be edited.
					//data.error is an error message.
					alert(data.error);
				} else {
					if ( data.errors.length == 0 ){
						//The link was successfully removed. Hide its details. 
						$('#link-details-'+link_id).hide();
						//Flash the main row green to indicate success, then hide it.
						var oldColor = master.css('background-color');
						master.animate({ backgroundColor: "#E0FFB3" }, 200).animate({ backgroundColor: oldColor }, 300, function(){
							master.hide();
						});
						
						alterLinkCounter(-1);
						
						return;
					} else {
						//Build and display an error message.
						var msg = '';
						
						if ( data.cnt_okay > 0 ){
							msg = msg + sprintf(
								'<?php echo esc_js(__("%d instances of the link were successfully unlinked.", 'broken-link-checker')); ?>\n', 
								data.cnt_okay
							);
							
							if ( data.cnt_error > 0 ){
								msg = msg + sprintf(
									'<?php echo esc_js(__("However, %d instances couldn't be removed.", 'broken-link-checker')); ?>\n',
									data.cnt_error
								);
							}
						} else {
							msg = msg + '<?php echo esc_js(__("The plugin failed to remove the link.", 'broken-link-checker')); ?>\n';
						}
														
						msg = msg + '\n<?php echo esc_js(__("The following error(s) occured :", 'broken-link-checker')); ?>\n* ';
						msg = msg + data.errors.join('\n* ');
						
						//Show the error message
						alert(msg);
					}				
				}
				
				$(me).html('<?php echo esc_js(__('Unlink' , 'broken-link-checker')); ?>'); 
			}
		);
    });
    
    //--------------------------------------------
    //The search box(es)
    //--------------------------------------------
    
    var searchForm = $('#search-links-dialog');
	    
    searchForm.dialog({
		autoOpen : false,
		dialogClass : 'blc-search-container',
		resizable: false
	});

    $('#blc-open-search-box').click(function(){
    	if ( searchForm.dialog('isOpen') ){
			searchForm.dialog('close');
		} else {
			searchForm
				.dialog('open')
				.dialog('widget')
				.position({
					my: 'right top',
					at: 'right bottom',
					of: $('#blc-open-search-box')
				});
		}
	});
	
	$('#blc-cancel-search').click(function(){
		searchForm.dialog('close');
	});
	
	//The "Save This Search Query" button creates a new custom filter based on the current search
	$('#blc-create-filter').click(function(){
		var filter_name = prompt("<?php echo esc_js(__("Enter a name for the new custom filter", 'broken-link-checker')); ?>", "");
		if ( filter_name ){
			$('#blc-custom-filter-name').val(filter_name);
			$('#custom-filter-form').submit();
		}
	});
	
	//Display a confirmation dialog when the user clicks the "Delete This Filter" button 
	$('#blc-delete-filter').click(function(){
		var message = '<?php
		echo esc_js(
			html_entity_decode(
				__("You are about to delete the current filter.\n'Cancel' to stop, 'OK' to delete", 'broken-link-checker'),
				ENT_QUOTES,
				get_bloginfo('charset')
			)
		);
		?>';
		return confirm(message);
	});
	
	//--------------------------------------------
    // Bulk actions
    //--------------------------------------------
    
    $('#blc-bulk-action-form').submit(function(){
    	var action = $('#blc-bulk-action').val(), message;
    	if ( action ==  '-1' ){
			action = $('#blc-bulk-action2').val();
		}
    	
    	if ( action == 'bulk-delete-sources' ){
    		//Convey the gravitas of deleting link sources.
    		message = '<?php
				echo esc_js(  
					html_entity_decode(
						__("Are you sure you want to delete all posts, bookmarks or other items that contain any of the selected links? This action can't be undone.\n'Cancel' to stop, 'OK' to delete", 'broken-link-checker'),
						ENT_QUOTES,
						get_bloginfo('charset')
					)
				); 
			?>'; 
			if ( !confirm(message) ){
				return false;
			}
		} else if ( action == 'bulk-unlink' ){
			//Likewise for unlinking.
			message = '<?php
				echo esc_js(  
					html_entity_decode(
						__("Are you sure you want to remove the selected links? This action can't be undone.\n'Cancel' to stop, 'OK' to remove", 'broken-link-checker'),
						ENT_QUOTES,
						get_bloginfo('charset')
					)
				); 
			?>'; 
			if ( !confirm(message) ){
				return false;
			}
		}
	});

	//Automatically disable bulk actions that don't apply to the currently selected links.
	$('#blc-bulk-action').focus(function() {
		var redirectsSelected = false, brokenLinksSelected = false;
		$('tr th.check-column input:checked', '#blc-links').each(function() {
			var row = $(this).closest('tr');
			if (row.hasClass('blc-redirect')) {
				redirectsSelected = true
			}
			if (row.hasClass('link-status-error') || row.hasClass('link-status-warning')) {
				brokenLinksSelected = true;
			}
		});

		var bulkAction = $(this);
		bulkAction.find('option[value="bulk-deredirect"]').prop('disabled', !redirectsSelected);
		bulkAction.find('option[value="bulk-not-broken"]').prop('disabled', !brokenLinksSelected);
	});
	
	//------------------------------------------------------------
    // Manipulate highlight settings for permanently broken links
    //------------------------------------------------------------
    var highlight_permanent_failures_checkbox = $('#highlight_permanent_failures');
	var failure_duration_threshold_input = $('#failure_duration_threshold');
	
    //Apply/remove highlights when the checkbox is (un)checked
    highlight_permanent_failures_checkbox.change(function(){
    	//save_highlight_settings();
    	
		if ( this.checked ){
			$('#blc-links tr.blc-permanently-broken').addClass('blc-permanently-broken-hl');
		} else {
			$('#blc-links tr.blc-permanently-broken').removeClass('blc-permanently-broken-hl');
		}
	});
	
	//Apply/remove highlights when the duration threshold is changed.
	failure_duration_threshold_input.change(function(){
		var new_threshold = parseInt($(this).val());
		//save_highlight_settings();
		if (isNaN(new_threshold) || (new_threshold < 1)) {
			return;
		}
		
		highlight_permanent_failures = highlight_permanent_failures_checkbox.is(':checked');
		
		$('#blc-links tr.blc-row').each(function(index){
			var days_broken = $(this).attr('data-days-broken');
			if ( days_broken >= new_threshold ){
				$(this).addClass('blc-permanently-broken');
				if ( highlight_permanent_failures ){
					$(this).addClass('blc-permanently-broken-hl');
				}
			} else {
				$(this).removeClass('blc-permanently-broken').removeClass('blc-permanently-broken-hl');
			}
		});
	});
	
	//Show/hide table columns dynamically
	$('#blc-column-selector input[type="checkbox"]').change(function(){
		var checkbox = $(this);
		var column_id = checkbox.attr('name').split(/\[|\]/)[1];
		if (checkbox.is(':checked')){
			$('td.column-'+column_id+', th.column-'+column_id, '#blc-links').removeClass('hidden');
		} else {
			$('td.column-'+column_id+', th.column-'+column_id, '#blc-links').addClass('hidden');
		}
		
		//Recalculate colspan's for detail rows to take into account the changed number of 
		//visible columns. Otherwise you can get some ugly layout glitches.
		$('#blc-links tr.blc-link-details td').attr(
			'colspan', 
			$('#blc-column-selector input[type="checkbox"]:checked').length+1
		);
	});
	
	//Unlike other fields in "Screen Options", the links-per-page setting 
	//is handled using straight form submission (POST), not AJAX.
	$('#blc-per-page-apply-button').click(function(){
		$('#adv-settings').submit();	
	});
	
	$('#blc_links_per_page').keypress(function(e){
		if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
			$('#adv-settings').submit();
		}	
	});
	
	//Toggle status code colors when the corresponding checkbox is toggled
	$('#table_color_code_status').click(function(){
		if ( $(this).is(':checked') ){
			$('#blc-links').addClass('color-code-link-status');
		} else {
			$('#blc-links').removeClass('color-code-link-status');
		}
	});
	
	//Show the bulk edit/find & replace form when the user applies the appropriate bulk action 
	$('#doaction, #doaction2').click(function(e){
		var n = $(this).attr('id').substr(2);
		if ( $('select[name="'+n+'"]').val() == 'bulk-edit' ) {
			e.preventDefault();
			//Any links selected?
			if ($('tbody th.check-column input:checked').length > 0){
				$('#bulk-edit').show();
			}
		}
	});
	
	//Hide the bulk edit/find & replace form when "Cancel" is clicked
	$('#bulk-edit .cancel').click(function(){
		$('#bulk-edit').hide();
		return false;
	});
	
	//Minimal input validation for the bulk edit form
	$('#bulk-edit input[type="submit"]').click(function(e){
		if( $('#bulk-edit input[name="search"]').val() == '' ){
			alert('<?php echo esc_js(__('Enter a search string first.', 'broken-link-checker')); ?>');
			$('#bulk-edit input[name="search"]').focus();
			e.preventDefault();
			return;
		}
		
		if ($('tbody th.check-column input:checked').length == 0){
			alert('<?php echo esc_js(__('Select one or more links to edit.', 'broken-link-checker')); ?>');
			e.preventDefault();
		}
	});


});

</script>