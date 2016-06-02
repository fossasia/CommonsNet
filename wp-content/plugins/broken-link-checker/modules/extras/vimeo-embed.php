<?php
/*
Plugin Name: Embedded Vimeo videos
Description: Parse embedded videos from Vimeo
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcVimeoEmbed
ModuleContext: on-demand
ModuleLazyInit: true
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcVimeoEmbed extends blcEmbedParserBase {
	var $supported_formats = array('html');
	
	function init(){
		parent::init();
		$this->url_search_string = 'vimeo.com/moogaloop.swf?';
		$this->short_title = __('Vimeo Video', 'broken-link-checker');
		$this->long_title = __('Embedded Vimeo video', 'broken-link-checker');
	}
	
	function link_url_from_src($src){
		//Extract video ID from the SRC
		$components = @parse_url($src);
		if ( empty($components['query']) ) {
			return '';
		}
		
		parse_str($components['query'], $query);
		if ( empty($query['clip_id']) ){
			return '';
		} else {
			$video_id = $query['clip_id'];
		}
		
		//Reconstruct the video permalink based on the ID
		$url = 'http://vimeo.com/'.$video_id;
		
		return $url;
	}
}
