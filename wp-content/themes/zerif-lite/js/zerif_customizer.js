jQuery(document).ready(function() {

	/* Upsells in customizer (Documentation link and Upgrade to PRO link */
	if( !jQuery( ".zerif-upsells" ).length ) {
		jQuery('#customize-theme-controls > ul').prepend('<li class="accordion-section zerif-upsells">');
	}

	if( jQuery( ".zerif-upsells" ).length ) {

		jQuery('.zerif-upsells').append('<a style="width: 80%; margin: 5px auto 5px auto; display: block; text-align: center;" href="http://themeisle.com/documentation-zerif-lite" class="button" target="_blank">{documentation}</a>'.replace('{documentation}', zerifLiteCustomizerObject.documentation));

	}
	jQuery('.preview-notice').append('<a class="zerif-upgrade-to-pro-button" href="http://themeisle.com/themes/zerif-pro-one-page-wordpress-theme/" class="button" target="_blank">{pro}</a>'.replace('{pro}',zerifLiteCustomizerObject.pro));

	if ( !jQuery( ".zerif-upsells" ).length ) {
		jQuery('#customize-theme-controls > ul').prepend('</li>');
	}

	jQuery( '.ui-state-default' ).on( 'mousedown', function() {
		jQuery( '#customize-header-actions #save' ).trigger( 'click' );

	});

	/* Move our focus widgets in the our focus panel */
	wp.customize.section( 'sidebar-widgets-sidebar-ourfocus' ).panel( 'panel_ourfocus' );
	wp.customize.section( 'sidebar-widgets-sidebar-ourfocus' ).priority( '2' );

	/* Move our team widgets in the our team panel */
	wp.customize.section( 'sidebar-widgets-sidebar-ourteam' ).panel( 'panel_ourteam' );
	wp.customize.section( 'sidebar-widgets-sidebar-ourteam' ).priority( '2' );
	
	/* Move testimonial widgets in the testimonials panel */
	wp.customize.section( 'sidebar-widgets-sidebar-testimonials' ).panel( 'panel_testimonials' );
	wp.customize.section( 'sidebar-widgets-sidebar-testimonials' ).priority( '2' );
	
	/* Move about us widgets in the about us panel */
	wp.customize.section( 'sidebar-widgets-sidebar-aboutus' ).panel( 'panel_about' );
	wp.customize.section( 'sidebar-widgets-sidebar-aboutus' ).priority( '7' );
	
	/* Tooltips for General Options */
	jQuery('#customize-control-zerif_use_safe_font label').append('<span class="dashicons dashicons-info zerif-moreinfo-icon"></span><div class="zerif-moreinfo-content">Zerif Lite main font is Montserrat, which only supports the Latin script. <br><br> If you are using other scripts like Cyrillic or Greek , you need to check this box to enable the safe fonts for better compatibility.</div>');
	
	jQuery('#customize-control-zerif_accessibility label').append('<div class="dashicons dashicons-info zerif-moreinfo-icon"></div><div class="zerif-moreinfo-content">Web accessibility means that people with disabilities can use the Web. More specifically, Web accessibility means that people with disabilities can perceive, understand, navigate, and interact with the Web, and that they can contribute to the Web. <br><br>Web accessibility also benefits others, including older people with changing abilities due to aging.<br><br>By checking this box, you will enable this option on the site.</div>');
	
	jQuery('#customize-control-zerif_disable_smooth_scroll label').append('<span class="dashicons dashicons-info zerif-moreinfo-icon"></span><div class="zerif-moreinfo-content">Smooth scrolling can be very useful if you read a lot of long pages. Normally, when you press Page Down, the view jumps directly down one page. <br><br>With smooth scrolling, it slides down smoothly, so you can see how much it scrolls. This makes it easier to resume reading from where you were before.<br><br>By checking this box, the smooth scroll will be disabled.</div>');
	
	jQuery('#customize-control-zerif_disable_preloader label').append('<span class="dashicons dashicons-info zerif-moreinfo-icon"></span><div class="zerif-moreinfo-content">The preloader is the circular progress element that first appears on the site. When the loader finishes its progress animation, the whole page elements are revealed. <br><br>The preloader is used as a creative way to make waiting a bit less boring for the visitor.<br><br>By checking this box, the preloader will be disabled.</div>');
	
	jQuery('.zerif-moreinfo-icon').hover(function() {
		jQuery(this).next('.zerif-moreinfo-content').show();
	},function(){
		jQuery(this).next('.zerif-moreinfo-content').hide();
	});
});