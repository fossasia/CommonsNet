<?php
/*
Plugin Name: Metadata
Description: Parses metadata (AKA custom fields)
Version: 1.0
Author: Janis Elsts

ModuleID: metadata
ModuleCategory: parser
ModuleClassName: blcMetadataParser
ModuleContext: on-demand
ModuleLazyInit: true
ModuleAlwaysActive: true
ModuleHidden: true
*/

class blcMetadataParser extends blcParser {
	var $supported_formats = array('metadata');
	var $supported_containers = array();
	
  /**
   * Parse a metadata value.
   *
   * @param string|array $content Metadata value(s).
   * @param string $base_url The base URL to use for normalizing relative URLs. If ommitted, the blog's root URL will be used. 
   * @param string $default_link_text
   * @return array An array of new blcLinkInstance objects.  
   */
	function parse($content, $base_url = '', $default_link_text = ''){
		$instances = array();
		
		if ( !is_array($content) ){
			$content = array($content);
		}
		
		foreach($content as $value){
			//The complete contents of the meta field are stored in raw_url.
			//This is useful for editing/unlinking, when one may need to
			//distinguish between multiple fields with the same name. 
			$raw_url = $value; 
			
			//If this is a multiline metadata field take only the first line (workaround for the 'enclosure' field).
			$lines = explode("\n", $value);
			$url = trim(reset($lines));
			
			//Attempt to parse the URL
			$parts = @parse_url($url);
		    if(!$parts) {
				return $instances; //Ignore invalid URLs
			};
					
			if ( !isset($parts['scheme']) ){
				//No scheme - likely a relative URL. Turn it into an absolute one.
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
	    }
			    
		return $instances;
	}
	
  /**
   * Change the URL in a metadata field to another one.
   * 
   * This is tricky because there can be multiple metadata fields with the same name
   * but different values. So we ignore $content (which might be an array of multiple
   * metadata values) and use the old raw_url that we stored when parsing the field(s)
   * instead.
   *
   * @see blcMetadataParser::parse()
   *
   * @param string $content Ignored.
   * @param string $new_url The new URL.
   * @param string $old_url Ignored.
   * @param string $old_raw_url The current meta value. 
   *
   * @return array|WP_Error 
   */
	function edit($content, $new_url, $old_url, $old_raw_url){
		//For multiline fields (like 'enclosure') we only want to change the first line.
		$lines = explode("\n", $old_raw_url); 
		array_shift($lines); //Discard the old first line
		array_unshift($lines, $new_url); //Insert the new URL in its place.
		$content = implode("\n", $lines); 
		
		return array(
			'content' => $content,
			'raw_url' => $new_url,
		);
	}
	
  /**
   * Get the link text for printing in the "Broken Links" table.
   *
   * @param blcLinkInstance $instance
   * @param string $context
   * @return string HTML 
   */
	function ui_get_link_text($instance, $context = 'display'){
		$image_html = sprintf(
			'<img src="%s" class="blc-small-image" title="%2$s" alt="%2$s"> ',
			esc_attr( plugins_url('/images/font-awesome/font-awesome-code.png', BLC_PLUGIN_FILE) ),
			__('Custom field', 'broken-link-checker')
		);
		
		$field_html = sprintf(
			'<code>%s</code>',
			$instance->container_field
		); 
		
		if ( $context != 'email' ){
			$field_html = $image_html . $field_html;
		}
		
		return $field_html;
	}
}
