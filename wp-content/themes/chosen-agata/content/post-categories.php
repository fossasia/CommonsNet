<?php

$categories = get_the_category( $post->ID );
$separator  = ' ';
$output     = '';

if ( $categories ) {
	echo '<p class="post-categories">';
		echo '<span>' . __( 'Categories:', 'chosen' ) . '</span>';
		foreach ( $categories as $category ) {
			$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s", 'chosen' ), $category->name ) ) . '">' . $category->cat_name . '</a>' . $separator;
		}
		echo trim( $output, $separator );
	echo "</p>";
}