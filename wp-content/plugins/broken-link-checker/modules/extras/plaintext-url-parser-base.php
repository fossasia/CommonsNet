<?php

class blcPlaintextUrlBase extends blcParser {
	var $supported_formats = array('html', 'plaintext');

	//Regexp for detecting plaintext URLs lifted from make_clickable()
	var $url_regexp = '#(?<=[\s>\]]|^)(\()?([\w]+?://(?:[\w\\x80-\\xff\#$%&~/=?@\[\](+-]|[.,;:](?![\s<]|(\))?([\s]|$))|(?(1)\)(?![\s<.,;:]|$)|\)))+)#is';

	//Used by the edit and unlink callbacks
	var $old_url = '';
	var $new_url = '';

	/**
	 * Parse a string for plaintext URLs
	 *
	 * @param string $content The text to parse.
	 * @param string $base_url The base URL. Ignored.
	 * @param string $default_link_text Default link text.
	 * @return array An array of new blcLinkInstance objects.
	 */
	function parse($content, $base_url = '', $default_link_text = ''){
		//Don't want to detect URLs inside links or tag attributes - 
		//there are already other parsers for that.

		//Avoid <a href="http://...">http://...</a>
		$content = preg_replace('#<a[^>]*>.*?</a>#si', '', $content);
		//HTML tags are treated as natural boundaries for plaintext URLs 
		//(since we strip tags, we must place another boundary char where they were).
		//The closing tag of [shortcodes] is also treated as a boundary.  
		$content = str_replace(array('<', '>', '[/'), array("\n<", ">\n", "\n[/"), $content);
		//Finally, kill all tags.
		$content = strip_tags($content);

		//Find all URLs
		$found = preg_match_all(
			$this->url_regexp,
			$content,
			$matches
		);

		$instances = array();

		if ( $found ){
			//Create a new instance for each match
			foreach($matches[2] as $match){
				$url = $this->validate_url(trim($match));
				if ( $url == false ) {
					continue;
				}

				//Create a new link instance.
				$instance = new blcLinkInstance();

				$instance->set_parser($this);
				$instance->raw_url = $match;
				$instance->link_text = $match;

				$link_obj = new blcLink($url); //Creates or loads the link
				$instance->set_link($link_obj);

				$instances[] = $instance;
			}
		}

		return $instances;
	}

	/**
	 * Validate and sanitize a URL.
	 *
	 * @param string $url
	 * @return bool|string A valid URL, or false if the URL is not valid.
	 */
	protected function validate_url($url) {
		//Do a little bit of validation
		$url = esc_url_raw($url);
		if ( empty($url) ){
			return false;
		}
		if ( function_exists('filter_var') ){
			//Note: filter_var() is no panacea as it accepts many invalid URLs
			if ( !filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) ){
				return false;
			}
		}
		$parts = @parse_url($url);
		if ( empty($parts['host']) || !strpos($parts['host'], '.') ){
			return false;
		}
		return $url;
	}

	/**
	 * Change all occurrences of a given plaintext URLs to a new URL.
	 *
	 * @param string $content Look for URLs in this string.
	 * @param string $new_url Change them to this URL.
	 * @param string $old_url The URL to look for.
	 * @param string $old_raw_url The raw, not-normalized URL. Optional.
	 *
	 * @return array|WP_Error If successful, the return value will be an associative array with two
	 * keys : 'content' - the modified content, and 'raw_url' - the new raw, non-normalized URL used
	 * for the modified links. In most cases, the returned raw_url will be equal to the new_url.
	 */
	function edit($content, $new_url, $old_url, $old_raw_url = ''){
		$this->new_url = $new_url;
		if ( empty($old_raw_url) ){
			$this->old_url = $old_url;
		} else {
			$this->old_url = $old_raw_url;
		}

		return array(
			'content' => preg_replace_callback($this->url_regexp, array(&$this, 'edit_callback'), $content),
			'raw_url' => $new_url,
			'link_text' => $new_url,
		);
	}

	function edit_callback($match){
		if ( $match[2] == $this->old_url ){
			return $this->new_url;
		} else {
			return $match[0];
		}
	}


	/**
	 * Remove all occurrences of a specific plaintext URL.
	 *
	 * @param string $content	Look for URLs in this string.
	 * @param string $url The URL to look for.
	 * @param string $raw_url The raw, non-normalized version of the URL to look for. Optional.
	 * @return string Input string with all matching plaintext URLs removed.
	 */
	function unlink($content, $url, $raw_url = ''){
		if ( empty($raw_url) ){
			$this->old_url = $url;
		} else {
			$this->old_url = $raw_url;
		}

		return preg_replace_callback($this->url_regexp, array(&$this, 'unlink_callback'), $content);
	}

	function unlink_callback($match){
		if ( $match[2] == $this->old_url ){
			return '';
		} else {
			return $match[0];
		}
	}
}