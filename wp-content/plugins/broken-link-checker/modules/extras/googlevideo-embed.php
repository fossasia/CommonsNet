<?php
/*
Plugin Name: Embedded GoogleVideo videos
Description: Parse embedded videos from GoogleVideo
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcGoogleVideoEmbed
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 110
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcGoogleVideoEmbed extends blcEmbedParserBase {
	
	function init(){
		parent::init();
		$this->short_title = __('GoogleVideo Video', 'broken-link-checker');
		$this->long_title = __('Embedded GoogleVideo video', 'broken-link-checker');
		$this->url_search_string = 'video.google.com/';
	}
	
	function link_url_from_src($src){
		parse_str(parse_url($src, PHP_URL_QUERY), $query);
		$url = 'http://video.google.com/videoplay?' . build_query(array('docid' => $query['docid']));
		return $url;
	}
}

