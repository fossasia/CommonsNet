<?php
/*
Plugin Name: URL fields
Description: Parses data fields that contain a single, plaintext URL.
Version: 1.0
Author: Janis Elsts

ModuleID: url_field
ModuleCategory: parser
ModuleClassName: blcUrlField
ModuleContext: on-demand
ModuleLazyInit: true
ModuleAlwaysActive: true
ModuleHidden: true
*/

/**
 * A "parser" for data fields that contain a single, plaintext URL.
 * 
 * Intended for parsing stuff like bookmarks and comment author links.
 *
 * @package Broken Link Checker
 * @access public
 */
class blcUrlField extends blcParser {
	var $supported_formats = array('url_field');
	
  /**
   * "Parse" an URL into an instance.
   *
   * @param string $content The entire content is expected to be a single plaintext URL.
   * @param string $base_url The base URL to use for normalizing relative URLs. If ommitted, the blog's root URL will be used. 
   * @param string $default_link_text
   * @return array An array of new blcLinkInstance objects.  
   */
	function parse($content, $base_url = '', $default_link_text = ''){
		$instances = array();
		
		$url = $raw_url = trim($content);
				
		//Attempt to parse the URL
		$parts = @parse_url($url);
	    if(!$parts) {
			return $instances; //Ignore invalid URLs
		};
				
		if ( !isset($parts['scheme']) ){
			//No sheme - likely a relative URL. Turn it into an absolute one.
			$url = $this->relative2absolute($url, $base_url);
			
			//Skip invalid URLs (again)
			if ( !$url || (strlen($url)<6) ) {
				return $instances;
			} 
		}
				
	    //The URL is okay, create and populate a new link instance.
	    $instance = new blcLinkInstance();
	    
	    $instance->set_parser($this);
	    $instance->raw_url = $raw_url;
	    $instance->link_text = $default_link_text;
	    
	    $link_obj = new blcLink($url); //Creates or loads the link
	    $instance->set_link($link_obj);
	    
	    $instances[] = $instance;
			    
		return $instances;
	}
	
  /**
   * Change one URL to another (just returns the new URL). 
   *
   * @param string $content The old URL.
   * @param string $new_url The new URL.
   * @param string $old_url Ignored.
   * @param string $old_raw_url Ignored. 
   *
   * @return array|WP_Error 
   */
	function edit($content, $new_url, $old_url, $old_raw_url){
		return array(
			'content' => $new_url,
			'raw_url' => $new_url,
		);
	}
	
  /**
   * For URL fields, "unlinking" simply means blanking the field.
   * (However, invididual link containers may implement a different logic for those fields.)
   */
	function unlink($content, $url, $raw_url){
		return '';
	}
}
