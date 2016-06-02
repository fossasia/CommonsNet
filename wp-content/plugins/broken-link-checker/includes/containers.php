<?php

/**
 * The base class for link container managers. 
 * 
 * Sub-classes should override at least the get_containers() and resynch() methods.
 *
 * @package Broken Link Checker
 * @access public
 */
class blcContainerManager extends blcModule {
	
	var $container_type = '';
	var $fields = array();
	var $container_class_name = 'blcContainer';
	
  /**
   * Do whatever setup necessary that wasn't already done in the constructor.
   *
   * This method was added so that sub-classes would have something "safe" to 
   * over-ride without having to deal with PHP4/5 constructors.
   * 
   * @return void
   */
	function init(){
		parent::init();
		$this->container_type = $this->module_id;
		//Sub-classes might also use it to set up hooks, etc.
	}
	
  /**
   * Instantiate a link container.
   *
   * @param array $container An associative array of container data.
   * @return blcContainer
   */
	function get_container($container){
		$container['fields'] = $this->get_parseable_fields();
        $container_obj = new $this->container_class_name($container);
        return $container_obj;
	}
	
  /**
   * Instantiate multiple containers of the container type managed by this class and optionally
   * pre-load container data used for display/parsing.
   *
   * Sub-classes should, if possible, use the $purpose argument to pre-load any extra data required for 
   * the specified task right away, instead of making multiple DB roundtrips later. For example, if 
   * $purpose is set to the BLC_FOR_DISPLAY constant, you might want to preload any DB data that the 
   * container will need in blcContainer::ui_get_source().
   *
   * @see blcContainer::make_containers()
   * @see blcContainer::ui_get_source()
   * @see blcContainer::ui_get_action_links()
   *
   * @param array $containers Array of assoc. arrays containing container data.
   * @param string $purpose An optional code indicating how the retrieved containers will be used.
   * @param bool $load_wrapped_objects Preload wrapped objects regardless of purpose. 
   * 
   * @return array of blcContainer indexed by "container_type|container_id"
   */
	function get_containers($containers, $purpose = '', $load_wrapped_objects = false){
		return $this->make_containers($containers);
	}
	
  /**
   * Instantiate multiple containers of the container type managed by this class
   *
   * @param array $containers Array of assoc. arrays containing container data.
   * @return array of blcContainer indexed by "container_type|container_id"
   */
	function make_containers($containers){
		$results = array();
		foreach($containers as $container){
			$key = $container['container_type'] . '|' . $container['container_id'];
			$results[ $key ] = $this->get_container($container);
		}
		return $results;
	}
	
  /**
   * Create or update synchronization records for all containers managed by this class.
   *
   * Must be over-ridden in subclasses.
   *
   * @param bool $forced If true, assume that all synch. records are gone and will need to be recreated from scratch. 
   * @return void
   */
	function resynch($forced = false){
		trigger_error('Function blcContainerManager::resynch() must be over-ridden in a sub-class', E_USER_ERROR);
	}
	
	/**
	 * Resynch when activated.
	 * 
	 * @uses blcContainerManager::resynch()
	 * 
	 * @return void
	 */
	function activated(){
		$this->resynch();
		blc_got_unsynched_items();
	}
	
	/**
	 * Get a list of the parseable fields and their formats common to all containers of this type. 
	 * 
	 * @return array Associative array of formats indexed by field name.
	 */
	function get_parseable_fields(){
		return $this->fields;
	}
	
  /**
   * Get the message to display after $n containers have been deleted.
   *
   * @param int $n Number of deleted containers.
   * @return string A delete confirmation message, e.g. "5 posts were moved to trash"
   */
	function ui_bulk_delete_message($n){
		return sprintf(
			_n(
				"%d '%s' has been deleted",
				"%d '%s' have been deleted",
				$n,
				'broken-link-checker'
			),
			$n,
			$this->container_type
		);
	}
	
  /**
   * Get the message to display after $n containers have been moved to the trash.
   *
   * @param int $n Number of trashed containers.
   * @return string A delete confirmation message, e.g. "5 posts were moved to trash"
   */
	function ui_bulk_trash_message($n){
		return $this->ui_bulk_delete_message($n);
	}
}

/**
 * The base class for link containers. All containers should extend this class.
 *
 * @package Broken Link Checker
 * @access public
 */
class blcContainer {
	
	var $fields = array();
	var $default_field = '';
	
	var $container_type;
	var $container_id = 0;	
	
	var $synched = false;
	var $last_synch = '0000-00-00 00:00:00';
	
	var $wrapped_object = null;
	
  /**
   * Constructor
   *
   * @param array $data
   * @param object $wrapped_object
   * @return void
   */
	function __construct( $data = null, $wrapped_object = null ){
		$this->wrapped_object = $wrapped_object;
		if ( !empty($data) && is_array($data) ){
			foreach($data as $name => $value){
				$this->$name = $value;
			}
		}
	}
	
  /**
   * Get the value of the specified field of the object wrapped by this container.
   * 
   * @access protected
   *
   * @param string $field Field name. If omitted, the value of the default field will be returned. 
   * @return string
   */
	function get_field($field = ''){
		if ( empty($field) ){
			$field = $this->default_field;
		}
		
		$w = $this->get_wrapped_object();
		return $w->$field;
	}
	
  /**
   * Update the value of the specified field in the wrapped object. 
   * This method will also immediately save the changed value by calling update_wrapped_object().  
   *
   * @access protected
   *
   * @param string $field Field name.
   * @param string $new_value Set the field to this value. 
   * @param string $old_value The previous value of the field. Optional, but can be useful for container that need the old value to distinguish between several instances of the same field (e.g. post metadata).     
   * @return bool|WP_Error True on success, an error object if something went wrong.
   */
	function update_field($field, $new_value, $old_value = ''){
		$w = $this->get_wrapped_object();
		$w->$field = $new_value;
		return $this->update_wrapped_object();
	}
	
  /**
   * Retrieve the entity wrapped by this container. 
   * The fetched object will also be cached in the $wrapped_object variable.  
   *
   * @access protected
   *
   * @param bool $ensure_consistency Set this to true to ignore the cached $wrapped_object value and retrieve an up-to-date copy of the wrapped object from the DB (or WP's internal cache).
   * @return object The wrapped object.
   */
	function get_wrapped_object($ensure_consistency = false){
		trigger_error('Function blcContainer::get_wrapped_object() must be over-ridden in a sub-class', E_USER_ERROR);
	}	
	
  /**
   * Update the entity wrapped by the container with values currently in the $wrapped_object.
   *
   * @access protected
   *
   * @return bool|WP_Error True on success, an error if something went wrong.
   */
	function update_wrapped_object(){
		trigger_error('Function blcContainer::update_wrapped_object() must be over-ridden in a sub-class', E_USER_ERROR);
	}
	
  /**
   * Parse the container for links and save the results to the DB.
   *
   * @return void
   */
	function synch(){
		//FB::log("Parsing {$this->container_type}[{$this->container_id}]");
		
		//Remove any existing link instance records associated with the container
		$this->delete_instances();
		
		//Load the wrapped object, if not done already
		$this->get_wrapped_object();
		
		//FB::log($this->fields, "Parseable fields :");
		
		//Iterate over all parse-able fields
		foreach($this->fields as $name => $format){
			//Get the field value
			$value = $this->get_field($name);
			if ( empty($value) ){
				//FB::log($name, "Skipping empty field");
				continue;
			}
			//FB::log($name, "Parsing field");
			
			//Get all parsers applicable to this field
			$parsers = blcParserHelper::get_parsers( $format, $this->container_type );
			//FB::log($parsers, "Applicable parsers");
			
			if ( empty($parsers) ) continue;
			
			$base_url = $this->base_url();
			$default_link_text = $this->default_link_text($name);
			
			//Parse the field with each parser
			foreach($parsers as $parser){
				//FB::log("Parsing $name with '{$parser->parser_type}' parser");
				$found_instances = $parser->parse( $value, $base_url, $default_link_text );
				//FB::log($found_instances, "Found instances");

				$transactionManager = TransactionManager::getInstance();
				$transactionManager->start();

				//Complete the link instances by adding container info, then save them to the DB.
				foreach($found_instances as $instance){
					$instance->set_container($this, $name);
					$instance->save(); 
				}

				$transactionManager->commit();

			}
		}
		
		$this->mark_as_synched();
	}
	
  /**
   * Mark the container as successfully synchronized (parsed for links).
   *
   * @return bool
   */
	function mark_as_synched(){
		global $wpdb; /* @var wpdb $wpdb */
		
		$this->last_synch = time();
		
		$q = "INSERT INTO {$wpdb->prefix}blc_synch( container_id, container_type, synched, last_synch)
			  VALUES( %d, %s, %d, NOW() )
			  ON DUPLICATE KEY UPDATE synched = VALUES(synched), last_synch = VALUES(last_synch)";
		$rez = $wpdb->query( $wpdb->prepare( $q, $this->container_id, $this->container_type, 1 ) );
		
		return ($rez !== false);
	}
	
  /**
   * blcContainer::mark_as_unsynched()
   * Mark the container as not synchronized (not parsed, or modified since the last parse).
   * The plugin will attempt to (re)parse the container at the earliest opportunity.
   *
   * @return bool
   */
	function mark_as_unsynched(){
		global $wpdb; /* @var wpdb $wpdb */
		
		$q = "INSERT INTO {$wpdb->prefix}blc_synch( container_id, container_type, synched, last_synch)
			  VALUES( %d, %s, %d, '0000-00-00 00:00:00' )
			  ON DUPLICATE KEY UPDATE synched = VALUES(synched)";
		$rez = $wpdb->query( $wpdb->prepare( $q, $this->container_id, $this->container_type, 0 ) );
		
		blc_got_unsynched_items();
		
		return ($rez !== false);
	}
	
  /**
   * Get the base URL of the container. Used to normalize relative URLs found
   * in the container. For example, for posts this would be the post permalink.
   *
   * @return string
   */
	function base_url(){
		return home_url();
	}
	
  /**
   * Get the default link text to use for links found in a specific container field.
   *
   * This is generally only meaningful for non-HTML container fields.  
   * For example, if the container is post metadata, the default
   * link text might be equal to the name of the custom field.   
   *
   * @param string $field
   * @return string
   */
	function default_link_text($field = ''){
		return '';
	}
	
	
	
  /**
   * Delete the DB record of this container.
   * Also deletes the DB records of all link instances associated with it. 
   * Calling this method will not affect the WP entity (e.g. a post) corresponding to this container.
   *
   * @return bool
   */
	function delete(){
		global $wpdb; /* @var wpdb $wpdb */
		
		//Delete instances first.
		$rez = $this->delete_instances();
		if ( !$rez ){
			return false;
		}
		
		//Now delete the container record.
		$q = "DELETE FROM {$wpdb->prefix}blc_synch
			  WHERE container_id = %d AND container_type = %s";
		$q = $wpdb->prepare($q, $this->container_id, $this->container_type);
		
        if ( $wpdb->query( $q ) === false ){
			return false;
		} else {
			return true;
		}
	}
	
  /**
   * Delete all link instance records associated with this container.
   * NB: Calling this method will not affect the WP entity (e.g. a post) corresponding to this container.
   *
   * @return bool
   */
	function delete_instances(){
		global $wpdb; /* @var wpdb $wpdb */
		
		//Remove instances associated with this container
		$q = "DELETE FROM {$wpdb->prefix}blc_instances 
			  WHERE container_id = %d AND container_type = %s";
		$q = $wpdb->prepare($q, $this->container_id, $this->container_type);
		
        if ( $wpdb->query( $q ) === false ){
			return false;
		} else {
			return true;
		}
	}
	
  /**
   * Delete or trash the WP entity corresponding to this container. Should prefer moving to trash, if possible. 
   * Also remove the synch. record of the container and all associated instances.
   *
   * Must be over-ridden in a sub-class.
   *
   * @return bool|WP_Error
   */
	function delete_wrapped_object(){
		trigger_error('Function blcContainer::delete_wrapped_object() must be over-ridden in a sub-class', E_USER_ERROR);
	}
	
	/**
	 * Move the WP entity corresponding to this container to the Trash.
	 * 
	 * Must be over-riden in a subclass.
	 * 
	 * @return bool|WP_Error
	 */
	function trash_wrapped_object(){
		trigger_error('Function blcContainer::trash_wrapped_object() must be over-ridden in a sub-class', E_USER_ERROR);
	}
	
	/**
	 * Check if the current user can delete/trash this container.
	 * 
	 * Should be over-ridden in a subclass.
	 * 
	 * @return bool
	 */
	function current_user_can_delete(){
		return false;
	}
	
	/**
	 * Determine if this container can be moved to the trash.
	 * 
	 * Should be over-ridden in a subclass.
	 * 
	 * @return bool
	 */
	function can_be_trashed(){
		return false;
	}	
	
	
  /**
   * Change all links with the specified URL to a new URL.
   *
   * @param string $field_name
   * @param blcParser $parser
   * @param string $new_url
   * @param string $old_url
   * @param string $old_raw_url
   * @param string $new_text Optional.
   *
   * @return array|WP_Error The new value of raw_url on success, or an error object if something went wrong.
   */
	function edit_link($field_name, $parser, $new_url, $old_url = '', $old_raw_url = '', $new_text = null){
		//Ensure we're operating on a consistent copy of the wrapped object.
		/* 
		Explanation 
		
		Consider this scenario where the container object wraps a blog post : 
			1) The container object gets created and loads the post data. 
			2) Someone modifies the DB data corresponding to the post.
			3) The container tries to edit a link present in the post. However, the post
			has changed since the time it was first cached, so when the container updates
			the post with it's changes, it will overwrite whatever modifications were made
			in step 2.
			
		This would not be a problem if WP entities like posts and comments were 
		actually real objects, not just bags of key=>value pairs, but oh well.
			
		Therefore, it is necessary to re-load the wrapped object before editing it.   
		*/  
		$this->get_wrapped_object(true);
		
		//Get the current value of the field that needs to be edited.
		$old_value = $this->get_field($field_name);
		
		//Have the parser modify the specified link. If successful, the parser will 
		//return an associative array with two keys - 'content' and 'raw_url'.
		//Otherwise we'll get an instance of WP_Error.
		if ( $parser->is_link_text_editable() ) {
			$edit_result = $parser->edit($old_value, $new_url, $old_url, $old_raw_url, $new_text);
		} else {
			$edit_result = $parser->edit($old_value, $new_url, $old_url, $old_raw_url);
		}
		if ( is_wp_error($edit_result) ){
			return $edit_result;
		}
			
		//Update the field with the new value returned by the parser.
		$update_result = $this->update_field( $field_name, $edit_result['content'], $old_value );
		if ( is_wp_error($update_result) ){
			return $update_result;
		}
		
		//Return the new values to the instance.
		unset($edit_result['content']); //(Except content, which it doesn't need.)
		return $edit_result;
	}
	
  /**
   * Remove all links with the specified URL, leaving their anchor text intact.
   *
   * @param string $field_name
   * @param blcParser $parser
   * @param string $url
   * @param string $raw_url
   * @return bool|WP_Error True on success, or an error object if something went wrong.
   */
	function unlink($field_name, $parser, $url, $raw_url =''){
		//Ensure we're operating on a consistent copy of the wrapped object.
		$this->get_wrapped_object(true);
		
		$old_value = $this->get_field($field_name);
		
		$new_value = $parser->unlink($old_value, $url, $raw_url);
		if ( is_wp_error($new_value) ){
			return $new_value;
		}
		
		return $this->update_field( $field_name, $new_value, $old_value ); 
	}
	
  /**
   * Retrieve a list of links found in this container.
   *
   * @access public 
   *
   * @return array of blcLink
   */
	function get_links(){
		$params = array(
			's_container_type' => $this->container_type,
			's_container_id' => $this->container_id,
		);
		return blc_get_links($params);
	}
	
	
  /**
   * Get action links to display in the "Source" column of the Tools -> Broken Links link table.
   *
   * @param string $container_field
   * @return array
   */
	function ui_get_action_links($container_field){
		return array();
	}
	
  /**
   * Get the container name to display in the "Source" column of the Tools -> Broken Links link table.
   *
   * @param string $container_field
   * @param string $context
   * @return string
   */
	function ui_get_source($container_field, $context = 'display'){
		return sprintf('%s[%d] : %s', $this->container_type, $this->container_id, $container_field);
	}
	
  /**
   * Get edit URL. Returns the URL of the Dashboard page where the item associated with this
   * container can be edited.
   *
   * HTML entities like '&' will be properly escaped for display.   
   *
   * @access protected   
   *
   * @return string
   */
	function get_edit_url(){
		//Should be over-ridden in a sub-class.
		return '';
	}
	
}


/**
 * An utility class for working with link container types.
 * All methods of this class should be called statically.  
 *  
 * @package Broken Link Checker
 */
class blcContainerHelper {
	
  /**
   * Get the manager associated with a container type.
   *
   * @param string $container_type
   * @param string $fallback If there is no manager associated with $container_type, return the manager of this container type instead.  
   * @return blcContainerManager|null
   */
	static function get_manager( $container_type, $fallback = '' ){
		$module_manager = blcModuleManager::getInstance();
		$container_manager = null;
		
		if ( $container_manager = $module_manager->get_module($container_type, true, 'container') ){
			return $container_manager;
		} elseif ( !empty($fallback) && ( $container_manager = $module_manager->get_module($fallback, true, 'container') ) ) {
			return $container_manager;
		} else {
			return null;
		}
	}
	
  /**
   * Retrieve or instantiate a container object.
   *
   * Pass an array containing the container type (string) and ID (int) to retrieve the container
   * from the database. Alternatively, pass an associative array to create a new container object
   * from the data in the array. 
   *
   * @param array $container Either [container_type, container_id], or an assoc. array of container data. 
   * @return blcContainer|null
   */
	static function get_container( $container ){
		global $wpdb; /* @var wpdb $wpdb */
		
		if ( !is_array($container) || ( count($container) < 2 ) ){
			return null;
		}
		
		if ( is_string($container[0]) && is_numeric($container[1]) ){
			//The argument is in the [container_type, id] format
			//Fetch the container's synch record.
			$q = "SELECT * FROM {$wpdb->prefix}blc_synch WHERE container_type = %s AND container_id = %d";
			$q = $wpdb->prepare($q, $container[0], $container[1]);
			$rez = $wpdb->get_row($q, ARRAY_A);
			
			if ( empty($rez) ){
				//The container wasn't found, so we'll create a new one.
				$container = array(
					'container_type' => $container[0],
					'container_id' => $container[1],
				);				
			} else {
				$container = $rez;
			}
		}
		
		if ( !($manager = blcContainerHelper::get_manager($container['container_type'])) ){
			return null;
		}
		
		return $manager->get_container($container);
	}
	
  /**
   * Retrieve or instantiate multiple link containers.
   *
   * Takes an array of container specifications and returns an array of container objects.
   * Each input array entry should be an array and consist either of a container type (string)
   * and container ID (int), or name => value pairs describing a container object.    
   *
   * @see blcContainerHelper::get_container()
   *
   * @param array $containers 
   * @param string $purpose Optional code indicating how the retrieved containers will be used.
   * @param string $fallback The fallback container type to use for unrecognized containers.
   * @param bool $load_wrapped_objects Preload wrapped objects regardless of purpose.
   * @return blcContainer[] Array of blcContainer indexed by "container_type|container_id"
   */
	static function get_containers( $containers, $purpose = '', $fallback = '', $load_wrapped_objects = false ){
		global $wpdb; /* @var wpdb $wpdb */
		
		//If the input is invalid or empty, return an empty array.
		if ( !is_array($containers) || (count($containers) < 1) ) {
			return array();
		}
		
		$first = reset($containers);
		if ( !is_array($first) ){
			return array();
		}
		
		if ( isset($first[0]) && is_string($first[0]) && is_numeric($first[1]) ){
			//The argument is an array of [container_type, id].
			//Divide the container IDs by container type.
			$by_type = array();
			
			foreach($containers as $container){
				if ( isset($by_type[$container[0]]) ){
					array_push($by_type[$container[0]], intval($container[1]));
				} else {
					$by_type[$container[0]] = array( intval($container[1]) );
				}
			}
			
			//Build the SQL to fetch all the specified containers
			$q = "SELECT *
			      FROM {$wpdb->prefix}blc_synch
				  WHERE";
				
			$pieces = array();
			foreach($by_type as $container_type => $container_ids){
				$pieces[] = '( container_type = "'. esc_sql($container_type) .'" AND container_id IN ('. implode(', ', $container_ids) .') )';
			}
			
			$q .= implode("\n\t OR ", $pieces);
			
			//Fetch the container synch. records from the DB.
			$containers = $wpdb->get_results($q, ARRAY_A);			
		}
		
		/*
		Divide the inputs into separate arrays by container type (again), then invoke 
		the appropriate manager for each type to instantiate the container objects.
		*/
		
		//At this point, $containers is an array of assoc. arrays comprising container data.
		$by_type = array();
		foreach($containers as $container){
			if ( isset($by_type[$container['container_type']]) ){
				array_push($by_type[$container['container_type']], $container);
			} else {
				$by_type[$container['container_type']] = array($container);
			}
		}
			
		$results = array();
		foreach($by_type as $container_type => $entries){
			$manager = blcContainerHelper::get_manager($container_type, $fallback);
			if ( !is_null($manager) ){
				$partial_results = $manager->get_containers($entries, $purpose, $load_wrapped_objects);
				$results = array_merge($results, $partial_results);
			}
		}
		
		return $results;
	}
	
  /**
   * Retrieve link containers that need to be synchronized (parsed).
   *
   * @param integer $max_results The maximum number of containers to return. Defaults to returning all unsynched containers. 
   * @return blcContainer[]
   */
	static function get_unsynched_containers($max_results = 0){
		global $wpdb; /* @var wpdb $wpdb */
		
		$q = "SELECT * FROM {$wpdb->prefix}blc_synch WHERE synched = 0";
		if ( $max_results > 0 ){
			$q .= " LIMIT $max_results";
		}
		
		$container_data = $wpdb->get_results($q, ARRAY_A);
		//FB::log($container_data, "Unsynched containers");
		if( empty($container_data) ){
			return array();
		}
		
		$containers = blcContainerHelper::get_containers($container_data, BLC_FOR_PARSING, 'dummy');
		return $containers;
	}
	
  /**
   * (Re)create and update synchronization records for all supported containers.
   * Calls the resynch() method of all registered managers.
   *
   * @param bool $forced If true, assume that no synch. records exist and build all of them from scratch.
   * @return void
   */
	static function resynch($forced = false){
		global $wpdb;
    	
		$module_manager = blcModuleManager::getInstance();
		$active_managers = $module_manager->get_active_by_category('container');    	
    	foreach($active_managers as $module_id => $module_data){
    		$manager = $module_manager->get_module($module_id);
    		if ( $manager ){
    			$manager->resynch($forced);
    		}
		}
	}
	
	/**
	 * Mark as unparsed all containers that match one of the the specified formats or 
	 * container types and that were last parsed after a specific timestamp.
	 * 
	 * Used by newly activated parsers to force the containers they're interested in 
	 * to resynchronize and thus let the parser process them.
	 * 
	 * @param array $formats Associative array of timestamps, indexed by format IDs.
	 * @param array $container_types Associative array of timestamps, indexed by container types. 
	 * @return bool
	 */
	static function mark_as_unsynched_where($formats, $container_types){
		global $wpdb; /* @var wpdb $wpdb */
		global $blclog;
		
		//Find containers that match any of the specified formats and add them to
		//the list of container types that need to be marked as unsynched.
		$module_manager = blcModuleManager::getInstance();
		$containers = $module_manager->get_active_by_category('container');
		
		foreach($containers as $module_id => $module_data){
			if ( $container_manager = $module_manager->get_module($module_id) ){
				$fields = $container_manager->get_parseable_fields();
				$container_type = $container_manager->container_type;
				foreach($formats as $format => $timestamp){
					if ( in_array($format, $fields) ){
						//Choose the earliest timestamp
						if ( isset($container_types[$container_type]) ){
							$container_types[$container_type] = min($timestamp, $container_types[$container_type]);
						} else {
							$container_types[$container_type] = $timestamp;
						}
					}
				}
			};
		}
		
		if ( empty($container_types) ){
			return true;
		}
		
		//Build the query to update all synch. records that match one of the specified 
		//container types and have been parsed after the specified time.
		$q = "UPDATE {$wpdb->prefix}blc_synch SET synched = 0 WHERE ";
		
		$pieces = array();
		foreach($container_types as $container_type => $timestamp){
			$pieces[] = $wpdb->prepare(
				'(container_type = %s AND last_synch >= %s)',
				$container_type,
				date('Y-m-d H:i:s', $timestamp)
			);
		}
		
		$q .= implode(' OR ', $pieces);
		$blclog->log('...... Executing query: ' . $q);

		$start_time = microtime(true);
		$rez = ($wpdb->query($q) !== false);
		$blclog->log(sprintf('...... %d rows affected, %.3f seconds', $wpdb->rows_affected, microtime(true) - $start_time));
		
		blc_got_unsynched_items();
		
		return $rez;
	}
	
	/**
	 * Remove synch. records that reference container types not currently loaded
	 * 
	 * @return bool
	 */
	static function cleanup_containers(){
		global $wpdb; /* @var wpdb $wpdb */
		global $blclog;
		
		$module_manager = blcModuleManager::getInstance();

		$start = microtime(true);
		$active_containers = $module_manager->get_escaped_ids('container');
		$q = "DELETE synch.*
		      FROM {$wpdb->prefix}blc_synch AS synch
		      WHERE
	      	    synch.container_type NOT IN ({$active_containers})";
		$rez = $wpdb->query($q);
		$elapsed = microtime(true) - $start;
		$blclog->log(sprintf('... %d synch records deleted in %.3f seconds', $wpdb->rows_affected, $elapsed));
		
		return $rez !== false;
	}
	
  /**
   * Get the message to display after $n containers of a specific type have been deleted.
   *
   * @param string $container_type 
   * @param int $n Number of deleted containers.
   * @return string A delete confirmation message, e.g. "5 posts were moved to trash"
   */
	static function ui_bulk_delete_message($container_type, $n){
		$manager = blcContainerHelper::get_manager($container_type);
		if ( is_null($manager) ){
			return sprintf(__("Container type '%s' not recognized", 'broken-link-checker'), $container_type);
		} else {
			return $manager->ui_bulk_delete_message($n);
		}
	}
	
	/**
	 * Get the message to display after $n containers of a specific type have been moved to the trash.
	 * 
	 * @see blcContainerHelper::ui_bulk_delete_message()
	 * 
	 * @param string $container_type
	 * @param int $n
	 * @return string
	 */
	static function ui_bulk_trash_message($container_type, $n){
		$manager = blcContainerHelper::get_manager($container_type);
		if ( is_null($manager) ){
			return sprintf(__("Container type '%s' not recognized", 'broken-link-checker'), $container_type);
		} else {
			return $manager->ui_bulk_trash_message($n);
		}
	}
}
