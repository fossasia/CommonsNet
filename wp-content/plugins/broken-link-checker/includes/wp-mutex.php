<?php

if ( !class_exists('WPMutex') ):
	
class WPMutex {
	/**
	 * Get an exclusive named lock.
	 * 
	 * @param string $name 
	 * @param integer $timeout 
	 * @param bool $network_wide 
	 * @return bool 
	 */
	static function acquire($name, $timeout = 0, $network_wide = false){
		global $wpdb; /* @var wpdb $wpdb */
		if ( !$network_wide ){
			$name = WPMutex::_get_private_name($name);
		}
		$state = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)', $name, $timeout));
		return $state == 1;
	}	
	
	/**
	 * Release a named lock.
	 * 
	 * @param string $name 
	 * @param bool $network_wide 
	 * @return bool
	 */
	static function release($name, $network_wide = false){
		global $wpdb; /* @var wpdb $wpdb */
		if ( !$network_wide ){
			$name = WPMutex::_get_private_name($name);
		}		
		$released = $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $name));
		return $released == 1;
	}
	
	/**
	 * Given a generic lock name, create a new one that's unique to the current blog.
	 * 
	 * @access private
	 * 
	 * @param string $name
	 * @return string
	 */
	static function _get_private_name($name){
		global $current_blog;
		if ( function_exists('is_multisite') && is_multisite() && isset($current_blog->blog_id) ){
			$name .= '-blog-' . $current_blog->blog_id;
		}
		return $name;
	}
}

endif;

