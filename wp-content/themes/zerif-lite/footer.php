<?php
/**
 * The template for displaying the footer.
 * Contains the closing of the #content div and all content after
 */

?>

</div><!-- .site-content -->

<footer id="footer" role="contentinfo">

<?php 
	if(is_active_sidebar( 'zerif-sidebar-footer' ) || is_active_sidebar( 'zerif-sidebar-footer-2' ) || is_active_sidebar( 'zerif-sidebar-footer-3' )):
		echo '<div class="footer-widget-wrap"><div class="container">';
		if(is_active_sidebar( 'zerif-sidebar-footer' )):
			echo '<div class="footer-widget col-xs-12 col-sm-4">';
			dynamic_sidebar( 'zerif-sidebar-footer' );
			echo '</div>';
		endif;
		if(is_active_sidebar( 'zerif-sidebar-footer-2' )):
			echo '<div class="footer-widget col-xs-12 col-sm-4">';
			dynamic_sidebar( 'zerif-sidebar-footer-2' );
			echo '</div>';
		endif;
		if(is_active_sidebar( 'zerif-sidebar-footer-3' )):
			echo '<div class="footer-widget col-xs-12 col-sm-4">';
			dynamic_sidebar( 'zerif-sidebar-footer-3' );
			echo '</div>';
		endif;
		echo '</div></div>';
	endif;
?>

<div class="container">

	<?php
		$footer_sections = 0;
		$zerif_address = get_theme_mod('zerif_address',__('Company address','zerif-lite'));
		$zerif_address_icon = get_theme_mod('zerif_address_icon',get_template_directory_uri().'/images/map25-redish.png');
		
		$zerif_email = get_theme_mod('zerif_email','<a href="mailto:contact@site.com">contact@site.com</a>');
		$zerif_email_icon = get_theme_mod('zerif_email_icon',get_template_directory_uri().'/images/envelope4-green.png');
		
		$zerif_phone = get_theme_mod('zerif_phone','<a href="tel:0 332 548 954">0 332 548 954</a>');
		$zerif_phone_icon = get_theme_mod('zerif_phone_icon',get_template_directory_uri().'/images/telephone65-blue.png');

		$zerif_socials_facebook = get_theme_mod('zerif_socials_facebook','#');
		$zerif_socials_twitter = get_theme_mod('zerif_socials_twitter','#');
		$zerif_socials_linkedin = get_theme_mod('zerif_socials_linkedin','#');
		$zerif_socials_behance = get_theme_mod('zerif_socials_behance','#');
		$zerif_socials_dribbble = get_theme_mod('zerif_socials_dribbble','#');
		$zerif_socials_instagram = get_theme_mod('zerif_socials_instagram');
		
		$zerif_accessibility = get_theme_mod('zerif_accessibility');
		$zerif_copyright = get_theme_mod('zerif_copyright');

		if(!empty($zerif_address) || !empty($zerif_address_icon)):
			$footer_sections++;
		endif;
		
		if(!empty($zerif_email) || !empty($zerif_email_icon)):
			$footer_sections++;
		endif;
		
		if(!empty($zerif_phone) || !empty($zerif_phone_icon)):
			$footer_sections++;
		endif;
		if( !empty($zerif_socials_facebook) || !empty($zerif_socials_twitter) || !empty($zerif_socials_linkedin) || !empty($zerif_socials_behance) || !empty($zerif_socials_dribbble) || 
		!empty($zerif_copyright) || !empty($zerif_socials_instagram) ):
			$footer_sections++;
		endif;
		
		if( $footer_sections == 1 ):
			$footer_class = 'col-md-12';
		elseif( $footer_sections == 2 ):
			$footer_class = 'col-md-6';
		elseif( $footer_sections == 3 ):
			$footer_class = 'col-md-4';
		elseif( $footer_sections == 4 ):
			$footer_class = 'col-md-3';
		else:
			$footer_class = 'col-md-3';
		endif;
		
		global $wp_customize;
		
		
		if( !empty($footer_class) ) {
			
			/* COMPANY ADDRESS */
			if( !empty($zerif_address_icon) || !empty($zerif_address) ) { 
				echo '<div class="'.$footer_class.' company-details">';
					
					if( !empty($zerif_address_icon) ) { 
						echo '<div class="icon-top red-text">';
							 echo '<img src="'.esc_url($zerif_address_icon).'" alt="" />';
						echo '</div>';
					}
					
					if( !empty($zerif_address) ) {
						echo '<div class="zerif-footer-address">';
							echo wp_kses_post( $zerif_address );
						echo '</div>';
					} else if( isset( $wp_customize ) ) {
						echo '<div class="zerif-footer-address zerif_hidden_if_not_customizer"></div>';
					}
					
				echo '</div>';
			}
			
			/* COMPANY EMAIL */
			if( !empty($zerif_email_icon) || !empty($zerif_email) ) {
				echo '<div class="'.$footer_class.' company-details">';
				
					if( !empty($zerif_email_icon) ) {
						echo '<div class="icon-top green-text">';
							echo '<img src="'.esc_url($zerif_email_icon).'" alt="" />';
						echo '</div>';
					}
					if( !empty($zerif_email) ) {
						echo '<div class="zerif-footer-email">';	
							echo wp_kses_post( $zerif_email );
						echo '</div>';
					} else if( isset( $wp_customize ) ) {
						echo '<div class="zerif-footer-email zerif_hidden_if_not_customizer"></div>';
					}	
				
				echo '</div>';
			}
			
			/* COMPANY PHONE NUMBER */
			if( !empty($zerif_phone_icon) || !empty($zerif_phone) ) {
				echo '<div class="'.$footer_class.' company-details">';
					if( !empty($zerif_phone_icon) ) {
						echo '<div class="icon-top blue-text">';
							echo '<img src="'.esc_url($zerif_phone_icon).'" alt="" />';
						echo '</div>';
					}
					if( !empty($zerif_phone) ) {
						echo '<div class="zerif-footer-phone">';
							echo wp_kses_post( $zerif_phone );
						echo '</div>';	
					} else if( isset( $wp_customize ) ) {
						echo '<div class="zerif-footer-phone zerif_hidden_if_not_customizer"></div>';
					}		
				echo '</div>';
			}
		}
		
		// open link in a new tab when checkbox "accessibility" is not ticked
		$attribut_new_tab = (isset($zerif_accessibility) && ($zerif_accessibility != 1) ? ' target="_blank"' : '' );
		
		if( !empty($zerif_socials_facebook) || !empty($zerif_socials_twitter) || !empty($zerif_socials_linkedin) || !empty($zerif_socials_behance) || !empty($zerif_socials_dribbble) || 
		!empty($zerif_copyright) || !empty($zerif_socials_instagram) ):
		
					echo '<div class="'.$footer_class.' copyright">';
					if(!empty($zerif_socials_facebook) || !empty($zerif_socials_twitter) || !empty($zerif_socials_linkedin) || !empty($zerif_socials_behance) || !empty($zerif_socials_dribbble)):
						echo '<ul class="social">';
						
						/* facebook */
						if( !empty($zerif_socials_facebook) ):
							echo '<li><a'.$attribut_new_tab.' href="'.esc_url($zerif_socials_facebook).'"><i class="fa fa-facebook"></i></a></li>';
						endif;
						/* twitter */
						if( !empty($zerif_socials_twitter) ):
							echo '<li><a'.$attribut_new_tab.' href="'.esc_url($zerif_socials_twitter).'"><i class="fa fa-twitter"></i></a></li>';
						endif;
						/* linkedin */
						if( !empty($zerif_socials_linkedin) ):
							echo '<li><a'.$attribut_new_tab.' href="'.esc_url($zerif_socials_linkedin).'"><i class="fa fa-linkedin"></i></a></li>';
						endif;
						/* behance */
						if( !empty($zerif_socials_behance) ):
							echo '<li><a'.$attribut_new_tab.' href="'.esc_url($zerif_socials_behance).'"><i class="fa fa-behance"></i></a></li>';
						endif;
						/* dribbble */
						if( !empty($zerif_socials_dribbble) ):
							echo '<li><a'.$attribut_new_tab.' href="'.esc_url($zerif_socials_dribbble).'"><i class="fa fa-dribbble"></i></a></li>';
						endif;
						/* instagram */
						if( !empty($zerif_socials_instagram) ):
							echo '<li><a'.$attribut_new_tab.' href="'.esc_url($zerif_socials_instagram).'"><i class="fa fa-instagram"></i></a></li>';
						endif;
						echo '</ul>';
					endif;	
			
					if( !empty($zerif_copyright) ):
						echo '<p id="zerif-copyright">'.wp_kses_post($zerif_copyright).'</p>';
					elseif( isset( $wp_customize ) ):
						echo '<p id="zerif-copyright" class="zerif_hidden_if_not_customizer"></p>';
					endif;
					
					echo '<div class="zerif-copyright-box"><a class="zerif-copyright" href="http://themeisle.com/themes/zerif-lite/"'.$attribut_new_tab.' rel="nofollow">Zerif Lite </a>'.__('powered by','zerif-lite').'<a class="zerif-copyright" href="http://wordpress.org/"'.$attribut_new_tab.' rel="nofollow"> WordPress</a></div>';
					
					echo '</div>';
			
		endif;
	?>

</div> <!-- / END CONTAINER -->

</footer> <!-- / END FOOOTER  -->


	</div><!-- mobile-bg-fix-whole-site -->
</div><!-- .mobile-bg-fix-wrap -->


<?php wp_footer(); ?>

</body>

</html>