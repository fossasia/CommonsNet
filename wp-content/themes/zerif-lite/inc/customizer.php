<?php
/**
 * zerif Theme Customizer
 *
 * @package zerif
 */
/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function zerif_customize_register( $wp_customize ) {
	class Zerif_Customize_Textarea_Control extends WP_Customize_Control {
		public $type = 'textarea';
	 
		public function render_content() {
			?>
			<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<textarea rows="5" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
			</label>
			<?php
		}
	}
	class Zerif_Customizer_Number_Control extends WP_Customize_Control {
		public $type = 'number';
		public function render_content() {
		?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<input type="number" <?php $this->link(); ?> value="<?php echo intval( $this->value() ); ?>" />
			</label>
		<?php
		}
	}
	class Zerif_Theme_Support extends WP_Customize_Control {
		public function render_content() {
			echo __('Check out the <a href="http://themeisle.com/themes/zerif-pro-one-page-wordpress-theme/">PRO version</a> for full control over the frontpage SECTIONS ORDER and the COLOR SCHEME!','zerif-lite');
		}
	}

	class Zerif_Theme_Support_Videobackground extends WP_Customize_Control {
		public function render_content() {
			echo __('Check out the <a href="http://themeisle.com/themes/zerif-pro-one-page-wordpress-theme/">PRO version</a> to add a nice looking background video!','zerif-lite');
		}
	}
	
	class Zerif_Theme_Support_Googlemap extends WP_Customize_Control {
		public function render_content() {
			echo __('Check out the <a href="http://themeisle.com/themes/zerif-pro-one-page-wordpress-theme/">PRO version</a> to add a google maps section !','zerif-lite');
		}
	} 
	
	class Zerif_Theme_Support_Pricing extends WP_Customize_Control {
		public function render_content() {
			echo __('Check out the <a href="http://themeisle.com/themes/zerif-pro-one-page-wordpress-theme/">PRO version</a> to add a pricing section !','zerif-lite');
		}
	} 
	
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
	$wp_customize->remove_section('colors');
	
	/**********************************************/
	/*************** ORDER ************************/
	/**********************************************/
	
	$wp_customize->add_section( 'zerif_order_section', array(
		'title'	=> __( 'Sections order and Colors', 'zerif-lite' ),
		'priority' => 28
	));
	
	$wp_customize->add_setting( 'zerif_order_section', array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	
	$wp_customize->add_control( new Zerif_Theme_Support( $wp_customize, 'zerif_order_section', array(
	    'section' => 'zerif_order_section',
	)));
	
	/***********************************************/
	/************** GENERAL OPTIONS  ***************/
	/***********************************************/
	if ( class_exists( 'WP_Customize_Panel' ) ):
	
		$wp_customize->add_panel( 'panel_general', array(
			'priority' => 30,
			'capability' => 'edit_theme_options',
			'title' => __( 'General options', 'zerif-lite' )
		));
		
		$wp_customize->add_section( 'zerif_general_section' , array(
			'title' => __( 'General', 'zerif-lite' ),
			'priority' => 30,
			'panel' => 'panel_general'
		));
		
		$wp_customize->add_setting( 'zerif_use_safe_font', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));
 
		$wp_customize->add_control( 'zerif_use_safe_font', array(
	        'type' 		=> 'checkbox',
	        'label' 	=> 'Use safe font?',
	        'section' 	=> 'zerif_general_section',
	        'priority'	=> 1
		));
		
		/* LOGO	*/
		$wp_customize->add_setting( 'zerif_logo', array(
			'sanitize_callback' => 'esc_url_raw',
			'transport' => 'postMessage'
		));
		
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_logo', array(
			'label' => __( 'Logo', 'zerif-lite' ),
			'section' => 'title_tagline',
			'settings' => 'zerif_logo',
			'priority' => 1,
		)));
		
		/* Disable preloader */
		$wp_customize->add_setting( 'zerif_disable_preloader', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));
		
		$wp_customize->add_control( 'zerif_disable_preloader', array(
			'type' => 'checkbox',
			'label' => __('Disable preloader?','zerif-lite'),
			'section' => 'zerif_general_section',
			'priority' => 2,
		));

		/* Disable smooth scroll */
		$wp_customize->add_setting( 'zerif_disable_smooth_scroll', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));
		
		$wp_customize->add_control( 'zerif_disable_smooth_scroll', array(
			'type' 		=> 'checkbox',
			'label' 	=> __('Disable smooth scroll?','zerif-lite'),
			'section' 	=> 'zerif_general_section',
			'priority'	=> 3,
		));

		/* Enable accessibility */
		$wp_customize->add_setting( 'zerif_accessibility', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));
		
		$wp_customize->add_control( 'zerif_accessibility', array(
			'type' 		=> 'checkbox',
			'label' 	=> __('Enable accessibility?','zerif-lite'),
			'section' 	=> 'zerif_general_section',
			'priority'	=> 4,
		));

		/* COPYRIGHT */
		$wp_customize->add_setting( 'zerif_copyright', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_copyright', array(
			'label'    => __( 'Footer Copyright', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 5,
		));

		/* Change the template to full width for page.php */
        $wp_customize->add_setting( 'zerif_change_to_full_width', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		) );

        $wp_customize->add_control( 'zerif_change_to_full_width', array(
             'type' 		=> 'checkbox',
             'label' 	=> 'Change the template to Full width for all the pages?',
             'section' 	=> 'zerif_general_section',
             'priority'	=> 6
         ) );

		$wp_customize->add_section( 'zerif_general_socials_section' , array(
			'title' => __( 'Footer Social Icons', 'zerif-lite' ),
			'priority' => 31,
			'panel' => 'panel_general'
		));

		/* facebook */
		$wp_customize->add_setting( 'zerif_socials_facebook', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));

		$wp_customize->add_control( 'zerif_socials_facebook', array(
			'label'    => __( 'Facebook link', 'zerif-lite' ),
			'section'  => 'zerif_general_socials_section',
			'priority' => 4,
		));

		/* twitter */
		$wp_customize->add_setting( 'zerif_socials_twitter', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));

		$wp_customize->add_control( 'zerif_socials_twitter', array(
			'label'    => __( 'Twitter link', 'zerif-lite' ),
			'section'  => 'zerif_general_socials_section',
			'priority' => 5,
		));

		/* linkedin */
		$wp_customize->add_setting( 'zerif_socials_linkedin', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));
		$wp_customize->add_control( 'zerif_socials_linkedin', array(
			'label'    => __( 'Linkedin link', 'zerif-lite' ),
			'section'  => 'zerif_general_socials_section',
			'priority' => 6,
		));

		/* behance */
		$wp_customize->add_setting( 'zerif_socials_behance', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));

		$wp_customize->add_control( 'zerif_socials_behance', array(
			'label'    => __( 'Behance link', 'zerif-lite' ),
			'section'  => 'zerif_general_socials_section',
			'priority' => 7,
		));

		/* dribbble */
		$wp_customize->add_setting( 'zerif_socials_dribbble', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));

		$wp_customize->add_control( 'zerif_socials_dribbble', array(
			'label'    => __( 'Dribbble link', 'zerif-lite' ),
			'section'  => 'zerif_general_socials_section',
			'priority' => 8,
		));

		/* instagram */
		$wp_customize->add_setting( 'zerif_socials_instagram', array(
			'sanitize_callback' => 'esc_url_raw',
		));

		$wp_customize->add_control( 'zerif_socials_instagram', array(
			'label'    => __( 'Instagram link', 'zerif-lite' ),
			'section'  => 'zerif_general_socials_section',
			'priority' => 9,
		));

		$wp_customize->add_section( 'zerif_general_footer_section' , array(
			'title' => __( 'Footer Content', 'zerif-lite' ),
			'priority' => 32,
			'panel' => 'panel_general'
		));

		/* address - ICON */
		$wp_customize->add_setting( 'zerif_address_icon', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri().'/images/map25-redish.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'zerif_address_icon', array(
			'label'    => __( 'Address section - icon', 'zerif-lite' ),
			'section'  => 'zerif_general_footer_section',
			'priority' => 9,
		)));

		/* address */
		$wp_customize->add_setting( 'zerif_address', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Company address','zerif-lite'),
		));

		$wp_customize->add_control( new Zerif_Customize_Textarea_Control( $wp_customize, 'zerif_address', array(
			'label'   => __( 'Address', 'zerif-lite' ),
			'section' => 'zerif_general_footer_section',
			'priority' => 10
		)));

		/* email - ICON */
		$wp_customize->add_setting( 'zerif_email_icon', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri().'/images/envelope4-green.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'zerif_email_icon', array(
			'label'    => __( 'Email section - icon', 'zerif-lite' ),
			'section'  => 'zerif_general_footer_section',
			'priority'    => 11,
		)));

		/* email */
		$wp_customize->add_setting( 'zerif_email', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => '<a href="mailto:contact@site.com">contact@site.com</a>',
		));

		$wp_customize->add_control( new Zerif_Customize_Textarea_Control( $wp_customize, 'zerif_email', array(
			'label'   => __( 'Email', 'zerif-lite' ),
			'section' => 'zerif_general_footer_section',
			'priority' => 12
		)));

		/* phone number - ICON */
		$wp_customize->add_setting( 'zerif_phone_icon', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri().'/images/telephone65-blue.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'zerif_phone_icon', array(
			'label'    => __( 'Phone number section - icon', 'zerif-lite' ),
			'section'  => 'zerif_general_footer_section',
			'priority' => 13,
		)));

		/* phone number */
		$wp_customize->add_setting( 'zerif_phone', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => '<a href="tel:0 332 548 954">0 332 548 954</a>',
		));

		$wp_customize->add_control(new Zerif_Customize_Textarea_Control( $wp_customize, 'zerif_phone', array(
			'label'   => __( 'Phone number', 'zerif-lite' ),
			'section' => 'zerif_general_footer_section',
			'priority' => 14
		)));

	else: /* Old versions of WordPress */

		$wp_customize->add_section( 'zerif_general_section' , array(
			'title'       => __( 'General options', 'zerif-lite' ),
			'priority'    => 30,
			'description' => __('Zerif theme general options','zerif-lite'),
		));

		/* LOGO	*/
		$wp_customize->add_setting( 'zerif_logo', array(
			'sanitize_callback' => 'esc_url_raw'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_logo', array(
			'label'    => __( 'Logo', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'settings' => 'zerif_logo',
			'priority'    => 1,
		)));

		/* Disable preloader */
		$wp_customize->add_setting( 'zerif_disable_preloader', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_disable_preloader', array(
			'type' => 'checkbox',
			'label' => __('Disable preloader?','zerif-lite'),
			'section' => 'zerif_general_section',
			'priority'    => 2,
		));

		/* Disable smooth scroll */
		$wp_customize->add_setting( 'zerif_disable_smooth_scroll', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_disable_smooth_scroll', array(
			'type' 		=> 'checkbox',
			'label' 	=> __('Disable smooth scroll?','zerif-lite'),
			'section' 	=> 'zerif_general_section',
			'priority'	=> 3,
		));

		/* Enable accessibility */
		$wp_customize->add_setting( 'zerif_accessibility', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_accessibility', array(
			'type' 		=> 'checkbox',
			'label' 	=> __('Enable accessibility?','zerif-lite'),
			'section' 	=> 'zerif_general_section',
			'priority'	=> 4,
		));

		/* COPYRIGHT */
		$wp_customize->add_setting( 'zerif_copyright', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_copyright', array(
			'label'    => __( 'Footer Copyright', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 5,
		));

		/* facebook */
		$wp_customize->add_setting( 'zerif_socials_facebook', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));
		$wp_customize->add_control( 'zerif_socials_facebook', array(
			'label'    => __( 'Facebook link', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 6,
		));
		/* twitter */
		$wp_customize->add_setting( 'zerif_socials_twitter', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));
		$wp_customize->add_control( 'zerif_socials_twitter', array(
			'label'    => __( 'Twitter link', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 7,
		));
		/* linkedin */
		$wp_customize->add_setting( 'zerif_socials_linkedin', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));
		$wp_customize->add_control( 'zerif_socials_linkedin', array(
			'label'    => __( 'Linkedin link', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 8,
		));
		/* behance */
		$wp_customize->add_setting( 'zerif_socials_behance', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));
		$wp_customize->add_control( 'zerif_socials_behance', array(
			'label'    => __( 'Behance link', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 9,
		));
		/* dribbble */
		$wp_customize->add_setting( 'zerif_socials_dribbble', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => '#'
		));
		$wp_customize->add_control( 'zerif_socials_dribbble', array(
			'label'    => __( 'Dribbble link', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 10,
		));
		/* instagram */
		$wp_customize->add_setting( 'zerif_socials_instagram', array(
			'sanitize_callback' => 'esc_url_raw',
		));

		$wp_customize->add_control( 'zerif_socials_instagram', array(
			'label'    => __( 'Instagram link', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 11,
		));
		/* address - ICON */
		$wp_customize->add_setting( 'zerif_address_icon', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri().'/images/map25-redish.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'zerif_address_icon', array(
			'label'    => __( 'Address section - icon', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority' => 12,
		)));

		/* address */
		$wp_customize->add_setting( 'zerif_address', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Company address','zerif-lite')
		));

		$wp_customize->add_control( new Zerif_Customize_Textarea_Control( $wp_customize, 'zerif_address', array(
			'label'   => __( 'Address', 'zerif-lite' ),
			'section' => 'zerif_general_section',
			'priority' => 13
		)));
		/* email - ICON */
		$wp_customize->add_setting( 'zerif_email_icon', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri().'/images/envelope4-green.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'zerif_email_icon', array(
			'label'    => __( 'Email section - icon', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority'    => 14,
		)));

		/* email */
		$wp_customize->add_setting( 'zerif_email', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => '<a href="mailto:contact@site.com">contact@site.com</a>'
		));

		$wp_customize->add_control( new Zerif_Customize_Textarea_Control( $wp_customize, 'zerif_email', array(
			'label'   => __( 'Email', 'zerif-lite' ),
			'section' => 'zerif_general_section',
			'priority' => 15
		)));

		/* phone number - ICON */
		$wp_customize->add_setting( 'zerif_phone_icon', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri().'/images/telephone65-blue.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'zerif_phone_icon', array(
			'label'    => __( 'Phone number section - icon', 'zerif-lite' ),
			'section'  => 'zerif_general_section',
			'priority' => 16,
		)));

		/* phone number */
		$wp_customize->add_setting( 'zerif_phone', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => '<a href="tel:0 332 548 954">0 332 548 954</a>'
		));

		$wp_customize->add_control(new Zerif_Customize_Textarea_Control( $wp_customize, 'zerif_phone', array(
			'label'   => __( 'Phone number', 'zerif-lite' ),
			'section' => 'zerif_general_section',
			'priority' => 17
		)) );

	endif;

	/*****************************************************/
    /**************	BIG TITLE SECTION *******************/
	/****************************************************/

	if ( class_exists( 'WP_Customize_Panel' ) ):

		$wp_customize->add_panel( 'panel_big_title', array(
			'priority' => 31,
			'capability' => 'edit_theme_options',
			'title' => __( 'Big title section', 'zerif-lite' )
		));

		$wp_customize->add_section( 'zerif_bigtitle_section' , array(
			'title'       => __( 'Main content', 'zerif-lite' ),
			'priority'    => 1,
			'panel'       => 'panel_big_title'
		));

		/* show/hide */
		$wp_customize->add_setting( 'zerif_bigtitle_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_show', array(
			'type' => 'checkbox',
			'label' => __('Hide big title section?','zerif-lite'),
			'section' => 'zerif_bigtitle_section',
			'priority'    => 1,
		));

		/* title */
		$wp_customize->add_setting( 'zerif_bigtitle_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('ONE OF THE TOP 10 MOST POPULAR THEMES ON WORDPRESS.ORG','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 2,
		));

		/* red button */
		$wp_customize->add_setting( 'zerif_bigtitle_redbutton_label', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Features','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_redbutton_label', array(
			'label'    => __( 'Red button label', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 3,
		));

		$wp_customize->add_setting( 'zerif_bigtitle_redbutton_url', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => esc_url( home_url( '/' ) ).'#focus',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_redbutton_url', array(
			'label'    => __( 'Red button link', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 4,
		));

		/* green button */
		$wp_customize->add_setting( 'zerif_bigtitle_greenbutton_label', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __("What's inside",'zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_greenbutton_label', array(
			'label'    => __( 'Green button label', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 5,
		));

		$wp_customize->add_setting( 'zerif_bigtitle_greenbutton_url', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => esc_url( home_url( '/' ) ).'#focus',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_greenbutton_url', array(
			'label'    => __( 'Green button link', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 6,
		));

		/****************************************************/
		/************	PARALLAX IMAGES *********************/
		/****************************************************/

		$wp_customize->add_section( 'zerif_parallax_section' , array(
			'title'       => __( 'Parallax effect', 'zerif-lite' ),
			'priority'    => 2,
			'panel'       => 'panel_big_title'
		));

		/* show/hide */
		$wp_customize->add_setting( 'zerif_parallax_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_parallax_show', array(
			'type' 		=> 'checkbox',
			'label' 	=> __('Use parallax effect?','zerif-lite'),
			'section' 	=> 'zerif_parallax_section',
			'priority'	=> 1,
		));

		/* IMAGE 1*/
		$wp_customize->add_setting( 'zerif_parallax_img1', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri() . '/images/background1.jpg'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_parallax_img1', array(
			'label'    	=> __( 'Image 1', 'zerif-lite' ),
			'section'  	=> 'zerif_parallax_section',
			'settings' 	=> 'zerif_parallax_img1',
			'priority'	=> 1,
		)));

		/* IMAGE 2 */
		$wp_customize->add_setting( 'zerif_parallax_img2', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri() . '/images/background2.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_parallax_img2', array(
			'label'    	=> __( 'Image 2', 'zerif-lite' ),
			'section'  	=> 'zerif_parallax_section',
			'settings' 	=> 'zerif_parallax_img2',
			'priority'	=> 2,
		)));

		/*************************************************************/
		/************* Video background(available in pro) ************/
		/*************************************************************/

		$wp_customize->add_section( 'zerif_videobackground_in_pro_section' , array(
			'title'       => __( 'Video background', 'zerif-lite' ),
			'priority'    => 3,
			'panel'       => 'panel_big_title'
		));

		$wp_customize->add_setting( 'zerif_videobackground_in_pro', array(
			'sanitize_callback' => 'sanitize_text_field'
		));

		$wp_customize->add_control( new Zerif_Theme_Support_Videobackground( $wp_customize, 'zerif_videobackground_in_pro', array(
			'section' => 'zerif_videobackground_in_pro_section',
		)));

	else:

		$wp_customize->add_section( 'zerif_bigtitle_section' , array(
			'title'       => __( 'Big title section', 'zerif-lite' ),
			'priority'    => 31
		));

		/* show/hide */
		$wp_customize->add_setting( 'zerif_bigtitle_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_show', array(
			'type' => 'checkbox',
			'label' => __('Hide big title section?','zerif-lite'),
			'section' => 'zerif_bigtitle_section',
			'priority'    => 1,
		));

		/* title */
		$wp_customize->add_setting( 'zerif_bigtitle_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('ONE OF THE TOP 10 MOST POPULAR THEMES ON WORDPRESS.ORG','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 2,
		));

		/* red button */
		$wp_customize->add_setting( 'zerif_bigtitle_redbutton_label', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Features','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_redbutton_label', array(
			'label'    => __( 'Red button label', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 3,
		));

		$wp_customize->add_setting( 'zerif_bigtitle_redbutton_url', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => esc_url( home_url( '/' ) ).'#focus',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_redbutton_url', array(
			'label'    => __( 'Red button link', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 4,
		));

		/* green button */
		$wp_customize->add_setting( 'zerif_bigtitle_greenbutton_label', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __("What's inside",'zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_greenbutton_label', array(
			'label'    => __( 'Green button label', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 5,
		));

		$wp_customize->add_setting( 'zerif_bigtitle_greenbutton_url', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => esc_url( home_url( '/' ) ).'#focus',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bigtitle_greenbutton_url', array(
			'label'    => __( 'Green button link', 'zerif-lite' ),
			'section'  => 'zerif_bigtitle_section',
			'priority'    => 6,
		));

		/****************************************************/
		/************	PARALLAX IMAGES *********************/
		/****************************************************/
		$wp_customize->add_section( 'zerif_parallax_section' , array(
			'title'       => __( 'Parallax effect', 'zerif-lite' ),
			'priority'    => 60
		));

		/* show/hide */
		$wp_customize->add_setting( 'zerif_parallax_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_parallax_show', array(
			'type' 		=> 'checkbox',
			'label' 	=> __('Use parallax effect?','zerif-lite'),
			'section' 	=> 'zerif_parallax_section',
			'priority'	=> 1,
		));

		/* IMAGE 1*/
		$wp_customize->add_setting( 'zerif_parallax_img1', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri() . '/images/background1.jpg'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_parallax_img1', array(
			'label'    	=> __( 'Image 1', 'zerif-lite' ),
			'section'  	=> 'zerif_parallax_section',
			'settings' 	=> 'zerif_parallax_img1',
			'priority'	=> 1,
		)));

		/* IMAGE 2 */
		$wp_customize->add_setting( 'zerif_parallax_img2', array(
			'sanitize_callback' => 'esc_url_raw',
			'default' => get_template_directory_uri() . '/images/background2.png'
		));

		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_parallax_img2', array(
			'label'    	=> __( 'Image 2', 'zerif-lite' ),
			'section'  	=> 'zerif_parallax_section',
			'settings' 	=> 'zerif_parallax_img2',
			'priority'	=> 2,
		)));

		/*************************************************************/
		/************* Video background(available in pro) ************/
		/*************************************************************/

		$wp_customize->add_section( 'zerif_videobackground_in_pro_section' , array(
			'title'       => __( 'Video background', 'zerif-lite' ),
			'priority'    => 61
		));

		$wp_customize->add_setting( 'zerif_videobackground_in_pro', array(
			'sanitize_callback' => 'sanitize_text_field'
		));

		$wp_customize->add_control( new Zerif_Theme_Support_Videobackground( $wp_customize, 'zerif_videobackground_in_pro', array(
			'section' => 'zerif_videobackground_in_pro_section',
		)));

	endif;


	/********************************************************************/
	/*************  OUR FOCUS SECTION **********************************/
	/********************************************************************/
	if ( class_exists( 'WP_Customize_Panel' ) ):

		$wp_customize->add_panel( 'panel_ourfocus', array(
			'priority' => 32,
			'capability' => 'edit_theme_options',
			'title' => __( 'Our focus section', 'zerif-lite' )
		));

		$wp_customize->add_section( 'zerif_ourfocus_section' , array(
			'title'       => __( 'Content', 'zerif-lite' ),
			'priority'    => 1,
			'panel'       => 'panel_ourfocus'
		));

		/* show/hide */
		$wp_customize->add_setting( 'zerif_ourfocus_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourfocus_show', array(
			'type' => 'checkbox',
			'label' => __('Hide our focus section?','zerif-lite'),
			'section' => 'zerif_ourfocus_section',
			'priority'    => 1,
		));

		/* our focus title */
		$wp_customize->add_setting( 'zerif_ourfocus_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('FEATURES','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourfocus_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_ourfocus_section',
			'priority'    => 2,
		));

		/* our focus subtitle */
		$wp_customize->add_setting( 'zerif_ourfocus_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('What makes this single-page WordPress theme unique.','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourfocus_subtitle', array(
			'label'    => __( 'Our focus subtitle', 'zerif-lite' ),
			'section'  => 'zerif_ourfocus_section',
			'priority'    => 3,
		));

	else:

		$wp_customize->add_section( 'zerif_ourfocus_section' , array(
			'title'       => __( 'Our focus section', 'zerif-lite' ),
			'priority'    => 32,
			'description' => __( 'The main content of this section is customizable in: Dashboard -> Appearance -> Widgets -> Our focus section. There you must add the "Zerif - Our focus widget"', 'zerif-lite' )
		));

		/* show/hide */
		$wp_customize->add_setting( 'zerif_ourfocus_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourfocus_show', array(
			'type' => 'checkbox',
			'label' => __('Hide our focus section?','zerif-lite'),
			'section' => 'zerif_ourfocus_section',
			'priority'    => 1,
		));

		/* our focus title */
		$wp_customize->add_setting( 'zerif_ourfocus_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('FEATURES','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourfocus_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_ourfocus_section',
			'priority'    => 2,
		));

		/* our focus subtitle */
		$wp_customize->add_setting( 'zerif_ourfocus_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('What makes this single-page WordPress theme unique.','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourfocus_subtitle', array(
			'label'    => __( 'Our focus subtitle', 'zerif-lite' ),
			'section'  => 'zerif_ourfocus_section',
			'priority'    => 3,
		));

	endif;

	/************************************/
	/******* ABOUT US SECTION ***********/
	/************************************/
	if ( class_exists( 'WP_Customize_Panel' ) ):

		$wp_customize->add_panel( 'panel_about', array(
			'priority' => 34,
			'capability' => 'edit_theme_options',
			'title' => __( 'About us section', 'zerif-lite' )
		));

		$wp_customize->add_section( 'zerif_aboutus_settings_section' , array(
			'title'       => __( 'Settings', 'zerif-lite' ),
			'priority'    => 1,
			'panel' => 'panel_about'
		));

		/* about us show/hide */
		$wp_customize->add_setting( 'zerif_aboutus_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_show', array(
			'type' => 'checkbox',
			'label' => __('Hide about us section?','zerif-lite'),
			'section' => 'zerif_aboutus_settings_section',
			'priority'    => 1,
		));

		$wp_customize->add_section( 'zerif_aboutus_main_section' , array(
			'title'       => __( 'Main content', 'zerif-lite' ),
			'priority'    => 2,
			'panel' => 'panel_about'
		));

		/* title */
		$wp_customize->add_setting( 'zerif_aboutus_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('About','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_main_section',
			'priority'    => 2,
		));

		/* subtitle */
		$wp_customize->add_setting( 'zerif_aboutus_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Use this section to showcase important details about your business.','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_subtitle', array(
			'label'    => __( 'Subtitle', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_main_section',
			'priority'    => 3,
		));

		/* big left title */
		$wp_customize->add_setting( 'zerif_aboutus_biglefttitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Everything you see here is responsive and mobile-friendly.','zerif-lite'),
		));

		$wp_customize->add_control( 'zerif_aboutus_biglefttitle', array(
			'label'    => __( 'Big left side title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_main_section',
			'priority'    => 4,
		));

		/* text */
		$wp_customize->add_setting( 'zerif_aboutus_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec massa enim. Aliquam viverra at est ullamcorper sollicitudin. Proin a leo sit amet nunc malesuada imperdiet pharetra ut eros.<br><br> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec massa enim. Aliquam viverra at est ullamcorper sollicitudin. Proin a leo sit amet nunc malesuada imperdiet pharetra ut eros. <br><br>Mauris vel nunc at ipsum fermentum pellentesque quis ut massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas non adipiscing massa. Sed ut fringilla sapien. Cras sollicitudin, lectus sed tincidunt cursus, magna lectus vehicula augue, a lobortis dui orci et est.','zerif-lite'),
		));

		$wp_customize->add_control( 'zerif_aboutus_text', array(
			'type'	=>	'textarea',
			'label'    => __( 'Text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_main_section',
			'priority'    => 5,
		));

		$wp_customize->add_section( 'zerif_aboutus_feat1_section' , array(
			'title'       => __( 'Feature no#1', 'zerif-lite' ),
			'priority'    => 3,
			'panel' => 'panel_about'
		));

		/* feature no#1 */
		$wp_customize->add_setting( 'zerif_aboutus_feature1_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #1','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature1_title', array(
			'label'    => __( 'Feature no1 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat1_section',
			'priority'    => 6,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature1_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature1_text', array(
			'label'    => __( 'Feature no1 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat1_section',
			'priority'    => 7,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature1_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '80'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature1_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no1 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_feat1_section',
			'priority'    => 8
		)));

		$wp_customize->add_section( 'zerif_aboutus_feat2_section' , array(
			'title'       => __( 'Feature no#2', 'zerif-lite' ),
			'priority'    => 4,
			'panel' => 'panel_about'
		));

		/* feature no#2 */
		$wp_customize->add_setting( 'zerif_aboutus_feature2_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #2','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature2_title', array(
			'label'    => __( 'Feature no2 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat2_section',
			'priority'    => 9,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature2_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature2_text', array(
			'label'    => __( 'Feature no2 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat2_section',
			'priority'    => 10,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature2_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '91'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature2_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no2 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_feat2_section',
			'priority'    => 11
		)));

		$wp_customize->add_section( 'zerif_aboutus_feat3_section' , array(
			'title'       => __( 'Feature no#3', 'zerif-lite' ),
			'priority'    => 5,
			'panel' => 'panel_about'
		));

		/* feature no#3 */
		$wp_customize->add_setting( 'zerif_aboutus_feature3_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #3','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature3_title', array(
			'label'    => __( 'Feature no3 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat3_section',
			'priority'    => 12,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature3_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature3_text', array(
			'label'    => __( 'Feature no3 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat3_section',
			'priority'    => 13,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature3_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '88'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature3_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no3 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_feat3_section',
			'priority'    => 14
		)));

		$wp_customize->add_section( 'zerif_aboutus_feat4_section' , array(
			'title'       => __( 'Feature no#4', 'zerif-lite' ),
			'priority'    => 6,
			'panel' => 'panel_about'
		));

		/* feature no#4 */
		$wp_customize->add_setting( 'zerif_aboutus_feature4_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #4','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature4_title', array(
			'label'    => __( 'Feature no4 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat4_section',
			'priority'    => 15,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature4_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature4_text', array(
			'label'    => __( 'Feature no4 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_feat4_section',
			'priority'    => 16,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature4_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '95'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature4_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no4 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_feat4_section',
			'priority' => 17
		)));

	else:	/* Old versions of WordPress */

		$wp_customize->add_section( 'zerif_aboutus_section' , array(
			'title' => __( 'About us section', 'zerif-lite' ),
			'priority' => 34
		));

		/* about us show/hide */
		$wp_customize->add_setting( 'zerif_aboutus_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_show', array(
			'type' => 'checkbox',
			'label' => __('Hide about us section?','zerif-lite'),
			'section' => 'zerif_aboutus_section',
			'priority'    => 1,
		));

		/* title */
		$wp_customize->add_setting( 'zerif_aboutus_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('About','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 2,
		));

		/* subtitle */
		$wp_customize->add_setting( 'zerif_aboutus_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Use this section to showcase important details about your business.','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_subtitle', array(
			'label'    => __( 'Subtitle', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 3,
		));

		/* big left title */
		$wp_customize->add_setting( 'zerif_aboutus_biglefttitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Everything you see here is responsive and mobile-friendly.','zerif-lite'),
		));

		$wp_customize->add_control( 'zerif_aboutus_biglefttitle', array(
			'label'    => __( 'Big left side title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 4,
		));

		/* text */
		$wp_customize->add_setting( 'zerif_aboutus_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec massa enim. Aliquam viverra at est ullamcorper sollicitudin. Proin a leo sit amet nunc malesuada imperdiet pharetra ut eros.<br><br> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec massa enim. Aliquam viverra at est ullamcorper sollicitudin. Proin a leo sit amet nunc malesuada imperdiet pharetra ut eros. <br><br>Mauris vel nunc at ipsum fermentum pellentesque quis ut massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas non adipiscing massa. Sed ut fringilla sapien. Cras sollicitudin, lectus sed tincidunt cursus, magna lectus vehicula augue, a lobortis dui orci et est.','zerif-lite'),
		));

		$wp_customize->add_control( 'zerif_aboutus_text', array(
			'label'    => __( 'Text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 5,
		));

		/* feature no#1 */
		$wp_customize->add_setting( 'zerif_aboutus_feature1_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #1','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature1_title', array(
			'label'    => __( 'Feature no1 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 6,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature1_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature1_text', array(
			'label'    => __( 'Feature no1 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 7,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature1_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '80'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature1_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no1 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_section',
			'priority'    => 8
		)));

		/* feature no#2 */
		$wp_customize->add_setting( 'zerif_aboutus_feature2_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #2','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature2_title', array(
			'label'    => __( 'Feature no2 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 9,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature2_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature2_text', array(
			'label'    => __( 'Feature no2 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 10,
		));
		$wp_customize->add_setting( 'zerif_aboutus_feature2_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '91'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature2_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no2 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_section',
			'priority'    => 11
		)));

		/* feature no#3 */
		$wp_customize->add_setting( 'zerif_aboutus_feature3_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #3','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature3_title', array(
			'label'    => __( 'Feature no3 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 12,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature3_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature3_text', array(
			'label'    => __( 'Feature no3 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 13,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature3_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '88'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature3_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no3 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_section',
			'priority'    => 14
		)));

		/* feature no#4 */
		$wp_customize->add_setting( 'zerif_aboutus_feature4_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR SKILL #4','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature4_title', array(
			'label'    => __( 'Feature no4 title', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 15,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature4_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_aboutus_feature4_text', array(
			'label'    => __( 'Feature no4 text', 'zerif-lite' ),
			'section'  => 'zerif_aboutus_section',
			'priority'    => 16,
		));

		$wp_customize->add_setting( 'zerif_aboutus_feature4_nr', array(
			'sanitize_callback' => 'absint',
			'default' => '95'
		));

		$wp_customize->add_control( new Zerif_Customizer_Number_Control( $wp_customize, 'zerif_aboutus_feature4_nr', array(
			'type' => 'number',
			'label' => __( 'Feature no4 percentage', 'zerif-lite' ),
			'section' => 'zerif_aboutus_section',
			'priority'    => 17
		)));

	endif;

	/******************************************/
    /**********	OUR TEAM SECTION **************/
	/******************************************/
	if ( class_exists( 'WP_Customize_Panel' ) ):

		$wp_customize->add_panel( 'panel_ourteam', array(
			'priority' => 35,
			'capability' => 'edit_theme_options',
			'title' => __( 'Our team section', 'zerif-lite' )
		));

		$wp_customize->add_section( 'zerif_ourteam_section' , array(
			'title'       => __( 'Content', 'zerif-lite' ),
			'priority'    => 1,
			'panel'       => 'panel_ourteam'
		));

		/* our team show/hide */
		$wp_customize->add_setting( 'zerif_ourteam_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourteam_show', array(
			'type' => 'checkbox',
			'label' => __('Hide our team section?','zerif-lite'),
			'section' => 'zerif_ourteam_section',
			'priority'    => 1,
		));

		/* our team title */
		$wp_customize->add_setting( 'zerif_ourteam_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR TEAM','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourteam_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_ourteam_section',
			'priority'    => 2,
		));

		/* our team subtitle */
		$wp_customize->add_setting( 'zerif_ourteam_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Prove that you have real people working for you, with some nice looking profile pictures and links to social media.','zerif-lite'),'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourteam_subtitle', array(
			'label'    => __( 'Our team subtitle', 'zerif-lite' ),
			'section'  => 'zerif_ourteam_section',
			'priority'    => 3,
		));

	else:

		$wp_customize->add_section( 'zerif_ourteam_section' , array(
			'title'       => __( 'Our team section', 'zerif-lite' ),
			'priority'    => 35,
			'description' => __( 'The main content of this section is customizable in: Dashboard -> Appearance -> Widgets -> Our team section. There you must add the "Zerif - Team member widget"', 'zerif-lite' )
		));

		/* our team show/hide */
		$wp_customize->add_setting( 'zerif_ourteam_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourteam_show', array(
			'type' => 'checkbox',
			'label' => __('Hide our team section?','zerif-lite'),
			'section' => 'zerif_ourteam_section',
			'priority'    => 1,
		));

		/* our team title */
		$wp_customize->add_setting( 'zerif_ourteam_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('YOUR TEAM','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourteam_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_ourteam_section',
			'priority'    => 2,
		));

		/* our team subtitle */
		$wp_customize->add_setting( 'zerif_ourteam_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Prove that you have real people working for you, with some nice looking profile pictures and links to social media.','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ourteam_subtitle', array(
			'label'    => __( 'Our team subtitle', 'zerif-lite' ),
			'section'  => 'zerif_ourteam_section',
			'priority'    => 3,
		));

	endif;

	/**********************************************/
    /**********	TESTIMONIALS SECTION **************/
	/**********************************************/
	if ( class_exists( 'WP_Customize_Panel' ) ):

		$wp_customize->add_panel( 'panel_testimonials', array(
			'priority' => 36,
			'capability' => 'edit_theme_options',
			'title' => __( 'Testimonials section', 'zerif-lite' )
		) );

		$wp_customize->add_section( 'zerif_testimonials_section' , array(
			'title'       => __( 'Testimonials section', 'zerif-lite' ),
			'priority'    => 1,
			'panel'       => 'panel_testimonials'
		));

		/* testimonials show/hide */
		$wp_customize->add_setting( 'zerif_testimonials_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_testimonials_show', array(
			'type' => 'checkbox',
			'label' => __('Hide testimonials section?','zerif-lite'),
			'section' => 'zerif_testimonials_section',
			'priority'    => 1,
		));

		/* testimonial pinterest layout */
		$wp_customize->add_setting( 'zerif_testimonials_pinterest_style', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_testimonials_pinterest_style', array(
			'type' 			=> 'checkbox',
			'label' 		=> __('Use pinterest layout?','zerif-lite'),
			'section' 		=> 'zerif_testimonials_section',
			'priority'    	=> 2,
		));

		/* testimonials title */
		$wp_customize->add_setting( 'zerif_testimonials_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Testimonials','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_testimonials_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_testimonials_section',
			'priority'    => 2,
		));

		/* testimonials subtitle */
		$wp_customize->add_setting( 'zerif_testimonials_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_testimonials_subtitle', array(
			'label'    => __( 'Testimonials subtitle', 'zerif-lite' ),
			'section'  => 'zerif_testimonials_section',
			'priority'    => 3,
		));

	else:

		$wp_customize->add_section( 'zerif_testimonials_section' , array(
			'title'       => __( 'Testimonials section', 'zerif-lite' ),
			'priority'    => 36,
			'description' => __( 'The main content of this section is customizable in: Dashboard -> Appearance -> Widgets -> Testimonials section. There you must add the "Zerif - Testimonial widget"', 'zerif-lite' )
		));

		/* testimonials show/hide */
		$wp_customize->add_setting( 'zerif_testimonials_show', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_testimonials_show', array(
			'type' => 'checkbox',
			'label' => __('Hide testimonials section?','zerif-lite'),
			'section' => 'zerif_testimonials_section',
			'priority'    => 1,
		));

		/* testimonial pinterest layout */
		$wp_customize->add_setting( 'zerif_testimonials_pinterest_style', array(
			'sanitize_callback' => 'zerif_sanitize_checkbox'
		));

		$wp_customize->add_control( 'zerif_testimonials_pinterest_style', array(
			'type' 			=> 'checkbox',
			'label' 		=> __('Use pinterest layout?','zerif-lite'),
			'section' 		=> 'zerif_testimonials_section',
			'priority'    	=> 2,
		));

		/* testimonials title */
		$wp_customize->add_setting( 'zerif_testimonials_title', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'default' => __('Testimonials','zerif-lite'),
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_testimonials_title', array(
			'label'    => __( 'Title', 'zerif-lite' ),
			'section'  => 'zerif_testimonials_section',
			'priority'    => 2,
		));

		/* testimonials subtitle */
		$wp_customize->add_setting( 'zerif_testimonials_subtitle', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_testimonials_subtitle', array(
			'label'    => __( 'Testimonials subtitle', 'zerif-lite' ),
			'section'  => 'zerif_testimonials_section',
			'priority'    => 3,
		));

	endif;

	/**********************************************/
    /**********	LATEST NEWS SECTION ***************/
	/**********************************************/
	$wp_customize->add_section( 'zerif_latestnews_section' , array(
		'title'       => __( 'Latest News section', 'zerif-lite' ),
    	'priority'    => 37
	));

	/* latest news show/hide */
	$wp_customize->add_setting( 'zerif_latestnews_show', array(
		'sanitize_callback' => 'zerif_sanitize_checkbox',
		'transport' => 'postMessage'
	));

    $wp_customize->add_control( 'zerif_latestnews_show', array(
		'type' => 'checkbox',
		'label' => __('Hide latest news section?','zerif-lite'),
		'section' => 'zerif_latestnews_section',
		'priority'    => 1,
	));

	/* latest news title */
	$wp_customize->add_setting( 'zerif_latestnews_title', array(
		'sanitize_callback' => 'zerif_sanitize_input',
		'transport' => 'postMessage'
	));

	$wp_customize->add_control( 'zerif_latestnews_title', array(
		'label'    		=> __( 'Latest News title', 'zerif-lite' ),
		'section'  		=> 'zerif_latestnews_section',
		'priority'    	=> 2,
	));

	/* latest news subtitle */
	$wp_customize->add_setting( 'zerif_latestnews_subtitle', array(
		'sanitize_callback' => 'zerif_sanitize_input',
		'transport' => 'postMessage'
	));

	$wp_customize->add_control( 'zerif_latestnews_subtitle', array(
		'label'    		=> __( 'Latest News subtitle', 'zerif-lite' ),
	    'section'  		=> 'zerif_latestnews_section',
		'priority'   	=> 3,
	));

	/***********************************************************/
	/********* RIBBONS ****************************************/
	/**********************************************************/
	if ( class_exists( 'WP_Customize_Panel' ) ):

		$wp_customize->add_panel( 'panel_ribbons', array(
			'priority' => 37,
			'capability' => 'edit_theme_options',
			'title' => __( 'Ribbon sections', 'zerif-lite' )
		));

		$wp_customize->add_section( 'zerif_bottomribbon_section' , array(
			'title'       => __( 'BottomButton Ribbon', 'zerif-lite' ),
			'priority'    => 1,
			'panel'       => 'panel_ribbons'
		));

		/* RIBBON SECTION WITH BOTTOM BUTTON */

		/* text */
		$wp_customize->add_setting( 'zerif_bottomribbon_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bottomribbon_text', array(
			'type'	=>	'textarea',
			'label'    => __( 'Text', 'zerif-lite' ),
			'section'  => 'zerif_bottomribbon_section',
			'priority'    => 1,
		));

		/* button label */
		$wp_customize->add_setting( 'zerif_bottomribbon_buttonlabel', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bottomribbon_buttonlabel', array(
			'label'    => __( 'Button label', 'zerif-lite' ),
			'section'  => 'zerif_bottomribbon_section',
			'priority'    => 2,
		));

		/* button link */
		$wp_customize->add_setting( 'zerif_bottomribbon_buttonlink', array(
			'sanitize_callback' => 'esc_url_raw',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bottomribbon_buttonlink', array(
			'label'    => __( 'Button link', 'zerif-lite' ),
			'section'  => 'zerif_bottomribbon_section',
			'priority'    => 3,
		));

		$wp_customize->add_section( 'zerif_rightribbon_section' , array(
			'title'       => __( 'RightButton Ribbon', 'zerif-lite' ),
			'priority'    => 2,
			'panel'       => 'panel_ribbons'
		));

		/* RIBBON SECTION WITH BUTTON IN THE RIGHT SIDE */

		/* text */
		$wp_customize->add_setting( 'zerif_ribbonright_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ribbonright_text', array(
			'type'	=>	'textarea',
			'label'    => __( 'Text', 'zerif-lite' ),
			'section'  => 'zerif_rightribbon_section',
			'priority'    => 4,
		));

		/* button label */
		$wp_customize->add_setting( 'zerif_ribbonright_buttonlabel', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ribbonright_buttonlabel', array(
			'label'    => __( 'Button label', 'zerif-lite' ),
			'section'  => 'zerif_rightribbon_section',
			'priority'    => 5,
		));

		/* button link */
		$wp_customize->add_setting( 'zerif_ribbonright_buttonlink', array(
			'sanitize_callback' => 'esc_url_raw',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ribbonright_buttonlink', array(
			'label'    => __( 'Button link', 'zerif-lite' ),
			'section'  => 'zerif_rightribbon_section',
			'priority'    => 6,
		));

	else: /* Old versions of WordPress */
		$wp_customize->add_section( 'zerif_ribbon_section' , array(
			'title'       => __( 'Ribbon sections', 'zerif-lite' ),
			'priority'    => 37
		));

		/* RIBBON SECTION WITH BOTTOM BUTTON */

		/* text */
		$wp_customize->add_setting( 'zerif_bottomribbon_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bottomribbon_text', array(
			'label'    => __( 'Ribbon section with bottom button - Text', 'zerif-lite' ),
			'section'  => 'zerif_ribbon_section',
			'priority'    => 1,
		));

		/* button label */
		$wp_customize->add_setting( 'zerif_bottomribbon_buttonlabel', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bottomribbon_buttonlabel', array(
			'label'    => __( 'Ribbon section with bottom button - Button label', 'zerif-lite' ),
			'section'  => 'zerif_ribbon_section',
			'priority'    => 2,
		));

		/* button link */
		$wp_customize->add_setting( 'zerif_bottomribbon_buttonlink', array(
			'sanitize_callback' => 'esc_url_raw',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_bottomribbon_buttonlink', array(
			'label'    => __( 'Ribbon section with bottom button - Button link', 'zerif-lite' ),
			'section'  => 'zerif_ribbon_section',
			'priority'    => 3,
		));

		/* RIBBON SECTION WITH BUTTON IN THE RIGHT SIDE */

		/* text */
		$wp_customize->add_setting( 'zerif_ribbonright_text', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ribbonright_text', array(
			'label'    => __( 'Ribbon section with button in the right side - Text', 'zerif-lite' ),
			'section'  => 'zerif_ribbon_section',
			'priority'    => 4,
		));

		/* button label */
		$wp_customize->add_setting( 'zerif_ribbonright_buttonlabel', array(
			'sanitize_callback' => 'zerif_sanitize_input',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ribbonright_buttonlabel', array(
			'label'    => __( 'Ribbon section with button in the right side - Button label', 'zerif-lite' ),
			'section'  => 'zerif_ribbon_section',
			'priority'    => 5,
		));

		/* button link */
		$wp_customize->add_setting( 'zerif_ribbonright_buttonlink', array(
			'sanitize_callback' => 'esc_url_raw',
			'transport' => 'postMessage'
		));

		$wp_customize->add_control( 'zerif_ribbonright_buttonlink', array(
			'label'    => __( 'Ribbon section with button in the right side - Button link', 'zerif-lite' ),
			'section'  => 'zerif_ribbon_section',
			'priority'    => 6,
		));
	endif;

	/*******************************************************/
    /************	CONTACT US SECTION *********************/
	/*******************************************************/

	$zerif_contact_us_section_description = '';

	/* if Pirate Forms is installed */
	if( defined("PIRATE_FORMS_VERSION") ):
		$zerif_contact_us_section_description = __( 'For more advanced settings please go to Settings -> Pirate Forms','zerif-lite' );
	endif;

	$wp_customize->add_section( 'zerif_contactus_section' , array(
		'title'       => __( 'Contact us section', 'zerif-lite' ),
		'description' => $zerif_contact_us_section_description,
    	'priority'    => 38
	));

	/* contact us show/hide */
	$wp_customize->add_setting( 'zerif_contactus_show', array(
		'sanitize_callback' => 'zerif_sanitize_checkbox',
		'transport' => 'postMessage'
	));

    $wp_customize->add_control( 'zerif_contactus_show', array(
		'type' => 'checkbox',
		'label' => __('Hide contact us section?','zerif-lite'),
		'section' => 'zerif_contactus_section',
		'priority'    => 1,
	));

	/* contactus title */
	$wp_customize->add_setting( 'zerif_contactus_title', array(
		'sanitize_callback' => 'zerif_sanitize_input',
		'default' => __('Get in touch','zerif-lite'),
		'transport' => 'postMessage'
	));

	$wp_customize->add_control( 'zerif_contactus_title', array(
		'label'    => __( 'Contact us section title', 'zerif-lite' ),
		'section'  => 'zerif_contactus_section',
		'priority'    => 2,
	));

	/* contactus subtitle */
	$wp_customize->add_setting( 'zerif_contactus_subtitle', array(
		'sanitize_callback' => 'zerif_sanitize_input',
		'transport' => 'postMessage'
	));

	$wp_customize->add_control( 'zerif_contactus_subtitle', array(
		'label'    => __( 'Contact us section subtitle', 'zerif-lite' ),
	    'section'  => 'zerif_contactus_section',
		'priority'    => 3,
	));

	/* contactus email */
	$wp_customize->add_setting( 'zerif_contactus_email', array(
		'sanitize_callback' => 'sanitize_email'
	));

	$wp_customize->add_control( 'zerif_contactus_email', array(
		'label'    => __( 'Email address', 'zerif-lite' ),
		'section'  => 'zerif_contactus_section',
		'priority'    => 4,
	));

	/* contactus button label */
	$wp_customize->add_setting( 'zerif_contactus_button_label', array(
		'sanitize_callback' => 'zerif_sanitize_input',
		'default' => __('Send Message','zerif-lite'),
		'transport' => 'postMessage'
	));
			
	$wp_customize->add_control( 'zerif_contactus_button_label', array(
		'label'    => __( 'Button label', 'zerif-lite' ),
		'section'  => 'zerif_contactus_section',
		'priority'    => 5,
	));
	
	/* recaptcha */
	$wp_customize->add_setting( 'zerif_contactus_recaptcha_show', array(
		'sanitize_callback' => 'zerif_sanitize_checkbox'
	));
	
	$wp_customize->add_control( 'zerif_contactus_recaptcha_show', array(
		'type' => 'checkbox',
		'label' => __('Hide reCaptcha?','zerif-lite'),
		'section' => 'zerif_contactus_section',
		'priority'    => 6,
	));
	
	/* site key */
	$attribut_new_tab = (isset($zerif_accessibility) && ($zerif_accessibility != 1) ? ' target="_blank"' : '' );
	$wp_customize->add_setting( 'zerif_contactus_sitekey', array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	
	$wp_customize->add_control( 'zerif_contactus_sitekey', array(
		'label'    => __( 'Site key', 'zerif-lite' ),
		'description' => '<a'.$attribut_new_tab.' href="https://www.google.com/recaptcha/admin#list">'.__('Create an account here','zerif-lite').'</a> to get the Site key and the Secret key for the reCaptcha.',
		'section'  => 'zerif_contactus_section',
		'priority'    => 7,
	));
	
	/* secret key */
	$wp_customize->add_setting( 'zerif_contactus_secretkey', array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	
	$wp_customize->add_control( 'zerif_contactus_secretkey', array(
		'label'    => __( 'Secret key', 'zerif-lite' ),
		'section'  => 'zerif_contactus_section',
		'priority'    => 8,
	));

	/****************************************/
	/*******	Google maps section *********/
	/****************************************/
	
	$wp_customize->add_section( 'zerif_googlemap_section' , array(
		'title'       => __( 'Google maps section', 'zerif-lite' ),
		'priority'    => 120
	));
	
	$wp_customize->add_setting(	'zerif_googlemap_section', array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	
	$wp_customize->add_control( new Zerif_Theme_Support_Googlemap( $wp_customize, 'zerif_googlemap_section', array(
		'section' => 'zerif_googlemap_section',
	)));
	
	/***************************************/
	/**********	  Pricing section   ********/
	/***************************************/
	
	$wp_customize->add_section( 'zerif_pricing_section' , array(
		'title'       => __( 'Pricing section', 'zerif-lite' ),
		'priority'    => 121
	));
	
	$wp_customize->add_setting( 'zerif_pricing_section', array(
		'sanitize_callback' => 'sanitize_text_field'
	));
	
	$wp_customize->add_control( new Zerif_Theme_Support_Pricing( $wp_customize, 'zerif_pricing_section', array(
		'section' => 'zerif_pricing_section',
	)));
	
}
add_action( 'customize_register', 'zerif_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function zerif_customize_preview_js() {
	wp_enqueue_script( 'zerif_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'zerif_customize_preview_js' );

function zerif_sanitize_input($input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}

function zerif_sanitize_checkbox( $input ){
	return ( isset( $input ) && true == $input ? true : false );
}


function zerif_registers() {

	wp_enqueue_script( 'zerif_customizer_script', get_template_directory_uri() . '/js/zerif_customizer.js', array("jquery"), '20120206', true  );
	
	wp_localize_script( 'zerif_customizer_script', 'zerifLiteCustomizerObject', array(
		
		'documentation' => __( 'View Documentation', 'zerif-lite' ),
		'pro' => __('View PRO version','zerif-lite')

	) );
}
add_action( 'customize_controls_enqueue_scripts', 'zerif_registers' );

/* ajax callback for dismissable Asking for reviews */
add_action( 'wp_ajax_zerif_lite_dismiss_asking_for_reviews','zerif_lite_dismiss_asking_for_reviews_callback' );
add_action( 'wp_ajax_nopriv_zerif_lite_dismiss_asking_for_reviews','zerif_lite_dismiss_asking_for_reviews_callback' );

/**
 * Dismiss asking for reviews
 */
function zerif_lite_dismiss_asking_for_reviews_callback() {
	
	if( !empty($_POST['ask']) ) {
		set_theme_mod('zerif_lite_ask_for_review',esc_attr($_POST['ask']));
	}

	die();
}

add_action( 'customize_controls_enqueue_scripts', 'zerif_lite_asking_for_reviews_script' );

function zerif_lite_asking_for_reviews_script() {
	
	$zerif_lite_review = 'yes';
	
	$zerif_lite_ask_for_review = get_theme_mod('zerif_lite_ask_for_review');
	if( !empty($zerif_lite_ask_for_review) ) {
		$zerif_lite_review = $zerif_lite_ask_for_review;
	}

	wp_enqueue_script( 'zerif-lite-asking-for-reviews-js', get_template_directory_uri() . '/js/zerif_reviews.js', array('jquery') );

	wp_localize_script( 'zerif-lite-asking-for-reviews-js', 'zerifLiteAskingForReviewsObject', array(
		'ask' => $zerif_lite_review,
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	) );
}
