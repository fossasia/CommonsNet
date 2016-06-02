<?php

/**
 * The manager to rule all (post) managers.
 * 
 * This class dynamically registers container modules for the available post types
 * (including custom post types) and does stuff that pertain to all of them, such 
 * as handling save/delete hooks and (re)creating synch records.
 * 
 * @package Broken Link Checker
 * @author Janis Elsts
 * @access private
 */
class blcPostTypeOverlord {
	public $enabled_post_types = array();  //Post types currently selected for link checking
	public $enabled_post_statuses = array('publish'); //Only posts that have one of these statuses shall be checked
	 
	var $plugin_conf;  
	var $resynch_already_done = false;
	
  /**
   * Class "constructor". Can't use an actual constructor due to how PHP4 handles object references.
   * 
   * Specifically, this class is a singleton. The function needs to pass $this to several other 
   * functions (to set up hooks), which will store the reference for later use. However, it appears 
   * that in PHP4 the actual value of $this is thrown away right after the constructor finishes, and
   * `new` returns a *copy* of $this. The result is that getInstance() won't be returning a ref.
   * to the same object as is used for hook callbacks. And that's horrible.   
   * 
   * Sets up hooks that monitor added/modified/deleted posts and registers
   * virtual modules for all post types.
   *
   * @return void
   */
	function init(){
 		$this->plugin_conf = blc_get_configuration();
 		
 		if ( isset($this->plugin_conf->options['enabled_post_statuses']) ){
 			$this->enabled_post_statuses = $this->plugin_conf->options['enabled_post_statuses'];
 		}
		
		//Register a virtual container module for each enabled post type
		$module_manager = blcModuleManager::getInstance();
		
		$post_types = get_post_types(array(), 'objects');
		$exceptions = array('revision', 'nav_menu_item', 'attachment');

		foreach($post_types as $data){
			$post_type = $data->name;
			
			if ( in_array($post_type, $exceptions) ){
				continue;
			}
			
			$module_manager->register_virtual_module(
				$post_type,
				array(
					'Name' => $data->labels->name,
					'ModuleCategory' => 'container',
					'ModuleContext' => 'all',
					'ModuleClassName' => 'blcAnyPostContainerManager',
				)
			);
		}	
		
		//These hooks update the synch & instance records when posts are added, deleted or modified.
		add_action('delete_post', array(&$this,'post_deleted'));
        add_action('save_post', array(&$this,'post_saved'));
        //We also treat post trashing/untrashing as delete/save. 
        add_action('trash_post', array(&$this,'post_deleted'));
        add_action('untrash_post', array(&$this,'post_saved'));
        
        //Highlight and nofollow broken links in posts & pages
        if ( $this->plugin_conf->options['mark_broken_links'] || $this->plugin_conf->options['nofollow_broken_links'] ){
        	add_filter( 'the_content', array(&$this, 'hook_the_content') );
        	if ( $this->plugin_conf->options['mark_broken_links'] && !empty( $this->plugin_conf->options['broken_link_css'] ) ){
	            add_action( 'wp_head', array(&$this,'hook_wp_head') );
			}
        }
	}	
	
	/**
	 * Retrieve an instance of the overlord class.
	 * 
	 * @return blcPostTypeOverlord
	 */
	static function getInstance(){
		static $instance = null;
		if ( is_null($instance) ){
			$instance = new blcPostTypeOverlord;
			$instance->init();
		}
		return $instance;
	}
	
	/**
	 * Notify the overlord that a post type is active.
	 * 
	 * Called by individual instances of blcAnyPostContainerManager to let 
	 * the overlord know that they've been created. Since a module instance 
	 * is only created if the module is active, this event indicates that
	 * the user has enabled the corresponding post type for link checking.
	 * 
	 * @param string $post_type
	 * @return void
	 */
	function post_type_enabled($post_type){
		if ( !in_array($post_type, $this->enabled_post_types) ){
			$this->enabled_post_types[] = $post_type;
		}
	}
	
  /**
   * Remove the synch. record and link instances associated with a post when it's deleted 
   *
   * @param int $post_id
   * @return void
   */
	function post_deleted($post_id){
		//Get the container type matching the type of the deleted post
		$post = get_post($post_id);
		if ( !$post ){
			return;
		}
		//Get the associated container object
		$post_container = blcContainerHelper::get_container( array($post->post_type, intval($post_id)) );
		
		if ( $post_container ){
			//Delete it
			$post_container->delete();
			//Clean up any dangling links
			blc_cleanup_links();
		}
	}
	
  /**
   * When a post is saved or modified, mark it as unparsed.
   * 
   * @param int $post_id
   * @return void
   */
	function post_saved($post_id){
		//Get the container type matching the type of the deleted post
		$post = get_post($post_id);
		if ( !$post ){
			return;
		}
		
        //Only check links in currently enabled post types
        if ( !in_array($post->post_type, $this->enabled_post_types) ) return;
		
        //Only check posts that have one of the allowed statuses
        if ( !in_array($post->post_status, $this->enabled_post_statuses) ) return;
        
    	//Get the container & mark it as unparsed
		$args = array($post->post_type, intval($post_id));
		$post_container = blcContainerHelper::get_container( $args );

        $post_container->mark_as_unsynched();
	}


	/**
	 * Create or update synchronization records for all posts.
	 *
	 * @param string $container_type
	 * @param bool $forced If true, assume that all synch. records are gone and will need to be recreated from scratch.
	 * @return void
	 */
	function resynch($container_type = '', $forced = false){
		global $wpdb; /** @var wpdb $wpdb */
		global $blclog;
		
		//Resynch is expensive in terms of DB performance. Thus we only do it once, processing
		//all post types in one go and ignoring any further resynch requests during this pageload.
		//BUG: This might be a problem if there ever is an actual need to run resynch twice or 
		//more per pageload.
		if ( $this->resynch_already_done ){
			$blclog->log(sprintf('...... Skipping "%s" resyncyh since all post types were already synched.', $container_type));
			return;
		}
		
		if ( empty($this->enabled_post_types) ){
			$blclog->warn(sprintf('...... Skipping "%s" resyncyh since no post types are enabled.', $container_type));
			return;
		}
		
		$escaped_post_types = array_map('esc_sql', $this->enabled_post_types);
		$escaped_post_statuses = array_map('esc_sql', $this->enabled_post_statuses);
		
		if ( $forced ){
			//Create new synchronization records for all posts. 
			$blclog->log('...... Creating synch records for these post types: '.implode(', ', $escaped_post_types) . ' that have one of these statuses: ' . implode(', ', $escaped_post_statuses));
			$start = microtime(true);
	    	$q = "INSERT INTO {$wpdb->prefix}blc_synch(container_id, container_type, synched)
				  SELECT posts.id, posts.post_type, 0
				  FROM {$wpdb->posts} AS posts
				  WHERE
				  	posts.post_status IN (%s)
	 				AND posts.post_type IN (%s)";
			$q = sprintf(
				$q,
				"'" . implode("', '", $escaped_post_statuses) . "'",
				"'" . implode("', '", $escaped_post_types) . "'"
			);
	 		$wpdb->query( $q );
	 		$blclog->log(sprintf('...... %d rows inserted in %.3f seconds', $wpdb->rows_affected, microtime(true) - $start));
 		} else {
 			//Delete synch records corresponding to posts that no longer exist.
 			$blclog->log('...... Deleting synch records for removed posts');
			$start = microtime(true);
			$q = "DELETE synch.*
				  FROM 
					 {$wpdb->prefix}blc_synch AS synch LEFT JOIN {$wpdb->posts} AS posts
					 ON posts.ID = synch.container_id
				  WHERE 
					 synch.container_type IN (%s) AND posts.ID IS NULL";
			$q = sprintf(
				$q,
				"'" . implode("', '", $escaped_post_types) . "'"
			);
			$wpdb->query( $q );
			$elapsed = microtime(true) - $start;
			$blclog->debug($q);
			$blclog->log(sprintf('...... %d rows deleted in %.3f seconds', $wpdb->rows_affected, $elapsed));

			//Delete records where the post status is not one of the enabled statuses.
			$blclog->log('...... Deleting synch records for posts that have a disallowed status');
			$start = microtime(true);
			$q = "DELETE synch.*
				  FROM
					 {$wpdb->prefix}blc_synch AS synch
					 LEFT JOIN {$wpdb->posts} AS posts
					 ON (synch.container_id = posts.ID and synch.container_type = posts.post_type)
				  WHERE
					 posts.post_status NOT IN (%s)";
			$q = sprintf(
				$q,
				"'" . implode("', '", $escaped_post_statuses) . "'"
			);
			$wpdb->query( $q );
			$elapsed = microtime(true) - $start;
			$blclog->debug($q);
			$blclog->log(sprintf('...... %d rows deleted in %.3f seconds', $wpdb->rows_affected, $elapsed));

			//Remove the 'synched' flag from all posts that have been updated
			//since the last time they were parsed/synchronized.
			$blclog->log('...... Marking changed posts as unsynched');
			$start = microtime(true);
			$q = "UPDATE
					{$wpdb->prefix}blc_synch AS synch
					JOIN {$wpdb->posts} AS posts ON (synch.container_id = posts.ID and synch.container_type=posts.post_type)
				  SET 
					synched = 0
				  WHERE
					synch.last_synch < posts.post_modified";
			$wpdb->query( $q );
			$elapsed = microtime(true) - $start;
			$blclog->debug($q);
			$blclog->log(sprintf('...... %d rows updated in %.3f seconds', $wpdb->rows_affected, $elapsed));
			
			//Create synch. records for posts that don't have them.
			$blclog->log('...... Creating synch records for new posts');
			$start = microtime(true);
			$q = "INSERT INTO {$wpdb->prefix}blc_synch(container_id, container_type, synched)
				  SELECT posts.id, posts.post_type, 0
				  FROM 
				    {$wpdb->posts} AS posts LEFT JOIN {$wpdb->prefix}blc_synch AS synch
					ON (synch.container_id = posts.ID and synch.container_type=posts.post_type)  
				  WHERE
				  	posts.post_status IN (%s)
	 				AND posts.post_type IN (%s)
					AND synch.container_id IS NULL";
			$q = sprintf(
				$q,
				"'" . implode("', '", $escaped_post_statuses) . "'",
				"'" . implode("', '", $escaped_post_types) . "'"
			);
			$wpdb->query($q);
			$elapsed = microtime(true) - $start;
			$blclog->debug($q);
			$blclog->log(sprintf('...... %d rows inserted in %.3f seconds', $wpdb->rows_affected, $elapsed));
		}
		
		$this->resynch_already_done = true;
	}
	
  /**
   * Hook for the 'the_content' filter. Scans the current post and adds the 'broken_link' 
   * CSS class to all links that are known to be broken. Currently works only on standard
   * HTML links (i.e. the '<a href=...' kind). 
   *
   * @param string $content Post content
   * @return string Modified post content.
   */
	function hook_the_content($content){
		global $post, $wpdb; /** @var wpdb $wpdb */
        if ( empty($post) || !in_array($post->post_type, $this->enabled_post_types)) {
        	return $content;
       	}
        
        //Retrieve info about all occurrences of broken links in the current post
        $q = "
			SELECT instances.raw_url
			FROM {$wpdb->prefix}blc_instances AS instances JOIN {$wpdb->prefix}blc_links AS links 
				ON instances.link_id = links.link_id
			WHERE 
				instances.container_type = %s
				AND instances.container_id = %d
				AND links.broken = 1
				AND parser_type = 'link' 
		";
		$q = $wpdb->prepare($q, $post->post_type, $post->ID);
		$links = $wpdb->get_results($q, ARRAY_A);
		
		//Return the content unmodified if there are no broken links in this post.
		if ( empty($links) || !is_array($links) ){
			return $content;
		}
				
		//Put the broken link URLs in an array
		$broken_link_urls = array();
		foreach($links as $link){
			$broken_link_urls[] = $link['raw_url'];
		}
		
        //Iterate over all HTML links and modify the broken ones
		if ( $parser = blcParserHelper::get_parser('link') ){
			$content = $parser->multi_edit($content, array(&$this, 'highlight_broken_link'), $broken_link_urls);
		}
		
		return $content;
	}
	
  /**
   * Analyse a link and add 'broken_link' CSS class if the link is broken.
   *
   * @see blcHtmlLink::multi_edit() 
   *
   * @param array $link Associative array of link data.
   * @param array $broken_link_urls List of broken link URLs present in the current post.
   * @return array|string The modified link
   */
	function highlight_broken_link($link, $broken_link_urls){
		if ( !in_array($link['href'], $broken_link_urls) ){
			//Link not broken = return the original link tag
			return $link['#raw'];
		}
		
		//Add 'broken_link' to the 'class' attribute (unless already present).
		if ( $this->plugin_conf->options['mark_broken_links'] ){
			if ( isset($link['class']) ){
				$classes = explode(' ', $link['class']);
				if ( !in_array('broken_link', $classes) ){
					$classes[] = 'broken_link';
					$link['class'] = implode(' ', $classes);
				}
			} else {
				$link['class'] = 'broken_link';
			}
		}
		
		//Nofollow the link (unless it's already nofollow'ed)
		if ( $this->plugin_conf->options['nofollow_broken_links'] ){
			if ( isset($link['rel']) ){
				$relations = explode(' ', $link['rel']);
				if ( !in_array('nofollow', $relations) ){
					$relations[] = 'nofollow';
					$link['rel'] = implode(' ', $relations);
				}
			} else {
				$link['rel'] = 'nofollow';
			}
		}
		
		return $link;
	}
	
  /**
   * A hook for the 'wp_head' action. Outputs the user-defined broken link CSS.
   *
   * @return void
   */
	function hook_wp_head(){
		echo '<style type="text/css">',$this->plugin_conf->options['broken_link_css'],'</style>';
	}
}

//Start up the post overlord
blcPostTypeOverlord::getInstance();


/**
 * Universal container item class used for all post types.
 * 
 * @package Broken Link Checker
 * @author Janis Elsts
 * @access public
 */
class blcAnyPostContainer extends blcContainer {
	var $default_field = 'post_content';
	
  /**
   * Get action links for this post.
   *
   * @param string $container_field Ignored.
   * @return array of action link HTML.
   */
	function ui_get_action_links($container_field = ''){
		$actions = array();
		
		//Fetch the post (it should be cached already)
		$post = $this->get_wrapped_object();
		if ( !$post ){
			return $actions;
		}
		
		$post_type_object = get_post_type_object($post->post_type);
		
		//Each post type can have its own cap requirements
		if ( current_user_can( $post_type_object->cap->edit_post, $this->container_id ) ){
			$actions['edit'] = sprintf(
				'<span class="edit"><a href="%s" title="%s">%s</a>',
				$this->get_edit_url(),
				$post_type_object->labels->edit_item,
				__('Edit')
			);
			
			//Trash/Delete link
			if ( current_user_can( $post_type_object->cap->delete_post, $this->container_id ) ){
				if ( $this->can_be_trashed() ) { 
					$actions['trash'] = sprintf(
						"<span class='trash'><a class='submitdelete' title='%s' href='%s'>%s</a>",
						esc_attr(__('Move this item to the Trash')),
						esc_attr(get_delete_post_link($this->container_id, '', false)),
						__('Trash')
					);
				} else {
					$actions['delete'] = sprintf(
						"<span><a class='submitdelete' title='%s' href='%s'>%s</a>",
						esc_attr(__('Delete this item permanently')),
						esc_attr(get_delete_post_link($this->container_id, '', true)),
						__('Delete')
					);
				}
			}
		}
		
		//View/Preview link
		$title = get_the_title($this->container_id);
		if ( in_array($post->post_status, array('pending', 'draft')) ) {
			if ( current_user_can($post_type_object->cap->edit_post, $this->container_id) ){
				$actions['view'] = sprintf(
					'<span class="view"><a href="%s" title="%s" rel="permalink">%s</a>',
					esc_url( add_query_arg( 'preview', 'true', get_permalink($this->container_id) ) ),
					esc_attr(sprintf(__('Preview &#8220;%s&#8221;'), $title)),
					__('Preview')
				);
			}
		} elseif ( 'trash' != $post->post_status ) {
			$actions['view'] = sprintf(
				'<span class="view"><a href="%s" title="%s" rel="permalink">%s</a>',
				esc_url( get_permalink($this->container_id) ),
				esc_attr(sprintf(__('View &#8220;%s&#8221;'), $title)),
				__('View')
			);
		}
		
		return $actions;
	}
	
  /**
   * Get the HTML for displaying the post title in the "Source" column.
   *
   * @param string $container_field Ignored.
   * @param string $context How to filter the output. Optional, defaults to 'display'. 
   * @return string HTML
   */
	function ui_get_source($container_field = '', $context = 'display'){
		$source = '<a class="row-title" href="%s" title="%s">%s</a>';
		$source = sprintf(
			$source,
			$this->get_edit_url(),
			esc_attr(__('Edit this item')),
			get_the_title($this->container_id)
		);
		
		return $source;
	}
	
  /**
   * Get edit URL for this container. Returns the URL of the Dashboard page where the item 
   * associated with this container can be edited.
   *
   * @access protected   
   *
   * @return string
   */
	function get_edit_url(){
		/*
		The below is a near-exact copy of the get_post_edit_link() function.  
		Unfortunately we can't just call that function because it has a hardcoded 
		caps-check which fails when called from the email notification script 
		executed by Cron.
		*/ 
		
		if ( !$post = $this->get_wrapped_object() ){
			return '';
		}
		
		$context = 'display';
		$action = '&amp;action=edit';
			
		$post_type_object = get_post_type_object( $post->post_type );
		if ( !$post_type_object ){
			return '';
		}
		
		return apply_filters( 'get_edit_post_link', admin_url( sprintf($post_type_object->_edit_link . $action, $post->ID) ), $post->ID, $context );
	}
	
  /**
   * Retrieve the post associated with this container. 
   *
   * @access protected
   *
   * @param bool $ensure_consistency Set this to true to ignore the cached $wrapped_object value and retrieve an up-to-date copy of the wrapped object from the DB (or WP's internal cache).
   * @return object Post data.
   */
	function get_wrapped_object($ensure_consistency = false){
		if( $ensure_consistency || is_null($this->wrapped_object) ){
			$this->wrapped_object = get_post($this->container_id);
		}		
		return $this->wrapped_object;
	}

  /**
   * Update the post associated with this container.
   *
   * @access protected
   *
   * @return bool|WP_Error True on success, an error if something went wrong.
   */
	function update_wrapped_object(){
		if ( is_null($this->wrapped_object) ){
			return new WP_Error(
				'no_wrapped_object',
				__('Nothing to update', 'broken-link-checker')
			);
		}

		$post_id = wp_update_post($this->wrapped_object, true);
		if ( is_wp_error($post_id) ) {
			return $post_id;
		} else if ( $post_id == 0 ){
			return new WP_Error(
				'update_failed',
				sprintf(__('Updating post %d failed', 'broken-link-checker'), $this->container_id)
			);
		} else {
			return true;
		}
	}
	
  /**
   * Get the base URL of the container. For posts, the post permalink is used
   * as the base URL when normalizing relative links.
   *
   * @return string
   */
	function base_url(){
		return get_permalink($this->container_id);
	}
	
  /**
   * Delete or trash the post corresponding to this container.
   * Will always move to trash instead of deleting if trash is enabled.
   *
   * @return bool|WP_error
   */
	function delete_wrapped_object(){
		//Note that we don't need to delete the synch record and instances here - 
		//wp_delete_post()/wp_trash_post() will run the post_delete/trash hook, 
		//which will be caught by blcPostContainerManager, which will in turn 
		//delete anything that needs to be deleted.
		if ( EMPTY_TRASH_DAYS ){
			return $this->trash_wrapped_object();
		} else {
			if ( wp_delete_post($this->container_id, true) ){
				return true;
			} else {
				return new WP_Error(
					'delete_failed',
					sprintf(
						__('Failed to delete post "%s" (%d)', 'broken-link-checker'),
						get_the_title($this->container_id),
						$this->container_id
					)
				);
			}
		}
	}
	
	/**
	 * Move the post corresponding to this container to the Trash.
	 * 
	 * @return bool|WP_Error
	 */
	function trash_wrapped_object(){
		if ( !EMPTY_TRASH_DAYS ){
			return new WP_Error(
				'trash_disabled',
				sprintf(
					__('Can\'t move post "%s" (%d) to the trash because the trash feature is disabled', 'broken-link-checker'),
					get_the_title($this->container_id),
					$this->container_id
				)
			);
		}
		
		$post = get_post($this->container_id);
		if ( $post->post_status == 'trash' ){
			//Prevent conflicts between post and custom field containers trying to trash the same post.
			//BUG: Post and custom field containers shouldn't wrap the same object
			return true;
		}
		
		if ( wp_trash_post($this->container_id) ){
			return true;
		} else {
			return new WP_Error(
				'trash_failed',
				sprintf(
					__('Failed to move post "%s" (%d) to the trash', 'broken-link-checker'),
					get_the_title($this->container_id),
					$this->container_id
				)
			);
		}
	}
	
	/**
	 * Check if the current user can delete/trash this post.
	 * 
	 * @return bool
	 */
	function current_user_can_delete(){
		$post = $this->get_wrapped_object();
		$post_type_object = get_post_type_object($post->post_type);
		return current_user_can( $post_type_object->cap->delete_post, $this->container_id );
	}
	
	function can_be_trashed(){
		return defined('EMPTY_TRASH_DAYS') && EMPTY_TRASH_DAYS;
	}
}



/**
 * Universal manager usable for most post types.
 * 
 * @package Broken Link Checker
 * @access public
 */
class blcAnyPostContainerManager extends blcContainerManager {
	var $container_class_name = 'blcAnyPostContainer';
	var $fields = array('post_content' => 'html');
	
	function init(){
		parent::init();

		//Notify the overlord that the post/container type that this instance is 
		//responsible for is enabled.
		$overlord = blcPostTypeOverlord::getInstance();
		$overlord->post_type_enabled($this->container_type); 
	}
	
  /**
   * Instantiate multiple containers of the container type managed by this class.
   *
   * @param array $containers Array of assoc. arrays containing container data.
   * @param string $purpose An optional code indicating how the retrieved containers will be used.
   * @param bool $load_wrapped_objects Preload wrapped objects regardless of purpose. 
   * 
   * @return array of blcPostContainer indexed by "container_type|container_id"
   */
	function get_containers($containers, $purpose = '', $load_wrapped_objects = false){
		$containers = $this->make_containers($containers);
		
		//Preload post data if it is likely to be useful later
		$preload = $load_wrapped_objects || in_array($purpose, array(BLC_FOR_DISPLAY, BLC_FOR_PARSING));
		if ( $preload ){
			$post_ids = array();
			foreach($containers as $container){
				$post_ids[] = $container->container_id;
			}
			
			$args = array('include' => implode(',', $post_ids));
			$posts = get_posts($args);
			
			foreach($posts as $post){
				$key = $this->container_type . '|' . $post->ID;
				if ( isset($containers[$key]) ){
					$containers[$key]->wrapped_object = $post;
				}
			}
		}
		
		return $containers;
	}
	
  /**
   * Create or update synchronization records for all posts.
   *
   * @param bool $forced If true, assume that all synch. records are gone and will need to be recreated from scratch. 
   * @return void
   */
	function resynch($forced = false){
		$overlord = blcPostTypeOverlord::getInstance();
		$overlord->resynch($this->container_type, $forced);
	}
	
  /**
   * Get the message to display after $n posts have been deleted.
   *
   * @param int $n Number of deleted posts.
   * @return string A delete confirmation message, e.g. "5 posts were moved deleted"
   */
	function ui_bulk_delete_message($n){
		//Since the "Trash" feature has been introduced, calling wp_delete_post
		//doesn't actually delete the post (unless you set force_delete to True), 
		//just moves it to the trash. So we pick the message accordingly. 
		//(If possible, BLC *always* moves to trash instead of deleting permanently.)
		if ( function_exists('wp_trash_post') && EMPTY_TRASH_DAYS ){
			return blcAnyPostContainerManager::ui_bulk_trash_message($n);
		} else {
			$post_type_object = get_post_type_object($this->container_type);
			$type_name = '';
			
			if ( $this->container_type == 'post' || is_null($post_type_object) ){
				$delete_msg = _n("%d post deleted.", "%d posts deleted.", $n, 'broken-link-checker');
			} elseif ( $this->container_type == 'page' ){
				$delete_msg = _n("%d page deleted.", "%d pages deleted.", $n, 'broken-link-checker');
			} else {
				$delete_msg = _n('%d "%s" deleted.', '%d "%s" deleted.', $n, 'broken-link-checker');
				$type_name = ($n == 1 ? $post_type_object->labels->singular_name : $post_type_object->labels->name); 
			}
			return sprintf($delete_msg, $n, $type_name);
		}
	}
	
		
  /**
   * Get the message to display after $n posts have been trashed.
   *
   * @param int $n Number of deleted posts.
   * @return string A confirmation message, e.g. "5 posts were moved to trash"
   */
	function ui_bulk_trash_message($n){
		$post_type_object = get_post_type_object($this->container_type);
		$type_name = '';
		
		if ( $this->container_type == 'post' || is_null($post_type_object) ){
			$delete_msg = _n("%d post moved to the Trash.", "%d posts moved to the Trash.", $n, 'broken-link-checker');
		} elseif ( $this->container_type == 'page' ){
			$delete_msg = _n("%d page moved to the Trash.", "%d pages moved to the Trash.", $n, 'broken-link-checker');
		} else {
			$delete_msg = _n('%d "%s" moved to the Trash.', '%d "%s" moved to the Trash.', $n, 'broken-link-checker');
			$type_name = ($n == 1 ? $post_type_object->labels->singular_name : $post_type_object->labels->name); 
		}
		return sprintf($delete_msg, $n, $type_name);
	}
}
