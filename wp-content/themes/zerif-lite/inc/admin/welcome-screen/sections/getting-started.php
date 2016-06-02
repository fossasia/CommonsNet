<?php
/**
 * Getting started template
 */

$customizer_url = admin_url() . 'customize.php' ;
?>

<div id="getting_started" class="zerif-lite-tab-pane active">

	<div class="zerif-tab-pane-center">

		<h1 class="zerif-lite-welcome-title">Welcome to Zerif Lite! <?php if( !empty($zerif_lite['Version']) ): ?> <sup id="zerif-lite-theme-version"><?php echo esc_attr( $zerif_lite['Version'] ); ?> </sup><?php endif; ?></h1>

		<p><?php esc_html_e( 'Our most popular free one page WordPress theme, Zerif Lite!','zerif-lite'); ?></p>
		<p><?php esc_html_e( 'We want to make sure you have the best experience using Zerif Lite and that is why we gathered here all the necessary informations for you. We hope you will enjoy using Zerif Lite, as much as we enjoy creating great products.', 'zerif-lite' ); ?>

	</div>

	<hr />

	<div class="zerif-tab-pane-center">

		<h1><?php esc_html_e( 'Getting started', 'zerif-lite' ); ?></h1>

		<h4><?php esc_html_e( 'Customize everything in a single place.' ,'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Using the WordPress Customizer you can easily customize every aspect of the theme.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( $customizer_url ); ?>" class="button button-primary"><?php esc_html_e( 'Go to Customizer', 'zerif-lite' ); ?></a></p>

	</div>

	<hr />

	<div class="zerif-tab-pane-center">

		<h1><?php esc_html_e( 'FAQ', 'zerif-lite' ); ?></h1>

	</div>

	<div class="zerif-tab-pane-half zerif-tab-pane-first-half">

		<h4><?php esc_html_e( 'Create a child theme', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'If you want to make changes to the theme\'s files, those changes are likely to be overwritten when you next update the theme. In order to prevent that from happening, you need to create a child theme. For this, please follow the documentation below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/14-how-to-create-a-child-theme/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />
		
		<h4><?php esc_html_e( 'Build a landing page with a drag-and-drop content builder', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'In the below documentation you will find an easy way to build a great looking landing page using a drag-and-drop content builder plugin.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/219-how-to-build-a-landing-page-with-a-drag-and-drop-content-builder' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />
		
		<h4><?php esc_html_e( 'Translate Zerif Lite', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'In the below documentation you will find an easy way to translate Zerif Lite into your native language or any other language you need for you site.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/80-how-to-translate-zerif' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />

		<h4><?php esc_html_e( 'Change dimensions for footer social icons', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'In the below documentation you will find an easy way to change the default dimensions for you social icons.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/249-how-to-increase-the-size-of-social-icons-in-zerif' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />

		<h4><?php esc_html_e( 'Turn off the animations', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'You can turn off the animation effects you see when Zerif Lite loads a section in an easy way with just few changes. Check the documentation below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/15-turn-off-loading-animations-in-zerif/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />
		
		<h4><?php esc_html_e( 'Add a search bar in the top menu', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Find out how to add a search bar in the top menu bar, in an easy way be following the link below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/78-zerif-adding-a-search-bar-in-the-top-menu' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>
		
		<hr />
		
		<h4><?php esc_html_e( 'Slider in big title section', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'If you are in the position where you want to change the default appearance of the big title section, you may want to replace it with a nice looking slider. This can be accomplished by following the documention below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/13-replacing-big-title-section-with-an-image-slider/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

	</div>

	<div class="zerif-tab-pane-half">

		<h4><?php esc_html_e( 'Speed up your site', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'If you find yourself in the situation where everything on your site is running very slow, you might consider having a look at the below documentation where you will find the most common issues causing this and possible solutions for each of the issues.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/63-speed-up-your-wordpress-site/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />

		<h4><?php esc_html_e( 'Link Menu to sections', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Linking the frontpage sections with the top menu is very simple, all you need to do is assign section anchors to the menu. In the below documentation you will find a nice tutorial about this.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/33-how-to-link-menus-to-sections-in-zerif/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />

		<h4><?php esc_html_e( 'Change anchors', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'To better suit your site\'s needs, you can change each section\'s anchor to what you want. The entire process is described below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/36-how-to-change-section-anchor-in-zerif/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />

		<h4><?php esc_html_e( 'Change the page template', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Zerif Lite has three page templates available, two for the blog and one for full width pages. To make sure you take full advantage of those page templates, make sure you read the documentation.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/32-how-to-change-the-page-template-in-wordpress' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>

		<hr />

		<h4><?php esc_html_e( 'Remove the opacity', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'You don\'t like the way Zerif Lite looks with its background opacity? No problem. Just remove it using the steps below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/30-removing-background-opacity-in-zerif/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>
		
		<hr />
		
		<h4><?php esc_html_e( 'Configure the portfolio', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Set up your portfolio section in an easy way be following the link below.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://docs.themeisle.com/article/85-configuring-portfolio/' ); ?>" class="button"><?php esc_html_e( 'View how to do this', 'zerif-lite' ); ?></a></p>
		
		<hr />
		
		<h4><?php esc_html_e( '30 Experts Share: The Top *Non-Obvious* WordPress Plugins That\'ll Make You a Better Blogger', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( ' At the address below you will find a cool set of original WordPress plugins that can give you great benefits despite being a little lesser known out there.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://www.codeinwp.com/blog/top-non-obvious-wordpress-plugins/' ); ?>" class="button"><?php esc_html_e( 'Read more', 'zerif-lite' ); ?></a></p>

	</div>

	<div class="zerif-lite-clear"></div>

	<hr />

	<div class="zerif-tab-pane-center">

		<h1><?php esc_html_e( 'View full documentation', 'zerif-lite' ); ?></h1>
		<p><?php esc_html_e( 'Need more details? Please check our full documentation for detailed information on how to use Zerif Lite.', 'zerif-lite' ); ?></p>
		<p><a href="<?php echo esc_url( 'http://themeisle.com/documentation-zerif-lite/' ); ?>" class="button button-primary"><?php esc_html_e( 'Read full documentation', 'zerif-lite' ); ?></a></p>

	</div>

	<hr />

	<div class="zerif-tab-pane-center">
		<h1><?php esc_html_e( 'Recommended plugins', 'zerif-lite' ); ?></h1>
	</div>

	<div class="zerif-tab-pane-half zerif-tab-pane-first-half">
	
		<!-- Page Builder by SiteOrigin -->
		<h4><?php esc_html_e( 'Page Builder by SiteOrigin', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Build responsive page layouts using the widgets you know and love using this simple drag and drop page builder.', 'zerif-lite' ); ?></p>

		<?php if ( is_plugin_active( 'siteorigin-panels/siteorigin-panels.php' ) ) { ?>

				<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

				<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=siteorigin-panels' ), 'install-plugin_siteorigin-panels' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install Page Builder by SiteOrigin', 'zerif-lite' ); ?></a></p>

			<?php
		}

		?>

		<hr />

		<!-- WP Product Review -->
		<h4><?php esc_html_e( 'WP Product Review', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Easily turn your basic posts into in-depth reviews with ratings, pros and cons, affiliate links, rich snippets and user reviews.', 'zerif-lite' ); ?></p>

		<?php if ( is_plugin_active( 'wp-product-review/wp-product-review.php' ) ) { ?>

				<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

				<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wp-product-review' ), 'install-plugin_wp-product-review' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install WP Product Review', 'zerif-lite' ); ?></a></p>

			<?php
		}

		?>

		<hr />

		<!-- Custom Login Customizer -->
		<h4><?php esc_html_e( 'Custom Login Customizer', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Login Customizer plugin allows you to easily customize your login page straight from your WordPress Customizer! You can preview your changes before you save them!', 'zerif-lite' ); ?></p>

		<?php if ( is_plugin_active( 'login-customizer/login-customizer.php' ) ) { ?>

			<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

			<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=login-customizer' ), 'install-plugin_login-customizer' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install Custom Login Customizer', 'zerif-lite' ); ?></a></p>

			<?php
		}
		?>
		
		<hr />
		
		<!-- Adblock Notify -->
		<h4>Adblock Notify</h4>

		<?php if ( is_plugin_active( 'adblock-notify-by-bweb/adblock-notify.php' ) ) { ?>

			<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

			<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=adblock-notify-by-bweb' ), 'install-plugin_adblock-notify-by-bweb' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install', 'zerif-lite' ); ?> Adblock Notify</a></p>

			<?php
		} 
		?>

	</div>

	<div class="zerif-tab-pane-half">

		<!-- Visualizer: Charts and Graphs -->
		<h4><?php esc_html_e( 'Visualizer: Charts and Graphs', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'A simple, easy to use and quite powerful chart tool to create, manage and embed interactive charts into your WordPress posts and pages.', 'zerif-lite' ); ?></p>

		<?php if ( class_exists( 'Visualizer_Plugin' ) ) { ?>

			<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

			<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=visualizer' ), 'install-plugin_visualizer' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install Visualizer', 'zerif-lite' ); ?></a></p>

			<?php
		}
		?>
		
		<hr />
		
		<!-- ECPT -->
		<h4><?php esc_html_e( 'Easy Content Types', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'Custom Post Types, Taxonomies and Metaboxes in Minutes', 'zerif-lite' ); ?></p>

		<?php if ( is_plugin_active( 'easy-content-types/easy-content-types.php' ) ) { ?>

				<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

				<p><a href="<?php echo esc_url( 'http://themeisle.com/plugins/easy-content-types/' ); ?>" class="button button-primary"><?php esc_html_e( 'Download Easy Content Types', 'zerif-lite' ); ?></a></p>

			<?php
		}
		?>
		
		<hr />
		
		<!-- Revive Old Post -->
		<h4><?php esc_html_e( 'Revive Old Post', 'zerif-lite' ); ?></h4>
		<p><?php esc_html_e( 'A plugin to share about your old posts on twitter, facebook, linkedin to get more hits for them and keep them alive.', 'zerif-lite' ); ?></p>

		<?php if ( is_plugin_active( 'tweet-old-post/tweet-old-post.php' ) ) { ?>

			<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

			<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=tweet-old-post' ), 'install-plugin_tweet-old-post' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install Revive Old Post', 'zerif-lite' ); ?></a></p>

			<?php
		}
		?>

		<hr />
		
		<!-- FEEDZY RSS Feeds -->
		<h4>FEEDZY RSS Feeds</h4>

		<?php if ( is_plugin_active( 'feedzy-rss-feeds/feedzy-rss-feed.php' ) ) { ?>

			<p><span class="zerif-lite-w-activated button"><?php esc_html_e( 'Already activated', 'zerif-lite' ); ?></span></p>

			<?php
		}
		else { ?>

			<p><a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=feedzy-rss-feeds' ), 'install-plugin_feedzy-rss-feeds' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Install', 'zerif-lite' ); ?> FEEDZY RSS Feeds</a></p>

			<?php
		}
		?>

	</div>

	<div class="zerif-lite-clear"></div>

</div>
