<?php 
error_reporting(E_ALL);
// create database model from scratch
function createDBModel(){
global $prg_option, $wdir, $odbc_handle;
$order_of_datatype = array ('INTEGER' => 0, 'DECIMAL' => 1, 'FLOAT' => 2, 'DATE' => 3, 'VARCHAR' => 4);

	if ($odbc_handle != null) {
		createDBModel_odbc(); return;
	}
// Create CSV file list
	$file_arr = array();
	$reg = '#^'.Wildcard2Regex($prg_option['FILE_MASK']).'$#i';
	if ( $dirhandle = opendir($prg_option['CSV_FOLDER'])) {
		while (false !== ($file = readdir($dirhandle))) {
			if (preg_match($reg, $file) > 0 and ($file != "." && $file != "..") ) {
				$name = preg_replace($reg, '${1}${2}${3}${4}${5}',$file);
				if ($name != '') {
					// detect mime-type with GNU file-5.03
					$csvfile = $prg_option['CSV_FOLDER'].'/'.$file;
					$mime_type = detectMimeType($csvfile, 'TYPE');
					if ($mime_type == 'text/plain' ) {
						// check DBMS name conformity
						if (testDBMSNaming($name) === true){
							$file_arr[$name] = $csvfile;
						}
						else {
							$fl = ansi2ascii($file);
							echo "CSV file name $fl does not conform to SQL naming convention \n";
						}
					}
					else {
						echo "Incorrect CSV file: ($mime_type) $csvfile\n";
					}
				}
			}
		}
	}
	@closedir($dirhandle);
	asort($file_arr);
	if (count($file_arr) == 0) {
		echo "No CSV files found with file mask '$prg_option[FILE_MASK]' in $prg_option[CSV_FOLDER]\n"; exit(2);
	}
	
	// Create encoding list
	$encoding_arr = array();
	reset($file_arr);
	while (list($name, $file) = each($file_arr)) {
		// detect encoding with GNU file-5.03
		$encoding_arr[$name] = strtoupper(detectMimeType($file, 'ENCODING'));
		if ($encoding_arr[$name] != $prg_option['CHARSET']) {
			$fl = ansi2ascii($file);
			echo "CSV file $fl does not conform to $prg_option[CHARSET] encoding\n";
		}
	}
	
	// Create column list for each file
	$csv_arr = array();
	reset($file_arr);
	// Read each CSV file
	while (list($name, $file) = each($file_arr)) {
		$csvhandle = @fopen($file, "r");
		if(!$csvhandle) {
			echo "Could not read CSV file $file\n"; exit(2);
		}
		$rowcount = 0;
		$colarr = array();
		while (($buf = fgetcsv($csvhandle, $prg_option['MAX_ROWSIZE'], $prg_option['DELIMITED'], $prg_option['QUOTE'])) !== false) {
			if (fmod($rowcount, $prg_option['PI_COUNT']*10) == 0) { echo chr(46); }
			// truncate last field when empty
			if (trim($buf[count($buf)-1]) == '') {
				array_pop($buf);
			}
			// Read first line to detect columns
			if ($rowcount == 0) {
				// first row may contain UTF-8 BOM
				if ((substr($buf[0], 0, 3)) == hex2bin("efbbbf")) {
					$buf[0] = substr($buf[0], 3);
				}
				$colcnt = 0;
				reset($buf);
				foreach ($buf as $b) {
					$col = array();
					$col['name'] = ($prg_option['COLUMN_NAMES']) ? trim($b) : "column$colcnt";
					if (testDBMSNaming($col['name']) === false){
						echo "\nColumn ". ($colcnt+1) ." does not conform to SQL naming convention \n";
						$orgname = ($encoding_arr[$name] == 'UTF-8') ? utf8_decode($col['name']) : $col['name'];
						$col['description'] = "Original column name: '$orgname'";
						$col['name'] = "column$colcnt";
						$prg_option['CHECK_NAMES'] = false;
					}
					$col['type'] = 'INTEGER'; // preset with INTEGER
					$col['size'] = 0;
					$colarr[] = $col;
					$colcnt++;
				}
			}
			// Read entire file to detect column type
			if (!($prg_option['COLUMN_NAMES'] and $rowcount == 0)) {
				$colcnt = 0;
				reset($buf);
				foreach ($buf as $b) {
					if (!array_key_exists($colcnt, $colarr)) {
						$colarr[$colcnt]['name'] = "column$colcnt";
						$colarr[$colcnt]['type'] = 'INTEGER';;
						$colarr[$colcnt]['size'] = 0;
					}
					// Different stringlength in case of ISO-8859 or UTF-8
					$slb = ($prg_option['CHARSET'] == 'UTF-8') ? strlen(utf8_decode(trim($b))) : strlen(trim($b));
					if ($slb > $colarr[$colcnt]['size']) {
						$colarr[$colcnt]['size'] = $slb;
					}
					$bt = guessDataType($b);
					if ($order_of_datatype[$bt] > $order_of_datatype[$colarr[$colcnt]['type']]) {
						$colarr[$colcnt]['type'] = $bt;
					}
					$colcnt++;
				}
			}
			$rowcount++;
		}
		// Check for empty column names
		$csv_arr[$name] = $colarr;
		fclose($csvhandle);
	}

	// create database description according to torque.v4 XML model
	writeDBModel($file_arr, $csv_arr, basename($prg_option['CSV_FOLDER']));

	// write console message
	echo "\nNew XML database model written: ".ansi2ascii($wdir)."/no_db_model.xml\n";
	reset($file_arr);
	while (list($key, $val) = each($file_arr)) {
		$val = ansi2ascii($val);
		echo "  [$key] => $val\n";
	}
}

// -----------------------------------------------------------------------------
// write database description according to torque.v4 XML model
function writeDBModel($file_arr, $csv_arr, $dbname) {
global $prg_option, $wdir, $torque_schema, $static_torque_schema;
	$xmldata = "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
	$xmldata = $xmldata . "<database name=\"$dbname\" xmlns=\"http://db.apache.org/torque/4.0/templates/database\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://db.apache.org/torque/4.0/templates/database database-torque-4-0.xsd\">\n";
	reset($csv_arr);
	while (list($name, $columns) = each($csv_arr)) {
		$xmldata = $xmldata . "\t<table name=\"$name\" description=\"".xml_encode($file_arr[$name])."\">\n";
		reset($columns);
		while (list($name, $attributes) = each($columns)) {
			if ($attributes['type'] == 'VARCHAR') {
				$size = ($attributes['size'] == '') ? '' : " size=\"$attributes[size]\"";
				$xmldata = $xmldata . "\t\t<column name=\"$attributes[name]\" type=\"$attributes[type]\"$size";
			} else {
				$xmldata = $xmldata . "\t\t<column name=\"$attributes[name]\" type=\"$attributes[type]\"";
			}
			if (array_key_exists('description', $attributes)) {
				$xmldata = $xmldata . " description=\"$attributes[description]\"";
			}
			$xmldata = $xmldata . "/>\n";
		}
		$xmldata = $xmldata . "\t</table>\n";
	}
	$xmldata = $xmldata . "</database>\n";

	// write database description no_db_model.xml
	$dbmodel = "$wdir/no_db_model.xml";
	if (!file_put_contents("$dbmodel", utf8_encode($xmldata))) {
		echo "Could not write database description $dbmodel\n"; $prg_option['ERR'] = 8; return;
	}

	$prg_option['DB_MODEL'] = "$dbmodel";
	
	// validate database description no_db_model.xml according to torque v4.0
	file_put_contents("$prg_option[TMPDIR]/$torque_schema", $static_torque_schema);
	if (!validateXML("$prg_option[TMPDIR]/$torque_schema", $dbmodel, "'$dbmodel' is not a valid database schema according to Torque v4.0")) {
		exit(16);
	}
	unlink("$prg_option[TMPDIR]/$torque_schema");
}
// -----------------------------------------------------------------------------
// guess data type of string $buf, returns data type
function guessDataType($buf) {
	// == empty string
	if ($buf == '' or $buf == ' ') {
		return('VARCHAR');
	}
	// == INTEGER
	if (ctype_digit(ltrim($buf, '-'))) {
		if ($buf > 2147483647) {
			return('DECIMAL');
		} else {
			return('INTEGER');
		}
	}
	// == DECIMAL
	$b = strtr ($buf, ',', '.');
	if (is_numeric ($buf)) {
		if (stristr($buf, 'E') or stristr($buf, '-')) {
			return('FLOAT');
		} 
		else {
			return('DECIMAL');
		
		}
	}
	// == DATE
	$bd = convert2XMLdate($buf);
	if ($bd and $bd['type'] != 'UNIX native date format' and $bd['date'] != '') {
		return('DATE');
	}
	// == anything else is VARCHAR
	return('VARCHAR');
}
?>
