<?php
/**
 * Template Name: Blog template with large images
 */
get_header(); ?>

<div class="clear"></div>

</header> <!-- / END HOME SECTION  -->

<div id="content" class="site-content">

	<div class="container">

		<div class="content-left-wrap col-md-9">

			<div id="primary" class="content-area">

				<main id="main" class="site-main" role="main" itemscope itemtype="http://schema.org/Blog">

					<?php 
				
					query_posts( array( 'post_type' => 'post', 'posts_per_page' => 6, 'paged' => ( get_query_var('paged') ? get_query_var('paged') : 1 ) ) );

					if ( have_posts() ) :

						while ( have_posts() ) : the_post();
						
							get_template_part( 'content-large' );
						
						endwhile; 
						
						zerif_paging_nav();
					
					else : 
					
						get_template_part( 'content', 'none' );
						
					endif;
					
					wp_reset_postdata(); 
					
					?>

				</main><!-- #main -->

			</div><!-- #primary -->

		</div><!-- .content-left-wrap -->

		<div class="sidebar-wrap col-md-3 content-left-wrap">

			<?php get_sidebar(); ?>

		</div><!-- .sidebar-wrap -->

	</div><!-- .container -->
<?php get_footer(); ?>