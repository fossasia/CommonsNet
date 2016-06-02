jQuery(document).ready(function($) {
	
	var load_ask_for_reviews = function(){
		$.ajax({
			type       : "POST",
			data       : {action: 'zerif_lite_dismiss_asking_for_reviews'},
			dataType   : "html",
			url        : zerifLiteAskingForReviewsObject.ajaxurl,
			success    : function(data){
				if( zerifLiteAskingForReviewsObject.ask == 'no') {
					jQuery('.customizer-review-link').remove();
				} else {
					jQuery('#customize-theme-controls').append('<div class="customizer-review-link"><p>Star this theme on <a href="https://wordpress.org/support/view/theme-reviews/zerif-lite" target="_blank">WordPress.org</a>!</p><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span></div>');
				}
			},
			error : function(jqXHR, textStatus, errorThrown) {   
				console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
			}
		});
    }
	
    var dismiss_ask_for_reviews = function(){
		$.ajax({
			type       : "POST",
			data       : {action: 'zerif_lite_dismiss_asking_for_reviews', ask: 'no'},
			dataType   : "html",
			url        : zerifLiteAskingForReviewsObject.ajaxurl,
			success    : function(data){
				jQuery('.customizer-review-link').remove();
			},
			error : function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR + " :: " + textStatus + " :: " + errorThrown);
			}
		});
    }
	
	$('.customizer-review-link a').die("click").live("click",function() {
        dismiss_ask_for_reviews();
    });
    load_ask_for_reviews();
});