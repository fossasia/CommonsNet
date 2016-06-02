<span class="comments-link">
	<i class="fa fa-comment" title="<?php _e( 'comment icon', 'chosen' ); ?>"></i>
	<?php
	if ( ! comments_open() && get_comments_number() < 1 ) :
		comments_number( __( 'Comments closed', 'chosen' ), __( '1 Comment', 'chosen' ), __( '% Comments', 'chosen' ) );
	else :
		echo '<a href="' . esc_url( get_comments_link() ) . '">';
		comments_number( __( 'Leave a Comment', 'chosen' ), __( '1 Comment', 'chosen' ), __( '% Comments', 'chosen' ) );
		echo '</a>';
	endif;
	?>
</span>