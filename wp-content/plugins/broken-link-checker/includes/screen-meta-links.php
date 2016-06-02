<?php

/**
 * @author Janis Elsts
 * @copyright 2011
 */


if ( !class_exists('wsScreenMetaLinks11') ):

//Load JSON functions for PHP < 5.2
if ( !(function_exists('json_encode') && function_exists('json_decode')) && !(class_exists('Services_JSON') || class_exists('Moxiecode_JSON')) ){
	$class_json_path = ABSPATH.WPINC.'/class-json.php';
	$class_moxiecode_json_path = ABSPATH.WPINC.'/js/tinymce/plugins/spellchecker/classes/utils/JSON.php';
	if ( file_exists($class_json_path) ){
		require $class_json_path;
		
	} elseif ( file_exists($class_moxiecode_json_path) ) {
		require $class_moxiecode_json_path;
	}
}
 
class wsScreenMetaLinks11 {
	var $registered_links; //List of meta links registered for each page. 
	
	/**
	 * Class constructor.
	 * 
	 * @return void
	 */
	function __construct(){
		$this->registered_links = array();
		
		add_action('admin_notices', array(&$this, 'append_meta_links'));
		add_action('admin_print_styles', array(&$this, 'add_link_styles'));
	}
	
	/**
	 * Add a new link to the screen meta area.
	 * 
	 * Do not call this method directly. Instead, use the global add_screen_meta_link() function.
	 * 
	 * @param string $id Link ID. Should be unique and a valid value for a HTML ID attribute.
	 * @param string $text Link text.
	 * @param string $href Link URL.
	 * @param string|array $page The page(s) where you want to add the link.
	 * @param array $attributes Optional. Additional attributes for the link tag.
	 * @return void
	 */
	function add_screen_meta_link($id, $text, $href, $page, $attributes = null){
		if ( !is_array($page) ){
			$page = array($page);
		}
		if ( is_null($attributes) ){
			$attributes = array();
		}
		
		//Basically a list of props for a jQuery() call
		$link = compact('id', 'text', 'href');
		$link = array_merge($link, $attributes);

		//Add the CSS classes that will make the look like a proper meta link
		if ( empty($link['class']) ){
			$link['class'] = '';
		}
		$link['class'] = 'show-settings custom-screen-meta-link ' . $link['class'];
		
		//Save the link in each relevant page's list
		foreach($page as $page_id){
			if ( !isset($this->registered_links[$page_id]) ){
				$this->registered_links[$page_id] = array();
			}
			$this->registered_links[$page_id][] = $link;
		}
	}
	
	/**
	 * Output the JS that appends the custom meta links to the page.
	 * Callback for the 'admin_notices' action.
	 * 
	 * @access private
	 * @return void
	 */
	function append_meta_links(){
		global $hook_suffix;
		
		//Find links registered for this page
		$links = $this->get_links_for_page($hook_suffix);
		if ( empty($links) ){
			return;
		}
		
		?>
		<script type="text/javascript">
			(function($, links){
				var container = $('#screen-meta-links');
				if ( container.length == 0 ) {
					container = $('<div />').attr('id', 'screen-meta-links').insertAfter('#screen-meta');
				}
				for(var i = 0; i < links.length; i++){
					container.append(
						$('<div/>')
							.attr({
								'id' : links[i].id + '-wrap',
								'class' : 'hide-if-no-js custom-screen-meta-link-wrap'
							})
							.append( $('<a/>', links[i]) )
					);
				}
			})(jQuery, <?php echo $this->json_encode($links); ?>);
		</script>
		<?php
	}
	
	/**
	 * Get a list of custom screen meta links registered for a specific page.
	 * 
	 * @param string $page
	 * @return array
	 */
	function get_links_for_page($page){
		$links = array();
		
		if ( isset($this->registered_links[$page]) ){
			$links = array_merge($links, $this->registered_links[$page]);
		}
		$page_as_screen = $this->page_to_screen_id($page);
		if ( ($page_as_screen != $page) && isset($this->registered_links[$page_as_screen]) ){
			$links = array_merge($links, $this->registered_links[$page_as_screen]);
		}
		
		return $links;
	}
	
	/**
	 * Output the CSS code for custom screen meta links. Required because WP only
	 * has styles for specific meta links (by #id), not meta links in general.
	 * 
	 * Callback for 'admin_print_styles'.
	 * 
	 * @access private 
	 * @return void
	 */
	function add_link_styles(){
		global $hook_suffix;
		//Don't output the CSS if there are no custom meta links for this page.
		$links = $this->get_links_for_page($hook_suffix);
		if ( empty($links) ){
			return;
		}
		
		if ( !isset($GLOBALS['wp_version']) || version_compare($GLOBALS['wp_version'], '3.8-RC1', '<') ) {
			$this->print_old_link_styles();
		} else {
			$this->print_link_styles();
		}
	}

	/**
	 * Print screen meta button styles (WP 3.8+).
	 */
	private function print_link_styles() {
		?>
		<style type="text/css">
			.custom-screen-meta-link-wrap {
				float: right;
				height: 28px;
				margin: 0 0 0 6px;

				border: 1px solid #ddd;
				border-top: none;
				background: #fff;
				-webkit-box-shadow: 0 1px 1px -1px rgba(0,0,0,0.1);
				box-shadow:         0 1px 1px -1px rgba(0,0,0,0.1);
			}

			#screen-meta .custom-screen-meta-link-wrap a.custom-screen-meta-link,
			#screen-meta-links .custom-screen-meta-link-wrap a.custom-screen-meta-link
			{
				padding: 3px 16px 3px 16px;
			}

			#screen-meta-links a.custom-screen-meta-link::after {
				display: none;
			}
			</style>
		<?php
	}

	/**
	 * Print old screen meta button styles (WP 3.7.x and older).
	 */
	private function print_old_link_styles() {
		?>
		<style type="text/css">
			.custom-screen-meta-link-wrap {
				float: right;
				height: 22px;
				padding: 0;
				margin: 0 0 0 6px;
				font-family: sans-serif;
				-moz-border-radius-bottomleft: 3px;
				-moz-border-radius-bottomright: 3px;
				-webkit-border-bottom-left-radius: 3px;
				-webkit-border-bottom-right-radius: 3px;
				border-bottom-left-radius: 3px;
				border-bottom-right-radius: 3px;

				background: #e3e3e3;

				border-right: 1px solid transparent;
				border-left: 1px solid transparent;
				border-bottom: 1px solid transparent;
				background-image: -ms-linear-gradient(bottom, #dfdfdf, #f1f1f1); /* IE10 */
				background-image: -moz-linear-gradient(bottom, #dfdfdf, #f1f1f1); /* Firefox */
				background-image: -o-linear-gradient(bottom, #dfdfdf, #f1f1f1); /* Opera */
				background-image: -webkit-gradient(linear, left bottom, left top, from(#dfdfdf), to(#f1f1f1)); /* old Webkit */
				background-image: -webkit-linear-gradient(bottom, #dfdfdf, #f1f1f1); /* new Webkit */
				background-image: linear-gradient(bottom, #dfdfdf, #f1f1f1); /* proposed W3C Markup */
			}

			#screen-meta .custom-screen-meta-link-wrap a.custom-screen-meta-link,
			#screen-meta-links .custom-screen-meta-link-wrap a.custom-screen-meta-link
			{
				background-image: none;
				padding-right: 6px;
				color: #777;
			}
		</style>
	<?php
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
	 * Back-wards compatible json_encode(). Used to encode link data before
	 * passing it to the JavaScript that actually creates the links.
	 * 
	 * @param mixed $data
	 * @return string
	 */
	function json_encode($data){
		if ( function_exists('json_encode') ){
    		return json_encode($data);
    	}
    	if ( class_exists('Services_JSON') ){
    		$json = new Services_JSON();
        	return( $json->encodeUnsafe($data) );
    	} elseif ( class_exists('Moxiecode_JSON') ){
    		$json = new Moxiecode_JSON();
    		return $json->encode($data);
    	} else {
    		trigger_error('No JSON parser available', E_USER_ERROR);
		    return null;
   		}
	}
	
}

global $ws_screen_meta_links_versions;
if ( !isset($ws_screen_meta_links_versions) ){
	$ws_screen_meta_links_versions = array();
} 
$ws_screen_meta_links_versions['1.1'] = 'wsScreenMetaLinks11';

endif;

/**
 * Add a new link to the screen meta area.
 * 
 * @param string $id Link ID. Should be unique and a valid value for a HTML ID attribute.
 * @param string $text Link text.
 * @param string $href Link URL.
 * @param string|array $page The page(s) where you want to add the link.
 * @param array $attributes Optional. Additional attributes for the link tag.
 * @return void
 */
function add_screen_meta_link($id, $text, $href, $page, $attributes = null){
	global $ws_screen_meta_links_versions;
		
	static $instance = null;
	if ( is_null($instance) ){
		//Instantiate the latest version of the wsScreenMetaLinks class
		uksort($ws_screen_meta_links_versions, 'version_compare');
		$className = end($ws_screen_meta_links_versions);
		$instance = new $className;
	}
	
	$instance->add_screen_meta_link($id, $text, $href, $page, $attributes);
}

?>
