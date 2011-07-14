<?php 
error_reporting(E_ALL);
// create database model from scratch
function createDBModel(){
global $prg_option, $wdir, $prgdir, $torqueschema;

// Create CSV file list
	$file_arr = array();
	$reg = '#'.Wildcard2Regex($prg_option['FILE_MASK']).'#i';
	if ( $dirhandle = opendir($prg_option['CSV_FOLDER'])) {
		while (false !== ($file = readdir($dirhandle))) {
			if (preg_match($reg, $file) > 0) {
				$name = preg_replace($reg, '${1}${2}${3}${4}${5}',$file);
				if ($name != '') {
					$file_arr[$name] = $prg_option['CSV_FOLDER'].'/'.$file;
				}
			}
		}
	}
	closedir($dirhandle);
	asort($file_arr);
	if (count($file_arr) == 0) {
		echo "No CSV files found with file mask '$prg_option[FILE_MASK]' in $prg_option[CSV_FOLDER]\n"; exit(-1);
	}
	
	// Create column list for each file
	$csv_arr = array();
	reset($file_arr);
	// Read each CSV file
	while (list($name, $file) = each($file_arr)) {
		$csvhandle = fopen($file, "r");
		if(!$csvhandle) {
			echo "Could not read CSV file $file\n"; exit(-1);
		}
		// Read first line to detect columns
		if (($buf = fgetcsv($csvhandle, $prg_option['MAX_ROWSIZE'], $prg_option['DELIMITED'], $prg_option['QUOTE'])) !== false) {
			$colcnt = 0;
			$colarr = array();
			// Read entire file to detect column type *** TO BE DONE ***
			foreach ($buf as $b) {
				if ($b != '') {
					$type['name'] = ($prg_option['COLUMN_NAMES']) ? $b : "column$colcnt";
					$type['type'] = 'VARCHAR';
					$type['size'] = '';
					$colarr[] = $type;
					$colcnt++;
				}
			}
		}
		$csv_arr[$name] = $colarr;
		fclose($csvhandle);
		
	}

	// create database description according to torque.v4 XML model
	$dbname = basename($prg_option['CSV_FOLDER']);
	
	$xmldata = "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n";
	$xmldata = $xmldata . "<database name=\"$dbname\" xmlns=\"http://db.apache.org/torque/4.0/templates/database\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://db.apache.org/torque/4.0/templates/database _database-torque-4-0.xsd\">\n";
	reset($csv_arr);
	while (list($name, $columns) = each($csv_arr)) {
		$xmldata = $xmldata . "\t<table name=\"$name\">\n";
		reset($columns);
		while (list($name, $attributes) = each($columns)) {
			$size = ($attributes['size'] == '') ? '' : " size=\"$attributes[size]\"";
			$xmldata = $xmldata . "\t\t<column name=\"$attributes[name]\" type=\"$attributes[type]\"$size/>\n";
		}
		$xmldata = $xmldata . "\t</table>\n";
	}
	$xmldata = $xmldata . "</database>\n";

	// write database description no_db_model.xml
	$dbmodel = "$wdir/no_db_model.xml";
	file_put_contents("$dbmodel", utf8_encode($xmldata));
	$prg_option['DB_MODEL'] = "$dbmodel";
	
	// validate database description no_db_model.xml according to torque v4.0
	exec("$prgdir/xmllint.exe -noout -schema $prgdir/$torqueschema $dbmodel 2>$dbmodel.out", $result, $retval);
	if ($retval) {
		echo "'$dbmodel' is not a valid database schema according to Torque v4.0\n"; 
		$result = file_get_contents("$dbmodel.out");
		echo $result;
		exit(-1);
	}
	unlink("$dbmodel.out");

	// write console message
	echo "New XML database model written: $wdir/no_db_model.xml\n";
	reset($file_arr);
	while (list($key, $val) = each($file_arr)) {
		$val = ansi2ascii(utf8_decode($val));
		echo "  [$key] => $val\n";
	}
}
?>
