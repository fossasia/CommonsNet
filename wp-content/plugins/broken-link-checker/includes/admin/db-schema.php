<?php

if( !function_exists('blc_get_db_schema') ){

function blc_get_db_schema(){
	global $wpdb;
	
	//Use the character set and collation that's configured for WP tables
	$charset_collate = '';
	if ( !empty($wpdb->charset) ){
		//Some German installs use "utf-8" (invalid) instead of "utf8" (valid). None of 
		//the charset ids supported by MySQL contain dashes, so we can safely strip them.
		//See http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html 
		$charset = str_replace('-', '', $wpdb->charset);
		
		$charset_collate = "DEFAULT CHARACTER SET {$charset}";
	}
	if ( !empty($wpdb->collate) ){
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}
	
	$blc_db_schema = <<<EOM
	
	CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blc_filters` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`name` varchar(100) NOT NULL,
		`params` text NOT NULL,
		
		PRIMARY KEY (`id`)
	) {$charset_collate};
	
	CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blc_instances` (
		`instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`link_id` int(10) unsigned NOT NULL,
		`container_id` int(10) unsigned NOT NULL,
		`container_type` varchar(40) NOT NULL DEFAULT 'post',
		`link_text` text NOT NULL DEFAULT '',
		`parser_type` varchar(40) NOT NULL DEFAULT 'link',
		`container_field` varchar(250) NOT NULL DEFAULT '',
		`link_context` varchar(250) NOT NULL DEFAULT '',
		`raw_url` text NOT NULL,
		  
		PRIMARY KEY (`instance_id`),
		KEY `link_id` (`link_id`),
		KEY `source_id` (`container_type`, `container_id`),
		KEY `parser_type` (`parser_type`)
	) {$charset_collate};
	
	CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blc_links` (
		`link_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
		`url` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		`first_failure` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		`last_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		`last_success` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		`last_check_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		`check_count` int(4) unsigned NOT NULL DEFAULT '0',
		`final_url` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
		`redirect_count` smallint(5) unsigned NOT NULL DEFAULT '0',
		`log` text NOT NULL,
		`http_code` smallint(6) NOT NULL DEFAULT '0',
		`status_code` varchar(100) DEFAULT '',
		`status_text` varchar(250) DEFAULT '',
		`request_duration` float NOT NULL DEFAULT '0',
		`timeout` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`broken` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`warning` tinyint(1) unsigned NOT NULL DEFAULT '0',
		`may_recheck` tinyint(1) NOT NULL DEFAULT '1',
		`being_checked` tinyint(1) NOT NULL DEFAULT '0',

		`result_hash` varchar(200) NOT NULL DEFAULT '',
		`false_positive` tinyint(1) NOT NULL DEFAULT '0',
		`dismissed` tinyint(1) NOT NULL DEFAULT '0',
		
		PRIMARY KEY (`link_id`),
		KEY `url` (`url`(150)),
		KEY `final_url` (`final_url`(150)),
		KEY `http_code` (`http_code`),
		KEY `broken` (`broken`)
	) {$charset_collate};
	
	CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}blc_synch` (
		`container_id` int(20) unsigned NOT NULL,
		`container_type` varchar(40) NOT NULL,
		`synched` tinyint(2) unsigned NOT NULL,
		`last_synch` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		
		PRIMARY KEY (`container_type`,`container_id`),
		KEY `synched` (`synched`)
	) {$charset_collate};
	
EOM;
	
	return $blc_db_schema;
}

}
