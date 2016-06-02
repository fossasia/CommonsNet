<?php

class blcModuleManager {

	/* @var blcConfigurationManager */
	var $plugin_conf;
	var $module_dir = '';
	
	var $_module_cache;
	var $_category_cache;
	var $_category_cache_active;
	var $_virtual_modules = array();
	
	var $loaded;
	var $instances;
	var $default_active_modules;
	
	
	/**
	 * Class "constructor".
	 * 
	 * @param array $default_active_modules An array of module ids specifying which modules are active by default.
	 * @return void
	 */
	function init($default_active_modules = null){
		$this->module_dir = realpath(dirname(__FILE__) . '/../modules');
		
		$this->plugin_conf = blc_get_configuration();
		$this->default_active_modules = $default_active_modules;
		
		$this->loaded = array();
		$this->instances = array();
		
		add_filter('extra_plugin_headers', array(&$this, 'inject_module_headers'));
	}
	
	/**
	 * Get an instance of the module manager.
	 * 
	 * @param array|null $default_active_modules
	 * @return blcModuleManager
	 */
	static function getInstance($default_active_modules = null){
		static $instance = null;
		if ( is_null($instance) ){
			$instance = new blcModuleManager();
			$instance->init($default_active_modules);
		}
		return $instance;
	}
	
	/**
	 * Retrieve a list of all installed BLC modules.
	 * 
	 * This is essentially a slightly modified copy of get_plugins().
	 * 
	 * @return array An associative array of module headers indexed by module ID.
	 */
	function get_modules(){
		if ( !isset($this->_module_cache) ){
			//Refresh the module cache. 
			
			$relative_path = '/' . plugin_basename($this->module_dir);
			if ( !function_exists('get_plugins') ){
				//BUG: Potentional security flaw/bug. plugin.php is not meant to be loaded outside admin panel.
				require_once(ABSPATH . 'wp-admin/includes/plugin.php'); 
			}
			$modules = get_plugins( $relative_path );
			
			$this->_module_cache = array();
			
			foreach($modules as $module_filename => $module_header){
				//Figure out the module ID. If not specified, it is equal to module's filename (sans the .php)
				if ( !empty($module_header['ModuleID']) ){
					$module_id = strtolower(trim($module_header['ModuleID']));
				} else {
					$module_id = strtolower(basename($module_filename, '.php'));
				}
				
				$module_header = $this->normalize_module_header($module_header, $module_id, $module_filename);
				$this->_module_cache[$module_id] = $module_header;			
			}
			
			$this->_category_cache = null;
		}
		
		return array_merge($this->_module_cache, $this->_virtual_modules);
	}
	
	/**
	 * Retrieve modules that match a specific category, or all modules sorted by categories.
	 * 
	 * If a category ID is specified, this method returns the modules that have the "ModuleCategory:" 
	 * file header set to that value, or an empty array if no modules match that category. The 
	 * return array is indexed by module id :
	 * [module_id1 => module1_data, module_id1 => module2_data, ...] 
	 * 
	 * If category is omitted, this method returns a list of all categories plus the modules 
	 * they contain. Only categories that have at least one module will be included. The return
	 * value is an array of arrays, indexed by category ID :  
	 * [category1 => [module1_id => module1_data, module2_id => module2_data, ...], ...]
	 *   
	 * 
	 * @param string $category Category id, e.g. "parser" or "container". Optional.  
	 * @param bool $markup Apply markup to module headers. Not implemented.
	 * @param bool $translate Translate module headers. Defaults to false.
	 * @return array An array of categories or module data.
	 */
	function get_modules_by_category($category = '', $markup = false, $translate = false){
		if ( !isset($this->_category_cache) ){
			$this->_category_cache = $this->sort_into_categories($this->get_modules()); 
		}
		
		if ( empty($category) ){
			if ( $markup || $translate ){
				
				//Translate/apply markup to module headers
				$processed = array();
				foreach($this->_category_cache as $category_id => $modules){
					$processed[$category_id] = array();
					foreach($modules as $module_id => $module_data){
						if ( $translate ){
							$module_data['Name'] = _x($module_data['Name'], 'module name', 'broken-link-checker');
						}
						$processed[$category_id][$module_id] = $module_data;
					}
				}
				
				return $processed;				
			} else {
				return $this->_category_cache;
			}
		} else {
			if ( isset($this->_category_cache[$category]) ){
				if ( $markup || $translate ){
					//Translate/apply markup to module headers
					$processed = array();
					foreach($this->_category_cache[$category] as $module_id => $module_data){
						if ( $translate ){
							$module_data['Name'] = _x($module_data['Name'], 'module name', 'broken-link-checker');
						}
						$processed[$module_id] = $module_data;
					}
					return $processed;
				} else {
					return $this->_category_cache[$category];
				}
			} else {
				return array();
			}
		}
	}
	
	/**
	 * Retrieve active modules that match a specific category, or all active modules sorted by categories.
	 * 
	 * @see blcModuleManager::get_modules_by_category()
	 * 
	 * @param string $category Category id. Optional.
	 * @return array An associative array of categories or module data.
	 */
	function get_active_by_category($category = ''){
		if ( !isset($this->_category_cache_active) ){
			$this->_category_cache_active = $this->sort_into_categories($this->get_active_modules());
		}
		
		if ( empty($category) ){
			return $this->_category_cache_active;
		} else {
			if ( isset($this->_category_cache_active[$category]) ){
				return $this->_category_cache_active[$category];
			} else {
				return array();
			}
		}
	}
	
	/**
	 * Get the module ids of all active modules that belong to a specific category, 
	 * quoted and ready for use in SQL.
	 * 
	 * @param string $category Category ID. If not specified, a list of all active modules will be returned.
	 * @return string A comma separated list of single-quoted module ids, e.g. 'module-foo','module-bar','modbaz'
	 */
	function get_escaped_ids($category = ''){
		global $wpdb;
		
		if ( empty($category) ){
			$modules = $this->get_active_modules();
		} else {
			$modules = $this->get_active_by_category($category);
		}
		
		$modules = array_map('esc_sql', array_keys($modules));
		$modules = "'" . implode("', '", $modules) . "'";
		
		return $modules;
	}
	
	/**
	 * Sort a list of modules into categories. Inside each category, modules are sorted by priority (descending).
	 * 
	 * @access private
	 * 
	 * @param array $modules
	 * @return array
	 */
	function sort_into_categories($modules){
		$categories = array();
		
		foreach($modules as $module_id => $module_data){
			$cat = $module_data['ModuleCategory'];
			if ( isset($categories[$cat]) ){
				$categories[$cat][$module_id] = $module_data;
			} else {
				$categories[$cat] = array($module_id => $module_data);
			} 
		}
		
		foreach($categories as $cat => $cat_modules){
			uasort($categories[$cat], array(&$this, 'compare_priorities'));
		}
		
		return $categories;
	}
	
  /**
   * Callback for sorting modules by priority.
   *
   * @access private
   *
   * @param array $a First module header.
   * @param array $b Second module header.
   * @return int
   */
	function compare_priorities($a, $b){
		return $b['ModulePriority'] - $a['ModulePriority'];
	}
	
	/**
	 * Retrieve a reference to an active module.
	 * 
	 * Each module is instantiated only once, so if the module was already loaded you'll get back
	 * a reference to the existing module object. If the module isn't loaded or instantiated yet,
	 * the function will do it automatically (but only for active modules).
	 * 
	 * @param string $module_id Module ID.
	 * @param bool $autoload Optional. Whether to load the module file if it's not loaded yet. Defaults to TRUE.
	 * @param string $category Optional. Return the module only if it's in a specific category. Categories are ignored by default.
	 * @return blcModule A reference to a module object, or NULL on error. 
	 */
	function get_module($module_id, $autoload = true, $category=''){
        $no_result = null;
		if ( !is_string($module_id) ){
			//$backtrace = debug_backtrace();
			//FB::error($backtrace, "get_module called with a non-string argument");
			return $no_result;
		}
		
		if ( empty($this->loaded[$module_id]) ){
			if ( $autoload && $this->is_active($module_id) ){
				if ( !$this->load_module($module_id) ){
					return $no_result;
				}
			} else {
				return $no_result;
			}
		}
		
		if ( !empty($category) ){
			$data = $this->get_module_data($module_id);
			if ( $data['ModuleCategory'] != $category ){
				return $no_result;
			}
		}
		
		$module = $this->init_module($module_id);
		return $module;
	}
	
	/**
	 * Retrieve the header data of a specific module.
	 * Uses cached module info if available.
	 * 
	 * @param string $module_id
	 * @param bool $use_active_cache Check the active module cache before the general one. Defaults to true.
	 * @return array Associative array of module data, or FALSE if the specified module was not found.
	 */
	function get_module_data($module_id, $use_active_cache = true){
		//Check virtual modules
		if ( isset($this->_virtual_modules[$module_id]) ){
			return $this->_virtual_modules[$module_id];
		}
		
		//Check active modules
		if ( $use_active_cache && isset($this->plugin_conf->options['active_modules'][$module_id]) ){
			return $this->plugin_conf->options['active_modules'][$module_id];
		}
		
		//Otherwise, use the general module cache
		if ( !isset($this->_module_cache) ){
			$this->get_modules(); //Populates the cache
		}
		
		if ( isset($this->_module_cache[$module_id]) ){
			return $this->_module_cache[$module_id];
		} else {
			return false;
		}
	}
	
	/**
	 * Retrieve a list of active modules.
	 * 
	 * The list of active modules is stored in the 'active_modules' key of the
	 * plugin configuration object. If this key is not set, this function will 
	 * create it and populate it using the list of default active modules passed
	 * to the module manager's constructor.
	 * 
	 * @return array Associative array of module data indexed by module ID. 
	 */
	function get_active_modules(){
		if ( isset($this->plugin_conf->options['active_modules']) ){
			return $this->plugin_conf->options['active_modules'];
		}
		
		$active = array();
		$modules = $this->get_modules();
		
		if ( is_array($this->default_active_modules) ){
			foreach($this->default_active_modules as $module_id){
				if ( array_key_exists($module_id, $modules) ){
					$active[$module_id] = $modules[$module_id];
				}
			}
		}
		
		$this->plugin_conf->options['active_modules'] = $active;
		$this->plugin_conf->save_options();
		
		return $this->plugin_conf->options['active_modules']; 
	}
	
	/**
	 * Determine if module is active.
	 * 
	 * @param string $module_id
	 * @return bool
	 */
	function is_active($module_id){
		return array_key_exists($module_id, $this->get_active_modules());
	}
	
	/**
	 * Activate a module.
	 * Does nothing if the module is already active.
	 * 
	 * @param string $module_id
	 * @return bool True if module was activated successfully, false otherwise.
	 */
	function activate($module_id){
		if ( $this->is_active($module_id) ){
			return true;
		}
		
		//Retrieve the module header
		$module_data = $this->get_module_data($module_id, false);
		if ( !$module_data ){
			return false;
		}
		
		//Attempt to load the module
		if ( $this->load_module($module_id, $module_data) ){
			//Okay, if it loads, we can assume it works.
			$this->plugin_conf->options['active_modules'][$module_id] = $module_data;
			$this->plugin_conf->save_options();
			//Invalidate the per-category active module cache
			$this->_category_cache_active = null; 
			
			//Notify the module that it's been activated
			$module = $this->get_module($module_id);
			if ( $module ){
				$module->activated();
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Deactivate a module.
	 * Does nothing if the module is already inactive.
	 * 
	 * @param string $module_id
	 * @return bool
	 */
	function deactivate($module_id){
		if ( !$this->is_active($module_id) ){
			return true;
		}
		
		//Some modules are supposed to be always active and thus can't be deactivated
		$module_data = $this->get_module_data($module_id, false);
		if ( isset($module_data['ModuleAlwaysActive']) && $module_data['ModuleAlwaysActive'] ){
			return false;
		}
		
		//Notify the module that it's being deactivated
		$module = $this->get_module($module_id);
		if ( $module ){
			$module->deactivated();
		}
		
		unset($this->plugin_conf->options['active_modules'][$module_id]);
		
		//Keep track of when each module was last deactivated. Used for parser resynchronization.
		if ( isset($this->plugin_conf->options['module_deactivated_when']) ){
			$this->plugin_conf->options['module_deactivated_when'][$module_id] = current_time('timestamp');
		} else {
			$this->plugin_conf->options['module_deactivated_when'] = array(
				$module_id => current_time('timestamp'),
			);
		}
		$this->plugin_conf->save_options();
		
		$this->_category_cache_active = null; //Invalidate the by-category cache since we just changed something		
		return true;
	}
	
	/**
	 * Determine when a module was last deactivated.
	 * 
	 * @param string $module_id Module ID.
	 * @return int Timestamp of last deactivation, or 0 if the module has never been deactivated.
	 */
	function get_last_deactivation_time($module_id){
		if ( isset($this->plugin_conf->options['module_deactivated_when'][$module_id]) ){
			return $this->plugin_conf->options['module_deactivated_when'][$module_id];
		} else {
			return 0;
		}
	}
	
	/**
	 * Set the current list of active modules. If any of the modules are not currently active,
	 * they will be activated. Any currently active modules that are not on the new list will
	 * be deactivated.
	 * 
	 * @param array $ids An array of module IDs.
	 * @return void
	 */
	function set_active_modules($ids){
		$current_active = array_keys($this->get_active_modules());
		
		$activate = array_diff($ids, $current_active);
		$deactivate = array_diff($current_active, $ids);
		
		//Deactivate any modules not present in the new list
		foreach($deactivate as $module_id){
			$this->deactivate($module_id);
		}
		
		//Activate modules present in the new list but not in the old list
		foreach($activate as $module_id){
			$this->activate($module_id);
		}
		
		//Ensure all active modules have the latest headers
		$this->refresh_active_module_cache();
		
		//Invalidate the per-category active module cache
		$this->_category_cache_active = null;
	}
	
	/**
	 * Send the activation message to all currently active plugins when the plugin is activated.
	 * 
	 * @return void
	 */
	function plugin_activated(){
		global $blclog;
		
		//Ensure all active modules have the latest headers
		$blclog->log('... Updating module cache');
		$start = microtime(true);
		$this->refresh_active_module_cache();
		$blclog->info(sprintf('... Cache refresh took %.3f seconds', microtime(true) - $start));
		
		//Notify them that we've been activated
		$blclog->log('... Loading modules');
		$start = microtime(true);
		$this->load_modules();
		$blclog->info(sprintf('... %d modules loaded in %.3f seconds', count($this->loaded), microtime(true) - $start));

		$active = $this->get_active_modules();
		foreach($active as $module_id => $module_data){
			$blclog->log( sprintf('... Notifying module "%s"', $module_id) );
			$module = $this->get_module($module_id);
			if ( $module ){
				$module->plugin_activated();
			} else {
				$blclog->warn(sprintf('... Module "%s" failed to load!', $module_id));
			}
		}
	}
	
	/**
	 * Refresh the cached data of all active modules. 
	 * 
	 * @return array An updated list of active modules.
	 */
	function refresh_active_module_cache(){
		$modules = $this->get_modules();
		foreach($this->plugin_conf->options['active_modules'] as $module_id => $module_header){
			if ( array_key_exists($module_id, $modules) ){
				$this->plugin_conf->options['active_modules'][$module_id] = $modules[$module_id];
			}
		}
		$this->plugin_conf->save_options();
		$this->_category_cache_active = null; //Invalidate the by-category active module cache
		return $this->plugin_conf->options['active_modules'];
	}
	
	/**
	 * Load active modules.
	 * 
	 * @param string $context Optional. If specified, only the modules that match this context (or the "all" context) will be loaded.
	 * @return void
	 */
	function load_modules($context = ''){
		$active = $this->get_active_modules();
		//Avoid trying to load a virtual module before the module that registered it has been loaded.
		$active = $this->put_virtual_last($active);  
		
		foreach($active as $module_id => $module_data){
			//Load the module
			$should_load = ($module_data['ModuleContext'] == 'all') || (!empty($context) && $module_data['ModuleContext'] == $context);
   			if ( $should_load ){
   				$this->load_module($module_id, $module_data);
   			}
		} 
	}
	
	/**
	 * Load and possibly instantiate a specific module.
	 * 
	 * @access private
	 * 
	 * @param string $module_id
	 * @param array $module_data
	 * @return bool True if the module was successfully loaded, false otherwise.
	 */
	function load_module($module_id, $module_data = null){
		
		//Only load each module once.
		if ( !empty($this->loaded[$module_id]) ){
			return true;
		}
		
		if ( !isset($module_data) ){
			$module_data = $this->get_module_data($module_id);
			if ( empty($module_data) ){
				return false;
			}
		}
		
		//Load a normal module
		if ( empty($module_data['virtual']) ){
			
			//Skip invalid and missing modules
			if ( empty($module_data['file']) ){
				return false;
			}
			
			//Get the full path to the module file
			$filename = $this->module_dir . '/' . $module_data['file'];
			if ( !file_exists($filename) ){
				return false;
			}
			
			//Load it
			include $filename;
			$this->loaded[$module_id] = true;
			
		} else {
			
			//Virtual modules don't need to be explicitly loaded, but they must
			//be registered.
			if ( !array_key_exists($module_id, $this->_virtual_modules) ) {
				return false;
			}
			$this->loaded[$module_id] = true;
			
		}
		
		//Instantiate the main module class unless lazy init is on (default is off)
		if ( !array_key_exists($module_id, $this->instances) ){ //Only try to instantiate once
			if ( !$module_data['ModuleLazyInit'] ){
				$this->init_module($module_id, $module_data);
			}
		}
		
		return true;
	}
	
	/**
	 * Instantiate a certain module.
	 * 
	 * @param string $module_id
	 * @param array $module_data Optional. The header data of the module that needs to be instantiated. If not specified, it will be retrieved automatically.  
	 * @return object The newly instantiated module object (extends blcModule), or NULL on error.
	 */
	function init_module($module_id, $module_data = null){
		//Each module is only instantiated once.
		if ( isset($this->instances[$module_id]) ){
			return $this->instances[$module_id];
		}
		
		if ( !isset($module_data) ){
			$module_data = $this->get_module_data($module_id);
			if ( empty($module_data) ){
				return null;
			}
		}
		
		if ( !empty($module_data['ModuleClassName']) && class_exists($module_data['ModuleClassName']) ){
			$className = $module_data['ModuleClassName'];
			$this->instances[$module_id] = new $className(
			   	$module_id, 
				$module_data,
				$this->plugin_conf,
				$this
			);
			return $this->instances[$module_id]; 
		};
		
		return null;		
	}
	
	function is_instantiated($module_id){
		return !empty($this->instances[$module_id]);
	}
	
	/**
	 * Register a virtual module. 
	 * 
	 * Virtual modules are the same as normal modules, except that they can be added
	 * on the fly, e.g. by other modules.
	 * 
	 * @param string $module_id Module Id. 
	 * @param string $module_data Associative array of module data. All module header fields are allowed, except ModuleID.
	 * @return void
	 */
	function register_virtual_module($module_id, $module_data){
		$module_data = $this->normalize_module_header($module_data, $module_id);
		$module_data['virtual'] = true;
		$this->_virtual_modules[$module_id] = $module_data;
	}
	
	/**
	 * Sort an array of modules so that all virtual modules are placed at its end.
	 * 
	 * @param array $modules Input array, [module_id => module_data, ...].
	 * @return array Sorted array.
	 */
	function put_virtual_last($modules){
		uasort($modules, array(&$this, 'compare_virtual_flags'));
		return $modules;
	}
	
	/**
	 * Callback function for sorting modules by the state of their 'virtual' flag.
	 * 
	 * @param array $a Associative array of module A data
	 * @param array $b Associative array of module B data
	 * @return int 
	 */
	function compare_virtual_flags($a, $b){
		if ( empty($a['virtual']) ){
			return empty($b['virtual'])?0:-1;
		} else {
			return empty($b['virtual'])?1:0;
		}
	}
	
	/**
	 * Validate active modules.
	 * 
	 * Validates all active modules, deactivates invalid ones and returns
	 * an array of deactivated modules.
	 * 
	 * @return array
	 */
	function validate_active_modules(){
		$active = $this->get_active_modules();
		if ( empty($active) ){
			return array();
		}
		
		$invalid = array();
		foreach($active as $module_id => $module_data){
			$rez = $this->validate_module($module_data);
			if ( is_wp_error($rez) ){
				$invalid[$module_id] = $rez;
				$this->deactivate($module_id);
			}
		}
		
		return $invalid;
	}
	
	/**
	 * Validate module data.
	 * 
	 * Checks that the module file exists or that the module 
	 * is a currently registered virtual module.
	 * 
	 * @param array $module_data Associative array of module data.
	 * @return bool|WP_Error True on success, an error object if the module fails validation
	 */
	function validate_module($module_data){
		if ( empty($module_data['ModuleID']) ){
			return new WP_Error('invalid_cached_header', 'The cached module header is invalid');
		}
		
		if ( empty($module_data['virtual']) ){
			//Normal modules must have a valid filename
			if ( empty($module_data['file']) ){
				return new WP_Error('module_not_found', 'Invalid module file');
			}
			
			$filename = $this->module_dir . '/' . $module_data['file'];
			if ( !file_exists($filename) ){
				return new WP_Error('module_not_found', 'Module file not found');
			}
			
			//The module file header must be in the proper format. While $module_data comes
			//from cache and can be assumed to be correct, get_modules() will attempt to load 
			//the current headers and only return modules with semi-valid headers.
			$installed = $this->get_modules();
			if ( !array_key_exists($module_data['ModuleID'], $installed) ){
				return new WP_Error('invalid_module_header', 'Invalid module header');
			}
		} else {
			//Virtual modules need to be currently registered
			if ( !array_key_exists($module_data['ModuleID'], $this->_virtual_modules) ){
				return new WP_Error('module_not_registered', 'The virtual module is not registered');
			}
		}
		
		return true;
	}
	
	/**
	 * Add BLC-module specific headers to the list of allowed plugin headers. This
	 * lets us use get_plugins() to retrieve the list of BLC modules.
	 * 
	 * @param array $headers Currently known plugin headers.
	 * @return array New plugin headers.
	 */
	function inject_module_headers($headers){
		$module_headers = array(
			'ModuleID',
			'ModuleCategory', 
			'ModuleContext',
			'ModuleLazyInit',
			'ModuleClassName',
			'ModulePriority',
			'ModuleCheckerUrlPattern',
			'ModuleHidden',            //Don't show the module in the Settings page
			'ModuleAlwaysActive',      //Module can't be deactivated.
			'ModuleRequiresPro',       //Can only be activated in the Pro version
		);
		
		return array_merge($headers, $module_headers);
	}
	
	/**
	 * Normalize a module header, using defaults where necessary.
	 * 
	 * @param array $module_header Module header, as read from the module's .php file.
	 * @param string $module_id Module ID. 
	 * @param string $module_filename Module filename. Optional.
	 * @return array Normalized module header.
	 */
	function normalize_module_header($module_header, $module_id, $module_filename = ''){
		//Default values for optional module header fields
		$defaults = array(
			'ModuleContext' => 'all',
			'ModuleCategory' => 'other',
			'ModuleLazyInit' => 'false',
			'ModulePriority' => '0',
			'ModuleHidden' => 'false',
			'ModuleAlwaysActive' => 'false',
			'ModuleRequiresPro' => 'false',
			'TextDomain' => 'broken-link-checker', //For translating module headers
		);
		
		$module_header['ModuleID'] = $module_id;   //Just for consistency
		$module_header['file'] = $module_filename; //Used later to load the module
			
		//Apply defaults
		foreach($defaults as $field => $default_value){
			if ( empty($module_header[$field]) ){
				$module_header[$field] = $default_value;
			}
		}
		
		//Convert bool/int fields from strings to native datatypes
		$module_header['ModuleLazyInit'] = $this->str_to_bool($module_header['ModuleLazyInit']);
		$module_header['ModuleHidden'] = $this->str_to_bool($module_header['ModuleHidden']);
		$module_header['ModuleAlwaysActive'] = $this->str_to_bool($module_header['ModuleAlwaysActive']);
		$module_header['ModuleRequiresPro'] = $this->str_to_bool($module_header['ModuleRequiresPro']);
		$module_header['ModulePriority'] = intval($module_header['ModulePriority']);
			
		return $module_header;			
	}
	
	/**
	 * Converts the strings "true" and "false" to boolean TRUE and FALSE, respectively.
	 * Any other string will yield FALSE.
	 *  
	 * @param string $value "true" or "false", case-insensitive.
	 * @return bool
	 */
	function str_to_bool($value){
		$value = trim(strtolower($value));
		return $value == 'true';
	}
	
	/**
	 * Generates a PHP script that calls the __() i18n function with
	 * the name and description of each available module. The generated
	 * script is used to make module headers show up in the .POT file.
	 * 
	 * @access private
	 * 
	 * @return string
	 */
	function _build_header_translation_code(){
		$this->_module_cache = null; //Clear the cache
		$modules = $this->get_modules();
		
		$strings = array();
		foreach($modules as $module_id => $module_header){
			if ( $module_header['ModuleHidden'] || ($module_id == 'write-module-placeholders')) {
				continue;
			}
			if ( !empty($module_header['Name']) ){
				$strings[] = sprintf(
					'_x("%s", "module name", "broken-link-checker");',
					str_replace('"', '\"', $module_header['Name'])
				);
			}
		}
		
		return "<?php\n" . implode("\n", $strings) . "\n";
	}
}
