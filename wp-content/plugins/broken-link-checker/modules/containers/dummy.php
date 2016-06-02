<?php

/*
Plugin Name: Dummy
Description: 
Version: 1.0
Author: Janis Elsts

ModuleID: dummy
ModuleCategory: container
ModuleClassName: blcDummyManager
ModuleAlwaysActive: true
ModuleHidden: true
*/

/**
 * A "dummy" container class that can be used as a fallback when the real container class can't be found.
 * 
 *
 * @package Broken Link Checker
 * @access public
 */
class blcDummyContainer extends blcContainer{
	
	function synch(){
		//Just mark it as synched so that it doesn't bother us anymore.
		$this->mark_as_synched();
	}
	
	function edit_link($field_name, $parser, $new_url, $old_url = '', $old_raw_url = '', $new_text = null){
		return new WP_Error(
			'container_not_found',
			sprintf(
				__("I don't know how to edit a '%s' [%d].", 'broken-link-checker'), 
				$this->container_type,
				$this->container_id
			)
		);
	}
	
	function unlink($field_name, $parser, $url, $raw_url =''){
		return new WP_Error(
			'container_not_found',
			sprintf(
				__("I don't know how to edit a '%s' [%d].", 'broken-link-checker'), 
				$this->container_type,
				$this->container_id
			)
		); 
	}
	
	function ui_get_source($container_field, $context = 'display'){
		return sprintf(
			'<em>Unknown source %s[%d]:%s</em>', 
			$this->container_type, 
			$this->container_id, 
			$container_field
		);
	}
}

/**
 * A dummy manager class.
 *
 * @package Broken Link Checker
 * @access public
 */
class blcDummyManager extends blcContainerManager {
	
	var $container_class_name = 'blcDummyContainer';
	
	function resynch($forced = false){
		//Do nothing.
	}
}
