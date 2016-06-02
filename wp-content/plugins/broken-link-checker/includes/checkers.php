<?php

/**
 * Base class for link checking algorithms.
 *
 * All link checkering algorithms should extend this class.
 *
 * @package Broken Link Checker
 * @access public
 */
class blcChecker extends blcModule {
	
  /**
   * Priority determines the order in which the plugin will try all registered checkers 
   * when looking for one that can check a particular URL. Registered checkers will be
   * tried in order, from highest to lowest priority, and the first one that returns
   * true when its can_check() method is called will be used.
   * 
   * Checker implementations should set their priority depending on how specific they are
   * in choosing the URLs that they check.
   *
   * -10 .. 10  : checks all URLs that have a certain protocol, e.g. all HTTP URLs.
   * 11  .. 100 : checks only URLs from a restricted number of domains, e.g. video site URLs.	
   * 100+ 		: checks only certain URLs from a certain domain, e.g. YouTube video links.
   * 
   */
	var $priority = -100;
	
  /**
   * Check if this checker knows how to check a particular URL.
   *
   * @param string $url
   * @param array|bool $parsed_url The result of parsing $url with parse_url(). See PHP docs for details.
   * @return bool  
   */
	function can_check($url, $parsed_url){
		return false;
	}
	
  /**
   * Check an URL.
   *
   * This method returns an associative array containing results of 
   * the check. The following array keys are recognized by the plugin and
   * their values will be stored in the link's DB record :
   *	'broken' (bool) - True if the URL points to a missing/broken page. Required.
   *	'http_code' (int) - HTTP code returned when requesting the URL. Defaults to 0.     
   *	'redirect_count' (int) - The number of redirects. Defaults to 0.  
   *	'final_url' (string) - The redirected-to URL. Assumed to be equal to the checked URL by default.
   *	'request_duration' (float) - How long it took for the server to respond. Defaults to 0 seconds.
   *	'timeout' (bool) - True if checking the URL resulted in a timeout. Defaults to false.
   *	'may_recheck' (bool) - Allow the plugin to re-check the URL after 'recheck_threshold' seconds (see broken-link-checker.php).
   *	'log' (string) - Free-form log of the performed check. It will be displayed in the "Details" section of the checked link.
   *	'result_hash' (string) - A free-form hash or code uniquely identifying the detected link status. See sub-classes for examples. Max 200 characters.   
   *
   * @see blcLink:check() 
   *
   * @param string $url
   * @return array 
   */
	function check($url){
		trigger_error('Function blcChecker::check() must be over-ridden in a subclass', E_USER_ERROR);
	}
}

class blcCheckerHelper {
	
	/**
	 * Get a reference to a specific checker.
	 * 
	 * @uses blcModuleManager::get_module()
	 * 
	 * @param string $checker_id
	 * @return blcChecker
	 */
	static function get_checker($checker_id){
		$manager = blcModuleManager::getInstance();
		return $manager->get_module($checker_id, true, 'checker');
	}
	
  /**
   * Get a checker object that can check the specified URL. 
   *
   * @param string $url
   * @return blcChecker|null
   */
	static function get_checker_for($url){
		$parsed = @parse_url($url);
		
		$manager = blcModuleManager::getInstance();
		$active_checkers = $manager->get_active_by_category('checker'); 
		
		foreach($active_checkers as $module_id => $module_data){
			//Try the URL pattern in the header first. If it doesn't match,
			//we can avoid loading the module altogether.
			if ( !empty($module_data['ModuleCheckerUrlPattern']) ){
				if ( !preg_match($module_data['ModuleCheckerUrlPattern'], $url) ){
					continue;
				}
			}
			
			$checker = $manager->get_module($module_id);
			
			if ( !$checker ){
				continue;
			}
			
			//The can_check() method can perform more sophisticated filtering,
			//or just return true if the checker thinks matching the URL regex
			//is sufficient.
			if ( $checker->can_check($url, $parsed) ){
				return $checker;
			}
		}

		$checker = null;
		return $checker;
	}
}
		
