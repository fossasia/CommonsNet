<?php

/**
 * A base class for parsers.
 *
 * In the context of this plugin, a "parser" is a class that knows how to extract or modify
 * a specific type of links from a given piece of text. For example, there could be a "HTML Link"
 * parser that knows how to find and modify standard HTML links such as this one : 
 * <a href="http://example.com/">Example</a>
 * 
 * Other parsers could extract plaintext URLs or handle metadata fields.
 *
 * Each parser has a list of supported formats (e.g. "html", "plaintext", etc) and container types
 * (e.g. "post", "comment", "blogroll", etc). When something needs to be parsed, the involved
 * container class will look up the parsers that support the relevant format or the container's type,
 * and apply them to the to-be-parsed string.
 *
 * All sub-classes of blcParser should override at least the blcParser::parse() method.
 *
 * @see blcContainer::$fields
 *
 * @package Broken Link Checker
 * @access public
 */
class blcParser extends blcModule {
	
	var $parser_type;
	var $supported_formats = array();
	var $supported_containers = array();
	
	/**
	 * Initialize the parser. Nothing much here.
	 * 
	 * @return void
	 */
	function init(){
		parent::init();
		$this->parser_type = $this->module_id;
	}
	
	/**
	 * Called when the parser is activated.
	 * 
	 * @return void
	 */
	function activated(){
		parent::activated();
		$this->resynch_relevant_containers();
	}

	/**
	 * Called when BLC is activated.
	 */
	function plugin_activated() {
		//Intentionally do nothing. BLC can not parse containers while it's inactive, so we can be
		//pretty sure that there are no already-parsed containers that need to be resynchronized.
	}

	/**
	 * Mark containers that this parser might be interested in as unparsed.
	 * 
	 * @uses blcContainerHelper::mark_as_unsynched_where()
	 * 
	 * @param bool $only_return If true, just return the list of formats and container types without actually modifying any synch. records.  
	 * @return void|array Either nothing or an array in the form [ [format1=>timestamp1, ...], [container_type1=>timestamp1, ...] ]
	 */
	function resynch_relevant_containers($only_return = false){
		global $blclog;
		$blclog->log(sprintf('...... Parser "%s" is marking relevant items as unsynched', $this->module_id));
		
		$last_deactivated = $this->module_manager->get_last_deactivation_time($this->module_id);
		
		$formats = array();
		foreach($this->supported_formats as $format){
			$formats[$format] = $last_deactivated;
		}
		
		$container_types = array();
		foreach($this->supported_containers as $container_type){
			$container_types[$container_type] = $last_deactivated;
		}
		
		if ( $only_return ){
			return array($formats, $container_types);
		} else {
			blcContainerHelper::mark_as_unsynched_where($formats, $container_types);
		}
	}
	
  /**
   * Parse a string for links.
   *
   * @param string $content The text to parse.
   * @param string $base_url The base URL to use for normalizing relative URLs. If ommitted, the blog's root URL will be used. 
   * @param string $default_link_text 
   * @return array An array of new blcLinkInstance objects. The objects will include info about the links found, but not about the corresponding container entity. 
   */
	function parse($content, $base_url = '', $default_link_text = ''){
		return array();
	}
	
  /**
   * Change all links that have a certain URL to a new URL. 
   *
   * @param string $content Look for links in this string.
   * @param string $new_url Change the links to this URL.
   * @param string $old_url The URL to look for.
   * @param string $old_raw_url The raw, not-normalized URL of the links to look for. Optional. 
   *
   * @return array|WP_Error If successful, the return value will be an associative array with two
   * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
   * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
   */
	function edit($content, $new_url, $old_url, $old_raw_url){
		return new WP_Error(
			'not_implemented',
			sprintf(__("Editing is not implemented in the '%s' parser", 'broken-link-checker'), $this->parser_type)
		);
	}
	
  /**
   * Remove all links that have a certain URL, leaving anchor text intact.
   *
   * @param string $content	Look for links in this string.
   * @param string $url The URL to look for.
   * @param string $raw_url The raw, non-normalized version of the URL to look for. Optional.
   * @return string Input string with all matching links removed. 
   */
	function unlink($content, $url, $raw_url){
		return new WP_Error(
			'not_implemented',
			sprintf(__("Unlinking is not implemented in the '%s' parser", 'broken-link-checker'), $this->parser_type)
		);
	}
	
  /**
   * Get the link text for printing in the "Broken Links" table.
   * Sub-classes should override this method and display the link text in a way appropriate for the link type.
   *
   * @param blcLinkInstance $instance
   * @param string $context
   * @return string HTML 
   */
	function ui_get_link_text($instance, $context = 'display'){
		return $instance->link_text;
	}

	/**
	 * Check if the parser supports editing the link text.
	 *
	 * @return bool
	 */
	public function is_link_text_editable() {
		return false;
	}

	/**
	 * Check if the parser supports editing the link URL.
	 *
	 * @return bool
	 */
	public function is_url_editable() {
		return true;
	}
	
  /**
   * Turn a relative URL into an absolute one.
   *
   * WordPress 3.4 has WP_Http::make_absolute_url() which is well-tested but not as comprehensive
   * as this implementation. For example, WP_Http doesn't properly handle directory traversal with "..",
   * and it removes #anchors for no good reason. The BLC implementation deals with both pretty well.
   * 
   * @param string $url Relative URL.
   * @param string $base_url Base URL. If omitted, the blog's root URL will be used.
   * @return string
   */
	function relative2absolute($url, $base_url = ''){
		if ( empty($base_url) ){
			$base_url = home_url();
		}
		
		$p = @parse_url($url);
	    if(!$p) {
	        //URL is a malformed
	        return false;
	    }
	    if( isset($p["scheme"]) ) return $url;
	    
	    //If the relative URL is just a query string or anchor, simply attach it to the absolute URL and return
	    $first_char = substr($url, 0, 1); 
	    if ( ($first_char == '?') || ($first_char == '#') ){
			return $base_url . $url;
		}
	
	    $parts=(parse_url($base_url));

        //Protocol-relative URLs start with "//". We just need to prepend the right protocol.
        if ( substr($url, 0, 2) === '//' ) {
            $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'http';
            return $scheme . ':'. $url;
        }
	    
	    if(substr($url,0,1)=='/') {
	    	//Relative URL starts with a slash => ignore the base path and jump straight to the root. 
	        $path_segments = explode("/", $url);
	        array_shift($path_segments);
	    } else {
	        if(isset($parts['path'])){
	            $aparts=explode('/',$parts['path']);
	            array_pop($aparts);
	            $aparts=array_filter($aparts);
	        } else {
	            $aparts=array();
	        }
	        
	        //Merge together the base path & the relative path
	        $aparts = array_merge($aparts, explode("/", $url));
	        
	        //Filter the merged path 
	        $path_segments = array();
	        foreach($aparts as $part){
	        	if ( $part == '.' ){
					continue; //. = "this directory". It's basically a no-op, so we skip it.
				} elseif ( $part == '..' )  {
					array_pop($path_segments);	//.. = one directory up. Remove the last seen path segment.
				} else {
					array_push($path_segments, $part); //Normal directory -> add it to the path.
				}
			}
	    }
	    $path = implode("/", $path_segments);
	
		//Build the absolute URL.
	    $url = '';
	    if($parts['scheme']) {
	        $url = "$parts[scheme]://";
	    }
	    if(isset($parts['user'])) {
	        $url .= $parts['user'];
	        if(isset($parts['pass'])) {
	            $url .= ":".$parts['pass'];
	        }
	        $url .= "@";
	    }
	    if(isset($parts['host'])) {
	        $url .= $parts['host'];
	        if(isset($parts['port'])) {
		        $url .= ':' . $parts['port'];
		    }
		    $url .= '/';
	    }
	    $url .= $path;
	
	    return $url;
	}
	
  /**
   * Apply a callback function to all links found in a string and return the results.
   *
   * The first argument passed to the callback function will be an associative array
   * of link data. If the optional $extra parameter is set, it will be passed as the 
   * second argument to the callback function.
   *
   * The link data array will contain at least these keys :
   *  'href' - the URL of the link, as-is (i.e. without any sanitization or relative-to-absolute translation).
   *  '#raw' - the raw link code, e.g. the entire '<a href="...">...</a>' tag of a HTML link.
   *
   * Sub-classes may also set additional keys.
   *
   * This method is currently used only internally, so sub-classes are not required
   * to implement it.
   *
   * @param string $content A text string to parse for links. 
   * @param callback $callback Callback function to apply to all found links.  
   * @param mixed $extra If the optional $extra param. is supplied, it will be passed as the second parameter to the function $callback. 
   * @return array An array of all detected links after applying $callback to each of them.
   */
	function map($content, $callback, $extra = null){
		return array(); 
	}
	
  /**
   * Modify all links found in a string using a callback function.
   *
   * The first argument passed to the callback function will be an associative array
   * of link data. If the optional $extra parameter is set, it will be passed as the 
   * second argument to the callback function. See the map() method of this class for
   * details on the first argument.
   * 
   * The callback function should return either an associative array or a string. If 
   * a string is returned, the parser will replace the current link with the contents
   * of that string. If an array is returned, the current link will be modified/rebuilt
   * by substituting the new values for the old ones (e.g. returning array with the key
   * 'href' set to 'http://example.com/' will replace the current link's URL with 
   * http://example.com/).
   *
   * This method is currently only used internally, so sub-classes are not required
   * to implement it.
   *
   * @see blcParser::map()
   *
   * @param string $content A text string containing the links to edit.
   * @param callback $callback Callback function used to modify the links.
   * @param mixed $extra If supplied, $extra will be passed as the second parameter to the function $callback. 
   * @return string The modified input string. 
   */
	function multi_edit($content, $callback, $extra = null){
		return $content; //No-op
	}	
}

/**
 * A helper class for working with parsers. All its methods should be called statically. 
 *  
 * @see blcParser
 * 
 * @package Broken Link Checker
 * @access public
 */
class blcParserHelper {
	
  /**
   * Get the parser matching a parser type ID.
   * 
   * @uses blcModuleManager::get_module()
   *
   * @param string $parser_type
   * @return blcParser|null
   */
	static function get_parser( $parser_type ){
		$manager = blcModuleManager::getInstance();
		return $manager->get_module($parser_type, true, 'parser');
	}
	
  /**
   * Get all parsers that support either the specified format or the container type.
   * If a parser supports both, it will still be included only once.
   *
   * @param string $format
   * @param string $container_type
   * @return blcParser[]
   */
	static function get_parsers( $format, $container_type ){
		$found = array();
		
		//Retrieve a list of active parsers
		$manager = blcModuleManager::getInstance();
		$active_parsers = $manager->get_modules_by_category('parser');
		
		//Try each one
		foreach($active_parsers as $module_id => $module_data){
			$parser = $manager->get_module($module_id); //Will autoload if necessary
			if ( !$parser ){
				continue;
			}
			
			if ( in_array($format, $parser->supported_formats) || in_array($container_type, $parser->supported_containers) ){
				array_push($found, $parser);
			}
		}
		
		return $found;
	}
}

