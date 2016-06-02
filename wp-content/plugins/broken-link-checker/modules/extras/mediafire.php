<?php
/*
Plugin Name: MediaFire API
Description: Check links to files hosted on MediaFire. 
Version: 1.0
Author: Janis Elsts

ModuleID: mediafire-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcMediaFireChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^http://(?:www\.)?mediafire\.com/(?:(?:download\.php)?\?|download/)([0-9a-zA-Z]{11,20})(?:$|[^0-9a-zA-Z])@
*/

/**
 * MediaFire link checker.
 * 
 * @package Broken Link Checker
 * @author Janis Elsts
 * @access public
 */
class blcMediaFireChecker extends blcChecker {
	
	/**
	 * Determine if the checker can parse a specific URL.
	 * Always returns true because the ModuleCheckerUrlPattern header constitutes sufficient verification.
	 * 
	 * @param string $url
	 * @param array $parsed
	 * @return bool True.
	 */
	function can_check($url, $parsed){
		return true;
	}
	
	/**
	 * Check a MediaFire link.
	 * 
	 * @param string $url
	 * @return array
	 */
	function check($url){
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => "<em>(Using MediaFire checker module)</em>\n\n",
			'http_code' => 0,
			'result_hash' => '',
		);
		
		//URLs like http://www.mediafire.com/download.php?03mj0mwmnnm are technically valid,
		//but they introduce unnecessary redirects.
		$url = str_replace('download.php','', $url);
		
		//Since MediaFire doesn't have an API, we just send a HEAD request
		//and try do divine the file state from the response headers.
		$start = microtime_float();
		$rez = $this->head($url);
		$result['request_duration'] = microtime_float() - $start;
				
		if ( is_wp_error($rez) ){
			
			//An unexpected error.
			$result['broken'] = true; 
			$result['log'] .= "Error : " . $rez->get_error_message();
			if ( $data = $rez->get_error_data() ){
				$result['log'] .= "\n\nError data : " . print_r($data, true);
			}
			
		} else {
															
			$result['http_code'] = intval($rez['response']['code']);
			
			if ( $result['http_code'] == 200 ){
				//200 - OK
				$result['broken'] = false;
				$result['log'] .= "File OK";
			} elseif ( isset($rez['headers']['location']) ) {
				//Redirect = either an error or a redirect to the full file URL.
				//For errors, the redirect URL is structured like this : '/error.php?errno=320'.
				//The 'errno' argument contains an (undocumented) error code.
				$result['broken'] = true;

				if ( strpos($rez['headers']['location'], '/download/') !== false ) {
					$result['broken'] = false;
					$result['http_code'] = 200;
					$result['log'] .= "File OK";
					$result['log'] .= "\nFull URL: " . $rez['headers']['location'];

				} elseif ( strpos($rez['headers']['location'], 'errno=320') !== false ){
					$result['status_code'] = BLC_LINK_STATUS_ERROR;
					$result['status_text'] = __('Not Found', 'broken-link-checker');
					$result['http_code'] = 0;
					$result['log'] .= "The file is invalid or has been removed.";

				} elseif ( strpos($rez['headers']['location'], 'errno=378') !== false ) {
					$result['status_code'] = BLC_LINK_STATUS_ERROR;
					$result['status_text'] = __('Not Found', 'broken-link-checker');
					$result['http_code'] = 0;
					$result['log'] .= "The file has been removed due to a violation of MediaFire ToS.";

				} elseif ( strpos($rez['headers']['location'], 'errno=388') !== false ) {
					$result['status_code'] = BLC_LINK_STATUS_WARNING;
					$result['status_text'] = __('Permission Denied', 'broken-link-checker');
					$result['http_code'] = 0;
					$result['log'] .= "Permission denied. Most likely the plugin sent too many requests too quickly. Try again later.";

				} else {
					$result['status_code'] = BLC_LINK_STATUS_INFO;
					$result['status_text'] = __('Unknown Error', 'broken-link-checker');
					$result['log'] .= "Unknown error.\n\n";
					foreach($rez['headers'] as $name => $value){
						$result['log'] .= sprintf("%s: %s\n", $name, $value);
					}
				}

			} else {
				$result['log'] .= "Unknown error.\n\n" . implode("\n",$rez['headers']);
			}
		}
		
		//Generate the result hash (used for detecting false positives)  
        $result['result_hash'] = implode('|', array(
        	'mediafire',
			$result['http_code'],
			$result['broken']?'broken':'0', 
			$result['timeout']?'timeout':'0'
		));
		
		return $result;
	}
    
	/**
	 * Perform a HEAD request to the specified URL.
	 * 
	 * Note : 
	 * 
	 * Since the MediaFire checker works by parsing the "Location" header, redirect following
	 * _must_ be disabled. This can become a problem on servers where WP is forced to fall back
	 * on using WP_Http_Fopen which ignores the 'redirection' flag. WP_Http_Fsockopen would work, 
	 * but it has the lowest priority of all transports. 
	 * 
	 * Alas, there is no way to reliably influence which transport is chosen - the WP_Http::_getTransport
	 * function caches the available choices, so plugins can disable individual transports only during
	 * its first run. Therefore, we must pick the best transport manually.
	 * 
	 * @param string $url
	 * @return array|WP_Error
	 */
	function head($url){
		$conf = blc_get_configuration();
		$args = array(
			'timeout' => $conf->options['timeout'],
			'redirection' => 0,
			'_redirection' => 0, //Internal flag that turns off redirect handling. See WP_Http::handle_redirects()
		);

		return wp_remote_head($url, $args);
	}
	
}
