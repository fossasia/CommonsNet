<?php

/**
 * Class for querying, sorting and filtering links.
 * Used as a singleton.
 * 
 * @package Broken Link Checker
 * @access public
 */
class blcLinkQuery {
	
	var $native_filters;
	var $search_filter;
	var $custom_filters = array();
	
	var $valid_url_params = array(); 
	
	function __construct(){
		//Init. the available native filters.
		$this->native_filters = array(
			'all' => array(
				'params' => array(
					'where_expr' => '1',
				),
				'name' => __('All', 'broken-link-checker'),
				'heading' => __('Detected Links', 'broken-link-checker'),
				'heading_zero' => __('No links found (yet)', 'broken-link-checker'),
				'native' => true,
			),

			'broken' => array(
				'params' => array(
					'where_expr' => '( broken = 1 )',
					's_include_dismissed' => false,
				),
				'name' => __('Broken', 'broken-link-checker'),
				'heading' => __('Broken Links', 'broken-link-checker'),
				'heading_zero' => __('No broken links found', 'broken-link-checker'),
				'native' => true,
			),
			'warnings' => array(
				'params' => array(
					'where_expr' => '( warning = 1 )',
					's_include_dismissed' => false,
				),
				'name' => _x('Warnings', 'filter name', 'broken-link-checker'),
				'heading' => __('Warnings', 'filter heading', 'broken-link-checker'),
				'heading_zero' => __('No warnings found', 'broken-link-checker'),
				'native' => true,
			),
			'redirects' => array(
				'params' => array(
					'where_expr' => '( redirect_count > 0 )',
					's_include_dismissed' => false,
				),
				'name' => __('Redirects', 'broken-link-checker'),
				'heading' => __('Redirected Links', 'broken-link-checker'),
				'heading_zero' => __('No redirects found', 'broken-link-checker'),
				'native' => true,
			),

			'dismissed' => array(
				'params' => array(
					'where_expr' => '( dismissed = 1 )',
				),
				'name' => __('Dismissed', 'broken-link-checker'),
				'heading' => __('Dismissed Links', 'broken-link-checker'),
				'heading_zero' => __('No dismissed links found', 'broken-link-checker'),
				'native' => true,
			),
		);

		//The user can turn off warnings. In that case, all errors will show up in the "broken" filter instead.
		$conf = blc_get_configuration();
		if ( !$conf->get('warnings_enabled') ) {
			unset($this->native_filters['warnings']);
		}
		
		//Create the special "search" filter
		$this->search_filter = array(
			'name' => __('Search', 'broken-link-checker'),
			'heading' => __('Search Results', 'broken-link-checker'),
			'heading_zero' => __('No links found for your query', 'broken-link-checker'),
			'params' => array(),
			'use_url_params' => true,
			'hidden' => true,
		);
		
		//These search arguments may be passed via the URL if the filter's 'use_url_params' field is set to True.
		//They map to the fields of the search form on the Tools -> Broken Links page. Only these arguments
		//can be used in user-defined filters.
		$this->valid_url_params = array( 
 			's_link_text',
 			's_link_url',
 			's_parser_type',
 			's_container_type',
 			's_link_type',   
 			's_http_code',
 			's_filter',
		);
	}
	
	static function getInstance(){
		static $instance = null;
		if ( is_null($instance) ){
			$instance = new blcLinkQuery;
		}
		return $instance;
	}
	
  /**
   * Load and return the list of user-defined link filters.
   *
   * @return array An array of custom filter definitions. If there are no custom filters defined returns an empty array.
   */
	function load_custom_filters(){
		global $wpdb; /** @var wpdb $wpdb */
		
		$filter_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}blc_filters ORDER BY name ASC", ARRAY_A);
		$filters = array();
		
		if ( !empty($filter_data) ) {		
			foreach($filter_data as $data){
				wp_parse_str($data['params'], $params);
				
				$filters[ 'f'.$data['id'] ] = array(
					'name' => $data['name'],
					'params' => $params,
					'heading' => ucwords($data['name']),
					'heading_zero' => __('No links found for your query', 'broken-link-checker'),
					'custom' => true,
				);
			}
		}
		
		$this->custom_filters = $filters;
		
		return $filters;
	}
	
  /**
   * Add a custom link filter.
   *
   * @param string $name Filter name.
   * @param string|array $params Filter params. Either as a query string, or an array.
   * @return string|bool The ID of the newly added filter, or False.  
   */
	function create_custom_filter($name, $params){
		global $wpdb; /** @var wpdb $wpdb */
		
		if ( is_array($params) ){
			$params = http_build_query($params, null, '&');
		}
		
		//Save the new filter
		$q = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}blc_filters(name, params) VALUES (%s, %s)",
			$name, $params
		);
		
		if ( $wpdb->query($q) !== false ){
			$filter_id = 'f'.$wpdb->insert_id;
			return $filter_id;
		} else {
			return false;
		}
	}
	
  /**
   * Delete a custom filter
   *
   * @param string $filter_id
   * @return bool True on success, False if a database error occured.
   */
	function delete_custom_filter($filter_id){
		global $wpdb; /** @var wpdb $wpdb */

		if ( !isset($filter_id) ) {
			$filter_id = $_POST['filter_id'];
		}
		//Remove the "f" character from the filter ID to get its database key
		$filter_id = intval(ltrim($filter_id, 'f'));
		
		//Try to delete the filter
		$q = $wpdb->prepare("DELETE FROM {$wpdb->prefix}blc_filters WHERE id = %d", $filter_id);
		if ( $wpdb->query($q) !== false ){
			return true;
		} else {
			return false;
		}
	}
	
	function get_filters(){
		$filters = array_merge($this->native_filters, $this->custom_filters);
		$filters['search'] = $this->search_filter;
		return $filters;
	}
	
  /**
   * Get a link search filter by filter ID.
   *
   * @param string $filter_id
   * @return array|null
   */
	function get_filter($filter_id){
		$filters = $this->get_filters();
		if ( isset($filters[$filter_id]) ){
			return $filters[$filter_id];
		} else {
			return null;
		}
	}
	
  /**
   * Get link search parameters from the specified filter. 
   *
   * @param array $filter
   * @return array An array of parameters suitable for use with blcLinkQuery::get_links()
   */
	function get_search_params( $filter = null ){
		//If present, the filter's parameters may be saved either as an array or a string.
		$params = array();
		if ( !empty($filter) && !empty($filter['params']) ){
			$params = $filter['params']; 
			if ( is_string( $params ) ){
				wp_parse_str($params, $params);
			}
		}
		
		//Merge in the parameters from the current request, if required
		if ( isset($filter['use_url_params']) && $filter['use_url_params'] ){
			$params = array_merge($params, $this->get_url_search_params());
		}
		
		return $params;
	}
	
  /**
   * Extract search query parameters from the current URL
   *
   * @return array
   */
	function get_url_search_params(){
		$url_params = array();
		foreach ($_GET as $param => $value){
			if ( in_array($param, $this->valid_url_params) ){
				$url_params[$param] = $value;
			}
		}
		return $url_params;
	}
	
	
	
  /**
   * A helper method for parsing a list of search criteria and generating the parts of the SQL query.
   *
   * @see blcLinkQuery::get_links() 
   *
   * @param array $params An array of search criteria.
   * @return array 'where_exprs' - an array of search expressions, 'join_instances' - whether joining the instance table is required. 
   */
	function compile_search_params($params){
		global $wpdb; /** @var wpdb $wpdb */
		
		//Track whether we'll need to left-join the instance table to run the query.
		$join_instances = false;
		
		//Generate the individual clauses of the WHERE expression and store them in an array.
		$pieces = array();
		
		//Convert parser and container type lists to arrays of valid values
		$s_parser_type = array();
		if ( !empty($params['s_parser_type']) ){
			$s_parser_type = $params['s_parser_type'];
			if ( is_string($s_parser_type) ){
				$s_parser_type =  preg_split('/[,\s]+/', $s_parser_type);
			}
		}
		
		$s_container_type = array();
		if ( !empty($params['s_container_type']) ){
			$s_container_type = $params['s_container_type'];
			if ( is_string($s_container_type) ){
				$s_container_type =  preg_split('/[,\s]+/', $s_container_type);
			}
		}
		
		//Don't include links with instances that reference invalid (not currently loaded) 
		//containers and parsers (unless specifically told to also include invalid links).
		if ( empty($params['include_invalid']) ){
			$join_instances = true;
			
			$module_manager = blcModuleManager::getInstance();
			$loaded_containers = array_keys($module_manager->get_active_by_category('container'));
			$loaded_parsers = array_keys($module_manager->get_active_by_category('parser'));
			
			if ( empty($s_parser_type) ){
				$s_parser_type = $loaded_parsers;
			} else {
				$s_parser_type = array_intersect($s_parser_type, $loaded_parsers);
			}
			
			if ( empty($s_container_type) ){
				$s_container_type = $loaded_containers;
			} else {
				$s_container_type = array_intersect($s_container_type, $loaded_containers);
			}
		}
		
		//Parser type should match the parser_type column in the instance table.
		if ( !empty($s_parser_type) ){
			$s_parser_type = array_map('trim', array_unique($s_parser_type));
			$s_parser_type = array_map('esc_sql', $s_parser_type);
			
			if ( count($s_parser_type) == 1 ){
				$pieces[] = sprintf("instances.parser_type = '%s'", reset($s_parser_type));
			} else {
				$pieces[] = "instances.parser_type IN ('" . implode("', '", $s_parser_type) . "')";
			}
			
			$join_instances = true;
		}
		
		//Container type should match the container_type column in the instance table.
		if ( !empty($s_container_type) ){
			//Sanitize for use in SQL
			$s_container_type = array_map('trim', array_unique($s_container_type));
			$s_container_type = array_map('esc_sql', $s_container_type);
			
			if ( count($s_container_type) == 1 ){
				$pieces[] = sprintf("instances.container_type = '%s'", reset($s_container_type));
			} else {
				$pieces[] = "instances.container_type IN ('" . implode("', '", $s_container_type) . "')";
			}
			
			$join_instances = true;
		}
		
		//A part of the WHERE expression can be specified explicitly
		if ( !empty($params['where_expr']) ){
			$pieces[] = $params['where_expr'];
			$join_instances = $join_instances || ( stripos($params['where_expr'], 'instances') !== false );
		}
		
		//List of allowed link ids (either an array or comma-separated)
		if ( !empty($params['link_ids']) ){
			$link_ids = $params['link_ids'];
			
			if ( is_string($link_ids) ){
				$link_ids = preg_split('/[,\s]+/', $link_ids);
			}
			
			//Only accept non-zero integers
			$sanitized_link_ids = array();
			foreach($link_ids as $id){
				$id = intval($id);
				if ( $id != 0 ){
					$sanitized_link_ids[] = $id;
				}
			}
			
			$pieces[] = 'links.link_id IN (' . implode(', ', $sanitized_link_ids) . ')';
		}
		
		//Anchor text - use LIKE search
		if ( !empty($params['s_link_text']) ){
			$s_link_text = esc_sql($this->esc_like($params['s_link_text']));
			$s_link_text  = str_replace('*', '%', $s_link_text);
			
			$pieces[] = '(instances.link_text LIKE "%' . $s_link_text . '%")';
			$join_instances = true;
		}
		
		//URL - try to match both the initial URL and the final URL.
		//There is limited wildcard support, e.g. "google.*/search" will match both 
		//"google.com/search" and "google.lv/search" 
		if ( !empty($params['s_link_url']) ){
			$s_link_url = esc_sql($this->esc_like($params['s_link_url']));
			$s_link_url = str_replace('*', '%', $s_link_url);
			
			$pieces[] = '(links.url LIKE "%'. $s_link_url .'%") OR '.
				        '(links.final_url LIKE "%'. $s_link_url .'%")';
		}
		
		//Container ID should match... you guessed it - container_id
		if ( !empty($params['s_container_id']) ){
			$s_container_id = intval($params['s_container_id']);
			if ( $s_container_id != 0 ){
				$pieces[] = "instances.container_id = $s_container_id";
				$join_instances = true;
			}
		}
			
		//Link type can match either the the parser_type or the container_type.
		if ( !empty($params['s_link_type']) ){
			$s_link_type = esc_sql($params['s_link_type']);
			$pieces[] = "instances.parser_type = '$s_link_type' OR instances.container_type='$s_link_type'";
			$join_instances = true;
		}
			
		//HTTP code - the user can provide a list of HTTP response codes and code ranges.
		//Example : 201,400-410,500 
		if ( !empty($params['s_http_code']) ){
			//Strip spaces.
			$params['s_http_code'] = str_replace(' ', '', $params['s_http_code']);
			//Split by comma
			$codes = explode(',', $params['s_http_code']);
			
			$individual_codes = array();
			$ranges = array();
			
			//Try to parse each response code or range. Invalid ones are simply ignored.
			foreach($codes as $code){
				if ( is_numeric($code) ){
					//It's a single number
					$individual_codes[] = abs(intval($code));
				} elseif ( strpos($code, '-') !== false ) {
					//Try to parse it as a range
					$range = explode( '-', $code, 2 );
					if ( (count($range) == 2) && is_numeric($range[0]) && is_numeric($range[0]) ){
						//Make sure the smaller code comes first
						$range = array( intval($range[0]), intval($range[1]) );
						$ranges[] = array( min($range), max($range) );
					}
				}
			}
			
			$piece = array();
			
			//All individual response codes get one "http_code IN (...)" clause 
			if ( !empty($individual_codes) ){
				$piece[] = '(links.http_code IN ('. implode(', ', $individual_codes) .'))';
			}
			
			//Ranges get a "http_code BETWEEN min AND max" clause each
			if ( !empty($ranges) ){
				$range_strings = array();
				foreach($ranges as $range){
					$range_strings[] = "(links.http_code BETWEEN $range[0] AND $range[1])";
				}
				$piece[] = '( ' . implode(' OR ', $range_strings) . ' )';
			}
			
			//Finally, generate a composite WHERE clause for both types of response code queries
			if ( !empty($piece) ){
				$pieces[] = implode(' OR ', $piece);
			}
			
		}

		//Dismissed links are included by default, but can explicitly included
		//or filtered out by passing a special param.
		if ( isset($params['s_include_dismissed']) ) {
			$s_include_dismissed = !empty($params['s_include_dismissed']);
			$pieces['filter_dismissed'] = $s_include_dismissed ? '1' : '(dismissed = 0)';
		}

		//Optionally sorting is also possible
		$order_exprs = array();
		if ( !empty($params['orderby']) ) {
			$allowed_columns = array(
				'url' => 'links.url',
				'link_text' => 'instances.link_text',
				'redirect_url' => 'links.final_url',
			);
			$column = $params['orderby'];

			$direction = !empty($params['order']) ? strtolower($params['order']) : 'asc';
			if ( !in_array($direction, array('asc', 'desc')) ) {
				$direction = 'asc';
			}

			if ( array_key_exists($column, $allowed_columns) ) {
				if ( $column === 'redirect_url' ) {
					//Sort links that are not redirects last.
					$order_exprs[] = '(links.redirect_count > 0) DESC';
				}

				$order_exprs[] = $allowed_columns[$column] . ' ' . $direction;
			}
		}
			
		//Custom filters can optionally call one of the native filters
		//to narrow down the result set. 
		if ( !empty($params['s_filter']) && isset($this->native_filters[$params['s_filter']]) ){
			$the_filter = $this->native_filters[$params['s_filter']];
			$extra_criteria = $this->compile_search_params($the_filter['params']);
			
			$pieces = array_merge($extra_criteria['where_exprs'], $pieces);
			$join_instances = $join_instances || $extra_criteria['join_instances'];			
		}
		
		return array(
			'where_exprs' => $pieces,
			'join_instances' => $join_instances,
			'order_exprs' => $order_exprs,
		);
	}

	private function esc_like($input) {
		global $wpdb; /** @var wpdb $wpdb */
		if ( method_exists($wpdb, 'esc_like') ) {
			return $wpdb->esc_like($input);
		} else {
			return like_escape($input);
		}
	}
	
  /**
   * blcLinkQuery::get_links()
   *
   * @see blc_get_links()
   *
   * @param array $params
   * @return array|int
   */
	function get_links($params = null){
		global $wpdb; /** @var wpdb $wpdb */
		
		if( !is_array($params) ){
			$params = array();
		} 
		
		$defaults = array(
			'offset' => 0,
			'max_results' => 0,
			'load_instances' => false,
			'load_containers' => false,
			'load_wrapped_objects' => false,
			'count_only' => false,
			'purpose' => '',
			'include_invalid' => false,
			'orderby' => '',
			'order' => '',
		);
		
		$params = array_merge($defaults, $params);
		
		//Compile the search-related params into search expressions usable in a WHERE clause
		$criteria = $this->compile_search_params($params);
		
		//Build the WHERE clause
		if ( !empty($criteria['where_exprs']) ){
			$where_expr = "\t( " . implode(" ) AND\n\t( ", $criteria['where_exprs']) . ' ) ';
		} else {
			$where_expr = '1';
		}
		
		//Join the blc_instances table if it's required to perform the search.  
		$joins = "";
		if ( $criteria['join_instances'] ){
			$joins = "JOIN {$wpdb->prefix}blc_instances AS instances ON links.link_id = instances.link_id";
		}

		//Optional sorting
		if ( !empty($criteria['order_exprs']) ) {
			$order_clause = 'ORDER BY ' . implode(', ', $criteria['order_exprs']);
		} else {
			$order_clause = '';
		}
		
		if ( $params['count_only'] ){
			//Only get the number of matching links.
			$q = "
				SELECT COUNT(*)
				FROM (	
					SELECT 0
					
					FROM 
						{$wpdb->prefix}blc_links AS links 
						$joins
					
					WHERE
						$where_expr
					
				   GROUP BY links.link_id) AS foo";
			
			return $wpdb->get_var($q);
		}
		 
		//Select the required links.
		$q = "SELECT 
				 links.*
				
			  FROM 
				 {$wpdb->prefix}blc_links AS links
				 $joins
				
			  WHERE
				 $where_expr
				 
			   GROUP BY links.link_id

			   {$order_clause}"; //Note: would be a lot faster without GROUP BY
			   
		//Add the LIMIT clause
		if ( $params['max_results'] || $params['offset'] ){
			$q .= sprintf("\nLIMIT %d, %d", $params['offset'], $params['max_results']);
		}

		$results = $wpdb->get_results($q, ARRAY_A);
		if ( empty($results) ){
			return array();
		}
		
		//Create the link objects
		$links = array();
		
		foreach($results as $result){
			$link = new blcLink($result);
			$links[$link->link_id] = $link;
		}
		
		$purpose = $params['purpose'];
		/*
		Preload instances if :
			* It has been requested via the 'load_instances' argument. 
			* The links are going to be displayed or edited, which involves instances. 
		*/
		$load_instances = $params['load_instances'] || in_array($purpose, array(BLC_FOR_DISPLAY, BLC_FOR_EDITING));
		
		if ( $load_instances ){
			$link_ids = array_keys($links);
			$all_instances = blc_get_instances($link_ids, $purpose, $params['load_containers'], $params['load_wrapped_objects']);
			//Assign each batch of instances to the right link
			foreach($all_instances as $link_id => $instances){
				foreach($instances as $instance) { /** @var blcLinkInstance $instance */
					$instance->_link = $links[$link_id];
				}
				$links[$link_id]->_instances = $instances;
			}
		}

		return $links;
	}
	
  /**
   * Calculate the number of results for all known filters
   *
   * @return void
   */
	function count_filter_results(){
		foreach($this->native_filters as $filter_id => $filter){
			$this->native_filters[$filter_id]['count'] = $this->get_filter_links(
				$filter, array('count_only' => true)
			);
		}
		
		foreach($this->custom_filters as $filter_id => $filter){
			$this->custom_filters[$filter_id]['count'] = $this->get_filter_links(
				$filter, array('count_only' => true)
			);
		}
		
		$this->search_filter['count'] = $this->get_filter_links($this->search_filter, array('count_only' => true));
	}
	
  /**
   * Retrieve a list of links matching a filter. 
   *
   * @uses blcLinkQuery::get_links()
   *
   * @param string|array $filter Either a filter ID or an array containing filter data.
   * @param array $extra_params Optional extra criteria that will override those set by the filter. See blc_get_links() for details. 
   * @return array|int Either an array of blcLink objects, or an integer indicating the number of links that match the filter. 
   */
	function get_filter_links($filter, $extra_params = null){
		if ( is_string($filter) ){
			$filter = $this->get_filter($filter);
		}
		
		$params = $this->get_search_params($filter);
		

		if ( !empty($extra_params) ){
			$params = array_merge($params, $extra_params);
		}
		
		return $this->get_links($params);		
	}
	
	/**
	 * Print a menu of available filters, both native and user-created.
	 * 
	 * @param string $current Current filter ID.
	 * @return void
	 */
	function print_filter_menu($current = ''){
		$filters = $this->get_filters();
		
		echo '<ul class="subsubsub">';
    	
		//Construct a submenu of filter types
		$items = array();
		foreach ($filters as $filter => $data){
			if ( !empty($data['hidden']) ) continue; //skip hidden filters
															
			$class = '';
			$number_class = 'filter-' . $filter . '-link-count';
			
			if ( $current == $filter ) {
				$class = 'class="current"';
				$number_class .= ' current-link-count';
			}

			$items[] = sprintf(
				"<li><a href='tools.php?page=view-broken-links&filter_id=%s' %s>%s</a> <span class='count'>(<span class='%s'>%d</span>)</span>",
				esc_attr($filter),
				$class,
				esc_html($data['name']),
				$number_class,
				$data['count']
			);
		}
		echo implode(' |</li>', $items);
		
		echo '</ul>';
	}
	
	/**
	 * Print the appropriate heading for the given filter. 
	 * 
	 * @param array $current_filter
	 * @return void
	 */
	function print_filter_heading($current_filter){
		echo '<h2>';
		//Output a header matching the current filter
		if ( $current_filter['count'] > 0 ){
			echo $current_filter['heading'] . " (<span class='current-link-count'>{$current_filter['count']}</span>)";
		} else {
			echo $current_filter['heading_zero'] . "<span class='current-link-count'></span>";
		}
		echo '</h2>';
	}
	
	/**
	 * Execute a filter.
	 * 
	 * Gathers paging and search parameters from $_GET and executes the specified filter.
	 * The returned array contains standard filter data plus several additional fields :
	 *  'filter_id'     - Which filter was used. May differ from the specified $filter_id due to fallback settings. 
	 * 	'per_page'      - How many results per page the method tried to retrieve.
	 * 	'page'          - Which page of results was retrieved.
	 * 	'max_pages'     - The total number of results pages, calculated using the above 'per_page' value.
	 *  'links'         - An array of retrieved links (blcLink objects).
	 *  'search_params' - An associative array of the current search parameters as extracted either from the current URL or the filter itself.
	 *  'is_broken_filter' - TRUE if the filter was set to retrieve only broken links, FALSE otherwise.
	 * 
	 * @param string $filter_id Filter ID.
	 * @param int $page Optional. Which page of results to retrieve. Defaults to returning the first page of results.
	 * @param int $per_page Optional. The number of results per page. Defaults to 30.
	 * @param string $fallback Optional. Which filter to use if none match the specified $filter_id. Defaults to the native broken link filter.
	 * @param string $orderby Optional. Sort results by this column.
	 * @param string $order Optional. Sort direction ('asc' or 'desc').
	 * @return array Associative array of filter data and the results of its execution.
	 */
	function exec_filter($filter_id, $page = 1, $per_page = 30, $fallback = 'broken', $orderby = '', $order = 'asc'){
		//The only valid sort directions are 'asc' and 'desc'.
		if ( !in_array($order, array('asc', 'desc')) ) {
			$order = 'asc';
		}
		
		//Get the selected filter (defaults to displaying broken links)
		$current_filter = $this->get_filter($filter_id);
		if ( empty($current_filter) ){
			$current_filter = $this->get_filter($fallback);
			$filter_id = $fallback;
		}
		
		//Page number must be > 0 
		if ($page < 1) $page = 1;
		
		//Links per page [1 - 500]
		if ($per_page < 1){
			$per_page = 30;
		} else if ($per_page > 500){
			$per_page = 500;
		}
		
		//Calculate the maximum number of pages.
		$max_pages = ceil($current_filter['count'] / $per_page);
		
		//Select the required links
		$extra_params = array(
			'offset' => ( ($page-1) * $per_page ),
			'max_results' => $per_page,
			'purpose' => BLC_FOR_DISPLAY,
			'orderby' => $orderby,
			'order' => $order,
		);
		$links = $this->get_filter_links($current_filter, $extra_params);
		
		//If the current request is a user-initiated search query (either directly or 
		//via a custom filter), save the search params. They can later be used to pre-fill
		//the search form or build a new/modified custom filter.
		$search_params = array(); 
		if ( !empty($current_filter['custom']) || ($filter_id == 'search') ){
			$search_params = $this->get_search_params($current_filter);
		}
		
		$base_filter = '';
		if ( array_key_exists($filter_id, $this->native_filters) ) {
			$base_filter = $filter_id;
		} else if ( isset($current_filter['params']['s_filter']) && !empty($current_filter['params']['s_filter']) ) {
			$base_filter = $current_filter['params']['s_filter'];
		} else if ( isset($_GET['s_filter']) && !empty($_GET['s_filter']) ) {
			$base_filter = $_GET['s_filter'];
		}

		$is_broken_filter = ($base_filter == 'broken');

		//Save the effective filter data in the filter array. 
		//It can be used later to print the link table.
		$current_filter = array_merge(array(
			'filter_id' => $filter_id,
			'page' => $page,
			'per_page' => $per_page,
			'max_pages' => $max_pages,
			'links' => $links,
			'search_params' => $search_params,
			'is_broken_filter' => $is_broken_filter,
			'base_filter' => $base_filter,
		), $current_filter);
		
		return $current_filter;
	}
}

/**
 * Retrieve a list of links matching some criteria.
 *
 * The function argument should be an associative array describing the criteria.
 * The supported keys are :  
 *     'offset' - Skip the first X results. Default is 0. 
 *     'max_results' - The maximum number of links to return. Defaults to returning all results.
 *     'link_ids' - Retrieve only links with these IDs. This should either be a comma-separated list or an array.
 *     's_link_text' - Link text must match this keyphrase (performs a fulltext search).
 *     's_link_url' - Link URL must contain this string. You can use "*" as a wildcard.
 *     's_parser_type' - Filter links by the type of link parser that was used to find them.
 *     's_container_type' - Filter links by where they were found, e.g. 'post'.
 *     's_container_id' - Find links that belong to a container with this ID (should be used together with s_container_type).
 *     's_link_type' - Either parser type or container type must match this.   
 *     's_http_code' - Filter by HTTP code. Example : 201,400-410,500
 *     's_filter' - Use a built-in filter. Available filters : 'broken', 'redirects', 'all'
 *     'where_expr' - Advanced. Lets you directly specify a part of the WHERE clause.
 *     'load_instances' - Pre-load all link instance data for each link. Default is false. 
 *     'load_containers' - Pre-load container data for each instance. Default is false.
 *     'load_wrapped_objects' - Pre-load wrapped object data (e.g. posts, comments, etc) for each container. Default is false.
 *     'count_only' - Only return the number of results (int), not the whole result set. 'offset' and 'max_results' will be ignored if this is set. Default is false.
 *     'purpose' -  An optional code indicating how the links will be used.
 *     'include_invalid' - Include links that have no instances and links that only have instances that reference not-loaded containers or parsers. Defaults to false. 
 *
 * All keys are optional.
 *
 * @uses blcLinkQuery::get_links();
 *
 * @param array $params
 * @return int|blcLink[] Either an array of blcLink objects, or the number of results for the query.
 */
function blc_get_links($params = null){
	$instance = blcLinkQuery::getInstance();
	return $instance->get_links($params);
}

