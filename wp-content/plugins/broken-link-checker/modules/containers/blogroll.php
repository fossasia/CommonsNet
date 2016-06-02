<?php
/*
Plugin Name: Blogroll items
Description: 
Version: 1.0
Author: Janis Elsts

ModuleID: blogroll
ModuleCategory: container
ModuleClassName: blcBookmarkManager
*/

class blcBookmark extends blcContainer{
	
	function ui_get_source($container_field = '', $context = 'display'){
		$bookmark = $this->get_wrapped_object();
		
		$image = sprintf(
			'<img src="%1$s" class="blc-small-image" title="%2$s" alt="%2$s">',
			esc_attr( plugins_url('/images/font-awesome/font-awesome-link.png', BLC_PLUGIN_FILE) ),
			__('Bookmark', 'broken-link-checker')						
		);
		
		$link_name = sprintf(
			'<a class="row-title" href="%s" title="%s">%s</a>',
			$this->get_edit_url(),
			__('Edit this bookmark', 'broken-link-checker'),
			sanitize_bookmark_field('link_name', $bookmark->link_name, $this->container_id, 'display')
		);
		
		if ( $context != 'email' ){
			return "$image $link_name";
		} else {
			return $link_name;
		}
	}
	
	function ui_get_action_links($container_field){
		//Inline action links for bookmarks     
		$bookmark = $this->get_wrapped_object();
		
		$delete_url = admin_url( wp_nonce_url("link.php?action=delete&link_id={$this->container_id}", 'delete-bookmark_' . $this->container_id) ); 
		
      	$actions = array();
		if ( current_user_can('manage_links') ) {
			$actions['edit'] = '<span class="edit"><a href="' . $this->get_edit_url() . '" title="' . esc_attr(__('Edit this bookmark', 'broken-link-checker')) . '">' . __('Edit') . '</a>';
			$actions['delete'] = "<span class='delete'><a class='submitdelete' href='" . esc_url($delete_url) . "' onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to delete this link '%s'\n  'Cancel' to stop, 'OK' to delete."), $bookmark->link_name)) . "') ) { return true;}return false;\">" . __('Delete') . "</a>";
		}
	
		return $actions;
	}
	
	function get_edit_url(){
		return esc_url(admin_url("link.php?action=edit&link_id={$this->container_id}"));
	}
	
  /**
   * Retrieve the bookmark associated with this container. 
   *
   * @access protected
   *
   * @param bool $ensure_consistency Set this to true to ignore the cached $wrapped_object value and retrieve an up-to-date copy of the wrapped object from the DB (or WP's internal cache).
   * @return object Bookmark data.
   */
	function get_wrapped_object($ensure_consistency = false){
		if( $ensure_consistency || is_null($this->wrapped_object) ){
			$this->wrapped_object = get_bookmark($this->container_id);
		}		
		return $this->wrapped_object;
	}

  /**
   * Update the bookmark associated with this container.
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
		
		//wp_update_link() expects it's argument to be an array.
		$data = (array)$this->wrapped_object;
		//Update the bookmark
		$rez = wp_update_link($data);
		
		if ( !empty($rez) ){
			return true;
		} else {
			return new WP_Error(
				'update_failed',
				sprintf(__('Updating bookmark %d failed', 'broken-link-checker'), $this->container_id)
			);
		}
	}
	
  /**
   * Delete the bookmark corresponding to this container.
   * Also removes the synch. record of the container and removes all associated instances.
   *
   * @return bool|WP_error
   */
	function delete_wrapped_object(){
		if ( wp_delete_link($this->container_id) ){
			//Note that there is no need to explicitly delete the synch. record and instances 
			//associated with this link - wp_delete_link() will execute the 'delete_link' action, 
			//which will be noticed by blcBookmarkManager, which will then delete anything that needs
			//to be deleted.
			
			//But in case the (undocumented) behaviour of wp_delete_link() changes in a later WP version,
			//add a call to $this->delete() here.
			return true;
		} else {
			$bookmark = $this->get_wrapped_object();
			
			if ( is_null($bookmark) ){
				$link_name = "[nonexistent]";
			} else {
				$link_name = sanitize_bookmark_field('link_name', $bookmark->link_name, $this->container_id, 'display');
			}
			
			$msg = sprintf(
				__('Failed to delete blogroll link "%s" (%d)', 'broken-link-checker'),
				$link_name,
				$this->container_id
			);
			
			return new WP_Error( 'delete_failed', $msg );
		}
	}
	
	function current_user_can_delete(){
		return current_user_can('manage_links');
	}
	
	function can_be_trashed(){
		return false;
	}
	
  /**
   * Get the default link text. For bookmarks, this is the bookmark name.
   *
   * @param string $field
   * @return string
   */
	function default_link_text($field = ''){
		$bookmark = $this->get_wrapped_object();
		return sanitize_bookmark_field('link_name', $bookmark->link_name, $this->container_id, 'display');
	}
	
  /**
   * For bookmarks, calling unlink() simply removes the bookmark.
   *
   * @return bool|WP_Error True on success, or an error object if something went wrong.
   */
	function unlink($field_name, $parser, $url, $raw_url =''){
		return $this->delete_wrapped_object(); 
	}
	
}

class blcBookmarkManager extends blcContainerManager{
	var $container_class_name = 'blcBookmark';
	var $fields = array('link_url' => 'url_field');
	
  /**
   * Set up hooks that monitor added/modified/deleted bookmarks.
   *
   * @return void
   */
	function init(){
		parent::init();
		
        add_action('add_link', array($this,'hook_add_link'));
        add_action('edit_link', array($this,'hook_edit_link'));
        add_action('delete_link', array($this,'hook_delete_link'));
	}
	
  /**
   * Instantiate multiple containers of the container type managed by this class.
   *
   * @param array $containers Array of assoc. arrays containing container data.
   * @param string $purpose An optional code indicating how the retrieved containers will be used.
   * @param bool $load_wrapped_objects Preload wrapped objects regardless of purpose. 
   * 
   * @return array of blcBookmark indexed by "container_type|container_id"
   */
	function get_containers($containers, $purpose = '', $load_wrapped_objects = false){
		$containers = $this->make_containers($containers);
		
		//Preload bookmark data if it is likely to be useful later
		$preload = $load_wrapped_objects || in_array($purpose, array(BLC_FOR_DISPLAY, BLC_FOR_PARSING));
		if ( $preload ){
			$bookmark_ids = array();
			foreach($containers as $container){
				$bookmark_ids[] = $container->container_id;
			}
			
			$args = array('include' => implode(',', $bookmark_ids));
			$bookmarks = get_bookmarks($args);
			
			foreach($bookmarks as $bookmark){
				$key = $this->container_type . '|' . $bookmark->link_id;
				if ( isset($containers[$key]) ){
					$containers[$key]->wrapped_object = $bookmark;
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
		global $wpdb; /** @var wpdb $wpdb */
		
		if ( !$forced ){
			//Usually the number of bookmarks is rather small, so it's cheap enough to always 
			//drop the entire list of bookmark-related synch records and recreate it from scratch.
			$q = $wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}blc_synch WHERE container_type = %s",
				$this->container_type
			);
			$wpdb->query( $q );	 
		}
		
		//Create new synchronization records for all bookmarks (AKA the blogroll).
 		$q = "INSERT INTO {$wpdb->prefix}blc_synch(container_id, container_type, synched)
			  SELECT link_id, %s, 0
			  FROM {$wpdb->links}
			  WHERE 1";
		$q = $wpdb->prepare($q, $this->container_type);
 		$wpdb->query( $q );
	}
	
  /**
   * When a bookmark is added mark it as unsynched.
   *
   * @param int $link_id
   * @return void
   */
	function hook_add_link( $link_id ){
		$container = blcContainerHelper::get_container( array($this->container_type, $link_id) );
		$container->mark_as_unsynched();
	}
	
  /**
   * Ditto for modified bookmarks.
   *
   * @param int $link_id
   * @return void
   */
	function hook_edit_link( $link_id ){
		$this->hook_add_link( $link_id );
	}
	
  /**
   * When a bookmark is deleted, remove the related DB records.
   *
   * @param int $link_id
   * @return void
   */
	function hook_delete_link( $link_id ){
		//Get the container object.
		$container = blcContainerHelper::get_container( array($this->container_type, $link_id) );
		//Get the link(s) associated with it.
		$links = $container->get_links(); 
		
		//Remove synch. record & instance records.
		$container->delete();
		
		//Clean up links associated with this bookmark (there's probably only one)
		$link_ids = array();
		foreach($links as $link){
			$link_ids[] = $link->link_id;
		}
		blc_cleanup_links($link_ids);
	}
	
  /**
   * Get the message to display after $n bookmarks have been deleted.
   *
   * @param int $n Number of deleted bookmarks.
   * @return string The delete confirmation message.
   */
	function ui_bulk_delete_message($n){
		return sprintf(
			_n(
				"%d blogroll link deleted.", 
				"%d blogroll links deleted.", 
				$n, 
				'broken-link-checker'
			),
			$n
		);
	}
}
