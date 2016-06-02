jQuery(document).ready(function() {

	/* If there are required actions, add an icon with the number of required actions in the About Zerif page -> Actions required tab */
    var zerif_nr_actions_required = zerifLiteWelcomeScreenObject.nr_actions_required;

    if ( (typeof zerif_nr_actions_required !== 'undefined') && (zerif_nr_actions_required != '0') ) {
        jQuery('li.zerif-lite-w-red-tab a').append('<span class="zerif-lite-actions-count">' + zerif_nr_actions_required + '</span>');
    }

    /* Dismiss required actions */
    jQuery(".zerif-dismiss-required-action").click(function(){

        var id= jQuery(this).attr('id');
        console.log(id);
        jQuery.ajax({
            type       : "GET",
            data       : { action: 'zerif_lite_dismiss_required_action',dismiss_id : id },
            dataType   : "html",
            url        : zerifLiteWelcomeScreenObject.ajaxurl,
            beforeSend : function(data,settings){
				jQuery('.zerif-lite-tab-pane#actions_required h1').append('<div id="temp_load" style="text-align:center"><img src="' + zerifLiteWelcomeScreenObject.template_directory + '/inc/admin/welcome-screen/img/ajax-loader.gif" /></div>');
            },
            success    : function(data){
				jQuery("#temp_load").remove(); /* Remove loading gif */
                jQuery('#'+ data).parent().remove(); /* Remove required action box */

                var zerif_lite_actions_count = jQuery('.zerif-lite-actions-count').text(); /* Decrease or remove the counter for required actions */
                if( typeof zerif_lite_actions_count !== 'undefined' ) {
                    if( zerif_lite_actions_count == '1' ) {
                        jQuery('.zerif-lite-actions-count').remove();
                        jQuery('.zerif-lite-tab-pane#actions_required').append('<p>' + zerifLiteWelcomeScreenObject.no_required_actions_text + '</p>');
                    }
                    else {
                        jQuery('.zerif-lite-actions-count').text(parseInt(zerif_lite_actions_count) - 1);
                    }
                }
            },
            error     : function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
            }
        });
    });

	/* Tabs in welcome page */
	function zerif_welcome_page_tabs(event) {
		jQuery(event).parent().addClass("active");
        jQuery(event).parent().siblings().removeClass("active");
        var tab = jQuery(event).attr("href");
        jQuery(".zerif-lite-tab-pane").not(tab).css("display", "none");
        jQuery(tab).fadeIn();
	}

	var zerif_actions_anchor = location.hash;

	if( (typeof zerif_actions_anchor !== 'undefined') && (zerif_actions_anchor != '') ) {
		zerif_welcome_page_tabs('a[href="'+ zerif_actions_anchor +'"]');
	}

    jQuery(".zerif-lite-nav-tabs a").click(function(event) {
        event.preventDefault();
		zerif_welcome_page_tabs(this);
    });

		/* Tab Content height matches admin menu height for scrolling purpouses */
	 $tab = jQuery('.zerif-lite-tab-content > div');
	 $admin_menu_height = jQuery('#adminmenu').height();
	 if( (typeof $tab !== 'undefined') && (typeof $admin_menu_height !== 'undefined') )
	 {
		 $newheight = $admin_menu_height - 180;
		 $tab.css('min-height',$newheight);
	 }

});
