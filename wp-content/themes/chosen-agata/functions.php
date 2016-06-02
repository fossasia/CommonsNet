<?php

if ( ! isset( $content_width ) ) {
	$content_width = 891;
}

if ( ! function_exists( ( 'ct_chosen_theme_setup' ) ) ) {
	function ct_chosen_theme_setup() {

		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption'
		) );
		add_theme_support( 'infinite-scroll', array(
			'container' => 'loop-container',
			'footer'    => 'overflow-container',
			'render'    => 'ct_chosen_infinite_scroll_render'
		) );

		require_once( trailingslashit( get_template_directory() ) . 'theme-options.php' );
		foreach ( glob( trailingslashit( get_template_directory() ) . 'inc/*' ) as $filename ) {
			include $filename;
		}

		register_nav_menus( array(
			'primary' => __( 'Primary', 'chosen' )
		) );

		load_theme_textdomain( 'chosen', get_template_directory() . '/languages' );
	}
}
add_action( 'after_setup_theme', 'ct_chosen_theme_setup', 10 );

if ( ! function_exists( ( 'ct_chosen_customize_comments' ) ) ) {
	function ct_chosen_customize_comments( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		global $post;
		?>
		<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<div class="comment-author">
				<?php
				echo get_avatar( get_comment_author_email(), 36, '', get_comment_author() );
				?>
				<span class="author-name"><?php comment_author_link(); ?></span>
			</div>
			<div class="comment-content">
				<?php if ( $comment->comment_approved == '0' ) : ?>
					<em><?php _e( 'Your comment is awaiting moderation.', 'chosen' ) ?></em>
					<br/>
				<?php endif; ?>
				<?php comment_text(); ?>
			</div>
			<div class="comment-footer">
				<span class="comment-date"><?php comment_date(); ?></span>
				<?php comment_reply_link( array_merge( $args, array(
					'reply_text' => __( 'Reply', 'chosen' ),
					'depth'      => $depth,
					'max_depth'  => $args['max_depth']
				) ) ); ?>
				<?php edit_comment_link( __( 'Edit', 'chosen' ) ); ?>
			</div>
		</article>
		<?php
	}
}

if ( ! function_exists( 'ct_chosen_update_fields' ) ) {
	function ct_chosen_update_fields( $fields ) {

		$commenter = wp_get_current_commenter();
		$req       = get_option( 'require_name_email' );
		$label     = $req ? '*' : ' ' . __( '(optional)', 'chosen' );
		$aria_req  = $req ? "aria-required='true'" : '';

		$fields['author'] =
			'<p class="comment-form-author">
	            <label for="author">' . __( "Name", "chosen" ) . $label . '</label>
	            <input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
			'" size="30" ' . $aria_req . ' />
	        </p>';

		$fields['email'] =
			'<p class="comment-form-email">
	            <label for="email">' . __( "Email", "chosen" ) . $label . '</label>
	            <input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) .
			'" size="30" ' . $aria_req . ' />
	        </p>';

		$fields['url'] =
			'<p class="comment-form-url">
	            <label for="url">' . __( "Website", "chosen" ) . '</label>
	            <input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) .
			'" size="30" />
	            </p>';

		return $fields;
	}
}
add_filter( 'comment_form_default_fields', 'ct_chosen_update_fields' );

if ( ! function_exists( 'ct_chosen_update_comment_field' ) ) {
	function ct_chosen_update_comment_field( $comment_field ) {

		$comment_field =
			'<p class="comment-form-comment">
	            <label for="comment">' . __( "Comment", "chosen" ) . '</label>
	            <textarea required id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
	        </p>';

		return $comment_field;
	}
}
add_filter( 'comment_form_field_comment', 'ct_chosen_update_comment_field' );

if ( ! function_exists( 'ct_chosen_remove_comments_notes_after' ) ) {
	function ct_chosen_remove_comments_notes_after( $defaults ) {
		$defaults['comment_notes_after'] = '';
		return $defaults;
	}
}
add_action( 'comment_form_defaults', 'ct_chosen_remove_comments_notes_after' );

if ( ! function_exists( 'ct_chosen_excerpt' ) ) {
	function ct_chosen_excerpt() {

		global $post;
		$show_full_post = get_theme_mod( 'full_post' );
		$read_more_text = get_theme_mod( 'read_more_text' );
		$ismore         = strpos( $post->post_content, '<!--more-->' );

		if ( ( $show_full_post == 'yes' ) && ! is_search() ) {
			if ( $ismore ) {
				// Has to be written this way because i18n text CANNOT be stored in a variable
				if ( ! empty( $read_more_text ) ) {
					the_content( $read_more_text . " <span class='screen-reader-text'>" . get_the_title() . "</span>" );
				} else {
					the_content( __( 'Continue reading', 'chosen' ) . " <span class='screen-reader-text'>" . get_the_title() . "</span>" );
				}
			} else {
				the_content();
			}
		} elseif ( $ismore ) {
			if ( ! empty( $read_more_text ) ) {
				the_content( $read_more_text . " <span class='screen-reader-text'>" . get_the_title() . "</span>" );
			} else {
				the_content( __( 'Continue reading', 'chosen' ) . " <span class='screen-reader-text'>" . get_the_title() . "</span>" );
			}
		} else {
			the_excerpt();
		}
	}
}

if ( ! function_exists( 'ct_chosen_excerpt_read_more_link' ) ) {
	function ct_chosen_excerpt_read_more_link( $output ) {

		$read_more_text = get_theme_mod( 'read_more_text' );

		if ( ! empty( $read_more_text ) ) {
			return $output . "<p><a class='more-link' href='" . esc_url( get_permalink() ) . "'>" . $read_more_text . " <span class='screen-reader-text'>" . get_the_title() . "</span></a></p>";
		} else {
			return $output . "<p><a class='more-link' href='" . esc_url( get_permalink() ) . "'>" . __( 'Continue reading', 'chosen' ) . " <span class='screen-reader-text'>" . get_the_title() . "</span></a></p>";
		}
	}
}
add_filter( 'the_excerpt', 'ct_chosen_excerpt_read_more_link' );

if ( ! function_exists( 'ct_chosen_custom_excerpt_length' ) ) {
	function ct_chosen_custom_excerpt_length( $length ) {

		$new_excerpt_length = get_theme_mod( 'excerpt_length' );

		if ( ! empty( $new_excerpt_length ) && $new_excerpt_length != 25 ) {
			return $new_excerpt_length;
		} elseif ( $new_excerpt_length === 0 ) {
			return 0;
		} else {
			return 25;
		}
	}
}
add_filter( 'excerpt_length', 'ct_chosen_custom_excerpt_length', 99 );

if ( ! function_exists( 'ct_chosen_new_excerpt_more' ) ) {
	function ct_chosen_new_excerpt_more( $more ) {

		$new_excerpt_length = get_theme_mod( 'excerpt_length' );
		$excerpt_more       = ( $new_excerpt_length === 0 ) ? '' : '&#8230;';

		return $excerpt_more;
	}
}
add_filter( 'excerpt_more', 'ct_chosen_new_excerpt_more' );

if ( ! function_exists( 'ct_chosen_remove_more_link_scroll' ) ) {
	function ct_chosen_remove_more_link_scroll( $link ) {
		$link = preg_replace( '|#more-[0-9]+|', '', $link );
		return $link;
	}
}
add_filter( 'the_content_more_link', 'ct_chosen_remove_more_link_scroll' );

if ( ! function_exists( 'ct_chosen_featured_image' ) ) {
	function ct_chosen_featured_image() {

		global $post;
		$featured_image = '';

		if ( has_post_thumbnail( $post->ID ) ) {

			if ( is_singular() ) {
				$featured_image = '<div class="featured-image">' . get_the_post_thumbnail( $post->ID, 'full' ) . '</div>';
			} else {
				$featured_image = '<div class="featured-image"><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . get_the_post_thumbnail( $post->ID, 'full' ) . '</a></div>';
			}
		}

		$featured_image = apply_filters( 'ct_chosen_featured_image', $featured_image );

		if ( $featured_image ) {
			echo $featured_image;
		}
	}
}

if ( ! function_exists( 'ct_chosen_social_array' ) ) {
	function ct_chosen_social_array() {

		$social_sites = array(
			'twitter'       => 'chosen_twitter_profile',
			'facebook'      => 'chosen_facebook_profile',
			'google-plus'   => 'chosen_googleplus_profile',
			'pinterest'     => 'chosen_pinterest_profile',
			'linkedin'      => 'chosen_linkedin_profile',
			'youtube'       => 'chosen_youtube_profile',
			'vimeo'         => 'chosen_vimeo_profile',
			'tumblr'        => 'chosen_tumblr_profile',
			'instagram'     => 'chosen_instagram_profile',
			'flickr'        => 'chosen_flickr_profile',
			'dribbble'      => 'chosen_dribbble_profile',
			'rss'           => 'chosen_rss_profile',
			'reddit'        => 'chosen_reddit_profile',
			'soundcloud'    => 'chosen_soundcloud_profile',
			'spotify'       => 'chosen_spotify_profile',
			'vine'          => 'chosen_vine_profile',
			'yahoo'         => 'chosen_yahoo_profile',
			'behance'       => 'chosen_behance_profile',
			'codepen'       => 'chosen_codepen_profile',
			'delicious'     => 'chosen_delicious_profile',
			'stumbleupon'   => 'chosen_stumbleupon_profile',
			'deviantart'    => 'chosen_deviantart_profile',
			'digg'          => 'chosen_digg_profile',
			'github'        => 'chosen_github_profile',
			'hacker-news'   => 'chosen_hacker-news_profile',
			'steam'         => 'chosen_steam_profile',
			'vk'            => 'chosen_vk_profile',
			'weibo'         => 'chosen_weibo_profile',
			'tencent-weibo' => 'chosen_tencent_weibo_profile',
			'500px'         => 'chosen_500px_profile',
			'foursquare'    => 'chosen_foursquare_profile',
			'slack'         => 'chosen_slack_profile',
			'slideshare'    => 'chosen_slideshare_profile',
			'qq'            => 'chosen_qq_profile',
			'whatsapp'      => 'chosen_whatsapp_profile',
			'skype'         => 'chosen_skype_profile',
			'wechat'        => 'chosen_wechat_profile',
			'xing'          => 'chosen_xing_profile',
			'paypal'        => 'chosen_paypal_profile',
			'email'         => 'chosen_email_profile',
			'email-form'    => 'chosen_email_form_profile'
		);

		return apply_filters( 'ct_chosen_social_array_filter', $social_sites );
	}
}

if ( ! function_exists( 'ct_chosen_social_icons_output' ) ) {
	function ct_chosen_social_icons_output() {

		$social_sites = ct_chosen_social_array();

		foreach ( $social_sites as $social_site => $profile ) {

			if ( strlen( get_theme_mod( $social_site ) ) > 0 ) {
				$active_sites[ $social_site ] = $social_site;
			}
		}

		if ( ! empty( $active_sites ) ) {

			echo "<ul class='social-media-icons'>";

			foreach ( $active_sites as $key => $active_site ) {

				if ( $active_site == 'email' ) {
					?>
					<li>
						<a class="email" target="_blank"
						   href="mailto:<?php echo antispambot( is_email( get_theme_mod( $key ) ) ); ?>">
							<i class="fa fa-envelope" title="<?php esc_attr_e( 'email', 'chosen' ); ?>"></i>
							<span class="screen-reader-text"><?php esc_attr_e('email', 'chosen'); ?></span>
						</a>
					</li>
				<?php } elseif ( $active_site == 'skype' ) { ?>
					<li>
						<a class="<?php echo esc_attr( $active_site ); ?>" target="_blank"
						   href="<?php echo esc_url( get_theme_mod( $key ), array( 'http', 'https', 'skype' ) ); ?>">
							<i class="fa fa-<?php echo esc_attr( $active_site ); ?>"
							   title="<?php echo esc_attr( $active_site ); ?>"></i>
							<span class="screen-reader-text"><?php echo esc_attr( $active_site );  ?></span>
						</a>
					</li>
				<?php } elseif ( $active_site == 'email-form' ) { ?>
					<li>
						<a class="<?php echo esc_attr( $active_site ); ?>" target="_blank"
						   href="<?php echo esc_url( get_theme_mod( $key ) ); ?>">
							<i class="fa fa-envelope-o"
							   title="<?php echo esc_attr( $active_site ); ?>"></i>
							<span class="screen-reader-text"><?php echo esc_attr( $active_site );  ?></span>
						</a>
					</li>
				<?php } else { ?>
					<li>
						<a class="<?php echo esc_attr( $active_site ); ?>" target="_blank"
						   href="<?php echo esc_url( get_theme_mod( $key ) ); ?>">
							<i class="fa fa-<?php echo esc_attr( $active_site ); ?>"
							   title="<?php echo esc_attr( $active_site ); ?>"></i>
							<span class="screen-reader-text"><?php echo esc_attr( $active_site );  ?></span>
						</a>
					</li>
					<?php
				}
			}
			echo "</ul>";
		}
	}
}

/*
 * WP will apply the ".menu-primary-items" class & id to the containing <div> instead of <ul>
 * making styling difficult and confusing. Using this wrapper to add a unique class to make styling easier.
 */
function ct_chosen_wp_page_menu() {
	wp_page_menu( array(
			"menu_class" => "menu-unset",
			"depth"      => - 1
		)
	);
}

if ( ! function_exists( '_wp_render_title_tag' ) ) :
	function ct_chosen_add_title_tag() {
		?>
		<title><?php wp_title( ' | ' ); ?></title>
		<?php
	}
	add_action( 'wp_head', 'ct_chosen_add_title_tag' );
endif;

function ct_chosen_nav_dropdown_buttons( $item_output, $item, $depth, $args ) {

	if ( $args->theme_location == 'primary' ) {

		if ( in_array( 'menu-item-has-children', $item->classes ) || in_array( 'page_item_has_children', $item->classes ) ) {
			$item_output = str_replace( $args->link_after . '</a>', $args->link_after . '</a><button class="toggle-dropdown" aria-expanded="false" name="toggle-dropdown"><span class="screen-reader-text">' . __( "open menu", "chosen" ) . '</span></button>', $item_output );
		}
	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'ct_chosen_nav_dropdown_buttons', 10, 4 );

function ct_chosen_sticky_post_marker() {

	if ( is_sticky() && ! is_archive() ) {
		echo '<div class="sticky-status"><span>' . __( "Featured Post", "chosen" ) . '</span></div>';
	}
}
add_action( 'sticky_post_status', 'ct_chosen_sticky_post_marker' );

function ct_chosen_reset_customizer_options() {

	if ( empty( $_POST['chosen_reset_customizer'] ) || 'chosen_reset_customizer_settings' !== $_POST['chosen_reset_customizer'] ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['chosen_reset_customizer_nonce'], 'chosen_reset_customizer_nonce' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$mods_array = array(
		'logo_upload',
		'search_bar',
		'full_post',
		'excerpt_length',
		'read_more_text',
		'full_width_post',
		'author_byline',
		'custom_css'
	);

	$social_sites = ct_chosen_social_array();

	// add social site settings to mods array
	foreach ( $social_sites as $social_site => $value ) {
		$mods_array[] = $social_site;
	}

	$mods_array = apply_filters( 'ct_chosen_mods_to_remove', $mods_array );

	foreach ( $mods_array as $theme_mod ) {
		remove_theme_mod( $theme_mod );
	}

	$redirect = admin_url( 'themes.php?page=chosen-options' );
	$redirect = add_query_arg( 'chosen_status', 'deleted', $redirect );

	// safely redirect
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_init', 'ct_chosen_reset_customizer_options' );

function ct_chosen_delete_settings_notice() {

	if ( isset( $_GET['chosen_status'] ) ) {
		?>
		<div class="updated">
			<p><?php _e( 'Customizer settings deleted', 'chosen' ); ?>.</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'ct_chosen_delete_settings_notice' );

function ct_chosen_body_class( $classes ) {

	global $post;
	$full_post       = get_theme_mod( 'full_post' );
	$pagination      = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$full_width_post = get_theme_mod( 'full_width_post' );

	if ( $full_post == 'yes' ) {
		$classes[] = 'full-post';
	}

	if ( is_home() && $pagination == 1 && $full_width_post != 'no' ) {
		$classes[] = 'posts-page-1';
	}
	if ( is_singular() ) {
		$classes[] = 'singular';
		if ( is_singular( 'page' ) ) {
			$classes[] = 'singular-page';
			$classes[] = 'singular-page-' . $post->ID;
		} elseif ( is_singular( 'post' ) ) {
			$classes[] = 'singular-post';
			$classes[] = 'singular-post-' . $post->ID;
		} elseif ( is_singular( 'attachment' ) ) {
			$classes[] = 'singular-attachment';
			$classes[] = 'singular-attachment-' . $post->ID;
		}
	}

	return $classes;
}
add_filter( 'body_class', 'ct_chosen_body_class' );

function ct_chosen_post_class( $classes ) {
	$classes[] = 'entry';
	return $classes;
}
add_filter( 'post_class', 'ct_chosen_post_class' );

function ct_chosen_custom_css_output() {

	$custom_css = get_theme_mod( 'custom_css' );

	if ( $custom_css ) {
		$custom_css = ct_chosen_sanitize_css( $custom_css );

		wp_add_inline_style( 'ct-chosen-style', $custom_css );
		wp_add_inline_style( 'ct-chosen-style-rtl', $custom_css );
	}
}
add_action( 'wp_enqueue_scripts', 'ct_chosen_custom_css_output', 20 );

function ct_chosen_svg_output( $type ) {

	$svg = '';

	if ( $type == 'toggle-navigation' ) {

		$svg = '<svg width="24px" height="18px" viewBox="0 0 24 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g transform="translate(-148.000000, -36.000000)" fill="#6B6B6B">
				            <g transform="translate(123.000000, 25.000000)">
				                <g transform="translate(25.000000, 11.000000)">
				                    <rect x="0" y="16" width="24" height="2"></rect>
				                    <rect x="0" y="8" width="24" height="2"></rect>
				                    <rect x="0" y="0" width="24" height="2"></rect>
				                </g>
				            </g>
				        </g>
				    </g>
				</svg>';
	}

	return $svg;
}

function ct_chosen_add_meta_elements() {

	$meta_elements = '';

	$meta_elements .= sprintf( '<meta charset="%s" />' . "\n", get_bloginfo( 'charset' ) );
	$meta_elements .= '<meta name="viewport" content="width=device-width, initial-scale=1" />' . "\n";

	$theme    = wp_get_theme( get_template() );
	$template = sprintf( '<meta name="template" content="%s %s" />' . "\n", esc_attr( $theme->get( 'Name' ) ), esc_attr( $theme->get( 'Version' ) ) );
	$meta_elements .= $template;

	echo $meta_elements;
}
add_action( 'wp_head', 'ct_chosen_add_meta_elements', 1 );

// Move the WordPress generator to a better priority.
remove_action( 'wp_head', 'wp_generator' );
add_action( 'wp_head', 'wp_generator', 1 );

function ct_chosen_infinite_scroll_render() {
	while ( have_posts() ) {
		the_post();
		get_template_part( 'content', 'archive' );
	}
}

if ( ! function_exists( 'ct_chosen_get_content_template' ) ) {
	function ct_chosen_get_content_template() {

		/* Blog */
		if ( is_home() ) {
			get_template_part( 'content', 'archive' );
		} /* Post */
		elseif ( is_singular( 'post' ) ) {
			get_template_part( 'content' );
		} /* Page */
		elseif ( is_page() ) {
			get_template_part( 'content', 'page' );
		} /* Attachment */
		elseif ( is_attachment() ) {
			get_template_part( 'content', 'attachment' );
		} /* Archive */
		elseif ( is_archive() ) {
			get_template_part( 'content', 'archive' );
		} /* Custom Post Type */
		else {
			get_template_part( 'content' );
		}
	}
}

// prevent odd number of posts on page 2+ of blog if extra-wide post used
if ( ! function_exists( 'ct_chosen_adjust_post_count' ) ) {
	function ct_chosen_adjust_post_count( $query ) {

		$extra_wide = get_theme_mod( 'full_width_post' );

		if ( $extra_wide != 'no' ) {

			if ( $query->is_home() && $query->is_main_query() && $query->is_paged() ) {

				$posts_per_page = get_option( 'posts_per_page' );

				// get number of previous posts
				$offset = ( $query->query_vars['paged'] - 1 ) * $posts_per_page;

				// offset post count minus one for every page after page 2
				$query->set( 'offset', $offset - ( $query->query_vars['paged'] - 2 ) );

				// drop the posts per page by 1
				$query->set( 'posts_per_page', $posts_per_page - 1 );
			}
		}
	}
}
add_action( 'pre_get_posts', 'ct_chosen_adjust_post_count' );

// allow skype URIs to be used
function ct_chosen_allow_skype_protocol( $protocols ){
	$protocols[] = 'skype';
	return $protocols;
}
add_filter( 'kses_allowed_protocols' , 'ct_chosen_allow_skype_protocol' );