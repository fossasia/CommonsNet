jQuery(document).ready(function() {
    jQuery(".pirate-forms-nav-tabs a").click(function (event) {
        event.preventDefault();
        jQuery(this).parent().addClass("active");
        jQuery(this).parent().siblings().removeClass("active");
        var tab = jQuery(this).attr("href");
        jQuery(".pirate-forms-tab-pane").not(tab).css("display", "none");
        jQuery(tab).fadeIn();
    });

    jQuery(".pirate-forms-save-button").click(function (e) {
        e.preventDefault();
        cwpTopUpdateForm();
        return false;
    });
    function cwpTopUpdateForm() {

        startAjaxIntro();

        var data = jQuery(".pirate_forms_contact_settings").serialize();

        jQuery.ajax({
            type: "POST",
            url: cwp_top_ajaxload.ajaxurl,

            data: {
                action: "pirate_forms_save",
                dataSent: data
            },
            success: function (response) {
                console.log(response);
            },
            error: function (MLHttpRequest, textStatus, errorThrown) {
                console.log("There was an error: " + errorThrown);
            }
        });

        endAjaxIntro();
        return false;
    }

    // Starting the AJAX intro animation
    function startAjaxIntro() {
        jQuery(".ajaxAnimation").fadeIn();
    }

    // Ending the AJAX intro animation
    function endAjaxIntro() {
        jQuery(".ajaxAnimation").fadeOut();
    }
});
