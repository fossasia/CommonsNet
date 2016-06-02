jQuery(document).ready(function() {

    var session_var = pirateFormsObject.errors;

    if( (typeof session_var != 'undefined') && (session_var != '') && (typeof jQuery('#contact') != 'undefined') && (typeof jQuery('#contact').offset() != 'undefined') ) {

        jQuery('html, body').animate({
            scrollTop: jQuery('#contact').offset().top
        }, 'slow');
    }
	
	if( typeof jQuery('.pirate_forms_three_inputs').val() != 'undefined' ) {
		 jQuery('.pirate_forms ').each(function(){
			  jQuery(this).find('.pirate_forms_three_inputs').wrapAll('<div class="pirate_forms_three_inputs_wrap">'); 
		  })
	}
});
