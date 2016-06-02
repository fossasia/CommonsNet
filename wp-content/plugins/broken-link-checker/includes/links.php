<?php

/**
 * @author W-Shadow 
 * @copyright 2010
 */
 
if (!class_exists('blcLink')){
	
define('BLC_LINK_STATUS_UNKNOWN', 'unknown');
define('BLC_LINK_STATUS_OK', 'ok');
define('BLC_LINK_STATUS_INFO', 'info');
define('BLC_LINK_STATUS_WARNING', 'warning');
define('BLC_LINK_STATUS_ERROR', 'error');
	
class blcLink {
	
	//Object state
	var $is_new = false;
	
	//DB fields
	var $link_id = 0;
	var $url = '';
	
	var $being_checked = false;
	var $last_check = 0;
	var $last_check_attempt = 0;
	var $check_count = 0;
	var $http_code = 0;
	var $request_duration = 0;
	var $timeout = false;
	
	var $redirect_count = 0;
	var $final_url = '';
	
	var $broken = false;
	public $warning = false;
	var $first_failure = 0;
	var $last_success = 0;
	var $may_recheck = 1; 
	
	var $false_positive = false;
	var $result_hash = '';

	var $dismissed = false;
	
	var $status_text = '';
	var $status_code = '';
		
	var $log = '';
	
	//A list of DB fields and their storage formats
	var $field_format;
	
	//A cached list of the link's instances
	var $_instances = null;
	
	var $http_status_codes = array(
        // [Informational 1xx]  
        100=>'Continue',  
        101=>'Switching Protocols',  
        // [Successful 2xx]  
        200=>'OK',  
        201=>'Created',  
        202=>'Accepted',  
        203=>'Non-Authoritative Information',  
        204=>'No Content',  
        205=>'Reset Content',  
        206=>'Partial Content',  
        // [Redirection 3xx]  
        300=>'Multiple Choices',  
        301=>'Moved Permanently',  
        302=>'Found',  
        303=>'See Other',  
        304=>'Not Modified',  
        305=>'Use Proxy',  
        //306=>'(Unused)',  
        307=>'Temporary Redirect',  
        // [Client Error 4xx]  
        400=>'Bad Request',  
        401=>'Unauthorized',  
        402=>'Payment Required',  
        403=>'Forbidden',  
        404=>'Not Found',  
        405=>'Method Not Allowed',  
        406=>'Not Acceptable',  
        407=>'Proxy Authentication Required',  
        408=>'Request Timeout',  
        409=>'Conflict',  
        410=>'Gone',  
        411=>'Length Required', 
        412=>'Precondition Failed',  
        413=>'Request Entity Too Large',  
        414=>'Request-URI Too Long',  
        415=>'Unsupported Media Type',  
        416=>'Requested Range Not Satisfiable',  
        417=>'Expectation Failed',  
        // [Server Error 5xx]  
        500=>'Internal Server Error',  
        501=>'Not Implemented',  
        502=>'Bad Gateway',  
        503=>'Service Unavailable',  
        504=>'Gateway Timeout',  
        505=>'HTTP Version Not Supported',
        509=>'Bandwidth Limit Exceeded',
        510=>'Not Extended',
	);
	var $isOptionLinkChanged = false;
	function __construct($arg = null){
		global $wpdb, $blclog; /** @var wpdb $wpdb  */
		
		$this->field_format = array(
			'url' => '%s',
			'first_failure' => 'datetime',
			'last_check' => 'datetime',
			'last_success' => 'datetime',
			'last_check_attempt' => 'datetime',
			'check_count' => '%d',
			'final_url' => '%s',
			'redirect_count' => '%d',
			'log' => '%s',
			'http_code' => '%d',
			'request_duration' => '%F',
			'timeout' => 'bool',
			'result_hash' => '%s',
			'broken' => 'bool',
			'warning' => 'bool',
			'false_positive' => 'bool',
			'may_recheck' => 'bool',
			'being_checked' => 'bool',
		 	'status_text' => '%s',
		 	'status_code' => '%s',
			'dismissed' => 'bool',
		);
		
		if (is_numeric($arg)){
			//Load a link with ID = $arg from the DB.
			$q = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}blc_links WHERE link_id=%d LIMIT 1", $arg);
			$arr = $wpdb->get_row( $q, ARRAY_A );
			
			if ( is_array($arr) ){ //Loaded successfully
				$this->set_values($arr);
			} else {
				//Link not found. The object is invalid.
				//I'd throw an error, but that wouldn't be PHP 4 compatible...
				$blclog->warn(__CLASS__ .':' . __FUNCTION__ . ' Link not found.', $arg);
			}			
			
		} else if (is_string($arg)){
			//Load a link with URL = $arg from the DB. Create a new one if the record isn't found.
//			$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Trying to load a link by URL:', $arg);
			$q = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}blc_links WHERE url=%s LIMIT 1", $arg);
			$arr = $wpdb->get_row( $q, ARRAY_A );
			
			if ( is_array($arr) ){ //Loaded successfully
//				$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Success!');
				$this->set_values($arr);
			} else { //Link not found, treat as new
//				$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Link not found.');
				$this->url = $arg;
				$this->is_new = true;
			}			
			
		} else if (is_array($arg)){
			$this->set_values($arg);
			//Is this a new link?
			$this->is_new  = empty($this->link_id);
		} else {
			$this->is_new = true;
		}
	}
	
	function blcLink($arg = null){
		$this->__construct($arg);
	}
	
  /**
   * blcLink::set_values()
   * Set the internal values to the ones provided in an array (doesn't sanitize).
   *
   * @param array $arr An associative array of values
   * @return void
   */
	function set_values($arr){
		$arr = $this->to_native_format($arr);
		
		foreach( $arr as $key => $value ){
			$this->$key = $value;
		}
	}
	
  /**
   * Check whether the object represents a valid link
   *
   * @return bool
   */
	function valid(){
		return !empty( $this->url ) && ( !empty($this->link_id) || $this->is_new );
	}
	
  /**
   * Check if the link is working.
   *
   * @param bool $save_results Automatically save the results of the check. 
   * @return bool 
   */
	function check( $save_results = true ){
		if ( !$this->valid() ) return false;
		
		$this->last_check_attempt = time();
		
		/*
		If the link is still marked as in the process of being checked, that probably means
		that the last time the plugin tried to check it the script got terminated by PHP for 
		running over the execution time limit or causing a fatal error.
		
		This problem is likely to be temporary for most links, so we leave it be and treat it
		as any other link (i.e. check it again later using the default recheck periodicity). 
        */
        if ( $this->being_checked ) {
        	$this->being_checked = false;
        	
        	//Add an explanatory notice to the link's log
        	$error_notice = "[" . __("The plugin script was terminated while trying to check the link.", 'broken-link-checker') . "]";
        	if ( strpos($this->log, $error_notice) === false ){
        		$this->log = $error_notice . "\r\n" . $this->log;
        	}
        	
        	if ( $save_results ){
				$this->save();
			}
			
            return false;
        }
        
        $this->being_checked = true;
        $this->check_count++;
		
		if ( $save_results ) {
			
	        //Update the DB record before actually performing the check.
	        //Useful if something goes terribly wrong while checking this particular URL 
			//(e.g. the server might kill the script for running over the exec. time limit).
	        //Note : might be unnecessary.
	        $this->save();
        }
        
        $defaults = array(
        	'broken' => false,
        	'warning' => false,
        	'http_code' => 0,
        	'redirect_count' => 0,
        	'final_url' => $this->url,
        	'request_duration' => 0,
        	'timeout' => false,
        	'may_recheck' => true,
        	'log' => '',
        	'result_hash' => '',
        	'status_text' => '',
        	'status_code' => '',
		);
        
        
        $checker = blcCheckerHelper::get_checker_for($this->get_ascii_url());
        
		if ( is_null($checker) ){
			//Oops, there are no checker implementations that can handle this link.
			//Assume the link is working, but leave a note in the log.
			$this->broken = false;
			$this->being_checked = false;
			$this->log = __("The plugin doesn't know how to check this type of link.", 'broken-link-checker');
						
			if ( $save_results ){
				$this->save();
			}
			
			return true;
		}
		
		//Check the link
		$rez = $checker->check($this->get_ascii_url());
		//FB::info($rez, "Check results");

		$results = array_merge($defaults, $rez);

		//Some HTTP errors can be treated as warnings.
		$results = $this->decide_warning_state($results);

		//Filter the returned array to leave only the restricted set of keys that we're interested in.
		$results = array_intersect_key($results, $defaults);

		//The result hash is special - see blcLink::status_changed()
		$new_result_hash = $results['result_hash'];
		unset($results['result_hash']);

		//Update the object's fields with the new results
		$this->set_values($results);
		
		//Update timestamps & state-dependent fields
		$this->status_changed($results['broken'], $new_result_hash);
		$this->being_checked = false;
		
		//Save results to the DB 
		if($save_results){
			$this->save();
		}
		
		return $this->broken;
	}

	/**
	 * Decide whether the result of the latest check means that the link is really broken
	 * or should just be reported as a warning.
	 *
	 * @param array $check_results
	 * @return array
	 */
	private function decide_warning_state($check_results) {
		if ( !$check_results['broken'] && !$check_results['warning'] ) {
			//Nothing to do, this is a working link.
			return $check_results;
		}

		$configuration = blc_get_configuration();
		if ( !$configuration->get('warnings_enabled', true) ) {
			//The user wants all failures to be reported as "broken", regardless of severity.
			if ( $check_results['warning'] ) {
				$check_results['broken'] = true;
				$check_results['warning'] = false;
			}
			return $check_results;
		}

		$warning_reason = null;
		$failure_count = $this->check_count;
		$failure_duration = ($this->first_failure != 0) ? (time() - $this->first_failure) : 0;
		//These could be configurable, but lets put that off until someone actually asks for it.
		$duration_threshold = 24 * 3600;
		$count_threshold = 3;

		//We can't just use ($check_results['status_code'] == 'warning' because some "warning" problems are not
		//temporary. For example, region-restricted YouTube videos use the "warning" status code.
		$maybe_temporary_error = false;

		//Some basic heuristics to determine if this failure might be temporary.
		//----------------------------------------------------------------------
		if ( $check_results['timeout'] ) {
			$maybe_temporary_error = true;
			$warning_reason = 'Timeouts are sometimes caused by high server load or other temporary issues.';
		}

		$error_code = isset($check_results['error_code']) ? $check_results['error_code'] : '';
		if ( $error_code === 'connection_failed' ) {
			$maybe_temporary_error = true;
			$warning_reason = 'Connection failures are sometimes caused by high server load or other temporary issues.';
		}

		$http_code = intval($check_results['http_code']);
		$temporary_http_errors = array(
			408, //Request timeout. Probably a plugin bug, but could just be an overloaded client server.
			420, //Custom Twitter code returned when the client gets rate-limited.
			429, //Client has sent too many requests in a given amount of time.
			502, //Bad Gateway. Often a sign of a temporarily overloaded or misconfigured server.
			503, //Service Unavailable.
			504, //Gateway Timeout.
			509, //Bandwidth Limit Exceeded.
			520, //CloudFlare-specific "Origin Error" code.
			522, //CloudFlare-specific "Connection timed out" code.
			524, //Another CloudFlare-specific timeout code.
		);
		if ( in_array($http_code, $temporary_http_errors) ) {
			$maybe_temporary_error = true;

			if ( in_array($http_code, array(502, 503, 504, 509)) ) {
				$warning_reason = sprintf(
					'HTTP error %d usually means that the site is down due to high server load or a configuration problem. '
					. 'This error is often temporary and will go away after while.',
					$http_code
				);
			} else {
				$warning_reason = 'This HTTP error is often temporary.';
			}
		}

		//----------------------------------------------------------------------

		//Attempt to detect false positives.
		$suspected_false_positive = false;

		//A "403 Forbidden" error on an internal link usually means something on the site is blocking automated
		//requests. Possible culprits include hotlink protection rules in .htaccess, badly configured IDS, and so on.
		$is_internal_link = $this->is_internal_to_domain();
		if ( $is_internal_link && ($http_code == 403) ) {
			$suspected_false_positive = true;
			$warning_reason = 'This might be a false positive. Make sure the link is not password-protected, '
				. 'and that your server is not set up to block automated requests.';
		}

		//Some hosting providers turn off loopback connections. This causes all internal links to be reported as broken.
		if ( $is_internal_link && in_array($error_code, array('connection_failed', 'couldnt_resolve_host')) ) {
			$suspected_false_positive = true;
			$warning_reason = 'This is probably a false positive. ';
			if ( $error_code === 'connection_failed' ) {
				$warning_reason .= 'The plugin could not connect to your site. That usually means that your '
					. 'hosting provider has disabled loopback connections.';
			} elseif ( $error_code === 'couldnt_resolve_host' ) {
				$warning_reason .= 'The plugin could not connect to your site because DNS resolution failed. '
					. 'This could mean DNS is configured incorrectly on your server.';
			}
		}

		//----------------------------------------------------------------------

		//Temporary problems and suspected false positives start out as warnings. False positives stay that way
		//indefinitely because they are usually caused by bugs and server configuration issues, not temporary downtime.
		if ( ($maybe_temporary_error && ($failure_count < $count_threshold)) || $suspected_false_positive ) {
			$check_results['warning'] = true;
			$check_results['broken'] = false;
		}

		//Upgrade temporary warnings to "broken" after X consecutive failures or Y hours, whichever comes first.
		$threshold_reached = ($failure_count >= $count_threshold) || ($failure_duration >= $duration_threshold);
		if ( $check_results['warning'] ) {
			if ( ($maybe_temporary_error && $threshold_reached) && !$suspected_false_positive ) {
				$check_results['warning'] = false;
				$check_results['broken'] = true;
			}
		}

		if ( !empty($warning_reason) && $check_results['warning'] ) {
			$formatted_reason = "\n==========\n"
				. 'Severity: Warning' . "\n"
				. 'Reason: ' . trim($warning_reason)
				. "\n==========\n";

			$check_results['log'] .= $formatted_reason;
		}

		return $check_results;
	}
	
  /**
   * A helper method used to update timestamps & other state-dependent fields 
   * after the state of the link (broken vs working) has just been determined.
   *
   * @access private
   *
   * @param bool $broken
   * @param string $new_result_hash
   * @return void
   */
	private function status_changed($broken, $new_result_hash = ''){
		//If a link's status changes, un-dismiss it.
		if ( $this->result_hash != $new_result_hash ) {
			if ( $this->dismissed ) {
				$this->log .= sprintf(
					"Restoring a dismissed link. \nOld status: \n%s\nNew status: \n%s\n",
					$this->result_hash,
					$new_result_hash
				);
			}
			$this->dismissed = false;
		}
		
		if ( $this->false_positive && !empty($new_result_hash) ){
			//If the link has been marked as a (probable) false positive, 
			//mark it as broken *only* if the new result is different from 
			//the one that caused the user to mark it as a false positive.
			if ( $broken || $this->warning ){
				if ( $this->result_hash == $new_result_hash ){
					//Got the same result as before, assume it's still incorrect and the link actually works.
					$broken = false;
					$this->warning = false;
				} else {
					//Got a new result. Assume (quite optimistically) that it's not a false positive.
					$this->false_positive = false;
				}
			} else {
				//The plugin now thinks the link is working, 
				//so it's no longer a false positive.
				$this->false_positive = false;
			}
		}
		
		$this->broken = $broken;
		$this->result_hash = $new_result_hash;
		
		//Update timestamps
		$this->last_check = $this->last_check_attempt;
		if ( $this->broken || $this->warning ){
			if ( empty($this->first_failure) ){
				$this->first_failure = $this->last_check;
			}
		} else {
			$this->first_failure = 0;
			$this->last_success = $this->last_check;
			$this->check_count = 0;
		}
		
		//Add a line indicating link status to the log
		if ( $this->broken || $this->warning ) {
			$this->log .= "\n" . __("Link is broken.", 'broken-link-checker');
		} else {
			$this->log .= "\n" . __("Link is valid.", 'broken-link-checker');
		}
	}
	
  /**
   * blcLink::save()
   * Save link data to DB.
   *
   * @return bool True if saved successfully, false otherwise.
   */
	function save(){
		global $wpdb, $blclog; /** @var wpdb $wpdb */

		if ( !$this->valid() ) return false;

		//A link can't be broken and treated as a warning at the same time.
		if ( $this->broken && $this->warning ) {
			$this->warning = false;
		}
		
		//Make a list of fields to be saved and their values in DB format
		$values = array();
		foreach($this->field_format as $field => $format){
			$values[$field] = $this->$field;
		}
		$values = $this->to_db_format($values);
		
		if ( $this->is_new ){

    			TransactionManager::getInstance()->commit();

			//BUG: Technically, there should be a 'LOCK TABLES wp_blc_links WRITE' here. In fact,
			//the plugin should probably lock all involved tables whenever it parses something, lest
			//the user (ot another plugin) modify the thing being parsed while we're working.
			//The problem with table locking, though, is that parsing takes a long time and having 
			//all of WP freeze while the plugin is working would be a Bad Thing. Food for thought.
			
			//Check if there's already a link with this URL present
			$q = $wpdb->prepare(
				"SELECT link_id FROM {$wpdb->prefix}blc_links WHERE url = %s",
				$this->url
			);
			$existing_id = $wpdb->get_var($q);
			
			if ( !empty($existing_id) ){
				//Dammit.
				$this->link_id = $existing_id;
				$this->is_new = false;
				return true;
			}		
			
			//Insert a new row
			$q = sprintf(
				"INSERT INTO {$wpdb->prefix}blc_links( %s ) VALUES( %s )", 
				implode(', ', array_keys($values)), 
				implode(', ', array_values($values))
			);
			//FB::log($q, 'Link add query');
			$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Adding a new link. SQL query:' . "\n", $q);

			$rez = $wpdb->query($q) !== false;
			
			if ($rez){
				$this->link_id = $wpdb->insert_id;
				$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Database record created. ID = ' . $this->link_id);
				//FB::info($this->link_id, "Link added");
				//If the link was successfully saved then it's no longer "new"
				$this->is_new = false;
			} else {
				$blclog->error(__CLASS__ .':' . __FUNCTION__ . ' Error adding link', $this->url);
				//FB::error($wpdb->last_error, "Error adding link {$this->url}");
			}
				
			return $rez;
									
		} else {
			if ($this->isOptionLinkChanged !== true ) {
				TransactionManager::getInstance()->start();
			}
			$this->isOptionLinkChanged = false;
			//Generate the field = dbvalue expressions 
			$set_exprs = array();
			foreach($values as $name => $value){
				$set_exprs[] = "$name = $value";
			}
			$set_exprs = implode(', ', $set_exprs);
			
			//Update an existing DB record
			$q = sprintf(
				"UPDATE {$wpdb->prefix}blc_links SET %s WHERE link_id=%d",
				$set_exprs,
				intval($this->link_id)
			);
			//FB::log($q, 'Link update query');
			$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Updating a link. SQL query:'. "\n", $q);
			
			$rez = $wpdb->query($q) !== false;
			if ( $rez ){
				//FB::log($this->link_id, "Link updated");
				$blclog->debug(__CLASS__ .':' . __FUNCTION__ . ' Link updated.');
			} else {
				$blclog->error(__CLASS__ .':' . __FUNCTION__ . ' Error updating link', $this->url);
				//FB::error($wpdb->last_error, "Error updating link {$this->url}");
			}
			
			return $rez;			
		}
	}
	
  /**
   * A helper method for converting the link's field values to DB format and escaping them 
   * for use in SQL queries. 
   *
   * @param array $values
   * @return array
   */
	function to_db_format($values){
		global $wpdb; /** @var wpdb $wpdb  */
		
		$dbvalues = array();
		
		foreach($values as $name => $value){
			//Skip fields that don't exist in the blc_links table.
			if ( !isset($this->field_format[$name]) ){
				continue;
			}
			
			$format = $this->field_format[$name];
			
			//Convert native values to a format comprehensible to the DB
			switch($format){
				
				case 'datetime' :
					if ( empty($value) ){
						$value = '0000-00-00 00:00:00';
					} else {
						$value = date('Y-m-d H:i:s', $value);
					}
					$format = '%s';
					break;
					
				case 'bool':
					if ( $value ){
						$value = 1;
					} else {
						$value = 0;
					}
					$format = '%d';
					break;
			}
			
			//Escapize
			$value = $wpdb->prepare($format, $value);
			
			$dbvalues[$name] = $value;
		}
		
		return $dbvalues;		
	}
	
  /**
   * A helper method for converting values fetched from the database to native datatypes.
   *
   * @param array $values
   * @return array
   */
	function to_native_format($values){
		
		foreach($values as $name => $value){
			//Don't process fields that don't exist in the blc_links table.
			if ( !isset($this->field_format[$name]) ){
				continue;
			}
			
			$format = $this->field_format[$name];
			
			//Convert values in DB format to native datatypes.
			switch($format){
				
				case 'datetime' :
					if ( $value == '0000-00-00 00:00:00' ){
						$value = 0;
					} elseif (is_string($value)) {
						$value = strtotime($value);
					}
					break;
					
				case 'bool':
					$value = (bool)$value;
					break;
					
				case '%d':
					$value = intval($value);
					break;
					
				case '%f':
					$value = floatval($value);
					break;
					
			}
			
			$values[$name] = $value;
		}
		
		return $values;
	}
	
  /**
   * blcLink::edit()
   * Edit all instances of the link by changing the URL.
   *
   * Here's how this really works : create a new link with the new URL. Then edit()
   * all instances and point them to the new link record. If some instance can't be 
   * edited they will still point to the old record. The old record is deleted
   * if all instances were edited successfully.   
   *
   * @param string $new_url
   * @param string $new_text Optional.
   * @return array An associative array with these keys : 
   *   new_link_id - the database ID of the new link.
   *   new_link - the new link (an instance of blcLink).
   *   cnt_okay - the number of successfully edited link instances. 
   *   cnt_error - the number of instances that caused problems.
   *   errors - an array of WP_Error objects corresponding to the failed edits.  
   */
	function edit($new_url, $new_text = null){
		if ( !$this->valid() ){
			return new WP_Error(
				'link_invalid',
				__("Link is not valid", 'broken-link-checker')
			);
		}
		
		//FB::info('Changing link '.$this->link_id .' to URL "'.$new_url.'"');
		
		$instances = $this->get_instances();
		//Fail if there are no instances
		if (empty($instances)) {
			return array(
				'new_link_id' => $this->link_id,
				'new_link' => $this,
				'cnt_okay' => 0,
				'cnt_error' => 0,
				'errors' => array(
					new WP_Error(
						'no_instances_found',
						__('This link can not be edited because it is not used anywhere on this site.', 'broken-link-checker')
					)
				)
			);
		};
		
		//Load or create a link with the URL = $new_url  
		$new_link = new blcLink($new_url);
		$was_new = $new_link->is_new;
		if ($new_link->is_new) {
			//FB::log($new_link, 'Saving a new link');
			$new_link->save(); //so that we get a valid link_id
		}
		
		//FB::log("Changing link to $new_url");
		
		if ( empty($new_link->link_id) ){
			//FB::error("Failed to create a new link record");
			return array(
				'new_link_id' => $this->link_id,
				'new_link' => $this,
				'cnt_okay' => 0,
				'cnt_error' => 0,
				'errors' => array(
					new WP_Error(
						'link_creation_failed',
						__('Failed to create a DB entry for the new URL.', 'broken-link-checker')
					)
				)
			);
		}
		
		$cnt_okay = $cnt_error = 0;
		$errors = array();
		
		//Edit each instance.
		//FB::info('Editing ' . count($instances) . ' instances');
		foreach ( $instances as $instance ){
			$rez = $instance->edit( $new_url, $this->url, $new_text );
			if ( is_wp_error($rez) ){
				$cnt_error++;
				array_push($errors, $rez);
				//FB::error($instance, 'Failed to edit instance ' . $instance->instance_id);
			} else {
				$cnt_okay++;
				$instance->link_id = $new_link->link_id;
				$instance->save();
				//FB::info($instance, 'Successfully edited instance '  . $instance->instance_id);
			}
		}
		
		//If all instances were edited successfully we can delete the old link record.
		//UNLESS this link is equal to the new link (which should never happen, but whatever).
		if ( ( $cnt_error == 0 ) && ( $cnt_okay > 0 ) && ( $this->link_id != $new_link->link_id ) ){
			$this->forget( false );
		}
		
		//On the other hand, if no instances could be edited and the $new_link was really new,
		//then delete it.
		if ( ( $cnt_okay == 0 ) && $was_new ){
			$new_link->forget( false );
			$new_link = $this;
		}
		
		return array(
			'new_link_id' => $new_link->link_id,
			'new_link' => $new_link,
			'cnt_okay' => $cnt_okay,
			'cnt_error' => $cnt_error, 
			'errors' => $errors,
		 );			 
	}
	
  /**
   * Edit all of of this link's instances and replace the URL with the URL that it redirects to. 
   * This method does nothing if the link isn't a redirect.
   *
   * @see blcLink::edit() 
   *
   * @return array|WP_Error  
   */ 
	function deredirect(){
		if ( !$this->valid() ){
			return new WP_Error(
				'link_invalid',
				__("Link is not valid", 'broken-link-checker')
			);
		}
		
		if ( ($this->redirect_count <= 0) || empty($this->final_url) ){
			return new WP_Error(
				'not_redirect',
				__("This link is not a redirect", 'broken-link-checker')
			);
		}

		//Preserve the existing #anchor if the redirect doesn't include one.
		$new_url = $this->final_url;
		$anchor = @parse_url($this->url, PHP_URL_FRAGMENT);
		if ( !empty($anchor) && (strrpos($new_url, '#') === false) ) {
			$new_url .= '#' . $anchor;
		}
		
		return $this->edit($new_url);
	}

  /**
   * Unlink all instances and delete the link record.
   *
   * @return array|WP_Error An associative array with these keys : 
   *    cnt_okay - the number of successfully removed instances.
   *    cnt_error - the number of instances that couldn't be removed.
   *    link_deleted - true if the link record was deleted.
   *    errors - an array of WP_Error objects describing the errors that were encountered, if any.
   */
	function unlink(){
		if ( !$this->valid() ){
			return new WP_Error(
				'link_invalid',
				__("Link is not valid", 'broken-link-checker')
			);
		}
		
		//FB::info($this, 'Removing link');
		$instances = $this->get_instances();
		
		//No instances? Just remove the link then.
		if (empty($instances)) {
			//FB::warn("This link has no instances. Deleting the link.");
			$rez = $this->forget( false ) !== false;
			
			if ( $rez ){
				return array(
					'cnt_okay' => 1,
					'cnt_error' => 0,
					'link_deleted' => true,
					'errors' => array(), 
				);
			} else {
				return array(
					'cnt_okay' => 0,
					'cnt_error' => 0,
					'link_deleted' => false,
					'errors' => array(
						new WP_Error(
							"deletion_failed",
							__("Couldn't delete the link's database record", 'broken-link-checker')
						)
					), 
				);
			}
		}
		
		
		//FB::info('Unlinking ' . count($instances) . ' instances');
		
		$cnt_okay = $cnt_error = 0;
		$errors = array();
		
		//Unlink each instance.
		foreach ( $instances as $instance ){
			$rez = $instance->unlink( $this->url ); 
			
			if ( is_wp_error($rez) ){
				$cnt_error++;
				array_push($errors, $rez);
				//FB::error( $instance, 'Failed to unlink instance' );
			} else {
				$cnt_okay++;
				//FB::info( $instance, 'Successfully unlinked instance' );
			}
		}
		
		//If all instances were unlinked successfully we can delete the link record.
		if ( ( $cnt_error == 0 ) && ( $cnt_okay > 0 ) ){
			//FB::log('Instances removed, deleting the link.');
			$link_deleted = $this->forget() !== false;
			
			if ( !$link_deleted ){
				array_push(
					$errors, 
					new WP_Error(
						"deletion_failed",
						__("Couldn't delete the link's database record", 'broken-link-checker')
					)
				);
			}
			
		} else {
			//FB::error("Something went wrong. Unlinked instances : $cnt_okay, errors : $cnt_error");
			$link_deleted = false;
		}
		
		return array(
			'cnt_okay' => $cnt_okay,
			'cnt_error' => $cnt_error,
			'link_deleted' => $link_deleted,
			'errors' => $errors,
		); 
	}

	/**
	 * Remove the link and (optionally) its instance records from the DB. Doesn't alter posts/etc.
	 *
	 * @param bool $remove_instances
	 * @return mixed 1 on success, 0 if link not found, false on error.
	 */
	function forget($remove_instances = true){
		global $wpdb; /** @var wpdb $wpdb */
		if ( !$this->valid() ) return false;
		
		if ( !empty($this->link_id) ){
			//FB::info($this, 'Deleting link from DB');
			
			if ( $remove_instances ){
				//Remove instances, if any
				$wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}blc_instances WHERE link_id=%d", $this->link_id) );
			}
			
			//Remove the link itself
			$rez = $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}blc_links WHERE link_id=%d", $this->link_id) );
			$this->link_id = 0;
			
			return $rez;
		} else {
			return false;
		}
		
	}
	
  /**
   * Get a list of the link's instances
   *
   * @param bool $ignore_cache Don't use the internally cached instance list.
   * @param string $purpose 
   * @return blcLinkInstance[] An array of instance objects or FALSE on failure.
   */
	function get_instances( $ignore_cache = false, $purpose = '' ){
		if ( !$this->valid() || empty($this->link_id) ) return false;
		
		if ( $ignore_cache || is_null($this->_instances) ){
			$instances = blc_get_instances( array($this->link_id), $purpose );
			if ( !empty($instances) ){
				$this->_instances = $instances[$this->link_id];
			}
		}
		
		return $this->_instances;
	}
	
	/**
	 * Determine the status text and status code corresponding to the current state of this link.
	 * 
	 * @return array Associative array with two keys, 'text' and 'code'.
	 */
	function analyse_status(){
		$code = BLC_LINK_STATUS_UNKNOWN;
		$text = _x('Unknown', 'link status', 'broken-link-checker');
		
		//Status text
		if ( isset($this->status_text) && !empty($this->status_text) && !empty($this->status_code) ){
			
			//Lucky, the checker module has already set it for us.
			$text = $this->status_text;
			$code = $this->status_code;
			 
		} else {
			
			if ( $this->broken || $this->warning ){
				$code = BLC_LINK_STATUS_WARNING;
				$text = __('Unknown Error', 'broken-link-checker');
				
				if ( $this->timeout ){
					
					$text = __('Timeout', 'broken-link-checker');
					$code = BLC_LINK_STATUS_WARNING;
					
				} elseif ( $this->http_code ) {
					
					//Only 404 (Not Found) and 410 (Gone) are treated as broken-for-sure.
					if ( in_array($this->http_code, array(404, 410)) ){
						$code = BLC_LINK_STATUS_ERROR;
					} else {
						$code = BLC_LINK_STATUS_WARNING;
					}
					
					if ( array_key_exists(intval($this->http_code), $this->http_status_codes) ){
						$text = $this->http_status_codes[intval($this->http_code)];
					}
				}
				
			} else {
				
				if ( !$this->last_check ) {
					$text = __('Not checked', 'broken-link-checker');
					$code = BLC_LINK_STATUS_UNKNOWN;
				} elseif ( $this->false_positive ) {
					$text = __('False positive', 'broken-link-checker');
					$code = BLC_LINK_STATUS_UNKNOWN;
				} else {
					$text = _x('OK', 'link status', 'broken-link-checker');
					$code = BLC_LINK_STATUS_OK;
				}
				
			}
		}
		
		return compact('text', 'code');
	}
	
	/**
	 * Get the link URL in ASCII-compatible encoding.
	 * 
	 * @return string
	 */
	function get_ascii_url(){
		return blcUtility::idn_to_ascii($this->url);
	}

	/**
	 * Check if this link points to a page on the same domain as the current site.
	 *
	 * Note: Only checks the domain name, not subdirectory. If there are two separate WP sites A and B installed
	 * in two different subdirectories of the same domain, this method will treat a link from site A to B as internal.
	 *
	 * @return bool
	 */
	public function is_internal_to_domain() {
		$host = @parse_url($this->url, PHP_URL_HOST);
		if ( empty($host) ) {
			return false;
		}

		$site_host = @parse_url(get_site_url(), PHP_URL_HOST);
		if ( empty($site_host) ) {
			return false;
		}

		//Some users are inconsistent with using/not using the www prefix, so get rid of it.
		$site_host = preg_replace('@^www\.@', '', $site_host, 1);

		//Check if $host ends with $site_host. This means blah.example.com will match example.com.
		return (substr($host, -strlen($site_host)) === $site_host);
	}

	/**
	 * Remove the query string from an URL.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function remove_query_string($url) {
		return preg_replace('@\?[^#]*?(#|$)@', '$1', $url);
	}
}

} //class_exists

/**
 * Remove orphaned links that have no corresponding instances.
 *
 * @param int|array $link_id (optional) Only check these links
 * @return bool
 */
function blc_cleanup_links( $link_id = null ){
	global $wpdb; /* @var wpdb $wpdb */
	global $blclog;

	$start = microtime(true);
	$q = "DELETE FROM {$wpdb->prefix}blc_links
			USING {$wpdb->prefix}blc_links LEFT JOIN {$wpdb->prefix}blc_instances 
				ON {$wpdb->prefix}blc_instances.link_id = {$wpdb->prefix}blc_links.link_id
			WHERE
				{$wpdb->prefix}blc_instances.link_id IS NULL";
				
	if ( $link_id !== null ) {
		if ( !is_array($link_id) ){
			$link_id = array( intval($link_id) );
		}
		$q .= " AND {$wpdb->prefix}blc_links.link_id IN (" . implode(', ', $link_id) . ')';
	}
	
	$rez = $wpdb->query( $q );
	$elapsed = microtime(true) - $start;
	$blclog->log(sprintf('... %d links deleted in %.3f seconds', $wpdb->rows_affected, $elapsed));
	
	return $rez !== false;	
}

