<?php
/*
Plugin Name: Embedded YouTube playlists (old embed code)
Description: Parse embedded playlists from YouTube
Version: 1.0
Author: Janis Elsts

ModuleCategory: parser
ModuleClassName: blcYouTubePlaylistEmbed
ModuleContext: on-demand
ModuleLazyInit: true

ModulePriority: 110
*/

if ( !class_exists('blcEmbedParserBase') ){
	require 'embed-parser-base.php';
}

class blcYouTubePlaylistEmbed extends blcEmbedParserBase {
	
	function init(){
		parent::init();
		$this->short_title = __('YouTube Playlist', 'broken-link-checker');
		$this->long_title = __('Embedded YouTube playlist', 'broken-link-checker');
		$this->url_search_string = 'youtube.com/p/';
	}
	
	function link_url_from_src($src){
		//Extract playlist ID from the SRC.
		$path = parse_url($src, PHP_URL_PATH);
		if ( empty($path) ) {
			return null;
		}

		if ( preg_match('@/p/(?P<id>[^/?&#]+?)(?:[?&#]|$)@', trim($path), $matches) ) {
			$playlist_id = $matches['id'];
		} else {
			return null;
		}

		//Reconstruct the playlist permalink based on the ID
		$url = 'http://www.youtube.com/playlist?list=' . $playlist_id;

		return $url;
	}
}
