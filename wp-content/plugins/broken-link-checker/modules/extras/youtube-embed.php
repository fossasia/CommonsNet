<?php
/*
Plugin Name: Embedded YouTube videos (old embed code)
Description: Parse embedded videos from YouTube
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcYouTubeEmbed
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 110
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcYouTubeEmbed extends blcEmbedParserBase {
	
	function init(){
		parent::init();
		$this->short_title = __('YouTube Video', 'broken-link-checker');
		$this->long_title = __('Embedded YouTube video', 'broken-link-checker');
		$this->url_search_string = 'youtube.com/v/';
	}
	
	function link_url_from_src($src){
		//Extract video ID from the SRC. The ID is always 11 characters.
		$parts = explode('/', $src);
		$video_id = substr(	end($parts), 0, 11 );
		
		//Reconstruct the video permalink based on the ID
		$url = 'http://www.youtube.com/watch?v='.$video_id;
		
		return $url;
	}
}
