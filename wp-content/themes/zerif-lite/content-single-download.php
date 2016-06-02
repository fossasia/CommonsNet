<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">

		<h1 class="entry-title"><?php the_title(); ?></h1>

	</header><!-- .entry-header -->

	<div class="entry-content">

		<div class="edd-image-wrap">
			<?php
				// check if the post has a Post Thumbnail assigned to it.
				if ( has_post_thumbnail() ) {
					the_post_thumbnail();
				} 
			?>
		</div>

		<?php 
			the_content();
		?>

	</div><!-- .entry-content -->

</article><!-- #post-## -->