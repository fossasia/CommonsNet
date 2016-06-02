<?php
/*
Plugin Name: Embedded DailyMotion videos
Description: Parse embedded videos from DailyMotion
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcDailyMotionEmbed
ModuleContext: on-demand
ModuleLazyInit: true
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcDailyMotionEmbed extends blcEmbedParserBase {
	
	function init(){
		parent::init();
		$this->url_search_string = 'dailymotion.com/swf/video/';
		$this->short_title = __('DailyMotion Video','broken-link-checker');
		$this->long_title = __('Embedded DailyMotion video', 'broken-link-checker');
	}
	
	function link_url_from_src($src){
		//Extract video ID from the SRC. Only the part before the underscore matters,
		//but we're going to use the entire slug to make the display URL look better.
		$video_id = end(explode('/', $src));
		
		//Reconstruct the video permalink based on the ID
		$url = 'http://www.dailymotion.com/video/' . $video_id;
		
		return $url;
	}
}