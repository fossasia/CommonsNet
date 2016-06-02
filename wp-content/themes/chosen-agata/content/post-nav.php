<?php

global $post;
$previous_post = get_adjacent_post( false, '', true );
$next_post     = get_adjacent_post( false, '', false );

if ( $previous_post ) {
	$previous_text  = __( 'Previous Post', 'chosen' );
	$previous_title = get_the_title( $previous_post ) ? get_the_title( $previous_post ) : __( "The Previous Post", 'chosen' );
	$previous_link  = get_permalink( $previous_post );
} else {
	$previous_text  = __( 'No Older Posts', 'chosen' );
	$previous_title = __( 'Return to Blog', 'chosen' );
	if ( get_option( 'show_on_front' ) == 'page' ) {
		$previous_link = get_permalink( get_option( 'page_for_posts' ) );
	} else {
		$previous_link = get_home_url();
	}
}

if ( $next_post ) {
	$next_text = __( 'Next Post', 'chosen' );
	$next_title = get_the_title( $next_post ) ? get_the_title( $next_post ) : __( "The Next Post", 'chosen' );
	$next_link = get_permalink( $next_post );
} else {
	$next_text  = __( 'No Newer Posts', 'chosen' );
	$next_title = __( 'Return to Blog', 'chosen' );
	if ( get_option( 'show_on_front' ) == 'page' ) {
		$next_link = get_permalink( get_option( 'page_for_posts' ) );
	} else {
		$next_link = get_home_url();
	}
}

?>
<nav class="further-reading">
	<div class="previous">
		<span><?php echo esc_html( $previous_text ); ?></span>
		<a href="<?php echo esc_url( $previous_link ); ?>"><?php echo esc_html( $previous_title ); ?></a>
	</div>
	<div class="next">
		<span><?php echo esc_html( $next_text ); ?></span>
		<a href="<?php echo esc_url( $next_link ); ?>"><?php echo esc_html( $next_title ); ?></a>
	</div>
</nav>