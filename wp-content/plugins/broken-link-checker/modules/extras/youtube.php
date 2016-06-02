<?php

/*
Plugin Name: YouTube API
Description: Check links to YouTube videos and playlists using the YouTube API.
Version: 3
Author: Janis Elsts

ModuleID: youtube-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcYouTubeChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^https?://(?:([\w\d]+\.)*youtube\.[^/]+/watch\?.*v=[^/#]|youtu\.be/[^/#\?]+|(?:[\w\d]+\.)*?youtube\.[^/]+/(playlist|view_play_list)\?[^/#]{15,}?)@i
*/

class blcYouTubeChecker extends blcChecker {
	var $youtube_developer_key = 'AIzaSyAyye_rE5jYd7VpwvcLNItXQCo5zxVvMFY';
	var $api_grace_period = 0.3; //How long to wait between YouTube API requests.
	var $last_api_request = 0;   //Timestamp of the last request.
	
	function can_check($url, $parsed){
		return true;
	}
	
	function check($url){
		//Throttle API requests to avoid getting blocked due to quota violation.
		$delta = microtime_float() - $this->last_api_request; 
		if ( $delta < $this->api_grace_period ) {
			usleep(($this->api_grace_period - $delta) * 1000000);
		}
		
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => "<em>(Using YouTube API)</em>\n\n",
			'result_hash' => '',
		);
		
		$components = @parse_url($url);
		if ( isset($components['query']) ) {
			parse_str($components['query'], $query);
		} else {
			$query = array();
		}

		//Extract the video or playlist ID from the URL
		$video_id = $playlist_id = null;
		if ( strtolower($components['host']) === 'youtu.be' ) {
			$video_id = trim($components['path'], '/');
		} else if ( (strpos($components['path'], 'watch') !== false) && isset($query['v']) ) {
			$video_id = $query['v'];
		} else if ( $components['path'] == '/playlist' ) {
			$playlist_id = $query['list'];
		} else if ( $components['path'] == '/view_play_list' ) {
			$playlist_id = $query['p'];
		}

		if ( empty($playlist_id) && empty($video_id) ) {
			$result['status_text'] = 'Unsupported URL Syntax';
			$result['status_code'] = BLC_LINK_STATUS_UNKNOWN;
			return $result;
		}

		//Fetch video or playlist from the YouTube API
		if ( !empty($video_id) ) {
			$api_url = $this->get_video_resource_url($video_id);
		} else {
			$api_url = $this->get_playlist_resource_url($playlist_id);
		}

		$conf = blc_get_configuration();
		$args = array( 'timeout' => $conf->options['timeout'], );
		
		$start = microtime_float();
		$response = wp_remote_get($api_url, $args);
		$result['request_duration'] = microtime_float() - $start;
		$this->last_api_request = $start;
		
		//Got anything?
		if ( is_wp_error($response) ){
			$result['log'] .= "Error.\n" . $response->get_error_message();
			//WP doesn't make it easy to distinguish between different internal errors.
        	$result['broken'] = true;
        	$result['http_code'] = 0;
		} else {
			$result['http_code'] = intval($response['response']['code']);

			if ( !empty($video_id) ) {
				$result = $this->check_video($response, $result);
			} else {
				$result = $this->check_playlist($response, $result);
			}
		}

		//The hash should contain info about all pieces of data that pertain to determining if the 
		//link is working.  
        $result['result_hash'] = implode('|', array(
        	'youtube',
			$result['http_code'],
			$result['broken']?'broken':'0', 
			$result['timeout']?'timeout':'0',
			isset($result['state_name']) ? $result['state_name'] : '-',
			isset($result['state_reason']) ? $result['state_reason'] : '-',
		));
        
        return $result;
	}

	/**
	 * Check API response for a single video.
	 *
	 * @param array $response WP HTTP API response.
	 * @param array $result Current result array.
	 * @return array New result array.
	 */
	protected function check_video($response, $result) {
		$api = json_decode($response['body'], true);
		$videoFound = ($result['http_code'] == 200) && isset($api['items'], $api['items'][0]);

		if ( isset($api['error']) && ($result['http_code'] != 404) ) { //404's are handled later.
			$result['status_code'] = BLC_LINK_STATUS_WARNING;
			$result['warning'] = true;

			if ( isset($api['error']['message']) ) {
				$result['status_text'] = $api['error']['message'];
			} else {
				$result['status_text'] = __('Unknown Error', 'broken-link-checker');
			}
			$result['log'] .= $this->format_api_error($response, $api);

		} else if ( $videoFound ) {
			$result['log'] .= __("Video OK", 'broken-link-checker');
			$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
			$result['status_code'] = BLC_LINK_STATUS_OK;
			$result['http_code'] = 0;

			//Add the video title to the log, purely for information.
			if ( isset($api['items'][0]['snippet']['title']) ) {
				$title = $api['items'][0]['snippet']['title'];
				$result['log'] .= "\n\nTitle : \"" . htmlentities($title) . '"';
			}

		} else {
			$result['log'] .= __('Video Not Found', 'broken-link-checker');
			$result['broken'] = true;
			$result['http_code'] = 0;
			$result['status_text'] = __('Video Not Found', 'broken-link-checker');
			$result['status_code'] = BLC_LINK_STATUS_ERROR;
		}

		return $result;
	}

	/**
	 * Check a YouTube API response that contains a single playlist.
	 *
	 * @param array $response
	 * @param array $result
	 * @return array
	 */
	protected function check_playlist($response, $result) {
		$api = json_decode($response['body'], true);

		if ( $result['http_code'] == 404 ) {
			//Not found.
			$result['log'] .= __('Playlist Not Found', 'broken-link-checker');
			$result['broken'] = true;
			$result['http_code'] = 0;
			$result['status_text'] = __('Playlist Not Found', 'broken-link-checker');
			$result['status_code'] = BLC_LINK_STATUS_ERROR;

		} else if ( $result['http_code'] == 403 ) {
			//Forbidden. We're unlikely to see this code for playlists, but lets allow it.
			$result['log'] .= htmlentities($response['body']);
			$result['broken'] = true;
			$result['status_text'] = __('Playlist Restricted', 'broken-link-checker');
			$result['status_code'] = BLC_LINK_STATUS_ERROR;

		} else if ( ($result['http_code'] == 200) && isset($api['items']) && is_array($api['items']) ) {
			//The playlist exists.
			if ( empty($api['items']) ) {
				//An empty playlist. It is possible that all of the videos have been deleted.
				$result['log'] .= __("This playlist has no entries or all entries have been deleted.", 'broken-link-checker');
				$result['status_text'] = _x('Empty Playlist', 'link status', 'broken-link-checker');
				$result['status_code'] = BLC_LINK_STATUS_WARNING;
				$result['http_code'] = 0;
				$result['broken'] = true;
			} else {
				//Treat the playlist as broken if at least one video is inaccessible.
				foreach($api['items'] as $video) {
					$is_private = isset($video['status']['privacyStatus']) && ($video['status']['privacyStatus'] == 'private');
					if ( $is_private ) {
						$result['log'] .= sprintf(
							__('Video status : %s%s', 'broken-link-checker'),
							$video['status']['privacyStatus'],
							''
						);

						$result['broken'] = true;
						$result['status_text'] = __('Video Restricted', 'broken-link-checker');
						$result['status_code'] = BLC_LINK_STATUS_WARNING;
						$result['http_code'] = 0;
						break;
					}
				}

				if ( !$result['broken'] ) {
					//All is well.
					$result['log'] .= __("Playlist OK", 'broken-link-checker');
					$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
					$result['status_code'] = BLC_LINK_STATUS_OK;
					$result['http_code'] = 0;
				}
			}

		} else {
			//Some other error.
			$result['status_code'] = BLC_LINK_STATUS_WARNING;
			$result['warning'] = true;

			if ( isset($api['error']['message']) ) {
				$result['status_text'] = $api['error']['message'];
			} else {
				$result['status_text'] = __('Unknown Error', 'broken-link-checker');
			}
			$result['log'] .= $this->format_api_error($response, $api);
		}

		return $result;
	}

	protected function get_video_resource_url($video_id) {
		$params = array(
			'part' => 'status,snippet',
			'id' => $video_id,
			'key' => $this->youtube_developer_key,
		);
		$params = array_map('urlencode', $params);
		return 'https://www.googleapis.com/youtube/v3/videos?' . build_query($params);
	}

	protected function get_playlist_resource_url($playlist_id) {
		if ( strpos($playlist_id, 'PL') === 0 ) {
			$playlist_id = substr($playlist_id, 2);
		}
		$params = array(
			'key' => $this->youtube_developer_key,
			'playlistId' => $playlist_id,
			'part' => 'snippet,status',
			'maxResults' => 10, //Playlists can be big. Lets just check the first few videos.
		);
		$query = build_query(array_map('urlencode', $params));
		return 'https://www.googleapis.com/youtube/v3/playlistItems?' . $query;
	}

	protected function format_api_error($response, $api) {
		$log = $response['response']['code'] . ' ' . $response['response']['message'];
		$log .= "\n" . __('Unknown YouTube API response received.');

		//Log error details.
		if ( isset($api['error']['errors']) && is_array($api['error']['errors']) ) {
			foreach($api['error']['errors'] as $error) {
				$log .= "\n---\n";

				if (is_array($error)) {
					foreach($error as $key => $value) {
						$log .= sprintf(
							"%s: %s\n",
							htmlentities($key),
							htmlentities($value)
						);
					}
				}
			}
		}

		return $log;
	}

}
