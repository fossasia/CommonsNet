<article class="large-container" id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemtype="http://schema.org/BlogPosting" itemtype="http://schema.org/BlogPosting">

	<?php if ( ! is_search() ) : ?>

		<?php if ( has_post_thumbnail()) : ?>

		<div class="post-img-wrap-large">

			 	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" >

					<?php 
						$image_id = get_post_thumbnail_id();
						$image_url_big = wp_get_attachment_image_src($image_id,'post-thumbnail-large', true);
						$image_url_tablet = wp_get_attachment_image_src($image_id,'post-thumbnail-large-table', true);
						$image_url_mobile = wp_get_attachment_image_src($image_id,'post-thumbnail-large-mobile', true);
					?>

			 		<picture>
						<source media="(max-width: 600px)" srcset="<?php echo esc_url( $image_url_mobile[0] ); ?>">
						<source media="(max-width: 768px)" srcset="<?php echo esc_url( $image_url_tablet[0] ); ?>">
						<img src="<?php echo esc_url( $image_url_big[0] ); ?>" alt="<?php the_title_attribute(); ?>">
					</picture>

				</a>

		</div>

		<div class="listpost-content-wrap-large">

		<?php else: ?>

		<div class="listpost-content-wrap-full">

		<?php endif; ?>

	<?php else:  ?>

			<div class="listpost-content-wrap-full">

	<?php endif; ?>

	<div class="list-post-top">

	<header class="entry-header">

		<h1 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h1>

	</header><!-- .entry-header -->

	<?php if ( is_search() ) : /* Only display Excerpts for Search */ ?>

	<div class="entry-summary">

		<?php the_excerpt(); ?>

	</div><!-- .entry-summary -->

	<?php else : ?>

	<div class="entry-content">

		<?php 
			$ismore = @strpos( $post->post_content, '<!--more-->');
			
			if($ismore) {
				the_content( sprintf( esc_html__('[...]','zerif-lite'), '<span class="screen-reader-text">'.esc_html__('about ', 'zerif-lite').get_the_title().'</span>' ) );
			} else {
				the_excerpt();
			}

			wp_link_pages( array(

				'before' => '<div class="page-links">' . __( 'Pages:', 'zerif-lite' ),

				'after'  => '</div>',

			) );
		?>

	</div><!-- .entry-content -->

	<?php endif; ?>

	</div><!-- .list-post-top -->

	<footer class="entry-footer-large">

		<?php if ( 'post' == get_post_type() ) : ?>

		<div class="entry-meta-large">

			<?php zerif_posted_on(); ?>

		</div><!-- .entry-meta -->

		<?php endif; ?>

		<div class="entry-footer-large-left">

			<?php if ( 'post' == get_post_type() ) : /* Hide category and tag text for pages on Search */ ?>

				<?php

					/* translators: used between list items, there is a space after the comma */

					$categories_list = get_the_category_list( __( ', ', 'zerif-lite' ) );

					if ( $categories_list && zerif_categorized_blog() ) :

				?>

				<span class="cat-links">

					<?php printf( __( 'Posted in %1$s', 'zerif-lite' ), $categories_list ); ?>

				</span>

				<?php endif; ?>

				<?php

					/* translators: used between list items, there is a space after the comma */

					$tags_list = get_the_tag_list( '', __( ', ', 'zerif-lite' ) );

					if ( $tags_list ) :

				?>

				<span class="tags-links">

					<?php printf( __( 'Tagged %1$s', 'zerif-lite' ), $tags_list ); ?>

				</span>

				<?php endif; /* End if $tags_list */ ?>

			<?php endif; /* End if 'post' == get_post_type() */ ?>
			
			
			<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>

			<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'zerif-lite' ), __( '1 Comment', 'zerif-lite' ), __( '% Comments', 'zerif-lite' ) ); ?></span>

			<?php endif; ?>
		
			<?php edit_post_link( __( 'Edit', 'zerif-lite' ), '<span class="edit-link">', '</span>' ); ?>

		</div>

	</footer><!-- .entry-footer -->

</div><!-- .listpost-content-wrap -->

</article><!-- #post-## -->