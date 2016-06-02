<?php
/*
Plugin Name: RapidShare API
Description: Check links to RapidShare files using the RapidShare API.
Version: 1.0
Author: Janis Elsts

ModuleID: rapidshare-checker
ModuleCategory: checker
ModuleContext: on-demand
ModuleLazyInit: true
ModuleClassName: blcRapidShareChecker
ModulePriority: 100

ModuleCheckerUrlPattern: @^https?://(?:[\w\d]+\.)*rapidshare\.\w+/files/(\d+)/([^&?#/]+?)(?:$|[&?#/])@i
*/

/**
 * RapidShare API link checker.
 * 
 * @package Broken Link Checker
 * @author Janis Elsts
 * @access public
 */
class blcRapidShareChecker extends blcChecker {
	
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
	 * Check a RapidShare file.
	 * 
	 * @param string $url File URL.
	 * @return array
	 */
	function check($url){
		$result = array(
			'final_url' => $url,
			'redirect_count' => 0,
			'timeout' => false,
			'broken' => false,
			'log' => sprintf("<em>(%s)</em>\n\n", __('Using RapidShare API', 'broken-link-checker')),
			'result_hash' => '',
			'status_code' => '',
			'status_text' => '',
		);
		
		//We know the URL will match because ModuleCheckerUrlPattern matched.
		preg_match('@^https?://(?:[\w\d]+\.)*rapidshare\.\w+/files/(\d+)/([^&?#/]+?)(?:$|[&?#/])@i', $url, $matches);
		
		$file_id = $matches[1];
		$file_name = $matches[2];
		
		/*
		We use the checkfiles function to check file status. The RapidShare API docs can be found here :
		http://images.rapidshare.com/apidoc.txt
	 	
	 	The relevant function is documented thusly :  
	 	
		sub=checkfiles
		
		Description:
				Gets status details about a list of given files. (files parameter limited to 3000 bytes.
				filenames parameter limited to 30000 bytes.)

		Parameters:
				files=comma separated list of file ids
				filenames=comma separated list of the respective filename. Example: files=50444381,50444382 filenames=test1.rar,test2.rar

		Reply fields:
				1:File ID
				2:Filename
				3:Size (in bytes. If size is 0, this file does not exist.)
				4:Server ID
				5:Status integer, which can have the following numeric values:
					0=File not found
					1=File OK
					3=Server down
					4=File marked as illegal
				6:Short host (Use the short host to get the best download mirror: http://rs$serverid$shorthost.rapidshare.com/files/$fileid/$filename)
				7:MD5 (hexadecimal)

		Reply format:	integer,string,integer,integer,integer,string,string
		*/
		
		$api_url = sprintf(
			'http://api.rapidshare.com/cgi-bin/rsapi.cgi?sub=checkfiles&files=%d&filenames=%s',
			$file_id,
			$file_name
		);
		
		$conf = blc_get_configuration();
		$args = array( 'timeout' => $conf->options['timeout'], );
		
		$start = microtime_float();
		$response = wp_remote_get($api_url, $args);
		$result['request_duration'] = microtime_float() - $start;
		
		$file_status = 0;
		$file_status_text = '';	
		
		//Is the response valid?
		if ( is_wp_error($response) ){
			$result['log'] .= "Error : " . $response->get_error_message();
        	$result['broken'] = true;
        	$result['http_code'] = 0;
		} else {
			$result['http_code'] = intval($response['response']['code']);
			
			if ( $result['http_code'] == 200 ){
				//Parse the API response
				$data = explode(',', $response['body']);
				
				//Check file status
				if ( isset($data[4]) ){
					
					$file_status = intval($data[4]);
					$file_status_text = '';
					if ( $file_status >= 0 && $file_status <= 6  ){
						//Lets not confuse the user by showing the HTTP code we got from the API.
						//It's always "200" - whether the file exists or not.
						$result['http_code'] = 0;
					}
										
					switch( $file_status ){
						case 0:
							$file_status_text = 'File not found';
							$result['broken'] = true;
							$result['status_code'] = BLC_LINK_STATUS_ERROR;
							$result['status_text'] = __('Not Found', 'broken-link-checker');
							break;
							
						case 1:
							$file_status_text = 'File OK (Anonymous downloading)';
							$result['status_code'] = BLC_LINK_STATUS_OK;
							$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
							break;
							
						case 2:
							$file_status_text = 'File OK (TrafficShare direct download without any logging)';
							$result['status_code'] = BLC_LINK_STATUS_OK;
							$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
							break;
							
						case 3:
							$file_status_text = 'Server down';
							$result['broken'] = true;
							$result['status_code'] = BLC_LINK_STATUS_WARNING;
							$result['status_text'] = __('RS Server Down', 'broken-link-checker');
							break;
							
						case 4:
							$file_status_text = 'File marked as illegal';
							$result['broken'] = true;
							$result['status_code'] = BLC_LINK_STATUS_ERROR;
							$result['status_text'] = __('File Blocked', 'broken-link-checker');
							break;
							
						case 5:
							$file_status_text = 'Anonymous file locked because it has more than 10 downloads';
							$result['broken'] = true;
							$result['status_code'] = BLC_LINK_STATUS_WARNING;
							$result['status_text'] = __('File Locked', 'broken-link-checker');
							break;
							
						case 6:
							$file_status_text = 'File OK (TrafficShare direct download with enabled logging)';
							$result['status_code'] = BLC_LINK_STATUS_OK;
							$result['status_text'] = _x('OK', 'link status', 'broken-link-checker');
							break;
					}
				
					$result['log'] .= sprintf(
						__('RapidShare : %s', 'broken-link-checker'),
						$file_status_text
					);
												
				} else {
					$result['log'] .= sprintf(
						__('RapidShare API error: %s', 'broken-link-checker'),
						$response['body']
					);
				}			
																
			} else {
				//Unexpected error.
				$result['log'] .= $response['body'];
				$result['broken'] = true;
			}
		}
		
		//Generate the result hash (used for detecting false positives)  
        $result['result_hash'] = implode('|', array(
        	'rapidshare',
			$result['http_code'],
			$result['broken']?'broken':'0', 
			$result['timeout']?'timeout':'0',
			$file_status
		));
		
		return $result;
	}
	
}
