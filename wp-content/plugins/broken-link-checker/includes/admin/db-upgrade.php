<?php

class blcDatabaseUpgrader {
	
  /**
   * Create and/or upgrade the plugin's database tables.
   *
   * @return bool
   */
    public static function upgrade_database(){
		global $blclog;
		
		$conf = blc_get_configuration();
		$current = $conf->options['current_db_version'];
		
		if ( ($current != 0) && ( $current < 4 ) ){
			//The 4th DB version makes a lot of backwards-incompatible changes to the main
			//BLC tables, so instead of upgrading we just throw them away and recreate.
			if ( !blcDatabaseUpgrader::drop_tables() ){
				return false;
			};
			$current = 0;
		}
		
		//Create/update the plugin's tables
		if ( !blcDatabaseUpgrader::make_schema_current() ) {
			return false;
		}
		
		if ( $current != 0 ){
			
			if ( $current < 5 ){
				blcDatabaseUpgrader::upgrade_095();
			}
			
		}
		
		$conf->options['current_db_version'] = BLC_DATABASE_VERSION;
		$conf->save_options();
		$blclog->info('Database successfully upgraded.');
		
		return true;
	}
	
  /**
   * Create or update the plugin's DB tables.
   *
   * @return bool
   */
	static function make_schema_current(){
		global $blclog;

		$start = microtime(true);
		if ( !function_exists('blc_get_db_schema') ){
			require 'db-schema.php';
		}
		list($dummy, $query_log) = blcTableDelta::delta(blc_get_db_schema());
		
		$have_errors = false;
		foreach($query_log as $item){
			if ( $item['success'] ){
				$blclog->info(' [OK] ' . $item['query'] . sprintf(' (%.3f seconds)', $item['query_time']));
			} else {
				$blclog->error(' [  ] ' . $item['query']);
				$blclog->error(' Database error : ' . $item['error_message']);
				$have_errors = true;
			}
		}
		$blclog->info(sprintf('Schema update took %.3f seconds', microtime(true) - $start));
		
		$blclog->info('Database schema updated.');
		return !$have_errors;
	}
	
	/**
	 * Drop the plugin's tables.
	 * 
	 * @return bool
	 */
	static function drop_tables(){
		global $wpdb, $blclog; /** @var wpdb $wpdb */
		
		$blclog->info('Deleting the plugin\'s database tables'); 
		$tables = array(
			$wpdb->prefix . 'blc_linkdata',
			$wpdb->prefix . 'blc_postdata',
			$wpdb->prefix . 'blc_instances',
			$wpdb->prefix . 'blc_synch',
			$wpdb->prefix . 'blc_links',
		);
		
		$q = "DROP TABLE IF EXISTS " . implode(', ', $tables);
		$rez = $wpdb->query( $q );
		
		if ( $rez === false ){
			$error = sprintf(
				__("Failed to delete old DB tables. Database error : %s", 'broken-link-checker'),
				$wpdb->last_error
			); 
			
			$blclog->error($error);
			/*
			//FIXME: In very rare cases, DROP TABLE IF EXISTS throws an error when the table(s) don't exist. 
			return false;
			//*/
		}
		$blclog->info('Done.');
		
		return true;
	}
	
	static function upgrade_095($trigger_errors = false){
		global $wpdb; /** @var wpdb $wpdb */
		
		//Prior to 0.9.5 all supported post types were internally represented using 
		//a common 'post' container type. The current version creates a unique container 
		//type to each post type.
		
		//Update synch records and instances to reflect this change 
		$q = "
			UPDATE 
				{$wpdb->prefix}blc_synch AS synch 
				LEFT JOIN {$wpdb->posts} AS posts ON (posts.ID = synch.container_id)
			SET
			 synch.container_type = posts.post_type
			WHERE
			 synch.container_type = 'post' AND posts.post_type IS NOT NULL";
		$wpdb->query($q);
		 
		$q = "
			UPDATE 
				{$wpdb->prefix}blc_instances AS instances 
				LEFT JOIN {$wpdb->posts} AS posts ON (posts.ID = instances.container_id)
			SET
			 instances.container_type = posts.post_type
			WHERE
			 instances.container_type = 'post' AND posts.post_type IS NOT NULL";
		$wpdb->query($q); 
	}
	
}

class blcTableDelta {
	
	/**
	 * Parse one or more CREATE TABLE queries and generate a list of SQL queries that need 
	 * to be executed to make the current database schema match those queries. Will also
	 * execute those queries by default.
	 * 
	 * This function returns an array with two items. The first is a list of human-readable
	 * messages explaining what database changes were/would be made. The second array item
	 * is an array of the generated SQL queries and (if $execute was True) their results.
	 *  
	 * Each item of this second array is itself an associative array with these keys : 
	 *  'query' - the generated query.
	 *  'success' - True if the query was executed successfully, False if it caused an error.
	 *  'error_message' - the MySQL error message (only meaningful when 'success' = false).
	 * 
	 * The 'success' and 'error_message' keys will only be present if $execute was set to True.  
	 * 
	 * @param string $queries One or more CREATE TABLE queries separated by a semicolon.
	 * @param bool $execute Whether to apply the schema changes. Defaults to true.
	 * @param bool $drop_columns Whether to drop columns not present in the input. Defaults to true.
	 * @param bool $drop_indexes Whether to drop indexes not present in the input. Defaults to true.
	 * @return array   
	 */
	static function delta($queries, $execute = true, $drop_columns = true, $drop_indexes = true){
		global $wpdb, $blclog; /** @var wpdb $wpdb */
	
		// Separate individual queries into an array
		if ( !is_array($queries) ) {
			$queries = explode( ';', $queries );
			if ('' == $queries[count($queries) - 1]) array_pop($queries);
		}
	
		$cqueries = array(); // Creation Queries
		$for_update = array();
		
		// Create a tablename index for an array ($cqueries) of queries
		foreach($queries as $qry) {
			if (preg_match("|CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?([^\s(]+)|i", $qry, $matches)) {
				$table = trim( $matches[1], '`' );
				$cqueries[$table] = $qry;
				$for_update[$table] = 'Create table `'.$table.'`';
			}
		}
		
		// Check to see which tables and fields exist
		$start_show_tables = microtime(true);
		if ($tables = $wpdb->get_col('SHOW TABLES;')) {
			$blclog->info(sprintf('... SHOW TABLES (%.3f seconds)', microtime(true) - $start_show_tables));

			// For every table in the database
			foreach ($tables as $table) {

				// If a table query exists for the database table...
				if ( array_key_exists($table, $cqueries) ) {
					
					// Clear the field and index arrays
					$cfields = $indices = array();
					
					// Get all of the field names in the query from between the parens
					preg_match("|\((.*)\)|ms", $cqueries[$table], $match2);
					$qryline = trim($match2[1]);
	
					// Separate field lines into an array
					$flds = preg_split('@[\r\n]+@', $qryline);
	
					//echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($flds, true)."</pre><hr/>";
	
					// For every field line specified in the query
					foreach ($flds as $fld) {
						$definition = blcTableDelta::parse_create_definition($fld);
							
						if ( $definition ){
							if ( $definition['index'] ){
								$indices[ $definition['index_definition'] ] = $definition; //Index
							} else {
								$cfields[ $definition['name'] ] = $definition; //Column
							}
						}
					}
					
					//echo "Detected fields : <br>"; print_r($cfields);
	
					// Fetch the table column structure from the database
					$start = microtime(true);
					$tablefields = $wpdb->get_results("SHOW FULL COLUMNS FROM {$table};");
					$blclog->info(sprintf('... SHOW FULL COLUMNS FROM %s %.3f seconds', $table, microtime(true) - $start));
	
					// For every field in the table
					foreach ($tablefields as $tablefield) {
						$field_name = strtolower($tablefield->Field); //Field names are case-insensitive in MySQL
						
						// If the table field exists in the field array...
						if (array_key_exists($field_name, $cfields)) {
							$definition = $cfields[$field_name];
							
							// Is actual field definition different from that in the query?
							$different = 
								( $tablefield->Type != $definition['data_type'] ) ||
								( $definition['collation'] && ($tablefield->Collation != $definition['collation']) ) ||
								( $definition['null_allowed'] && ($tablefield->Null == 'NO') ) ||
								( !$definition['null_allowed'] && ($tablefield->Null == 'YES') ) ||
								( $tablefield->Default !== $definition['default'] ); 
							
							// Add a query to change the column type
							if ( $different ) {
								$cqueries[] = "ALTER TABLE `{$table}` MODIFY COLUMN `{$field_name}` {$definition['column_definition']}";
								$for_update[$table.'.'.$field_name] = "Changed type of {$table}.{$field_name} from {$tablefield->Type} to {$definition['column_definition']}";
							}
	
							// Remove the field from the array (so it's not added)
							unset($cfields[$field_name]);
						} else {
							// This field exists in the table, but not in the creation queries? Drop it.
							if ( $drop_columns ){
								$cqueries[] = "ALTER TABLE `{$table}` DROP COLUMN `$field_name`";
								$for_update[$table.'.'.$field_name] = 'Removed column '.$table.'.'.$field_name;
							}
						}
					}
	
					// For every remaining field specified for the table
					foreach ($cfields as $field_name => $definition) {
						// Push a query line into $cqueries that adds the field to that table
						$cqueries[] = "ALTER TABLE `{$table}` ADD COLUMN `$field_name` {$definition['column_definition']}";
						$for_update[$table.'.'.$field_name] = 'Added column '.$table.'.'.$field_name;
					}
					
					// Index stuff goes here
					//echo 'Detected indexes : <br>'; print_r($indices);
					
					// Fetch the table index structure from the database
					$start = microtime(true);
					$tableindices = $wpdb->get_results("SHOW INDEX FROM `{$table}`;");
					$blclog->info(sprintf('... SHOW INDEX FROM %s %.3f seconds', $table, microtime(true) - $start));

					if ($tableindices) {
						// Clear the index array
						$index_ary = array();

						// For every index in the table
						foreach ($tableindices as $tableindex) {
							// Add the index to the index data array
							$keyname = strtolower($tableindex->Key_name);
							$index_ary[$keyname]['name'] = $keyname;
							
							$index_ary[$keyname]['columns'][] = array(
								'column_name' => strtolower($tableindex->Column_name), 
								'length' => $tableindex->Sub_part
							);
							
							if ( !isset($index_ary[$keyname]['index_modifier']) ){
								if ( $keyname == 'primary' ){
									$index_ary[$keyname]['index_modifier'] = 'primary';
								} else if ( $tableindex->Non_unique == 0 ){
									$index_ary[$keyname]['index_modifier'] = 'unique';
								}
							} 
						}
	
						// For each actual index in the index array
						foreach ($index_ary as $index_name => $index_data) {
							// Build a create string to compare to the query
							$index_string = blcTableDelta::generate_index_string($index_data);
							if ( array_key_exists($index_string, $indices) ){
								//echo "Found index $index_string<br>";
								unset($indices[$index_string]);
							} else {
								//echo "Didn't find index $index_string<br>";
								if ( $drop_indexes ){ 
									if ( $index_name == 'primary' ){
										$cqueries[] = "ALTER TABLE `{$table}` DROP PRIMARY KEY";
									} else {
										$cqueries[] = "ALTER TABLE `{$table}` DROP KEY `$index_name`";
									}
									$for_update[$table.'.'.$index_name] = 'Removed index '.$table.'.'.$index_name;
								}
							}
						}
					}
	
					// For every remaining index specified for the table
					foreach ( $indices as $index ) {
						// Push a query line into $cqueries that adds the index to that table
						$cqueries[] = "ALTER TABLE `{$table}` ADD {$index['index_definition']}";
						$for_update[$table.'.'.$index['name']] = 'Added index '.$table.' '.$index['index_definition'];
					}
	
					// Remove the original table creation query from processing
					unset($cqueries[$table]);
					unset($for_update[$table]);
				} else {
					// This table exists in the database, but not in the creation queries?
				}
			}
		}
	
		//echo "Execute queries : <br>"; print_r($cqueries);
		$query_log = array();
		foreach ($cqueries as $query) {
			$log_item = array('query' => $query,);
			if ( $execute ) {
				$start = microtime(true);
				$log_item['success'] = ($wpdb->query($query) !== false);
				$log_item['error_message'] = $wpdb->last_error;
				$log_item['query_time'] = microtime(true) - $start;
			}
			$query_log[] = $log_item;
		}
	
		return array($for_update, $query_log);
	}
	
	/**
	 * Parse a a single column or index definition.
	 * 
	 * This function can parse many (but not all) types of syntax used to define columns
	 * and indexes in a "CREATE TABLE" query.  
	 * 
	 * @param string $line
	 * @return array
	 */
	static function parse_create_definition($line){
		$line = preg_replace('@[,\r\n\s]+$@', '', $line); //Strip the ", " line separator
		
		$pieces = preg_split('@\s+|(?=\()@', $line, -1, PREG_SPLIT_NO_EMPTY);
		if ( empty($pieces) ){
			return null;
		}
		
		$token = strtolower(array_shift($pieces));
		
		$index_modifier = '';
		$index = false;
		
		//Determine if this line defines an index
		if ( in_array($token, array('primary', 'unique', 'fulltext')) ){
			$index_modifier = $token;
			$index = true;
			$token = strtolower(array_shift($pieces)); 
		} 
		
		if ( in_array($token, array('index', 'key')) ){
			$index = true;
			$token = strtolower(array_shift($pieces)); 
		}
		
		//Determine column/index name
		$name = '';
		if ( $index ){
			//Names are optional for indexes; the INDEX/etc keyword can be immediately 
			//followed by a column list (or index_type, but we're ignoring that possibility).
			if ( strpos($token, '(') === false ){
				$name = $token;
			} else {
				if ( $index_modifier == 'primary' ){
					$name = 'primary';
				}
				array_unshift($pieces, $token);
			}
		} else {
			$name = $token;
		}
		$name = strtolower(trim($name, '`'));
		
		$definition = compact('name', 'index', 'index_modifier');
		
		//Parse the rest of the line
		$remainder = implode(' ', $pieces);
		if ( $index ){
			$definition['columns'] = blcTableDelta::parse_index_column_list($remainder);
			
			//If the index doesn't have a name, use the name of the first column 
			//(this is what MySQL does, but only when there isn't already an index with that name). 
			if ( empty($definition['name']) ){
				$definition['name'] = $definition['columns'][0]['column_name'];
			}
			//Rebuild the index def. in a normalized form
			$definition['index_definition'] = blcTableDelta::generate_index_string($definition);
			
		} else {
			$column_def = blcTableDelta::parse_column_definition($remainder);
			$definition = array_merge($definition, $column_def);
		}
		
		return $definition;
	}
	
	/**
	 * Parse the list of columns included in an index.
	 * 
	 * This function returns a list of column descriptors. Each descriptor is 
	 * an associative array with the keys 'column_name', 'length' and 'order'. 
	 * 
	 * @param string $line
	 * @return array Array of index columns
	 */
	static function parse_index_column_list($line){
		$line = preg_replace('@^\s*\(|\)\s*$@', '', $line); //Strip the braces that surround the column list
		$pieces = preg_split('@\s*,\s*@', $line);
		
		$columns = array();
		foreach($pieces as $piece){
			if ( preg_match('@`?(?P<column_name>[^\s`]+)`?(?:\s*\(\s*(?P<length>\d+)\s*\))?(?:\s+(?P<order>ASC|DESC))?@i', $piece, $matches) ){
				
				$column = array(
					'column_name' => strtolower($matches['column_name']), 
					'length' => null, 
					'order' => null //unused; included for completeness
				);
				
				if ( isset($matches['length']) && is_numeric($matches['length']) ){
					$column['length'] = intval($matches['length']);
				}
				if ( isset($matches['order']) && !empty($matches['order']) ){
					$column['order'] = strtolower($matches['order']);
				}
				
				$columns[] = $column;
			};
		}
		
		return $columns;
	}
	
	/**
	 * Parse column datatype and flags.
	 * 
	 * 
	 * @param string $line
	 * @return array
	 */
	static function parse_column_definition($line){
		$line = trim($line);
	
		//Extract datatype. This regexp is not entirely reliable - for example, it won't work 
		//with enum fields where one of values contains brackets "()".
		$data_type = '';
		$regexp = '
		@
			(?P<type_name>^\w+)
				
				# followed by an optional length or a list of enum values
				(?:\s* 
					\(
						\s*	(?P<length>[^()]+) \s* 
					\) 
				)?
				
   				# various type modifiers/keywords
				(?P<keywords> 
					(?:\s+
						(?: BINARY | UNSIGNED |	ZEROFILL )
					)*
				)?
		@xi';
		
		if ( preg_match($regexp, $line, $matches) ){
			$data_type = strtolower($matches['type_name']);
			if ( !empty($matches['length']) ){
				$data_type .= '(' . trim($matches['length']) . ')';
			}
			if ( !empty($matches['keywords']) ){
				$data_type .= preg_replace('@\s+@', ' ', $matches['keywords']); //Collapse spaces
			}
			$line = substr($line, strlen($data_type));
		}
		
		//Extract flags
		$null_allowed = !preg_match('@\sNOT\s+NULL\b@i', $line);
		$auto_increment = preg_match('@\sAUTO_INCREMENT\b@i', $line);
		
		//Got a default value?
		$default = null;
		if ( preg_match("@\sDEFAULT\s+('[^']*'|\"[^\"]*\"|\d+)@i", $line, $matches) ){
			$default = trim($matches[1], '"\'');
		}
		
		//Custom character set and/or collation?
		$charset = $collation = null;
		if ( preg_match('@ (?:\s CHARACTER \s+ SET \s+ (?P<charset>[^\s()]+) )? (?:\s COLLATE \s+ (?P<collation>[^\s()]+) )? @xi', $line, $matches) ){
			if ( isset($matches['charset']) ){
				$charset = $matches['charset'];
			}
			if ( isset($matches['collation']) ){
				$collation = $matches['collation'];
			}
		}
		
		//Generate the normalized column definition
		$column_definition = $data_type;
		if ( !empty($charset) ){
			$column_definition .= " CHARACTER SET {$charset}";
		}
		if ( !empty($collation) ){
			$column_definition .= " COLLATE {$collation}";
		}
		if ( !$null_allowed ){
			$column_definition .= " NOT NULL";
		}
		if ( !is_null($default) ){
			$column_definition .= " DEFAULT '{$default}'";
		}
		if ( $auto_increment ){
			$column_definition .= " AUTO_INCREMENT";
		} 
		
		return compact('data_type', 'null_allowed', 'auto_increment', 'default', 'charset', 'collation', 'column_definition');
	}
	
	/**
	 * Generate an index's definition string from its parsed representation.
	 * 
	 * @param array $definition The return value of blcTableDelta::parse_create_definition()
	 * @return string 
	 */
	static function generate_index_string($definition){
		
		//Rebuild the index def. in a normalized form
		$index_definition = '';
		if ( !empty($definition['index_modifier']) ){
			$index_definition .= strtoupper($definition['index_modifier']) . ' ';
		}
		$index_definition .= 'KEY';
		if ( empty($definition['index_modifier']) || ($definition['index_modifier'] != 'primary') ){
			$index_definition .= ' `' . $definition['name'].'`';
		}
		
		$column_strings = array(); 
		foreach($definition['columns'] as $column){
			$c = '`' . $column['column_name'] . '`';
			if ( $column['length'] ){
				$c .= '('.$column['length'].')';
			}
			$column_strings[] = $c;
		}
		
		$index_definition .= ' (' . implode(', ', $column_strings) . ')';
		return $index_definition;
	}
	
}

