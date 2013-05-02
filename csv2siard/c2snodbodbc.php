<?php 
error_reporting(E_ALL);
// create database model from scratch
function createDBModel_odbc(){
global $prg_option, $wdir, $prgdir, $torque_schema, $static_torque_schema, $odbc_handle;

// Create ODBC table list
	$table_arr = array();
	$tables = odbc_tables($odbc_handle);
	while ($table = odbc_fetch_array($tables)) {
		if ($table['TABLE_TYPE'] == 'TABLE' or substr($table['TABLE_NAME'], -1) == '$') {
			// no backslash allowed in metadata.xml description element
			$table_arr[$table['TABLE_NAME']] = utf8_decode($prg_option['DB_TYPE']).': Dbq='.str_replace('\\', '/', $table['TABLE_CAT']);
			$dbname = $table['TABLE_CAT'];
		}
	}
	if (count($table_arr) == 0) {
		log_echo("No Table found in '$prg_option[CONNECTION]'\n"); exit(2);
	}
	
	// Create encoding list
	
	// Create column list for each table
	$column_arr = array();
	$columns = odbc_columns($odbc_handle);
	while ($row = odbc_fetch_array($columns)) {
		if (array_key_exists ($row['TABLE_NAME'], $table_arr)) {
			$column_arr[$row['TABLE_NAME']][$row['ORDINAL']-1]['name'] = $row['COLUMN_NAME'];
			$column_arr[$row['TABLE_NAME']][$row['ORDINAL']-1]['size'] = $row['COLUMN_SIZE'];
			
			// convert MS-Access, MS-Excel data types to torque 4.0 types
			switch ($row['TYPE_NAME']) {
				case "DATETIME":
					$fieldtype = "TIMESTAMP"; break;
				case "LONGCHAR":
				case "HYPERLINK":
				case "GUID":
					$fieldtype = "VARCHAR"; break;
				case "TEXT":
				case "MEMO":
					$fieldtype = "LONGVARCHAR"; break;
				case "NUMBER":
				case "CURRENCY":
					$fieldtype = "DECIMAL"; break;
				case "COUNTER":
					$fieldtype = "BIGINT"; break;
				case "IMAGE":
				case "LONGBINARY":
					$fieldtype = "BINARY"; break;
				default:
					$fieldtype = $row['TYPE_NAME'];
			}
			$column_arr[$row['TABLE_NAME']][$row['ORDINAL']-1]['type'] = $fieldtype;
		}
	}

	// create database description according to torque.v4 XML model
	writeDBModel($table_arr, $column_arr, basename($dbname));

	// write console message
	log_echo("\nNew XML database model written: $prg_option[NO_DB_MODEL]\n");
	reset($table_arr);
	while (list($key, $val) = each($table_arr)) {
		$val = ansi2ascii($val);
		log_echo("  [$key] => $val\n");
	}

}
?>
