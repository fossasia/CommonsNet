<div id="menu-primary" class="menu-container menu-primary" role="navigation" style='float: right;'>
	<?php wp_nav_menu(
		array(
			'theme_location'  => 'primary',
			'container'       => 'nav',
			'container_class' => 'menu',
			'menu_class'      => 'menu-primary-items',
			'menu_id'         => 'menu-primary-items',
			'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'fallback_cb'     => 'ct_chosen_wp_page_menu'
		) ); ?>
</div>
