<?php
	$search_params = $current_filter['search_params'];
?>
<div class="search-box">
	
	<?php
			//If we're currently displaying search results offer the user the option to
			//save the search query as a custom filter. 	
			if ( $filter_id == 'search' ){
	?>
	<form name="save-search-query" id="custom-filter-form" action="<?php echo admin_url("tools.php?page=view-broken-links");  ?>" method="post" class="blc-inline-form">
		<?php wp_nonce_field('create-custom-filter');  ?>
		<input type="hidden" name="name" id="blc-custom-filter-name" value="" />
		<input type="hidden" name="params" id="blc-custom-filter-params" value="<?php echo http_build_query($search_params, null, '&'); ?>" />
		<input type="hidden" name="action" value="create-custom-filter" />
		<input type="button" value="<?php esc_attr_e( 'Save This Search As a Filter', 'broken-link-checker' ); ?>" id="blc-create-filter" class="button" />
	</form>				 				
	<?php
			} elseif ( !empty($current_filter['custom']) ){
			//If we're displaying a custom filter give an option to delete it.
	?>
	<form name="save-search-query" id="custom-filter-form" action="<?php echo admin_url("tools.php?page=view-broken-links");  ?>" method="post" class="blc-inline-form">
		<?php wp_nonce_field('delete-custom-filter');  ?>
		<input type="hidden" name="filter_id" id="blc-custom-filter-id" value="<?php echo $filter_id; ?>" />
		<input type="hidden" name="action" value="delete-custom-filter" />
		<input type="submit" value="<?php esc_attr_e( 'Delete This Filter', 'broken-link-checker' ); ?>" id="blc-delete-filter" class="button" />
	</form>
	<?php
			}
	?>
	
	<input type="button" value="<?php esc_attr_e( 'Search', 'broken-link-checker' ); ?> &raquo;" id="blc-open-search-box" class="button" />
</div>

<!-- The search dialog -->
<div id='search-links-dialog' title='Search'>
<form class="search-form" action="<?php echo admin_url('tools.php?page=view-broken-links'); ?>" method="get">
	<input type="hidden" name="page" value="view-broken-links" />
	<input type="hidden" name="filter_id" value="search" />
	<fieldset>
	
	<label for="s_link_text"><?php _e('Link text', 'broken-link-checker'); ?></label>
	<input type="text" name="s_link_text" value="<?php if(!empty($search_params['s_link_text'])) echo esc_attr($search_params['s_link_text']); ?>" id="s_link_text" class="text ui-widget-content" />
	
	<label for="s_link_url"><?php _e('URL', 'broken-link-checker'); ?></label>
	<input type="text" name="s_link_url" id="s_link_url" value="<?php if(!empty($search_params['s_link_url'])) echo esc_attr($search_params['s_link_url']); ?>" class="text ui-widget-content" />
	
	<label for="s_http_code"><?php _e('HTTP code', 'broken-link-checker'); ?></label>
	<input type="text" name="s_http_code" id="s_http_code" value="<?php if(!empty($search_params['s_http_code'])) echo esc_attr($search_params['s_http_code']); ?>" class="text ui-widget-content" />
	
	<label for="s_filter"><?php _e('Link status', 'broken-link-checker'); ?></label>
	<select name="s_filter" id="s_filter">
		<?php
		if ( !empty($search_params['s_filter']) ){
			$search_subfilter = $search_params['s_filter']; 
		} else {
			$search_subfilter = 'all';
		}
		
		$linkQuery = blcLinkQuery::getInstance();
		foreach ($linkQuery->native_filters as $filter => $data){
			$selected = ($search_subfilter == $filter)?' selected="selected"':'';
			printf('<option value="%s"%s>%s</option>', $filter, $selected, $data['name']);
		}		 
		?>
	</select>
	
	<label for="s_link_type"><?php _e('Link type', 'broken-link-checker'); ?></label>
	<select name="s_link_type" id="s_link_type">
		<option value=""><?php _e('Any', 'broken-link-checker'); ?></option>
		<?php
		$moduleManager = blcModuleManager::getInstance();
		
		printf('<optgroup label="%s">', esc_attr(__('Links used in', 'broken-link-checker')));
		$containers = $moduleManager->get_modules_by_category('container', false, true);
		foreach($containers as $container_type => $module_data){
			if ( !empty($module_data['ModuleHidden']) || !$moduleManager->is_active($container_type) ){
				continue;
			}
			$selected = ( isset($search_params['s_link_type']) && $search_params['s_link_type'] == $container_type )?' selected="selected"':'';
			printf('<option value="%s"%s>%s</option>', $container_type, $selected, $module_data['Name']);
		}
		echo '</optgroup>';
		//TODO: Better group labels
		printf('<optgroup label="%s">', esc_attr(__('Link type', 'broken-link-checker')));
		$parsers = $moduleManager->get_modules_by_category('parser', false, true);
		foreach($parsers as $parser_type => $module_data){
			if ( !empty($module_data['ModuleHidden']) || !$moduleManager->is_active($parser_type) ){
				continue;
			}
			$selected = ( isset($search_params['s_link_type']) && $search_params['s_link_type'] == $parser_type )?' selected="selected"':'';
			printf('<option value="%s"%s>%s</option>', $parser_type, $selected, $module_data['Name']);
		}
		echo '</optgroup>';
		
		/*
		$link_types = array(
			__('Any', 'broken-link-checker') => '',
			__('Normal link', 'broken-link-checker') => 'link',
			__('Image', 'broken-link-checker') => 'image',
			__('Custom field', 'broken-link-checker') => 'custom_field',
			__('Bookmark', 'broken-link-checker') => 'blogroll',
			__('Comment', 'broken-link-checker') => 'comment',
		);
		*/
		?>
	</select>
	
	</fieldset>
	
	<div id="blc-search-button-row">
		<input type="submit" value="<?php esc_attr_e( 'Search Links', 'broken-link-checker' ); ?>" id="blc-search-button" name="search_button" class="button-primary" />
		<input type="button" value="<?php esc_attr_e( 'Cancel', 'broken-link-checker' ); ?>" id="blc-cancel-search" class="button" />
	</div>
	
</form>	
</div>