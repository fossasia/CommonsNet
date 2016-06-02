jQuery(document).ready(function() {

    /* show/hide reCaptcha */

    var thisOpen = false;
    jQuery('.pirate_forms .form-control').each(function(){
        if ( jQuery(this).val().length > 0 ){
            thisOpen = true;
            jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
            return false;
        }
    });
    if ( thisOpen == false && (typeof jQuery('.pirate_forms textarea').val() != 'undefined') && (jQuery('.pirate_forms textarea').val().length > 0) ) {
        thisOpen = true;
        jQuery('.pirate-forms-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
    }
    jQuery('.pirate_forms input, .pirate_forms textarea').focus(function(){
        if ( !jQuery('.pirate-forms-g-recaptcha').hasClass('recaptcha-display') ) {
            jQuery('.pirate-forms-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
        }
    });

});