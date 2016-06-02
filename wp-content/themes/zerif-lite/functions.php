<?php
/**
 * Zerif Lite functions and definitions
 */

function zerif_setup() {    
	
    global $content_width;
	
    if (!isset($content_width)) {
        $content_width = 640;
    }

    /*
     * Make theme available for translation.
     * Translations can be filed in the /languages/ directory.
     * If you're building a theme based on zerif, use a find and replace
     * to change 'zerif-lite' to the name of your theme in all the template files
     */
    load_theme_textdomain('zerif-lite', get_template_directory() . '/languages'); 

    add_theme_support('automatic-feed-links');

    /*
     * Enable support for Post Thumbnails on posts and pages.
     */
    add_theme_support('post-thumbnails');

    /* Set the image size by cropping the image */
    add_image_size('post-thumbnail', 250, 250, true);
    add_image_size( 'post-thumbnail-large', 750, 500, true ); /* blog thumbnail */
    add_image_size( 'post-thumbnail-large-table', 600, 300, true ); /* blog thumbnail for table */
    add_image_size( 'post-thumbnail-large-mobile', 400, 200, true ); /* blog thumbnail for mobile */
    add_image_size('zerif_project_photo', 285, 214, true);
    add_image_size('zerif_our_team_photo', 174, 174, true);

    /* Register primary menu */
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'zerif-lite'),
    ));

    /* Enable support for Post Formats. */
    add_theme_support('post-formats', array('aside', 'image', 'video', 'quote', 'link'));

    /* Setup the WordPress core custom background feature. */
    add_theme_support('custom-background', apply_filters('zerif_custom_background_args', array(
        'default-color' => 'ffffff',
        'default-image' => get_stylesheet_directory_uri() . "/images/bg.jpg",
    )));

    /* Enable support for HTML5 markup. */
    add_theme_support('html5', array(
        'comment-list',
        'search-form',
        'comment-form',
        'gallery',
    ));
	
	/* Enable support for title-tag */
	add_theme_support( 'title-tag' );

	/* Custom template tags for this theme. */
	require get_template_directory() . '/inc/template-tags.php';

	/* Custom functions that act independently of the theme templates. */
	require get_template_directory() . '/inc/extras.php';

	/* Customizer additions. */
	require get_template_directory() . '/inc/customizer.php';

	/* Enables user customization via WordPress plugin API. */
	require get_template_directory() . '/inc/hooks.php';

	/* tgm-plugin-activation */
    require_once get_template_directory() . '/class-tgm-plugin-activation.php';

    /* woocommerce support */
	add_theme_support( 'woocommerce' );
		
	/*******************************************/
    /*************  Welcome screen *************/
    /*******************************************/

	if ( is_admin() ) {

        global $zerif_required_actions;

        /*
         * id - unique id; required
         * title
         * description
         * check - check for plugins (if installed)
         * plugin_slug - the plugin's slug (used for installing the plugin)
         *
         */
        $zerif_required_actions = array(
			array(
                "id" => 'zerif-lite-req-ac-frontpage-latest-news',
                "title" => esc_html__( 'Get the one page template' ,'zerif-lite' ),
                "description"=> esc_html__( 'If you just installed Zerif Lite, and are not able to see the one page template, you need to go to Settings -> Reading , Front page displays and select "Your latest posts".','zerif-lite' ),
				"check" => zerif_lite_is_not_latest_posts()
            ),
            array(
                "id" => 'zerif-lite-req-ac-install-pirate-forms',
                "title" => esc_html__( 'Install Pirate Forms' ,'zerif-lite' ),
                "description"=> esc_html__( 'In the next updates, Zerif Lite\'s default contact form will be removed. Please make sure you install the Pirate Forms plugin to keep your site updated, and experience a smooth transition to the latest version.','zerif-lite' ),
                "check" => defined("PIRATE_FORMS_VERSION"),
                "plugin_slug" => 'pirate-forms'
            ),
            array(
                "id" => 'zerif-lite-req-ac-check-pirate-forms',
                "title" => esc_html__( 'Check the contact form after installing Pirate Forms' ,'zerif-lite' ),
                "description"=> esc_html__( "After installing the Pirate Forms plugin, please make sure you check your frontpage contact form is working fine. Also, if you use Zerif Lite in other language(s) please make sure the translation is ok. If not, please translate the contact form again.",'zerif-lite' ),
            )
        );

		require get_template_directory() . '/inc/admin/welcome-screen/welcome-screen.php';
	}
}

add_action('after_setup_theme', 'zerif_setup');

function zerif_lite_is_not_latest_posts() {
	return ('posts' == get_option( 'show_on_front' ) ? true : false);
}

/**
 * Register widgetized area and update sidebar with default widgets.
 */

function zerif_widgets_init() {    

	register_sidebar(array(
        'name' => __('Sidebar', 'zerif-lite'),
        'id' => 'sidebar-1',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    register_sidebar(array(
        'name' => __('About us section', 'zerif-lite'),
        'id' => 'sidebar-aboutus',
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));

    register_sidebars( 
        3, 
        array(
            'name'          => __('Footer area %d','zerif-lite'),
            'id'            => 'zerif-sidebar-footer',
            'before_widget' => '<aside id="%1$s" class="widget footer-widget-footer %2$s">',
            'after_widget'  => '</aside>',
            'before_title'  => '<h1 class="widget-title">',
            'after_title'   => '</h1>'
        ) 
    );
    
}

add_action('widgets_init', 'zerif_widgets_init');

function zerif_slug_fonts_url() {
    $fonts_url = '';
     /* Translators: If there are characters in your language that are not
    * supported by Lora, translate this to 'off'. Do not translate
    * into your own language.
    */
    $lato = _x( 'on', 'Lato font: on or off', 'zerif-lite' );
    $homemade = _x( 'on', 'Homemade font: on or off', 'zerif-lite' );
    /* Translators: If there are characters in your language that are not
    * supported by Open Sans, translate this to 'off'. Do not translate
    * into your own language.
    */
    $monserrat = _x( 'on', 'Monserrat font: on or off', 'zerif-lite' );

    $zerif_use_safe_font = get_theme_mod('zerif_use_safe_font');
    
    if ( ( 'off' !== $lato || 'off' !== $monserrat || 'off' !== $homemade ) && isset($zerif_use_safe_font) && ($zerif_use_safe_font != 1) ) {
        $font_families = array();

        if ( 'off' !== $lato ) {
            $font_families[] = 'Lato:300,400,700,400italic';
        }
         if ( 'off' !== $monserrat ) {
            $font_families[] = 'Montserrat:700';
        }
        
        if ( 'off' !== $homemade ) {
            $font_families[] = 'Homemade Apple';
        }
         $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),
            'subset' => urlencode( 'latin,latin-ext' ),
        );
         $fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
    }
     return $fonts_url;
}
/**
 * Enqueue scripts and styles.
 */

function zerif_scripts() {    

	wp_enqueue_style('zerif_font', zerif_slug_fonts_url(), array(), null );

    wp_enqueue_style( 'zerif_font_all', '//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600italic,600,700,700italic,800,800italic');
    
    wp_enqueue_style('zerif_bootstrap_style', get_template_directory_uri() . '/css/bootstrap.css');
	
    wp_style_add_data( 'zerif_bootstrap_style', 'rtl', 'replace' );

    wp_enqueue_style('zerif_fontawesome', get_template_directory_uri() . '/css/font-awesome.min.css', array(), 'v1');

    wp_enqueue_style('zerif_style', get_stylesheet_uri(), array('zerif_fontawesome'), 'v1');

    wp_enqueue_style('zerif_responsive_style', get_template_directory_uri() . '/css/responsive.css', array('zerif_style'), 'v1');

    if ( wp_is_mobile() ){
        
        wp_enqueue_style( 'zerif_style_mobile', get_template_directory_uri() . '/css/style-mobile.css', array('zerif_bootstrap_style', 'zerif_style'),'v1' );
    
    }

    wp_enqueue_script('jquery');

    /* Bootstrap script */
    wp_enqueue_script('zerif_bootstrap_script', get_template_directory_uri() . '/js/bootstrap.min.js', array(), '20120206', true);

    /* Knob script */
    wp_enqueue_script('zerif_knob_nav', get_template_directory_uri() . '/js/jquery.knob.js', array("jquery"), '20120206', true);

    /* Smootscroll script */
    $zerif_disable_smooth_scroll = get_theme_mod('zerif_disable_smooth_scroll');
    if( isset($zerif_disable_smooth_scroll) && ($zerif_disable_smooth_scroll != 1)):
        wp_enqueue_script('zerif_smoothscroll', get_template_directory_uri() . '/js/smoothscroll.js', array("jquery"), '20120206', true);
    endif;  
	
	/* scrollReveal script */
	if ( !wp_is_mobile() ){
		wp_enqueue_script( 'zerif_scrollReveal_script', get_template_directory_uri() . '/js/scrollReveal.js', array("jquery"), '20120206', true  );
	}

    /* zerif script */
    wp_enqueue_script('zerif_script', get_template_directory_uri() . '/js/zerif.js', array("jquery", "zerif_knob_nav"), '20120206', true);

    if (is_singular() && comments_open() && get_option('thread_comments')) {

        wp_enqueue_script('comment-reply');

    }

    /* parallax effect */
    if ( !wp_is_mobile() ){

        /* include parallax only if on frontpage and the parallax effect is activated */
        $zerif_parallax_use = get_theme_mod('zerif_parallax_show');

        if ( !empty($zerif_parallax_use) && ($zerif_parallax_use == 1) && is_front_page() ):

            wp_enqueue_script( 'zerif_parallax', get_template_directory_uri() . '/js/parallax.js', array("jquery"), 'v1', true  );

        endif;
    }

	add_editor_style('/css/custom-editor-style.css');
	
}
add_action('wp_enqueue_scripts', 'zerif_scripts');

add_action('tgmpa_register', 'zerif_register_required_plugins');

function zerif_register_required_plugins() {	
	
	$wp_version_nr = get_bloginfo('version');
	
	if( $wp_version_nr < 3.9 ):

		$plugins = array(
			array(
				'name' => 'Widget customizer',
				'slug' => 'widget-customizer', 
				'required' => false 
			),
			array(
				'name'      => 'Pirate Forms',
				'slug'      => 'pirate-forms',
				'required'  => false,
			)
		);
		
	else:
	
		$plugins = array(
			array(
				'name'      => 'Pirate Forms',
				'slug'      => 'pirate-forms',
				'required'  => false,
			)
		);
	
	endif;

    $config = array(
        'default_path' => '',
        'menu' => 'tgmpa-install-plugins',
        'has_notices' => true,
        'dismissable' => true,
        'dismiss_msg' => '',
        'is_automatic' => false,
        'message' => '',
        'strings' => array(
            'page_title' => __('Install Required Plugins', 'zerif-lite'),
            'menu_title' => __('Install Plugins', 'zerif-lite'),
            'installing' => __('Installing Plugin: %s', 'zerif-lite'),
            'oops' => __('Something went wrong with the plugin API.', 'zerif-lite'),
            'notice_can_install_required' => _n_noop('This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.','zerif-lite'),
            'notice_can_install_recommended' => _n_noop('This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.','zerif-lite'),
            'notice_cannot_install' => _n_noop('Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.','zerif-lite'),
            'notice_can_activate_required' => _n_noop('The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.','zerif-lite'),
            'notice_can_activate_recommended' => _n_noop('The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.','zerif-lite'),
            'notice_cannot_activate' => _n_noop('Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.','zerif-lite'),
            'notice_ask_to_update' => _n_noop('The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.','zerif-lite'),
            'notice_cannot_update' => _n_noop('Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.','zerif-lite'),
            'install_link' => _n_noop('Begin installing plugin', 'Begin installing plugins','zerif-lite'),
            'activate_link' => _n_noop('Begin activating plugin', 'Begin activating plugins','zerif-lite'),
            'return' => __('Return to Required Plugins Installer', 'zerif-lite'),
            'plugin_activated' => __('Plugin activated successfully.', 'zerif-lite'),
            'complete' => __('All plugins installed and activated successfully. %s', 'zerif-lite'),
            'nag_type' => 'updated'
        )
    );

    tgmpa($plugins, $config);

}

/* Load Jetpack compatibility file. */

require get_template_directory() . '/inc/jetpack.php';

function zerif_wp_page_menu() {    

	echo '<ul class="nav navbar-nav navbar-right responsive-nav main-nav-list">';

		wp_list_pages(array('title_li' => '', 'depth' => 1));

    echo '</ul>';

}

add_filter('the_title', 'zerif_default_title');

function zerif_default_title($title) {    

	if ($title == '')

        $title = __("Default title",'zerif-lite');

    return $title;

}

/*****************************************/
/******          WIDGETS     *************/
/*****************************************/

add_action('widgets_init', 'zerif_register_widgets');

function zerif_register_widgets() {    

	register_widget('zerif_ourfocus');
    register_widget('zerif_testimonial_widget');
    register_widget('zerif_clients_widget');
    register_widget('zerif_team_widget');
	
	
	$zerif_lite_sidebars = array ( 'sidebar-ourfocus' => 'sidebar-ourfocus', 'sidebar-testimonials' => 'sidebar-testimonials', 'sidebar-ourteam' => 'sidebar-ourteam' );
	
	/* Register sidebars */
	foreach ( $zerif_lite_sidebars as $zerif_lite_sidebar ):
	
		if( $zerif_lite_sidebar == 'sidebar-ourfocus' ):
		
			$zerif_lite_name = __('Our focus section widgets', 'zerif-lite');
		
		elseif( $zerif_lite_sidebar == 'sidebar-testimonials' ):
		
			$zerif_lite_name = __('Testimonials section widgets', 'zerif-lite');
			
		elseif( $zerif_lite_sidebar == 'sidebar-ourteam' ):
		
			$zerif_lite_name = __('Our team section widgets', 'zerif-lite');
			
		else:
		
			$zerif_lite_name = $zerif_lite_sidebar;
			
		endif;
		
        register_sidebar(
            array (
                'name'          => $zerif_lite_name,
                'id'            => $zerif_lite_sidebar,
                'before_widget' => '',
                'after_widget'  => ''
            )
        );
		
    endforeach;
	
}

/**
 * Add default widgets
 */
add_action('after_switch_theme', 'zerif_register_default_widgets');
	
function zerif_register_default_widgets() {

	$zerif_lite_sidebars = array ( 'sidebar-ourfocus' => 'sidebar-ourfocus', 'sidebar-testimonials' => 'sidebar-testimonials', 'sidebar-ourteam' => 'sidebar-ourteam' );

	$active_widgets = get_option( 'sidebars_widgets' );	

	/**
     * Default Our Focus widgets
     */
	if ( empty ( $active_widgets[ $zerif_lite_sidebars['sidebar-ourfocus'] ] ) ):

		$zerif_lite_counter = 1;

        /* our focus widget #1 */
		$active_widgets[ 'sidebar-ourfocus' ][0] = 'ctup-ads-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/parallax.png' ) ):
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'PARALLAX EFFECT', 'text' => 'Create memorable pages with smooth parallax effects that everyone loves. Also, use our lightweight content slider offering you smooth and great-looking animations.', 'link' => '#', 'image_uri' => get_stylesheet_directory_uri()."/images/parallax.png" );
        else:
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'PARALLAX EFFECT', 'text' => 'Create memorable pages with smooth parallax effects that everyone loves. Also, use our lightweight content slider offering you smooth and great-looking animations.', 'link' => '#', 'image_uri' => get_template_directory_uri()."/images/parallax.png" );
        endif;
        update_option( 'widget_ctup-ads-widget', $ourfocus_content );
        $zerif_lite_counter++;

        /* our focus widget #2 */
        $active_widgets[ 'sidebar-ourfocus' ][] = 'ctup-ads-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/woo.png' ) ):
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'WOOCOMMERCE', 'text' => 'Build a front page for your WooCommerce store in a matter of minutes. The neat and clean presentation will help your sales and make your store accessible to everyone.', 'link' => '#', 'image_uri' => get_stylesheet_directory_uri()."/images/woo.png" );
        else:
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'WOOCOMMERCE', 'text' => 'Build a front page for your WooCommerce store in a matter of minutes. The neat and clean presentation will help your sales and make your store accessible to everyone.', 'link' => '#', 'image_uri' => get_template_directory_uri()."/images/woo.png" );
        endif;
        update_option( 'widget_ctup-ads-widget', $ourfocus_content );
        $zerif_lite_counter++;

        /* our focus widget #3 */
        $active_widgets[ 'sidebar-ourfocus' ][] = 'ctup-ads-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/ccc.png' ) ):
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'CUSTOM CONTENT BLOCKS', 'text' => 'Showcase your team, products, clients, about info, testimonials, latest posts from the blog, contact form, additional calls to action. Everything translation ready.', 'link' => '#', 'image_uri' => get_stylesheet_directory_uri()."/images/ccc.png" );
        else:
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'CUSTOM CONTENT BLOCKS', 'text' => 'Showcase your team, products, clients, about info, testimonials, latest posts from the blog, contact form, additional calls to action. Everything translation ready.', 'link' => '#', 'image_uri' => get_template_directory_uri()."/images/ccc.png" );
        endif;
        update_option( 'widget_ctup-ads-widget', $ourfocus_content );
        $zerif_lite_counter++;

        /* our focus widget #4 */
        $active_widgets[ 'sidebar-ourfocus' ][] = 'ctup-ads-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/ti-logo.png' ) ):
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'GO PRO FOR MORE FEATURES', 'text' => 'Get new content blocks: pricing table, Google Maps, and more. Change the sections order, display each block exactly where you need it, customize the blocks with whatever colors you wish.', 'link' => '#', 'image_uri' => get_stylesheet_directory_uri()."/images/ti-logo.png" );
        else:
            $ourfocus_content[ $zerif_lite_counter ] = array ( 'title' => 'GO PRO FOR MORE FEATURES', 'text' => 'Get new content blocks: pricing table, Google Maps, and more. Change the sections order, display each block exactly where you need it, customize the blocks with whatever colors you wish.', 'link' => '#', 'image_uri' => get_template_directory_uri()."/images/ti-logo.png" );
        endif;
        update_option( 'widget_ctup-ads-widget', $ourfocus_content );
        $zerif_lite_counter++;

		update_option( 'sidebars_widgets', $active_widgets );
		
    endif;

    /**
     * Default Testimonials widgets
     */
    if ( empty ( $active_widgets[ $zerif_lite_sidebars['sidebar-testimonials'] ] ) ):

        $zerif_lite_counter = 1;

        /* testimonial widget #1 */
        $active_widgets[ 'sidebar-testimonials' ][0] = 'zerif_testim-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/testimonial1.jpg' ) ):
            $testimonial_content[ $zerif_lite_counter ] = array ( 'title' => 'Dana Lorem', 'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur nec sem vel sapien venenatis mattis non vitae augue. Nullam congue commodo lorem vitae facilisis. Suspendisse malesuada id turpis interdum dictum.', 'image_uri' => get_stylesheet_directory_uri()."/images/testimonial1.jpg" );
        else:
            $testimonial_content[ $zerif_lite_counter ] = array ( 'title' => 'Dana Lorem', 'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur nec sem vel sapien venenatis mattis non vitae augue. Nullam congue commodo lorem vitae facilisis. Suspendisse malesuada id turpis interdum dictum.', 'image_uri' => get_template_directory_uri()."/images/testimonial1.jpg" );
        endif;
        update_option( 'widget_zerif_testim-widget', $testimonial_content );
        $zerif_lite_counter++;

        /* testimonial widget #2 */
        $active_widgets[ 'sidebar-testimonials' ][] = 'zerif_testim-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/testimonial2.jpg' ) ):
            $testimonial_content[ $zerif_lite_counter ] = array ( 'title' => 'Linda Guthrie', 'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur nec sem vel sapien venenatis mattis non vitae augue. Nullam congue commodo lorem vitae facilisis. Suspendisse malesuada id turpis interdum dictum.', 'image_uri' => get_stylesheet_directory_uri()."/images/testimonial2.jpg" );
        else:
            $testimonial_content[ $zerif_lite_counter ] = array ( 'title' => 'Linda Guthrie', 'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur nec sem vel sapien venenatis mattis non vitae augue. Nullam congue commodo lorem vitae facilisis. Suspendisse malesuada id turpis interdum dictum.', 'image_uri' => get_template_directory_uri()."/images/testimonial2.jpg" );
        endif;
        update_option( 'widget_zerif_testim-widget', $testimonial_content );
        $zerif_lite_counter++;

        /* testimonial widget #3 */
        $active_widgets[ 'sidebar-testimonials' ][] = 'zerif_testim-widget-' . $zerif_lite_counter;
        if ( file_exists( get_stylesheet_directory_uri().'/images/testimonial3.jpg' ) ):
            $testimonial_content[ $zerif_lite_counter ] = array ( 'title' => 'Cynthia Henry', 'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur nec sem vel sapien venenatis mattis non vitae augue. Nullam congue commodo lorem vitae facilisis. Suspendisse malesuada id turpis interdum dictum.', 'image_uri' => get_stylesheet_directory_uri()."/images/testimonial3.jpg" );
        else:
            $testimonial_content[ $zerif_lite_counter ] = array ( 'title' => 'Cynthia Henry', 'text' => 'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur nec sem vel sapien venenatis mattis non vitae augue. Nullam congue commodo lorem vitae facilisis. Suspendisse malesuada id turpis interdum dictum.', 'image_uri' => get_template_directory_uri()."/images/testimonial3.jpg" );
        endif;
        update_option( 'widget_zerif_testim-widget', $testimonial_content );
        $zerif_lite_counter++;

        update_option( 'sidebars_widgets', $active_widgets );

    endif;

    /**
     * Default Our Team widgets
     */
    if ( empty ( $active_widgets[ $zerif_lite_sidebars['sidebar-ourteam'] ] ) ):

        $zerif_lite_counter = 1;

        /* our team widget #1 */
        $active_widgets[ 'sidebar-ourteam' ][0] = 'zerif_team-widget-' . $zerif_lite_counter;
        $ourteam_content[ $zerif_lite_counter ] = array ( 'name' => 'ASHLEY SIMMONS', 'position' => 'Project Manager', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc dapibus, eros at accumsan auctor, felis eros condimentum quam, non porttitor est urna vel neque', 'fb_link' => '#', 'tw_link' => '#', 'bh_link' => '#', 'db_link' => '#', 'ln_link' => '#', 'image_uri' => get_template_directory_uri()."/images/team1.png" );
        update_option( 'widget_zerif_team-widget', $ourteam_content );
        $zerif_lite_counter++;

        /* our team widget #2 */
        $active_widgets[ 'sidebar-ourteam' ][] = 'zerif_team-widget-' . $zerif_lite_counter;
        $ourteam_content[ $zerif_lite_counter ] = array ( 'name' => 'TIMOTHY SPRAY', 'position' => 'Art Director', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc dapibus, eros at accumsan auctor, felis eros condimentum quam, non porttitor est urna vel neque', 'fb_link' => '#', 'tw_link' => '#', 'bh_link' => '#', 'db_link' => '#', 'ln_link' => '#', 'image_uri' => get_template_directory_uri()."/images/team2.png" );
        update_option( 'widget_zerif_team-widget', $ourteam_content );
        $zerif_lite_counter++;

        /* our team widget #3 */
        $active_widgets[ 'sidebar-ourteam' ][] = 'zerif_team-widget-' . $zerif_lite_counter;
        $ourteam_content[ $zerif_lite_counter ] = array ( 'name' => 'TONYA GARCIA', 'position' => 'Account Manager', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc dapibus, eros at accumsan auctor, felis eros condimentum quam, non porttitor est urna vel neque', 'fb_link' => '#', 'tw_link' => '#', 'bh_link' => '#', 'db_link' => '#', 'ln_link' => '#', 'image_uri' => get_template_directory_uri()."/images/team3.png" );
        update_option( 'widget_zerif_team-widget', $ourteam_content );
        $zerif_lite_counter++;

        /* our team widget #4 */
        $active_widgets[ 'sidebar-ourteam' ][] = 'zerif_team-widget-' . $zerif_lite_counter;
        $ourteam_content[ $zerif_lite_counter ] = array ( 'name' => 'JASON LANE', 'position' => 'Business Development', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc dapibus, eros at accumsan auctor, felis eros condimentum quam, non porttitor est urna vel neque', 'fb_link' => '#', 'tw_link' => '#', 'bh_link' => '#', 'db_link' => '#', 'ln_link' => '#', 'image_uri' => get_template_directory_uri()."/images/team4.png" );
        update_option( 'widget_zerif_team-widget', $ourteam_content );
        $zerif_lite_counter++;

        update_option( 'sidebars_widgets', $active_widgets );

    endif;

}

/**************************/
/****** our focus widget */
/************************/

add_action('admin_enqueue_scripts', 'zerif_ourfocus_widget_scripts');

function zerif_ourfocus_widget_scripts() {    

	wp_enqueue_media();
    wp_enqueue_script('zerif_our_focus_widget_script', get_template_directory_uri() . '/js/widget.js', false, '1.0', true);
	
}

class zerif_ourfocus extends WP_Widget {
	
	public function __construct() {
		parent::__construct(
			'ctUp-ads-widget',
			__( 'Zerif - Our focus widget', 'zerif-lite' )
		);
	}

    function widget($args, $instance) {

        extract($args);

        echo $before_widget;

        ?>

        <div class="col-lg-3 col-sm-3 focus-box" data-scrollreveal="enter left after 0.15s over 1s">

			<?php if( !empty($instance['image_uri']) && ($instance['image_uri'] != 'Upload Image') ) { ?>
			
				<div class="service-icon">
					
					<?php if( !empty($instance['link']) ) { ?>
					
						<a href="<?php echo $instance['link']; ?>"><i class="pixeden" style="background:url(<?php echo esc_url($instance['image_uri']); ?>) no-repeat center;width:100%; height:100%;"></i> <!-- FOCUS ICON--></a>
					
					<?php } else { ?>
					
						<i class="pixeden" style="background:url(<?php echo esc_url($instance['image_uri']); ?>) no-repeat center;width:100%; height:100%;"></i> <!-- FOCUS ICON-->
					
					<?php } ?>

				</div>
				
			<?php } elseif( !empty($instance['custom_media_id']) ) {
			
					$zerif_ourfocus_custom_media_id = wp_get_attachment_image_src($instance["custom_media_id"] );
					if( !empty($zerif_ourfocus_custom_media_id) && !empty($zerif_ourfocus_custom_media_id[0]) ) {
						?>

							<div class="service-icon">
					
								<?php if( !empty($instance['link']) ) { ?>
								
									<a href="<?php echo $instance['link']; ?>"><i class="pixeden" style="background:url(<?php echo esc_url($zerif_ourfocus_custom_media_id[0]); ?>) no-repeat center;width:100%; height:100%;"></i> <!-- FOCUS ICON--></a>
								
								<?php } else { ?>
								
									<i class="pixeden" style="background:url(<?php echo esc_url($zerif_ourfocus_custom_media_id[0]); ?>) no-repeat center;width:100%; height:100%;"></i> <!-- FOCUS ICON-->
								
								<?php } ?>

							</div>	
				
						<?php
					}
			
				} 
			?>

            <h3 class="red-border-bottom"><?php if( !empty($instance['title']) ): echo apply_filters('widget_title', $instance['title']); endif; ?></h3>
            <!-- FOCUS HEADING -->

			<?php 
				if( !empty($instance['text']) ) {
					echo '<p>';
						echo htmlspecialchars_decode(apply_filters('widget_title', $instance['text']));
					echo '</p>';
				}
			?>	

        </div>

        <?php

        echo $after_widget;

    }

    function update($new_instance, $old_instance) {

        $instance = $old_instance;
        $instance['text'] = stripslashes(wp_filter_post_kses($new_instance['text']));
        $instance['title'] = strip_tags($new_instance['title']);
		$instance['link'] = strip_tags( $new_instance['link'] );
        $instance['image_uri'] = strip_tags($new_instance['image_uri']);
		$instance['custom_media_id'] = strip_tags($new_instance['custom_media_id']);

        return $instance;

    }

    function form($instance) {
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php if( !empty($instance['title']) ): echo $instance['title']; endif; ?>" class="widefat">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text', 'zerif-lite'); ?></label><br/>
            <textarea class="widefat" rows="8" cols="20" name="<?php echo $this->get_field_name('text'); ?>" id="<?php echo $this->get_field_id('text'); ?>"><?php if( !empty($instance['text']) ): echo htmlspecialchars_decode($instance['text']); endif; ?></textarea>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Link','zerif-lite'); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name('link'); ?>" id="<?php echo $this->get_field_id('link'); ?>" value="<?php if( !empty($instance['link']) ): echo $instance['link']; endif; ?>" class="widefat">
		</p>
        <p>
            <label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('Image', 'zerif-lite'); ?></label><br/>
            <?php
            if ( !empty($instance['image_uri']) ) :
                echo '<img class="custom_media_image" src="' . $instance['image_uri'] . '" style="margin:0;padding:0;max-width:100px;float:left;display:inline-block" alt="'.__( 'Uploaded image', 'zerif-lite' ).'" /><br />';
            endif;
            ?>

            <input type="text" class="widefat custom_media_url" name="<?php echo $this->get_field_name('image_uri'); ?>" id="<?php echo $this->get_field_id('image_uri'); ?>" value="<?php if( !empty($instance['image_uri']) ): echo $instance['image_uri']; endif; ?>" style="margin-top:5px;">

            <input type="button" class="button button-primary custom_media_button" id="custom_media_button" name="<?php echo $this->get_field_name('image_uri'); ?>" value="<?php _e('Upload Image','zerif-lite'); ?>" style="margin-top:5px;"/>
        </p>
		
		<input class="custom_media_id" id="<?php echo $this->get_field_id( 'custom_media_id' ); ?>" name="<?php echo $this->get_field_name( 'custom_media_id' ); ?>" type="hidden" value="<?php if( !empty($instance["custom_media_id"]) ): echo $instance["custom_media_id"]; endif; ?>" />
		
    <?php

    }

}

/****************************/
/****** testimonial widget **/
/***************************/

add_action('admin_enqueue_scripts', 'zerif_testimonial_widget_scripts');

function zerif_testimonial_widget_scripts() {    

	wp_enqueue_media();

    wp_enqueue_script('zerif_testimonial_widget_script', get_template_directory_uri() . '/js/widget-testimonials.js', false, '1.0', true);

}

class zerif_testimonial_widget extends WP_Widget {	

	public function __construct() {
		parent::__construct(
			'zerif_testim-widget',
			__( 'Zerif - Testimonial widget', 'zerif-lite' )
		);
	}

    function widget($args, $instance) {

        extract($args);
		
		$zerif_accessibility = get_theme_mod('zerif_accessibility');
		// open link in a new tab when checkbox "accessibility" is not ticked
		$attribut_new_tab = (isset($zerif_accessibility) && ($zerif_accessibility != 1) ? ' target="_blank"' : '' );
        ?>

        <div class="feedback-box">

            <!-- MESSAGE OF THE CLIENT -->

			<?php if( !empty($instance['text']) ): ?>
				<div class="message">
					<?php echo htmlspecialchars_decode(apply_filters('widget_title', $instance['text'])); ?>
				</div>
			<?php endif; ?>

            <!-- CLIENT INFORMATION -->

            <div class="client">

                <div class="quote red-text">

                    <i class="icon-fontawesome-webfont-294"></i>

                </div>

                <div class="client-info">

					<a <?php echo $attribut_new_tab; ?> class="client-name" <?php if( !empty($instance['link']) ): echo 'href="'.esc_url($instance['link']).'"'; endif; ?>><?php if( !empty($instance['title']) ): echo apply_filters('widget_title', $instance['title'] ); endif; ?></a>
					

					<?php if( !empty($instance['details']) ): ?>
                    <div class="client-company">

                        <?php echo apply_filters('widget_title', $instance['details']); ?>

                    </div>
					<?php endif; ?>

                </div>

                <?php
				
				if( !empty($instance['image_uri']) && ($instance['image_uri'] != 'Upload Image') ) {

					echo '<div class="client-image hidden-xs">';

						echo '<img src="' . esc_url($instance['image_uri']) . '" alt="'.__( 'Uploaded image', 'zerif-lite' ).'" />';

					echo '</div>';
					
				} elseif( !empty($instance['custom_media_id']) ) {
			
					$zerif_testimonials_custom_media_id = wp_get_attachment_image_src($instance["custom_media_id"] );
					if( !empty($zerif_testimonials_custom_media_id) && !empty($zerif_testimonials_custom_media_id[0]) ) {
						
						echo '<div class="client-image hidden-xs">';

							echo '<img src="' . esc_url($zerif_testimonials_custom_media_id[0]) . '" alt="'.__( 'Uploaded image', 'zerif-lite' ).'" />';

						echo '</div>';
				
					}
				} 

                ?>

            </div>
            <!-- / END CLIENT INFORMATION-->

        </div> <!-- / END SINGLE FEEDBACK BOX-->

        <?php

    }

    function update($new_instance, $old_instance) {

        $instance = $old_instance;
        $instance['text'] = stripslashes(wp_filter_post_kses($new_instance['text']));
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['details'] = strip_tags($new_instance['details']);
        $instance['image_uri'] = strip_tags($new_instance['image_uri']);
		$instance['link'] = strip_tags( $new_instance['link'] );
		$instance['custom_media_id'] = strip_tags($new_instance['custom_media_id']);

        return $instance;

    }

    function form($instance) {
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Author', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" value="<?php if( !empty($instance['title']) ): echo $instance['title']; endif; ?>" class="widefat">
        </p>
		<p>
			<label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Author link','zerif-lite'); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name('link'); ?>" id="<?php echo $this->get_field_id('link'); ?>" value="<?php if( !empty($instance['link']) ): echo $instance['link']; endif; ?>" class="widefat">
		</p>
        <p>
            <label for="<?php echo $this->get_field_id('details'); ?>"><?php _e('Author details', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('details'); ?>" id="<?php echo $this->get_field_id('details'); ?>" value="<?php if( !empty($instance['details']) ): echo $instance['details']; endif; ?>" class="widefat">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text', 'zerif-lite'); ?></label><br/>
            <textarea class="widefat" rows="8" cols="20" name="<?php echo $this->get_field_name('text'); ?>" id="<?php echo $this->get_field_id('text'); ?>"><?php if( !empty($instance['text']) ): echo htmlspecialchars_decode($instance['text']); endif; ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('Image', 'zerif-lite'); ?></label><br/>
            <?php
            if ( !empty($instance['image_uri']) ) :

                echo '<img class="custom_media_image_testimonial" src="' . $instance['image_uri'] . '" style="margin:0;padding:0;max-width:100px;float:left;display:inline-block" alt="'.__( 'Uploaded image', 'zerif-lite' ).'" /><br />';

            endif;

            ?>
            <input type="text" class="widefat custom_media_url_testimonial" name="<?php echo $this->get_field_name('image_uri'); ?>" id="<?php echo $this->get_field_id('image_uri'); ?>" value="<?php if( !empty($instance['image_uri']) ): echo $instance['image_uri']; endif; ?>" style="margin-top:5px;">
            <input type="button" class="button button-primary custom_media_button_testimonial" id="custom_media_button_testimonial" name="<?php echo $this->get_field_name('image_uri'); ?>" value="<?php _e('Upload Image','zerif-lite'); ?>" style="margin-top:5px;">
        </p>
		
		<input class="custom_media_id" id="<?php echo $this->get_field_id( 'custom_media_id' ); ?>" name="<?php echo $this->get_field_name( 'custom_media_id' ); ?>" type="hidden" value="<?php if( !empty($instance["custom_media_id"]) ): echo $instance["custom_media_id"]; endif; ?>" />

    <?php

    }

}

/****************************/

/****** clients widget ******/

/***************************/

add_action('admin_enqueue_scripts', 'zerif_clients_widget_scripts');

function zerif_clients_widget_scripts(){    

	wp_enqueue_media();

    wp_enqueue_script('zerif_clients_widget_script', get_template_directory_uri() . '/js/widget-clients.js', false, '1.0', true);

}

class zerif_clients_widget extends WP_Widget{	

	public function __construct() {
		parent::__construct(
			'zerif_clients-widget',
			__( 'Zerif - Clients widget', 'zerif-lite' )
		);
	}

    function widget($args, $instance) {

        extract($args);

        echo $before_widget;

        ?>

        <a href="<?php if( !empty($instance['link']) ): echo apply_filters('widget_title', $instance['link']); endif; ?>">
			<?php 
				if( !empty($instance['image_uri']) && ($instance['image_uri'] != 'Upload Image') ) {
					
					echo '<img src="'.esc_url($instance['image_uri']).'" alt="'.__( 'Client', 'zerif-lite' ).'">';
					
				} elseif( !empty($instance['custom_media_id']) ) {
			
					$zerif_clients_custom_media_id = wp_get_attachment_image_src($instance["custom_media_id"] );
					if( !empty($zerif_clients_custom_media_id) && !empty($zerif_clients_custom_media_id[0]) ) {
						
						echo '<img src="'.esc_url($zerif_clients_custom_media_id[0]).'" alt="'.__( 'Client', 'zerif-lite' ).'">';
				
					}
				} 
			?>		
		</a>

        <?php

        echo $after_widget;

    }

    function update($new_instance, $old_instance) {

        $instance = $old_instance;

        $instance['link'] = strip_tags($new_instance['link']);

        $instance['image_uri'] = strip_tags($new_instance['image_uri']);
		
		$instance['custom_media_id'] = strip_tags($new_instance['custom_media_id']);

        return $instance;

    }

    function form($instance) {
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Link', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('link'); ?>" id="<?php echo $this->get_field_id('link'); ?>" value="<?php if( !empty($instance['link']) ): echo $instance['link']; endif; ?>" class="widefat">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('Image', 'zerif-lite'); ?></label><br/>
            <?php
            if ( !empty($instance['image_uri']) ) :
                echo '<img class="custom_media_image_clients" src="' . $instance['image_uri'] . '" style="margin:0;padding:0;max-width:100px;float:left;display:inline-block" alt="'.__( 'Uploaded image', 'zerif-lite' ).'" /><br />';
            endif;
            ?>

            <input type="text" class="widefat custom_media_url_clients" name="<?php echo $this->get_field_name('image_uri'); ?>" id="<?php echo $this->get_field_id('image_uri'); ?>" value="<?php if( !empty($instance['image_uri']) ): echo $instance['image_uri']; endif; ?>" style="margin-top:5px;">

            <input type="button" class="button button-primary custom_media_button_clients" id="custom_media_button_clients" name="<?php echo $this->get_field_name('image_uri'); ?>" value="<?php _e('Upload Image','zerif-lite'); ?>" style="margin-top:5px;">
        </p>

		<input class="custom_media_id" id="<?php echo $this->get_field_id( 'custom_media_id' ); ?>" name="<?php echo $this->get_field_name( 'custom_media_id' ); ?>" type="hidden" value="<?php if( !empty($instance["custom_media_id"]) ): echo $instance["custom_media_id"]; endif; ?>" />
    <?php

    }

}

/****************************/
/****** team member widget **/
/***************************/

add_action('admin_enqueue_scripts', 'zerif_team_widget_scripts');

function zerif_team_widget_scripts() {    

	wp_enqueue_media();

    wp_enqueue_script('zerif_team_widget_script', get_template_directory_uri() . '/js/widget-team.js', false, '1.0', true);

}

class zerif_team_widget extends WP_Widget{	

	public function __construct() {
		parent::__construct(
			'zerif_team-widget',
			__( 'Zerif - Team member widget', 'zerif-lite' )
		);
	}

    function widget($args, $instance) {

        extract($args);

        echo $before_widget;

        ?>

        <div class="col-lg-3 col-sm-3 team-box">

            <div class="team-member">

				<?php if( !empty($instance['image_uri']) && ($instance['image_uri'] != 'Upload Image') ) { ?>
				
					<figure class="profile-pic">

						<img src="<?php echo esc_url($instance['image_uri']); ?>" alt="<?php _e( 'Uploaded image', 'zerif-lite' ); ?>" />

					</figure>
				<?php
					} elseif( !empty($instance['custom_media_id']) ) {
			
						$zerif_team_custom_media_id = wp_get_attachment_image_src($instance["custom_media_id"] );
						if( !empty($zerif_team_custom_media_id) && !empty($zerif_team_custom_media_id[0]) ) {
							?>

								<figure class="profile-pic">

									<img src="<?php echo esc_url($zerif_team_custom_media_id[0]); ?>" alt="<?php _e( 'Uploaded image', 'zerif-lite' ); ?>" />

								</figure>
					
							<?php
						}
					} 
				?>

                <div class="member-details">

					<?php if( !empty($instance['name']) ): ?>
					
						<h3 class="dark-text red-border-bottom"><?php echo apply_filters('widget_title', $instance['name']); ?></h3>
						
					<?php endif; ?>	

					<?php if( !empty($instance['position']) ): ?>
					
						<div class="position"><?php echo htmlspecialchars_decode(apply_filters('widget_title', $instance['position'])); ?></div>
				
					<?php endif; ?>

                </div>

                <div class="social-icons">

                    <ul>
                        <?php
                            $zerif_team_target = '_self';
                            if( !empty($instance['open_new_window']) ):
                                $zerif_team_target = '_blank';
                            endif;
                        ?>

                        <?php if ( !empty($instance['fb_link']) ): ?>
                            <li><a href="<?php echo apply_filters('widget_title', $instance['fb_link']); ?>" target="<?php echo $zerif_team_target; ?>"><i
                                        class="fa fa-facebook"></i></a></li>
                        <?php endif; ?>

                        <?php if ( !empty($instance['tw_link']) ): ?>
                            <li><a href="<?php echo apply_filters('widget_title', $instance['tw_link']); ?>" target="<?php echo $zerif_team_target; ?>"><i
                                        class="fa fa-twitter"></i></a></li>
                        <?php endif; ?>

                        <?php if ( !empty($instance['bh_link']) ): ?>
                            <li><a href="<?php echo apply_filters('widget_title', $instance['bh_link']); ?>" target="<?php echo $zerif_team_target; ?>"><i
                                        class="fa fa-behance"></i></a></li>
                        <?php endif; ?>

                        <?php if ( !empty($instance['db_link']) ): ?>
                            <li><a href="<?php echo apply_filters('widget_title', $instance['db_link']); ?>" target="<?php echo $zerif_team_target; ?>"><i
                                        class="fa fa-dribbble"></i></a></li>
                        <?php endif; ?>
						
						<?php if ( !empty($instance['ln_link']) ): ?>
                            <li><a href="<?php echo apply_filters('widget_title', $instance['ln_link']); ?>" target="<?php echo $zerif_team_target; ?>"><i
                                        class="fa fa-linkedin"></i></a></li>
                        <?php endif; ?>

                    </ul>

                </div>

				<?php if( !empty($instance['description']) ): ?>
                <div class="details">

                    <?php echo htmlspecialchars_decode(apply_filters('widget_title', $instance['description'])); ?>

                </div>
				<?php endif; ?>

            </div>

        </div>

        <?php

        echo $after_widget;

    }

    function update($new_instance, $old_instance) {

        $instance = $old_instance;

        $instance['name'] = strip_tags($new_instance['name']);
        $instance['position'] = stripslashes(wp_filter_post_kses($new_instance['position']));
        $instance['description'] = stripslashes(wp_filter_post_kses($new_instance['description']));
        $instance['fb_link'] = strip_tags($new_instance['fb_link']);
        $instance['tw_link'] = strip_tags($new_instance['tw_link']);
        $instance['bh_link'] = strip_tags($new_instance['bh_link']);
        $instance['db_link'] = strip_tags($new_instance['db_link']);
		$instance['ln_link'] = strip_tags($new_instance['ln_link']);
        $instance['image_uri'] = strip_tags($new_instance['image_uri']);
        $instance['open_new_window'] = strip_tags($new_instance['open_new_window']);
		$instance['custom_media_id'] = strip_tags($new_instance['custom_media_id']);

        return $instance;

    }

    function form($instance) {

        ?>

        <p>
            <label for="<?php echo $this->get_field_id('name'); ?>"><?php _e('Name', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('name'); ?>" id="<?php echo $this->get_field_id('name'); ?>" value="<?php if( !empty($instance['name']) ): echo $instance['name']; endif; ?>" class="widefat"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('position'); ?>"><?php _e('Position', 'zerif-lite'); ?></label><br/>
            <textarea class="widefat" rows="8" cols="20" name="<?php echo $this->get_field_name('position'); ?>" id="<?php echo $this->get_field_id('position'); ?>"><?php if( !empty($instance['position']) ): echo htmlspecialchars_decode($instance['position']); endif; ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('description'); ?>"><?php _e('Description', 'zerif-lite'); ?></label><br/>
            <textarea class="widefat" rows="8" cols="20" name="<?php echo $this->get_field_name('description'); ?>"
                      id="<?php echo $this->get_field_id('description'); ?>"><?php
                if( !empty($instance['description']) ): echo htmlspecialchars_decode($instance['description']); endif;
            ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('fb_link'); ?>"><?php _e('Facebook link', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('fb_link'); ?>" id="<?php echo $this->get_field_id('fb_link'); ?>" value="<?php if( !empty($instance['fb_link']) ): echo $instance['fb_link']; endif; ?>" class="widefat">

        </p>
        <p>
            <label for="<?php echo $this->get_field_id('tw_link'); ?>"><?php _e('Twitter link', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('tw_link'); ?>" id="<?php echo $this->get_field_id('tw_link'); ?>" value="<?php if( !empty($instance['tw_link']) ): echo $instance['tw_link']; endif; ?>" class="widefat">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('bh_link'); ?>"><?php _e('Behance link', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('bh_link'); ?>" id="<?php echo $this->get_field_id('bh_link'); ?>" value="<?php if( !empty($instance['bh_link']) ): echo $instance['bh_link']; endif; ?>" class="widefat">

        </p>
        <p>
            <label for="<?php echo $this->get_field_id('db_link'); ?>"><?php _e('Dribble link', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('db_link'); ?>" id="<?php echo $this->get_field_id('db_link'); ?>" value="<?php if( !empty($instance['db_link']) ): echo $instance['db_link']; endif; ?>" class="widefat">
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('ln_link'); ?>"><?php _e('Linkedin link', 'zerif-lite'); ?></label><br/>
            <input type="text" name="<?php echo $this->get_field_name('ln_link'); ?>" id="<?php echo $this->get_field_id('ln_link'); ?>" value="<?php if( !empty($instance['ln_link']) ): echo $instance['ln_link']; endif; ?>" class="widefat">
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name('open_new_window'); ?>" id="<?php echo $this->get_field_id('open_new_window'); ?>" <?php if( !empty($instance['open_new_window']) ): checked( (bool) $instance['open_new_window'], true ); endif; ?> ><?php _e( 'Open links in new window?','zerif-lite' ); ?><br>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('Image', 'zerif-lite'); ?></label><br/>

            <?php

            if ( !empty($instance['image_uri']) ) :

                echo '<img class="custom_media_image_team" src="' . $instance['image_uri'] . '" style="margin:0;padding:0;max-width:100px;float:left;display:inline-block" alt="'.__( 'Uploaded image', 'zerif-lite' ).'" /><br />';

            endif;

            ?>

            <input type="text" class="widefat custom_media_url_team" name="<?php echo $this->get_field_name('image_uri'); ?>" id="<?php echo $this->get_field_id('image_uri'); ?>" value="<?php if( !empty($instance['image_uri']) ): echo $instance['image_uri']; endif; ?>" style="margin-top:5px;">
            <input type="button" class="button button-primary custom_media_button_team" id="custom_media_button_clients" name="<?php echo $this->get_field_name('image_uri'); ?>" value="<?php _e('Upload Image','zerif-lite'); ?>" style="margin-top:5px;">
        </p>
		
		<input class="custom_media_id" id="<?php echo $this->get_field_id( 'custom_media_id' ); ?>" name="<?php echo $this->get_field_name( 'custom_media_id' ); ?>" type="hidden" value="<?php if( !empty($instance["custom_media_id"]) ): echo $instance["custom_media_id"]; endif; ?>" />

    <?php

    }

}

function zerif_customizer_custom_css() {

    wp_enqueue_style('zerif_customizer_custom_css', get_template_directory_uri() . '/css/zerif_customizer_custom_css.css');

}
add_action('customize_controls_print_styles', 'zerif_customizer_custom_css');

function zerif_excerpt_more( $more ) {
	return '<a href="'.get_permalink().'">[...]</a>';
}
add_filter('excerpt_more', 'zerif_excerpt_more');

/* Enqueue Google reCAPTCHA scripts */
add_action( 'wp_enqueue_scripts', 'recaptcha_scripts' );

function recaptcha_scripts() {

    if ( is_home() ):
        $zerif_contactus_sitekey = get_theme_mod('zerif_contactus_sitekey');
        $zerif_contactus_secretkey = get_theme_mod('zerif_contactus_secretkey');
        $zerif_contactus_recaptcha_show = get_theme_mod('zerif_contactus_recaptcha_show');
        if( isset($zerif_contactus_recaptcha_show) && $zerif_contactus_recaptcha_show != 1 && !empty($zerif_contactus_sitekey) && !empty($zerif_contactus_secretkey) ) :
            wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );
        endif;
    endif;

}

/* remove custom-background from body_class() */
add_filter( 'body_class', 'remove_class_function' );
function remove_class_function( $classes ) {

    if ( !is_home() ) {   
        // index of custom-background
        $key = array_search('custom-background', $classes);
        // remove class
        unset($classes[$key]);
    }
    return $classes;

}

/* Update Pirate Forms plugin when there is a change in Customizer Contact us section */

add_action('customize_save_after', 'zerif_lite_update_options_in_pirate_forms', 99);

function zerif_lite_update_options_in_pirate_forms() {

    /* if Pirate Forms is installed */
    if( defined("PIRATE_FORMS_VERSION") ):

        $zerif_lite_current_mods = get_theme_mods(); /* all theme modification values in Customize for Zerif Lite */

        $pirate_forms_settings_array = get_option( 'pirate_forms_settings_array' ); /* Pirate Forms's options's array */

        if( !empty($zerif_lite_current_mods) ):

            if( isset($zerif_lite_current_mods['zerif_contactus_button_label']) ):
                $pirate_forms_settings_array['pirateformsopt_label_submit_btn'] = $zerif_lite_current_mods['zerif_contactus_button_label'];
            endif;

            if( isset($zerif_lite_current_mods['zerif_contactus_email']) ):

                $pirate_forms_settings_array['pirateformsopt_email'] = $zerif_lite_current_mods['zerif_contactus_email'];
                $pirate_forms_settings_array['pirateformsopt_email_recipients'] = $zerif_lite_current_mods['zerif_contactus_email'];

            endif;

            if( isset($zerif_lite_current_mods['zerif_contactus_recaptcha_show']) && ($zerif_lite_current_mods['zerif_contactus_recaptcha_show'] == 1) ):
                if( isset($pirate_forms_settings_array['pirateformsopt_recaptcha_field']) ):
                    unset($pirate_forms_settings_array['pirateformsopt_recaptcha_field']);
                endif;
            else:
                $pirate_forms_settings_array['pirateformsopt_recaptcha_field'] = 'yes';
            endif;

            if( isset($zerif_lite_current_mods['zerif_contactus_sitekey']) ):
                $pirate_forms_settings_array['pirateformsopt_recaptcha_sitekey'] = $zerif_lite_current_mods['zerif_contactus_sitekey'];
            endif;

            if( isset($zerif_lite_current_mods['zerif_contactus_secretkey']) ):
                $pirate_forms_settings_array['pirateformsopt_recaptcha_secretkey'] = $zerif_lite_current_mods['zerif_contactus_secretkey'];
            endif;


        endif;

        if( !empty($pirate_forms_settings_array) ):
            update_option('pirate_forms_settings_array', $pirate_forms_settings_array); /* Update the options */
        endif;

    endif;
}
