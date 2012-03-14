<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// read a ODCB table and write a SIARD table
function odbc2SIARDTable(&$table) {
global $prg_option, $prgdir;

	$tablename = $table['_a']['name'];

	// check for CSV file and open it for reading
	//$csvfile = $prg_option['CSV_FOLDER'].'/'.preg_replace('/([^\*]*)\*([^\*]*)/i', '${1}'.$tablename.'${2}', $prg_option['FILE_MASK']);
	$reg = '#^'.Wildcard2Regex($prg_option['FILE_MASK']).'$#i';
	if ( $dirhandle = opendir($prg_option['CSV_FOLDER'])) {
		while (false !== ($file = readdir($dirhandle))) {
			if (preg_match($reg, $file) > 0 and ($file != "." && $file != "..") ) {
				$name = preg_replace($reg, '${1}${2}${3}${4}${5}',$file);
				if ($name == $tablename) {
					$csvfile = $prg_option['CSV_FOLDER'].'/'.$file;
				}
			}
		}
		closedir($dirhandle);
	}
	if(!isset($csvfile) or !is_file($csvfile)) {
		echo "ODBC specification file $tablename not found\n"; $prg_option['ERR'] = 2; return;
	}

	//TODO
	setTableOption($table, 'localfile', xml_encode($csvfile));
	
	echo "Process table $tablename ";
	$odbchandle = @odbc_connect($prg_option['ODBC_DSN'], $prg_option['ODBC_USER'], $prg_option['ODBC_PASSWORD']);
	if (!$odbchandle) {
		echo "Could not open ODBC connection '$prg_option[ODBC_DSN]' for user '$prg_option[ODBC_USER]'\n"; $prg_option['ERR'] = 2; return;
	}
	$sql = rtrim(file_get_contents($csvfile), ';');
	$recordset = odbc_exec($odbchandle, $sql);
	if (!$recordset) {
		echo "Error in SQL '$sql'\n"; $prg_option['ERR'] = 2; return;
	}
	
	// open SIARD table XML file for writing
	$tablefolder = getTableOption($table, 'folder');
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xml";
	$siardhandle = fopen($siardfile, "w");
	if(!$siardhandle) {
		echo "Could not write SIARD table XML file $siardfile\n"; $prg_option['ERR'] = 8; return;
	}
	// write SIARD file XML header
	writeSIARDHeader($siardhandle, $tablefolder);
	
	// read and process CSV file
	reset($table);
	$rowcount = 1;
	$columcount = (array_key_exists('_a', $table['_c']['column'])) ? 1 : count($table['_c']['column']);
	
	while (odbc_fetch_into($recordset, $buf)) {
		if(count($buf) != $columcount) {
			echo "\nIncorrect columne count in table $csvfile"; $prg_option['ERR'] = 4;
		}

		// write SIARD table
		writeSIARDColumn($siardhandle, $buf, $columcount, $rowcount, $table);
		
		if (fmod($rowcount, $prg_option['PI_COUNT']*10) == 0) { echo chr(46); }
		$rowcount++;
	}

	// write SIARD file XML footer
	writeSIARDFooter($siardhandle);
	
	// update table row counter
	setTableOption($table, 'rowcount', $rowcount-1);

	echo "\n";
	odbc_close($odbchandle);
	fclose($siardhandle);
}

?>
