<?php
/*
Plugin Name: Smart YouTube httpv:// URLs
Description: Parse video URLs used by the Smart YouTube plugin
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcSmartYouTubeURL
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 100
*/

if ( !class_exists('blcPlaintextUrlBase') ) {
	require_once 'plaintext-url-parser-base.php';
}

class blcSmartYouTubeURL extends blcPlaintextUrlBase {

	protected function validate_url($url) {
		//Ignore invalid URLs.
		$parts = @parse_url($url);
		if ( empty($parts) ) {
			return false;
		}

		//We only care about httpv[hp]:// URLs as used by the Smart YouTube plugin.
		if ( stripos($parts['scheme'], 'httpv') !== 0 ) {
			return false;
		}

		//The URL should contain a domain name. AFAIK, Smart YouTube doesn't accept relative URLs.
		if ( empty($parts['host']) || !strpos($parts['host'], '.') ){
			return false;
		}

		//Replace the plugin-specific scheme with plain old http://.
		$url = preg_replace('#^httpv[^:]*?:#i', 'http:', $url);

		return $url;
	}

	/**
	 * Change all occurrences of a given URLs to a new URL.
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
		//If the user manually prefixes the URL with "httpv://" or other Smart YouTube scheme
		//then use the URL as-is. Otherwise change the scheme to the prefix from the old URL (if available).
		$new_scheme = @parse_url($new_url, PHP_URL_SCHEME);
		if ( empty($new_scheme) || (stripos($new_scheme, 'httpv') !== 0) ) {
			if ( !empty($old_raw_url) ) {
				$scheme = parse_url($old_raw_url, PHP_URL_SCHEME);
			} else {
				$scheme = 'httpv';
			}

			if ( empty($new_scheme) ) {
				$new_url = $scheme . '://' . $new_url;
			} else {
				$new_url = preg_replace(
					'#^' . preg_quote($new_scheme) . '://#i',
					$scheme . '://',
					$new_url
				);
			}
		}

		return parent::edit($content, $new_url, $old_url, $old_raw_url);
	}

}