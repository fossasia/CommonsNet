<?php

if ( !class_exists('wsScreenOptions12') ):

/**
 * Class for adding new panels to the "Screen Options" box.
 * 
 * Do not access this class directly. Instead, use the add_screen_options_panel() function. 
 * 
 * @author Janis Elsts
 * @copyright 2014
 * @version 1.3
 * @access public
 */
class wsScreenOptions13 {
	var $registered_panels; //List of custom "Screen Options" panels
	var $page_panels;       //Index of panels registered for each page ($page => array of panel ids).
	
	/**
	 * Class constructor
	 * 
	 * @return void
	 */
	function init(){
		$this->registered_panels = array();
		$this->page_panels = array();

		add_action('current_screen', array($this, 'populate_page_panels'));
		add_filter('screen_settings', array(&$this, 'append_screen_settings'), 10, 2);
		add_action('admin_print_scripts', array(&$this, 'add_autosave_script'));
	}

	/**
	 * Add a new settings panel to the "Screen Options" box.
	 * 
	 * @param string $id String to use in the 'id' attribute of the settings panel. Should be unique.
	 * @param string $title Title of the settings panel. Set to an empty string to omit title. 
	 * @param callback $callback Function that fills the panel with the desired content. Should return its output.
	 * @param string|array $page The page(s) on which to show the panel (similar to add_meta_box()).
	 * @param callback $save_callback Optional. Function that saves the settings.
	 * @param bool $autosave Optional. If set, settings will be automatically saved (via AJAX) when the value of any input element in the panel changes. Defaults to false.
	 * @return void
	 */
	function add_screen_options_panel($id, $title, $callback, $page, $save_callback = null, $autosave = false){
		if ( !is_array($page) ){
			$page = array($page);
		}

		$new_panel = array(
			'title' => $title,
			'callback' => $callback,
			'page' => $page,
			'save_callback' => $save_callback,
			'autosave' => $autosave,
		);
		$this->registered_panels[$id] = $new_panel;

		if ( $save_callback ){
			add_action('wp_ajax_save_settings-' . $id, array($this, 'ajax_save_callback'));
		}
	}

	/**
	 * Populate a lookup array for screen -> panels queries.
	 *
	 * This is a callback for the "current_screen" action. We have to do it in this hook or WordPress will
	 * complain about "doing it wrong" and incorrectly suggest using the "add_meta_boxes" action.
	 *
	 * "add_meta_boxes" doesn't work here because it only gets called on CPT pages and we want the ability
	 * to add screen options to any page.
	 */
	function populate_page_panels() {
		foreach($this->registered_panels as $id => $panel) {
			$page = $panel['page'];

			//Convert page hooks/slugs to screen IDs
			$page = array_map(array($this, 'page_to_screen_id'), $page);
			$page = array_unique($page);

			//Store the panel ID in each relevant page's list
			foreach($page as $page_id){
				if ( !isset($this->page_panels[$page_id]) ){
					$this->page_panels[$page_id] = array();
				}
				$this->page_panels[$page_id][] = $id;
			}
		}
	}
	
	/**
	 * Convert a page hook name to a screen ID.
	 * 
	 * @uses convert_to_screen()
	 * @access private
	 * 
	 * @param string $page
	 * @return string
	 */
	function page_to_screen_id($page){
		if ( function_exists('convert_to_screen') ){
			$screen = convert_to_screen($page);
			if ( isset($screen->id) ){
				return $screen->id;
			} else {
				return '';
			}
		} else {
			return str_replace( array('.php', '-new', '-add' ), '', $page);
		}
	}
	
	/**
	 * Append custom panel HTML to the "Screen Options" box of the current page.
	 * Callback for the 'screen_settings' filter (available in WP 3.0 and up).
	 * 
	 * @access private
	 * 
	 * @param string $current 
	 * @param string $screen Screen object (undocumented).
	 * @return string The HTML code to append to "Screen Options"
	 */
	function append_screen_settings($current, $screen){
		global $hook_suffix;
		
		//Sanity check
		if ( !isset($screen->id) ) {
			return $current;
		}
		
		//Are there any panels that want to appear on this page? 
		$panels = $this->get_panels_for_screen($screen->id, $hook_suffix);
		if ( empty($panels) ){
			return $current;
		}
		
		//Append all panels registered for this screen
		foreach($panels as $panel_id){
			$panel = $this->registered_panels[$panel_id];
			
			//Add panel title
			if ( !empty($panel['title']) ){
				$current .= "\n<h5>".$panel['title']."</h5>\n";
			}
			//Generate panel contents
			if ( is_callable($panel['callback']) ){
				$contents = call_user_func($panel['callback']);
				$classes = array(
					'custom-options-panel',
				);
				if ( $panel['autosave'] ){
					$classes[] = 'requires-autosave';
				}
				
				$contents = sprintf(
					'<div id="%s" class="%s"><input type="hidden" name="_wpnonce-%s" value="%s" />%s</div>',
					esc_attr($panel_id),
					implode(' ',$classes),
					esc_attr($panel_id),
					wp_create_nonce('save_settings-'.$panel_id),
					$contents						
				);
				
				$current .= $contents;
			}
		}		
		
		return $current;
	}
	
	/**
	 * AJAX callback for the "Screen Options" autosave.
	 * 
	 * @access private 
	 * @return void
	 */
	function ajax_save_callback(){
		if ( empty($_POST['action']) ){
			die('0');
		}
		
		//The 'action' argument is in the form "save_settings-panel_id"
		$id = end(explode('-', $_POST['action'], 2));
		
		//Basic security check.
		check_ajax_referer('save_settings-' . $id, '_wpnonce-' . $id);
		
		//Hand the request to the registered callback, if any
		if ( !isset($this->registered_panels[$id]) ){
			exit('0');
		}
		$panel = $this->registered_panels[$id];
		if ( is_callable($panel['save_callback']) ){
			call_user_func($panel['save_callback'], $_POST);
			die('1');
		} else {
			die('0');
		}
	}
	
	/**
	 * Add/enqueue supporting JavaScript for the autosave function of custom "Screen Options" panels.
	 * 
	 * Checks if the current page is supposed to contain any autosave-enabled
	 * panels and adds the script only if that's the case.   
	 * 
	 * @return void
	 */
	function add_autosave_script(){
		//Get the page id/hook/slug/whatever.
		global $hook_suffix;
		
		//Check if we have some panels with autosave registered for this page.
		$panels = $this->get_panels_for_screen('', $hook_suffix);
		if ( empty($panels) ){
			return;
		}
		
		$got_autosave = false;
		foreach($panels as $panel_id){
			if ( $this->registered_panels[$panel_id]['autosave'] ){
				$got_autosave = true;
				break;
			}
		}
	
		if ( $got_autosave ){
			//Enqueue the script itself
			$url = plugins_url('screen-options.js', __FILE__);
			wp_enqueue_script('screen-options-custom-autosave', $url, array('jquery'));
		}
	}
	
	/**
	 * Get custom panels registered for a particular screen and/or page. 
	 * 
	 * @param string $screen_id Screen ID.
	 * @param string $page Optional. Page filename or hook name.
	 * @return array Array of custom panels.
	 */
	function get_panels_for_screen($screen_id, $page = ''){
		if ( isset($this->page_panels[$screen_id]) && !empty($this->page_panels[$screen_id]) ){
			$panels = $this->page_panels[$screen_id];
		} else {
			$panels = array();
		}
		if ( !empty($page) ){
			$page_as_screen = $this->page_to_screen_id($page);
			if ( isset($this->page_panels[$page_as_screen]) && !empty($this->page_panels[$page_as_screen]) ){
				$panels = array_merge($panels, $this->page_panels[$page_as_screen]);
			}
		}		
		return array_unique($panels);
	}
}

//All versions of the class are stored in a global array 
//and only the latest version is actually used. 
global $ws_screen_options_versions;
if ( !isset($ws_screen_options_versions) ){
	$ws_screen_options_versions = array();
}
$ws_screen_options_versions['1.3'] = 'wsScreenOptions13';

endif;

if ( !function_exists('add_screen_options_panel') ){
	
	/**
	 * Add a new settings panel to the "Screen Options" box.
	 * 
	 * @see wsScreenOptions10::add_screen_options_panel()
	 * 
	 * @param string $id String to use in the 'id' attribute of the settings panel. Should be unique.
	 * @param string $title Title of the settings panel. Set to an empty string to omit title. 
	 * @param callback $callback Function that fills the panel with the desired content. Should return its output.
	 * @param string|array $page The page(s) on which to show the panel (similar to add_meta_box()).
	 * @param callback $save_callback Optional. Function that saves the settings contained in the panel.
	 * @param bool $autosave Optional. If set, settings will be automatically saved (via AJAX) when the value of any input element in the panel changes. Defaults to false.
	 * @return void
	 */
	function add_screen_options_panel($id, $title, $callback, $page, $save_callback = null, $autosave = false){
		global $ws_screen_options_versions;
		
		static $instance = null; /** @var wsScreenOptions13 $instance */
		if ( is_null($instance) ){
			//Instantiate the latest version of the wsScreenOptions class
			uksort($ws_screen_options_versions, 'version_compare');
			$className = end($ws_screen_options_versions);
			$instance = new $className;
			$instance->init();
		}
		
		$instance->add_screen_options_panel($id, $title, $callback, $page, $save_callback, $autosave);
	}
	
}    

