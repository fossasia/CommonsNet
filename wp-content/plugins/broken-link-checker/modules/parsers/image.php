<?php
/*
Plugin Name: HTML images
Description: e.g. <code>&lt;img src="http://example.com/fluffy.jpg"&gt;</code>
Version: 1.0
Author: Janis Elsts

ModuleID: image
ModuleCategory: parser
ModuleClassName: blcHTMLImage
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 900
*/

//TODO: Update image parser to use the same HTML tag parsing routine as the HTML link parser. 
class blcHTMLImage extends blcParser {
	var $supported_formats = array('html');
	
	//                    \1                        \2      \3 URL    \4
	var $img_pattern = '/(<img[\s]+[^>]*src\s*=\s*)([\"\'])([^>]+?)\2([^<>]*>)/i';

	/** @var string Used in link editing callbacks. */
	private $old_url = '';
	/** @var string */
	private $new_url = '';
	
  /**
   * Parse a string for HTML images - <img src="URL">
   *
   * @param string $content The text to parse.
   * @param string $base_url The base URL to use for normalizing relative URLs. If omitted, the blog's root URL will be used.
   * @param string $default_link_text 
   * @return array An array of new blcLinkInstance objects. The objects will include info about the links found, but not about the corresponding container entity. 
   */
	function parse($content, $base_url = '', $default_link_text = ''){
		global $blclog;

		$charset = get_bloginfo('charset');
		if ( strtoupper($charset) === 'UTF8' ) {
			$charset = 'UTF-8';
		}
		$blclog->info('Blog charset is "' . $charset . '"');

		$instances = array();
		
		//remove all <code></code> blocks first
		$content = preg_replace('/<code[^>]*>.+?<\/code>/si', ' ', $content);
		
		//Find images
		if(preg_match_all($this->img_pattern, $content, $matches, PREG_SET_ORDER)){
			foreach($matches as $link){
				$url = $raw_url = $link[3];
				//FB::log($url, "Found image");
				$blclog->info('Found image. SRC attribute: "' . $raw_url . '"');
				
				//Decode &amp; and other entities
				$url = html_entity_decode($url, ENT_QUOTES, $charset);
				$blclog->info('Decoded image URL: "' . $url . '"');
				$url = trim($url);
				$blclog->info('Trimmed image URL: "' . $url . '"');

				//Allow shortcodes in image URLs.
				$url = do_shortcode($url);
				
				//Attempt to parse the URL
				$parts = @parse_url($url);
			    if(!$parts) {
					continue; //Skip invalid URLs
				};
				
				if ( !isset($parts['scheme']) ){
					//No scheme - likely a relative URL. Turn it into an absolute one.
					$relativeUrl = $url;
					$url = $this->relative2absolute($url, $base_url);

					$blclog->info(sprintf(
						'%s:%s Resolving relative URL. Relative URL = "%s", base URL = "%s", result = "%s"',
						__CLASS__,
						__FUNCTION__,
						$relativeUrl,
						$base_url,
						$url
					));
				}
				
				//Skip invalid URLs (again)
				if ( !$url || (strlen($url)<6) ) {
					continue;
				}

				$blclog->info('Final URL: "' . $url . '"');
			    //The URL is okay, create and populate a new link instance.
			    $instance = new blcLinkInstance();
			    
			    $instance->set_parser($this);
			    $instance->raw_url = $raw_url;
			    $instance->link_text = '';
			    
			    $link_obj = new blcLink($url); //Creates or loads the link
			    $instance->set_link($link_obj);
			    
			    $instances[] = $instance;
			}
		};
		
		return $instances;
	}
	
  /**
   * Change all images that have a certain source URL to a new URL. 
   *
   * @param string $content Look for images in this string.
   * @param string $new_url Change the images to this URL.
   * @param string $old_url The URL to look for.
   * @param string $old_raw_url The raw, not-normalized URL of the links to look for. Optional. 
   *
   * @return array|WP_Error If successful, the return value will be an associative array with two
   * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
   * for the modified images. In most cases, the returned raw_url will be equal to the new_url.
   */
	function edit($content, $new_url, $old_url, $old_raw_url){
		if ( empty($old_raw_url) ){
			$old_raw_url = $old_url;
		}
		//Save the old & new URLs for use in the regex callback.
		$this->old_url = $old_raw_url;
		$this->new_url = htmlentities($new_url);
		
		//Find all images and replace those that match $old_url.
		$content = preg_replace_callback($this->img_pattern, array(&$this, 'edit_callback'), $content);
		
		return array(
			'content' => $content,
			'raw_url' => $this->new_url,
		);
	}
	
	function edit_callback($matches){
		$url = $matches[3];
		if ($url == $this->old_url){
			return $matches[1].'"'.$this->new_url.'"'.$matches[4];
		} else {
			return $matches[0];
		}
	}
	
  /**
   * Remove all images that have a certain URL.
   *
   * @param string $content	Look for images in this string.
   * @param string $url The URL to look for.
   * @param string $raw_url The raw, non-normalized version of the URL to look for. Optional.
   * @return string Input string with all matching images removed. 
   */
	function unlink($content, $url, $raw_url){
		if ( empty($raw_url) ){
			$raw_url = $url;
		}
		$this->old_url = $raw_url; //used by the callback
		$content = preg_replace_callback($this->img_pattern, array(&$this, 'unlink_callback'), $content);
		return $content;
	}
	
	function unlink_callback($matches){
		$url = $matches[3];

		//Does the URL match?
		if ($url == $this->old_url){
			return ''; //Completely remove the IMG tag
		} else {
			return $matches[0]; //return the image unchanged
		}
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
		$text = __('Image', 'broken-link-checker'); 
		
		$image = sprintf(
			'<img src="%s" class="blc-small-image" alt="%2$s" title="%2$s"> ',
			esc_attr(plugins_url('/images/font-awesome/font-awesome-picture.png', BLC_PLUGIN_FILE)),
			esc_attr($text)
		);
		
		if ( $context != 'email' ){
			$text = $image . $text;
		}
		
		return $text;
	}
}
