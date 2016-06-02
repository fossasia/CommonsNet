<?php
/*
Plugin Name: Embedded YouTube videos
Description: Parse embedded videos from YouTube
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcYouTubeIframe
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 120
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcYouTubeIframe extends blcEmbedParserBase {
	var $supported_formats = array('html');
	
	function init(){
		parent::init();
		$this->short_title = __('YouTube Video', 'broken-link-checker');
		$this->long_title = __('Embedded YouTube video', 'broken-link-checker');
		$this->url_search_string = 'youtube.com/embed/';
	}
	
	/**
	 * Extract embedded elements from a HTML string.
	 * 
	 * Returns an array of IFrame elements found in the input string. 
	 * Elements without a 'src' attribute are skipped. 
	 * 
	 * Each array item has the same basic structure as the array items
	 * returned by blcUtility::extract_tags(), plus an additional 'embed_code' key 
	 * that contains the full HTML code for the entire <ifram> tag.  
	 *  
	 * @uses blcUtility::extract_tags() This function is a simple wrapper around extract_tags()
	 * 
	 * @param string $html
	 * @return array 
	 */
	function extract_embeds($html){
		$results = array();
		
		//remove all <code></code> blocks first
		$html = preg_replace('/<code[^>]*>.+?<\/code>/si', ' ', $html);
		
		//Find likely-looking <iframe> elements
		$iframes = blcUtility::extract_tags($html, 'iframe', false, true);
		foreach($iframes as $embed){
			if ( empty($embed['attributes']['src']) ){
				continue;
			}
			
			$embed['embed_code'] = $embed['full_tag'];
			
			$results[] = $embed;
		}
		
		return $results;
	}
	
	function link_url_from_src($src){
		$parts = @parse_url($src);
		if ( empty($parts) || !isset($parts['path']) ) {
			return null;
		}

		//Is this a playlist?
		if ( strpos($parts['path'], 'videoseries') !== false ) {

			//Extract the playlist ID from the query string.
			if ( !isset($parts['query']) || empty($parts['query']) ) {
				return null;
			}
			parse_str($parts['query'], $query);
			if ( !isset($query['list']) || empty($query['list']) ) {
				return null;
			}

			$playlist_id = $query['list'];
			if ( substr($playlist_id, 0, 2) === 'PL' ) {
				$playlist_id = substr($playlist_id, 2);
			}

			//Reconstruct the playlist URL.
			$url = 'http://www.youtube.com/playlist?list=' . $playlist_id;

		} else {
			//Extract video ID from the SRC. The ID is always 11 characters.
			$exploded = explode('/', $parts['path']);
			$video_id = substr(	end($exploded), 0, 11 );

			//Reconstruct the video permalink based on the ID
			$url = 'http://www.youtube.com/watch?v='.$video_id;
		}

		return $url;
	}
}
