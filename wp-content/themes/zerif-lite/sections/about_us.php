<section class="about-us" id="aboutus">
	<div class="container">

		<!-- SECTION HEADER -->

		<div class="section-header">

			<!-- SECTION TITLE -->

			<?php 
			
			global $wp_customize;
			
			$zerif_aboutus_title = get_theme_mod('zerif_aboutus_title',__('About','zerif-lite'));
			
			if( !empty($zerif_aboutus_title) ):
				echo '<h2 class="white-text">'. wp_kses_post( $zerif_aboutus_title ) .'</h2>';
			elseif ( isset( $wp_customize ) ):	
				echo '<h2 class="white-text zerif_hidden_if_not_customizer"></h2>';
			endif;
			?>

			<!-- SHORT DESCRIPTION ABOUT THE SECTION -->

			<?php

				$zerif_aboutus_subtitle = get_theme_mod('zerif_aboutus_subtitle',__('Use this section to showcase important details about your business.','zerif-lite'));

				if( !empty($zerif_aboutus_subtitle) ):

					echo '<div class="white-text section-legend">';

						echo wp_kses_post( $zerif_aboutus_subtitle );

					echo '</div>';

				elseif ( isset( $wp_customize ) ):
					
					echo '<div class="white-text section-legend zerif_hidden_if_not_customizer">'.wp_kses_post( $zerif_aboutus_subtitle ).'</div>';

				endif;

			?>
		</div><!-- / END SECTION HEADER -->

		<!-- 3 COLUMNS OF ABOUT US-->

		<div class="row">

			<!-- COLUMN 1 - BIG MESSAGE ABOUT THE COMPANY-->

		<?php

			$zerif_aboutus_biglefttitle 	= get_theme_mod('zerif_aboutus_biglefttitle',__('Everything you see here is responsive and mobile-friendly.','zerif-lite'));
			$zerif_aboutus_text 			= get_theme_mod('zerif_aboutus_text','Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec massa enim. Aliquam viverra at est ullamcorper sollicitudin. Proin a leo sit amet nunc malesuada imperdiet pharetra ut eros.<br><br> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec massa enim. Aliquam viverra at est ullamcorper sollicitudin. Proin a leo sit amet nunc malesuada imperdiet pharetra ut eros. <br><br>Mauris vel nunc at ipsum fermentum pellentesque quis ut massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas non adipiscing massa. Sed ut fringilla sapien. Cras sollicitudin, lectus sed tincidunt cursus, magna lectus vehicula augue, a lobortis dui orci et est.');
			
			$zerif_aboutus_feature1_title 	= get_theme_mod('zerif_aboutus_feature1_title',__('YOUR SKILL #1','zerif-lite'));
			$zerif_aboutus_feature1_text 	= get_theme_mod('zerif_aboutus_feature1_text');

			$zerif_aboutus_feature2_title 	= get_theme_mod('zerif_aboutus_feature2_title',__('YOUR SKILL #2','zerif-lite'));
			$zerif_aboutus_feature2_text 	= get_theme_mod('zerif_aboutus_feature2_text');

			$zerif_aboutus_feature3_title 	= get_theme_mod('zerif_aboutus_feature3_title',__('YOUR SKILL #3','zerif-lite'));
			$zerif_aboutus_feature3_text 	= get_theme_mod('zerif_aboutus_feature3_text');

			$zerif_aboutus_feature4_title 	= get_theme_mod('zerif_aboutus_feature4_title',__('YOUR SKILL #4','zerif-lite'));
			$zerif_aboutus_feature4_text 	= get_theme_mod('zerif_aboutus_feature4_text');

			$text_and_skills = '';
			switch (
				(empty($zerif_aboutus_biglefttitle) ? 0 : 1)
				+ (empty($zerif_aboutus_text) ? 0 : 1)
				+ 						
					( empty($zerif_aboutus_feature1_title) && empty($zerif_aboutus_feature1_text) ? 
					( empty($zerif_aboutus_feature2_title) && empty($zerif_aboutus_feature2_text) ? 
					( empty($zerif_aboutus_feature3_title) && empty($zerif_aboutus_feature3_text) ? 
					( empty($zerif_aboutus_feature4_title) && empty($zerif_aboutus_feature4_text) ? 
					0 && $text_and_skills=''
					: 1 && $text_and_skills='text_and_skills' ) 
					: 1 && $text_and_skills='text_and_skills' ) 
					: 1 && $text_and_skills='text_and_skills' )
					: 1 && $text_and_skills='text_and_skills' )
				) {
				case 3:
					$colCount = 4;
					break;
				case 2:
					$colCount = 6;
					break;
				default:
					$colCount = 12;
			}

				if( !empty($zerif_aboutus_biglefttitle) ):

					echo '<div class="col-lg-' . $colCount . ' col-md-' . $colCount . ' column zerif-rtl-big-title">';

						echo '<div class="big-intro" data-scrollreveal="enter left after 0s over 1s">';

							echo wp_kses_post( $zerif_aboutus_biglefttitle );

						echo '</div>';

					echo '</div>';

				endif;

			if( !empty($zerif_aboutus_text) ):

				echo '<div class="col-lg-' . $colCount . ' col-md-' . $colCount . ' column zerif_about_us_center ' . $text_and_skills . '" data-scrollreveal="enter bottom after 0s over 1s">';

					echo '<p>';

						echo wp_kses_post( $zerif_aboutus_text );

					echo '</p>';

				echo '</div>';

			endif;

			$there_is_skills = '';
			( 
				!empty($zerif_aboutus_feature1_title) || !empty($zerif_aboutus_feature1_text) ? $there_is_skills='yes' :
				!empty($zerif_aboutus_feature2_title) || !empty($zerif_aboutus_feature2_text) ? $there_is_skills='yes' : 
				!empty($zerif_aboutus_feature3_title) || !empty($zerif_aboutus_feature3_text) ? $there_is_skills='yes' : 
				!empty($zerif_aboutus_feature4_title) || !empty($zerif_aboutus_feature4_text) ? $there_is_skills='yes' : 
				$there_is_skills='');

			$zerif_aboutus_feature1_nr 	= get_theme_mod('zerif_aboutus_feature1_nr', '80');
			$zerif_aboutus_feature2_nr 	= get_theme_mod('zerif_aboutus_feature2_nr', '91');
			$zerif_aboutus_feature3_nr 	= get_theme_mod('zerif_aboutus_feature3_nr', '88');
			$zerif_aboutus_feature4_nr 	= get_theme_mod('zerif_aboutus_feature4_nr', '95');

			/* COLUMN 1 - SKILS */

			if ( $there_is_skills!='' ) :
			
		?>
		<div class="col-lg-<?php echo $colCount; ?> col-md-<?php echo $colCount; ?> column zerif-rtl-skills ">

			<ul class="skills" data-scrollreveal="enter right after 0s over 1s">

				<!-- SKILL ONE -->

				<?php if( !empty($zerif_aboutus_feature1_nr) || !empty($zerif_aboutus_feature1_title) || !empty($zerif_aboutus_feature1_text) ): ?>

				<li class="skill skill_1">

					<?php

						if( !empty($zerif_aboutus_feature1_nr) ):

							echo '<div class="skill-count">';

								echo '<input type="text" value="'.esc_attr( $zerif_aboutus_feature1_nr ).'" data-thickness=".2" class="skill1" tabindex="-1">';

							echo '</div>';

						endif;

						if( !empty($zerif_aboutus_feature1_title) ):
							echo '<div class="section-legend">'.wp_kses_post( $zerif_aboutus_feature1_title ).'</div>';
						elseif ( isset( $wp_customize ) ):
							echo '<div class="section-legend zerif_hidden_if_not_customizer"></div>';
						endif;

						if( !empty($zerif_aboutus_feature1_text) ):
							echo '<p>'.wp_kses_post( $zerif_aboutus_feature1_text ).'</p>';
						elseif ( isset( $wp_customize ) ):
							echo '<p class="zerif_hidden_if_not_customizer"></p>';
						endif;

					?>

				</li>

				<?php endif; ?>

				<!-- SKILL TWO -->

				<?php	
					if( !empty($zerif_aboutus_feature2_nr) || !empty($zerif_aboutus_feature2_title) || !empty($zerif_aboutus_feature2_text) ):
				?>

				<li class="skill skill_2">

					<?php

						if( !empty($zerif_aboutus_feature2_nr) ):

							echo '<div class="skill-count">';

								echo '<input type="text" value="'.esc_attr( $zerif_aboutus_feature2_nr ).'" data-thickness=".2" class="skill2" tabindex="-1">';

							echo '</div>';

						endif;



						if( !empty($zerif_aboutus_feature2_title) ):
							echo '<div class="section-legend">'.wp_kses_post( $zerif_aboutus_feature2_title ).'</div>';
						elseif ( isset( $wp_customize ) ):
							echo '<div class="section-legend zerif_hidden_if_not_customizer"></div>';
						endif;

						if( !empty($zerif_aboutus_feature2_text) ):
							echo '<p>'.wp_kses_post( $zerif_aboutus_feature2_text ).'</p>';
						elseif ( isset( $wp_customize ) ):
							echo '<p class="zerif_hidden_if_not_customizer"></p>';
						endif;

					?>

				</li>

				<?php endif; ?>

				<!-- SKILL THREE -->
				<?php						
					if( !empty($zerif_aboutus_feature3_nr) || !empty($zerif_aboutus_feature3_title) || !empty($zerif_aboutus_feature3_text) ):
				?>
				<li class="skill skill_3">

					<?php

						if( !empty($zerif_aboutus_feature3_nr) ):

							echo '<div class="skill-count">';

								echo '<input type="text" value="'.esc_attr( $zerif_aboutus_feature3_nr ).'" data-thickness=".2" class="skill3" tabindex="-1">';

							echo '</div>';

						endif;

						if( !empty($zerif_aboutus_feature3_title) ):
							echo '<div class="section-legend">'.wp_kses_post( $zerif_aboutus_feature3_title ).'</div>';
						elseif ( isset( $wp_customize ) ):
							echo '<div class="section-legend zerif_hidden_if_not_customizer"></div>';
						endif;

						if( !empty($zerif_aboutus_feature3_text) ):
							echo '<p>'.wp_kses_post( $zerif_aboutus_feature3_text ).'</p>';
						elseif ( isset( $wp_customize ) ):
							echo '<p class="zerif_hidden_if_not_customizer"></p>';
						endif;

					?>

				</li>

				<?php endif; ?>

				<!-- SKILL FOUR -->
				<?php	
					if( !empty($zerif_aboutus_feature4_nr) || !empty($zerif_aboutus_feature4_title) || !empty($zerif_aboutus_feature4_text) ):
				?>

				<li class="skill skill_4">

					<?php

						if( !empty($zerif_aboutus_feature4_nr) ):

							echo '<div class="skill-count">';

								echo '<input type="text" value="'.esc_attr( $zerif_aboutus_feature4_nr ).'" data-thickness=".2" class="skill4" tabindex="-1">';

							echo '</div>';

						endif;

						if( !empty($zerif_aboutus_feature4_title) ):
							echo '<div class="section-legend">'.wp_kses_post( $zerif_aboutus_feature4_title ).'</div>';
						elseif ( isset( $wp_customize ) ):
							echo '<div class="section-legend zerif_hidden_if_not_customizer"></div>';
						endif;

						if( !empty($zerif_aboutus_feature4_text) ):
							echo '<p>'.wp_kses_post( $zerif_aboutus_feature4_text ).'</p>';
						elseif ( isset( $wp_customize ) ):
							echo '<p class="zerif_hidden_if_not_customizer"></p>';
						endif;

					?>

				</li>

				<?php endif; ?>

			</ul>

		</div> <!-- / END SKILLS COLUMN-->

		<?php endif; ?>

	</div> <!-- / END 3 COLUMNS OF ABOUT US-->

	<!-- CLIENTS -->
	<?php
		if(is_active_sidebar( 'sidebar-aboutus' )):
			echo '<div class="our-clients">';
				echo '<h2><span class="section-footer-title">'.__('OUR HAPPY CLIENTS','zerif-lite').'</span></h2>';
			echo '</div>';

			echo '<div class="client-list">';
				echo '<div data-scrollreveal="enter right move 60px after 0.00s over 2.5s">';
				dynamic_sidebar( 'sidebar-aboutus' );
				echo '</div>';
			echo '</div> ';
		endif;
	?>

</div> <!-- / END CONTAINER -->

</section> <!-- END ABOUT US SECTION -->