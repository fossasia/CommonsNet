<?php

/**
 * Load all files pertaining to BLC's module subsystem  
 */

require 'module-manager.php';
require 'module-base.php';

require 'containers.php';
require 'checkers.php';
require 'parsers.php';

$blc_module_manager = blcModuleManager::getInstance(array(
		//List of modules active by default 
		'http',             //Link checker for the HTTP(s) protocol
		'link',             //HTML link parser
		'image',            //HTML image parser
		'metadata',         //Metadata (custom field) parser
		'url_field',        //URL field parser
		'comment',          //Comment container
		'custom_field',     //Post metadata container (aka custom fields)
		'post',             //Post content container
		'page',             //Page content container
		'youtube-checker',  //Video checker using the YouTube API
		'youtube-iframe',   //Embedded YouTube video container
		'dummy',            //Dummy container used as a fallback
));

require 'any-post.php';

//Let other plugins register virtual modules.
do_action('blc_register_modules', $blc_module_manager);