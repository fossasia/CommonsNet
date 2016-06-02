jQuery(document).ready(function() {
    var zerif_aboutpage = zerifLiteWelcomeScreenCustomizerObject.aboutpage;
    var zerif_nr_actions_required = zerifLiteWelcomeScreenCustomizerObject.nr_actions_required;

    /* Number of required actions */
    if ((typeof zerif_aboutpage !== 'undefined') && (typeof zerif_nr_actions_required !== 'undefined') && (zerif_nr_actions_required != '0')) {
        jQuery('#accordion-section-themes .accordion-section-title').append('<a href="' + zerif_aboutpage + '"><span class="zerif-lite-actions-count">' + zerif_nr_actions_required + '</span></a>');
    }

    /* Upsell in Customizer (Link to Welcome page) */
    if ( !jQuery( ".zerif-upsells" ).length ) {
        jQuery('#customize-theme-controls > ul').prepend('<li class="accordion-section zerif-upsells">');
    }
    if (typeof zerif_aboutpage !== 'undefined') {
        jQuery('.zerif-upsells').append('<a style="width: 80%; margin: 5px auto 5px auto; display: block; text-align: center;" href="' + zerif_aboutpage + '" class="button" target="_blank">{themeinfo}</a>'.replace('{themeinfo}', zerifLiteWelcomeScreenCustomizerObject.themeinfo));
    }
    if ( !jQuery( ".zerif-upsells" ).length ) {
        jQuery('#customize-theme-controls > ul').prepend('</li>');
    }
});