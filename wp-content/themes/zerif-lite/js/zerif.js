/* =================================

 LOADER

 =================================== */

// makes sure the whole site is loaded

jQuery(window).load(function() {

    // will first fade out the loading animation

    jQuery(".status").fadeOut();

    // will fade out the whole DIV that covers the website.

    jQuery(".preloader").delay(1000).fadeOut("slow");


    jQuery('.carousel').carousel('pause');

    // Focus styles for menus.
    jQuery( '.navbar-collapse' ).find( 'a' ).on( 'focus blur', function() {
        jQuery( this ).parents().toggleClass( 'link-focus' );
    });

});

/*** DROPDOWN FOR MOBILE MENU */
var callback_mobile_dropdown = function () {

    if( jQuery( '.wr-megamenu-container' ).length <= 0 ) {
        var navLi = jQuery('#site-navigation li');
        navLi.each(function(){
            if ( jQuery(this).find('ul').length > 0 && !jQuery(this).hasClass('has_children') ){
                jQuery(this).addClass('has_children');
                jQuery(this).find('a').first().after('<p class="dropdownmenu"></p>');
            }
        });
        jQuery('.dropdownmenu').click(function(){
            if( jQuery(this).parent('li').hasClass('this-open') ){
                jQuery(this).parent('li').removeClass('this-open');
            }else{
                jQuery(this).parent('li').addClass('this-open');
            }
        });
        navLi.find('a').click(function(){
            jQuery('.navbar-toggle').addClass('collapsed');
            jQuery('.collapse').removeClass('in');
        });
    }

};
jQuery(document).ready(callback_mobile_dropdown);

jQuery(document).ready(function() {
    var current_height = jQuery('.header .container').height();
    jQuery('.header').css('min-height',current_height);

});


/* show/hide reCaptcha */
jQuery(document).ready(function() {

    var thisOpen = false;
    jQuery('.contact-form .form-control').each(function(){
        if ( jQuery(this).val().length > 0 ){
            thisOpen = true;
            jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
            return false;
        }
    });
    if ( thisOpen == false && (typeof jQuery('.contact-form textarea').val() != 'undefined') && (jQuery('.contact-form textarea').val().length > 0) ) {
        thisOpen = true;
        jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
    }
    jQuery('.contact-form input, .contact-form textarea').focus(function(){
        if ( !jQuery('.zerif-g-recaptcha').hasClass('recaptcha-display') ) {
            jQuery('.zerif-g-recaptcha').css('display','block').delay(1000).css('opacity','1');
        }
    });

});

/* =================================

 ===  Bootstrap Fix              ====

 =================================== */

if (navigator.userAgent.match(/IEMobile\/10\.0/)) {

    var msViewportStyle = document.createElement('style')

    msViewportStyle.appendChild(

        document.createTextNode(

            '@-ms-viewport{width:auto!important}'

        )

    )

    document.querySelector('head').appendChild(msViewportStyle)

}

/* =================================

 ===  STICKY NAV                 ====

 =================================== */

jQuery(document).ready(function() {

    // Sticky Header - http://jqueryfordesigners.com/fixed-floating-elements/
	if( typeof jQuery('#main-nav') != 'undefined' && typeof jQuery('#main-nav').offset() != 'undefined' ) {
		var top = jQuery('#main-nav').offset().top - parseFloat(jQuery('#main-nav').css('margin-top').replace(/auto/, 0));
	}

    jQuery(window).scroll(function (event) {

        // what the y position of the scroll is

        var y = jQuery(this).scrollTop();



        // whether that's below the form

        if (y >= top) {

            // if so, ad the fixed class

            jQuery('#main-nav').addClass('fixed');

        } else {

            // otherwise remove it

            jQuery('#main-nav').removeClass('fixed');

        }

    });

});


/*=================================

 ===  SMOOTH SCROLL             ====

 =================================== */

jQuery(document).ready(function(){
    jQuery('#site-navigation a[href*="#"]:not([href="#"]), header.header a[href*="#"]:not([href="#"])').bind('click',function () {
        var headerHeight;
        var hash    = this.hash;
        var idName  = hash.substring(1);    // get id name
        var alink   = this;                 // this button pressed
        // check if there is a section that had same id as the button pressed
        if ( jQuery('section [id*=' + idName + ']').length > 0 && jQuery(window).width() >= 751 ){
            jQuery('.current').removeClass('current');
            jQuery(alink).parent('li').addClass('current');
        }else{
            jQuery('.current').removeClass('current');
        }
        if ( jQuery(window).width() >= 751 ) {
            headerHeight = jQuery('#main-nav').height();
        } else {
            headerHeight = 0;
        }
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
            var target = jQuery(this.hash);
            target = target.length ? target : jQuery('[name=' + this.hash.slice(1) +']');
            if (target.length) {
                jQuery('html,body').animate({
                    scrollTop: target.offset().top - headerHeight + 10
                }, 1200);
                return false; 
            }
        }
    });
});

jQuery(document).ready(function(){
    var headerHeight;
    jQuery('.current').removeClass('current');
    jQuery('#site-navigation a[href$="' + window.location.hash + '"]').parent('li').addClass('current');
    if ( jQuery(window).width() >= 751 ) {
        headerHeight = jQuery('#main-nav').height();
    } else {
        headerHeight = 0;
    }
    if (location.pathname.replace(/^\//,'') == window.location.pathname.replace(/^\//,'') && location.hostname == window.location.hostname) {
        var target = jQuery(window.location.hash);
        if (target.length) {
            jQuery('html,body').animate({
                scrollTop: target.offset().top - headerHeight + 10
            }, 1200);
            return false;
        }
    }
});

/* TOP NAVIGATION MENU SELECTED ITEMS */

function zerif_lite_scrolled() {

    if ( jQuery(window).width() >= 751 ) {

        var zerif_scrollTop = jQuery(window).scrollTop();       // cursor position
        var headerHeight = jQuery('#main-nav').outerHeight();   // header height
        var isInOneSection = 'no';                              // used for checking if the cursor is in one section or not

        // for all sections check if the cursor is inside a section
        jQuery("section, header").each( function() {
            var thisID = '#' + jQuery(this).attr('id');           // section id
            var zerif_offset = jQuery(this).offset().top;         // distance between top and our section
            var thisHeight  = jQuery(this).outerHeight();         // section height
            var thisBegin   = zerif_offset - headerHeight;                      // where the section begins
            var thisEnd     = zerif_offset + thisHeight - headerHeight;         // where the section ends

            // if position of the cursor is inside of the this section
            if ( zerif_scrollTop >= thisBegin && zerif_scrollTop <= thisEnd ) {
                isInOneSection = 'yes';
                jQuery('.current').removeClass('current');
                jQuery('#site-navigation a[href$="' + thisID + '"]').parent('li').addClass('current');    // find the menu button with the same ID section
                return false;
            }
            if (isInOneSection == 'no') {
                jQuery('.current').removeClass('current');
            }
        });
    }
}
jQuery(window).on('scroll',zerif_lite_scrolled);

/* ================================

 ===  PARALLAX                  ====

 ================================= */

jQuery(document).ready(function(){

    var jQuerywindow = jQuery(window);

    jQuery('div[data-type="background"], header[data-type="background"], section[data-type="background"]').each(function(){

        var jQuerybgobj = jQuery(this);

        jQuery(window).scroll(function() {

            var yPos = -(jQuerywindow.scrollTop() / jQuerybgobj.data('speed'));

            var coords = '50% '+ yPos + 'px';

            jQuerybgobj.css({

                backgroundPosition: coords

            });

        });

    });

});



/* ================================

 ===  KNOB                      ====

 ================================= */

jQuery(function() {

    jQuery(".skill1").knob({

        'max':100,

        'width': 64,

        'readOnly':true,

        'inputColor':' #FFFFFF ',

        'bgColor':' #222222 ',

        'fgColor':' #e96656 '

    });

    jQuery(".skill2").knob({

        'max':100,

        'width': 64,

        'readOnly':true,

        'inputColor':' #FFFFFF ',

        'bgColor':' #222222 ',

        'fgColor':' #34d293 '

    });

    jQuery(".skill3").knob({

        'max': 100,

        'width': 64,

        'readOnly': true,

        'inputColor':' #FFFFFF ',

        'bgColor':' #222222 ',

        'fgColor':' #3ab0e2 '

    });

    jQuery(".skill4").knob({

        'max': 100,

        'width': 64,

        'readOnly': true,

        'inputColor':' #FFFFFF ',

        'bgColor':' #222222 ',

        'fgColor':' #E7AC44 '

    });

});



/* ======================================

 ============ MOBILE NAV =============== */

jQuery('.navbar-toggle').on('click', function () {

    jQuery(this).toggleClass('active');

});


/* SETS THE HEADER HEIGHT */
jQuery(window).load(function(){
    setminHeightHeader();
});
jQuery(window).resize(function() {
    setminHeightHeader();
});
function setminHeightHeader()
{
    jQuery('#main-nav').css('min-height','75px');
    jQuery('.header').css('min-height','75px');
    var minHeight = parseInt( jQuery('#main-nav').height() );
    jQuery('#main-nav').css('min-height',minHeight);
    jQuery('.header').css('min-height',minHeight);
}
/* - */


/* STICKY FOOTER */
jQuery(window).load(fixFooterBottom);
jQuery(window).resize(fixFooterBottom);

function fixFooterBottom(){

    var header      = jQuery('header.header');
    var footer      = jQuery('footer#footer');
    var content     = jQuery('.site-content > .container');

    content.css('min-height', '1px');

    var headerHeight  = header.outerHeight();
    var footerHeight  = footer.outerHeight();
    var contentHeight = content.outerHeight();
    var windowHeight  = jQuery(window).height();

    var totalHeight = headerHeight + footerHeight + contentHeight;

    if (totalHeight<windowHeight){
        content.css('min-height', windowHeight - headerHeight - footerHeight );
    }else{
        content.css('min-height','1px');
    }
}


/*** CENTERED MENU */
var callback_menu_align = function () {

    var headerWrap    = jQuery('.header');
    var navWrap     = jQuery('#site-navigation');
    var logoWrap    = jQuery('.responsive-logo');
    var containerWrap   = jQuery('.container');
    var classToAdd    = 'menu-align-center';

    if ( headerWrap.hasClass(classToAdd) )
    {
        headerWrap.removeClass(classToAdd);
    }
    var logoWidth     = logoWrap.outerWidth();
    var menuWidth     = navWrap.outerWidth();
    var containerWidth  = containerWrap.width();

    if ( menuWidth + logoWidth > containerWidth ) {
        headerWrap.addClass(classToAdd);
    }
    else
    {
        if ( headerWrap.hasClass(classToAdd) )
        {
            headerWrap.removeClass(classToAdd);
        }
    }
}
jQuery(window).load(callback_menu_align);
jQuery(window).resize(callback_menu_align);

var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

/* Rollover on mobile devices */
if( isMobile.any() ) {

    /* Our team section */
    jQuery('.team-member').on('click', function(){
        jQuery('.team-member-open').removeClass('team-member-open');
        jQuery(this).addClass('team-member-open');
        event.stopPropagation();
    });
    jQuery("html").click(function() {
        jQuery('.team-member-open').removeClass('team-member-open');
    });

    /* Portfolio section */
    jQuery(document).ready(function(){
        jQuery('.cbp-rfgrid li').prepend('<p class="cbp-rfgrid-tr"></p>');
    });
    jQuery('.cbp-rfgrid li').on('click', function(){
        if ( !jQuery(this).hasClass('cbp-rfgrid-open') ){
            jQuery('.cbp-rfgrid-tr').css('display','block');
            jQuery('.cbp-rfgrid-open').removeClass('cbp-rfgrid-open');

            jQuery(this).addClass('cbp-rfgrid-open');
            jQuery(this).find('.cbp-rfgrid-tr').css('display','none');
            event.stopPropagation();
        }
    });
    jQuery("html").click(function() {
        jQuery('.cbp-rfgrid-tr').css('display','block');
        jQuery('.cbp-rfgrid-open').removeClass('cbp-rfgrid-open');
    });

}

/* latest news */
jQuery(window).load(zerif_home_latest_news);
jQuery(window).resize(zerif_home_latest_news);
function zerif_home_latest_news(){
    if( jQuery( '#carousel-homepage-latestnews').length > 0 ) {
        jQuery( '#carousel-homepage-latestnews div.item' ).height('auto');
        if( isMobile.any() || (!isMobile.any() && jQuery('.container').outerWidth()>768) ) {

            if( jQuery( '#carousel-homepage-latestnews div.item' ).length < 2 ) {
                jQuery( '#carousel-homepage-latestnews > a' ).css('display','none');
            }
            var maxheight = 0;
            jQuery( '#carousel-homepage-latestnews div.item' ).each(function(){
                if( jQuery(this).height() > maxheight ) {
                    maxheight = jQuery(this).height();
                }
            });
            jQuery( '#carousel-homepage-latestnews div.item' ).height(maxheight);
        }
    }
}

/* fix for IE9 placeholders */

jQuery(document).ready(function(){

    if (document.createElement("input").placeholder == undefined) {

        jQuery('.contact-form input, .contact-form textarea').focus(function () {
            if ( (jQuery(this).attr('placeholder') != '') && (jQuery(this).val() == jQuery(this).attr('placeholder')) ) {
                jQuery(this).val('').removeClass('zerif-hasPlaceholder');
            }
        }).blur(function () {
            if ( (jQuery(this).attr('placeholder') != '') && (jQuery(this).val() == '' || (jQuery(this).val() == jQuery(this).attr('placeholder')))) {
                jQuery(this).val(jQuery(this).attr('placeholder')).addClass('zerif-hasPlaceholder');
            }
        });

        jQuery('.contact-form input').blur();
        jQuery('.contact-form textarea').blur();

        jQuery('form.contact-form').submit(function () {
            jQuery(this).find('.zerif-hasPlaceholder').each(function() { jQuery(this).val(''); });
        });
    }
});

/* Header section */
jQuery(window).load(parallax_effect);
jQuery(window).resize(parallax_effect);

function parallax_effect(){

    if( jQuery('#parallax_move').length>0 ) {
        var scene = document.getElementById('parallax_move');
        var window_width = jQuery(window).outerWidth();
        jQuery('#parallax_move').css({
            'width':            window_width + 120,
            'margin-left':      -60,
            'margin-top':       -60,
            'position':         'absolute',
        });
        var h = jQuery('header#home').outerHeight();
        jQuery('#parallax_move').children().each(function(){
            jQuery(this).css({
                'height': h+100,
            });
        });
        if( !isMobile.any() ) {
            var parallax = new Parallax(scene);
        } else {
            jQuery('#parallax_move').css({
                'z-index': '0',
            });
            jQuery('#parallax_move .layer').css({
                'position': 'absolute',
                'top': '0',
                'left': '0',
                'z-index': '1',
            });
        }
    }

}


/* testimonial Masonry style */
var window_width_old;
var exist_class = false;
jQuery(document).ready(function(){
    if( jQuery('.testimonial-masonry').length>0 ){
        exist_class = true;
        window_width_old = jQuery('.container').outerWidth();
        if( window_width_old < 970 ) {
            jQuery('.testimonial-masonry').zerifgridpinterest({columns: 1,selector: '.feedback-box'});
        } else {
            jQuery('.testimonial-masonry').zerifgridpinterest({columns: 3,selector: '.feedback-box'});
        }
    }
});

jQuery(window).resize(function() {
    if( window_width_old != jQuery('.container').outerWidth() && exist_class === true ){
        window_width_old = jQuery('.container').outerWidth();
        if( window_width_old < 970 ) {
            jQuery('.testimonial-masonry').zerifgridpinterest({columns: 1,selector: '.feedback-box'});
        } else {
            jQuery('.testimonial-masonry').zerifgridpinterest({columns: 3,selector: '.feedback-box'});
        }
    }
});

;(function ($, window, document, undefined) {
    var defaults = {
        columns:                3,
        selector:               'div',
        excludeParentClass:     '',
    };
    function ZerifGridPinterest(element, options) {
        this.element    = element;
        this.options    = $.extend({}, defaults, options);
        this.defaults   = defaults;
        this.init();
    }
    ZerifGridPinterest.prototype.init = function () {
        var self            = this,
            $container      = $(this.element);
        $select_options = $(this.element).children();
        self.make_magic( $container, $select_options );
    };
    ZerifGridPinterest.prototype.make_magic = function (container) {
        var self            = this;
        $container      = $(container),
            columns_height  = [],
            prefix          = 'zerif',
            unique_class    = prefix + '_grid_' + self.make_unique();
        local_class     = prefix + '_grid';
        var classname;
        var substr_index    = this.element.className.indexOf(prefix+'_grid_');
        if( substr_index>-1 ) {
            classname = this.element.className.substr( 0, this.element.className.length-unique_class.length-local_class.length-2 );
        } else {
            classname = this.element.className;
        }
        var my_id;
        if( this.element.id == '' ) {
            my_id = prefix+'_id_' + self.make_unique();
        } else {
            my_id = this.element.id;
        }
        $container.after('<div id="' + my_id + '" class="' + classname + ' ' + local_class + ' ' + unique_class + '"></div>');
        var i;
        for(i=1; i<=this.options.columns; i++){
            columns_height.push(0);
            var first_cols = '';
            var last_cols = '';
            if( i%self.options.columns == 1 ) { first_cols = prefix + '_grid_first'; }
            if( i%self.options.columns == 0 ) { first_cols = prefix + '_grid_last'; }
            $('.'+unique_class).append('<div class="' + prefix + '_grid_col_' + this.options.columns +' ' + prefix + '_grid_column_' + i +' ' + first_cols + ' ' + last_cols + '"></div>');
        }
        if( this.element.className.indexOf(local_class)<0 ){
            $container.children(this.options.selector).each(function(index){
                var min = Math.min.apply(null,columns_height);
                var this_index = columns_height.indexOf(min)+1;
                $(this).attr(prefix+'grid-attr','this-'+index).appendTo('.'+unique_class +' .' + prefix + '_grid_column_'+this_index);
                columns_height[this_index-1] = $('.'+unique_class +' .' + prefix + '_grid_column_'+this_index).height();
            });
        } else {
            var no_boxes = $container.find(this.options.selector).length;
            var i;
            for( i=0; i<no_boxes; i++ ){
                var min = Math.min.apply(null,columns_height);
                var this_index = columns_height.indexOf(min)+1;
                $('#'+this.element.id).find('['+prefix+'grid-attr="this-'+i+'"]').appendTo('.'+unique_class +' .' + prefix + '_grid_column_'+this_index);
                columns_height[this_index-1] = $('.'+unique_class +' .' + prefix + '_grid_column_'+this_index).height();
            }
        }
        $container.remove();
    }
    ZerifGridPinterest.prototype.make_unique = function () {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for( var i=0; i<10; i++ )
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
    }
    $.fn.zerifgridpinterest = function (options) {
        return this.each(function () {
            var value = '';
            if (!$.data(this, value)) {
                $.data(this, value, new ZerifGridPinterest(this, options) );
            }
        });
    }
})(jQuery);


/* mobile background fix */
var initHeight  = 0,
    initWidth   = 0;
var initViewMode,
    onlyInit = true; 
jQuery( document ).ready( function() { 
    initViewMode = type_view();
    mobile_bg_fix();
} );
jQuery( window ).resize( mobile_bg_fix );

function mobile_bg_fix() {
    if( isMobile.any() && jQuery( 'body.custom-background' ) ){
        var viewMode = type_view();
        if ( initViewMode != viewMode || onlyInit == true ) {
            jQuery( '.mobile-bg-fix-img' ).css( {
                'width' : window.innerWidth,
                'height': window.innerHeight + 100
            } );
            initViewMode = viewMode;
            if ( onlyInit == true ) { 
                onlyInit = false; 
                bodyClass   = jQuery( 'body.custom-background' );
                imgURL      = bodyClass.css( 'background-image' );
                imgSize     = bodyClass.css( 'background-size' );
                imgPosition = bodyClass.css( 'background-position' );
                imgRepeat   = bodyClass.css( 'background-repeat' );
                jQuery( '#mobilebgfix' ).addClass( 'mobile-bg-fix-wrap' ).find( '.mobile-bg-fix-img' ).css( {
                    'background-size':      imgSize,
                    'background-position':  imgPosition,
                    'background-repeat':    imgRepeat,
                    'background-image':     imgURL
                    } );
            }
        }
    }
}

function type_view() {
    var initHeight  = window.innerHeight;
    var initWidth   = window.innerWidth;
    if ( initWidth <= initHeight ) {
        return 'portrait';
    }
    return 'landscape';
}



/* Menu levels */
jQuery( document ).ready( function() {
  jQuery( '#site-navigation' ).zerifsubmenuorientation();
} );
;(function ($, window) {
    var defaults = {
        // 'true'   -> if there is a big submenu all submenu will be aligned to the right
        // 'false'  -> Only big submenu will be aligned to the right
        allItems: false,
      };
    function ZerifSubmenuOrientation(element, options) {
      this.element  = element;
      this.options  = $.extend({}, defaults, options);
      this.defaults = defaults;
      this.init();
    }
    ZerifSubmenuOrientation.prototype.init = function () {
      var self            = this,
          $container      = $(this.element),
          $select_options = $(this.element).children();
      var resize_finish;
      if( self.options.allItems !== true ) {
        $(window).resize(function() {
            clearTimeout(resize_finish);
            resize_finish = setTimeout( function () {
                self.make_magic($container, $select_options);
            }, 11);
        });
      }
      self.make_magic($container, $select_options);
      if( self.options.allItems !== true ) {
        setTimeout(function() {
            $(window).resize();
        }, 500);
      }
    };
    ZerifSubmenuOrientation.prototype.make_magic = function (container, select_options) {
      var self            = this,
          $container      = $(container),
          $select_options = $(select_options);
      var itemWrap;
      if( $container[0].tagName == 'UL' ) {
        itemWrap = $container[0];
      } else {
        itemWrap = $container.find( 'ul' )[0];
      }
      var windowsWidth = window.innerWidth;
	  if( typeof itemWrap != 'undefined' ) {
		  var itemId = '#' + itemWrap.id;
		  $( itemId ).children( 'li' ).each( function() {
			if ( this.id == '' ) { return; }
			var max_deep = self.max_deep( '#'+this.id );
			var offsetLeft        = $( "#"+this.id ).offset().left;
			var submenuWidthItem  = $( "#"+this.id ).find( 'ul' ).width();
			var submenuTotalWidth = max_deep * submenuWidthItem;
			if( submenuTotalWidth > 0 && windowsWidth < offsetLeft + submenuTotalWidth ) {
			  if( self.options.allItems === true ) {
				$( '#'+itemWrap.id ).addClass( 'menu-item-open-left-all' );
				return false;
			  }
			  $( '#'+this.id ).addClass( 'menu-item-open-left' );
			} else if( $( '#'+this.id ).hasClass( 'menu-item-open-left' ) ) {
			  $( '#'+this.id ).removeClass( 'menu-item-open-left' );
			}
		  } );
	  }
    };
    ZerifSubmenuOrientation.prototype.max_deep = function ( item ) {
      var maxDepth      = -1, 
          currentDepth  = -1;
      $( item + " li:not(:has(ul))").each(function() {
        currentDepth = $(this).parents("ul").length;
        if (currentDepth > maxDepth) {
           maxDepth = currentDepth;
        }
      });
      return maxDepth - 1;
    }
    $.fn.zerifsubmenuorientation = function (options) {
      return this.each(function () {
        var value = '';
          if (!$.data(this, value)) {
              $.data(this, value, new ZerifSubmenuOrientation(this, options) );
          }
      });
    }
})(jQuery,window);
