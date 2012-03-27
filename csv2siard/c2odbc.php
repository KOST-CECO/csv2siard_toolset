<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// read a ODCB table and write a SIARD table
function odbc2SIARDTable(&$table) {
global $prg_option, $prgdir;

	$tablename = $table['_a']['name'];

	if ($prg_option['CSV_FOLDER']=='ODBC') {
		$query = "select * from $tablename";
	}
	else {
		// check for ODBC specification file and open it for reading
		$reg = '#^'.Wildcard2Regex($prg_option['FILE_MASK']).'$#i';
		if ( $dirhandle = opendir($prg_option['CSV_FOLDER'])) {
			while (false !== ($file = readdir($dirhandle))) {
				if (preg_match($reg, $file) > 0 and ($file != "." && $file != "..") ) {
					$name = preg_replace($reg, '${1}${2}${3}${4}${5}',$file);
					if ($name == $tablename) {
						$sqlfile = $prg_option['CSV_FOLDER'].'/'.$file;
					}
				}
			}
			closedir($dirhandle);
		}
		if(!isset($sqlfile) or !is_file($sqlfile)) {
			echo "ODBC specification file for table $tablename not found\n"; $prg_option['ERR'] = 2; return;
		}
		setTableOption($table, 'localfile', xml_encode($sqlfile));
		
		// get sql query
		$query = trim(preg_replace('/\s[\s]+/',' ',strtr((file_get_contents($sqlfile)),"\x0A\x0D" , "  ")), '; ');
	}

	// open ODCB table
	echo "Process table (encoding: $prg_option[CHARSET]) $tablename ";
	$odbc_handle = @odbc_connect($prg_option['ODBC_DSN'], $prg_option['ODBC_USER'], $prg_option['ODBC_PASSWORD']);
	if (!$odbc_handle) {
		echo "Could not open ODBC connection '$prg_option[ODBC_DSN]' for user '$prg_option[ODBC_USER]'\n";
		if ($prg_option['VERBOSITY']) { echo odbc_errormsg()."\n"; }
		$prg_option['ERR'] = 2;
		return;
	}
	// execute a dummy odbc query to get typ of ODCB connection out of error message
	@odbc_exec($odbc_handle, 'SELECT * from ODCB');
	// set type and connection info
	$prg_option['DB_TYPE'] = xml_encode(utf8_encode(trim(preg_replace('/(\[.+\])(\[.+\]).+/','${2}', odbc_errormsg($odbc_handle)), '[]')));
	$prg_option['CONNECTION'] = 'odbc:'.$prg_option['ODBC_DSN'].' - query from file://'.xml_encode(utf8_encode($prg_option['CSV_FOLDER']));

	// execute query command to select table content
	$recordset = @odbc_exec($odbc_handle, $query);
	if (!$recordset) {
		echo "Error in SQL command '$query'\n";
		if ($prg_option['VERBOSITY']) { echo odbc_errormsg()."\n"; }
		$prg_option['ERR'] = 2;
		odbc_close($odbc_handle);
		return;
	}
	
	// open SIARD table XML file for writing
	$tablefolder = getTableOption($table, 'folder');
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xml";
	$siard_handle = fopen($siardfile, "w");
	if(!$siard_handle) {
		echo "Could not write SIARD table XML file $siardfile\n"; $prg_option['ERR'] = 8; odbc_close($odbc_handle); return;
	}
	// write SIARD file XML header
	writeSIARDHeader($siard_handle, $tablefolder);
	
	// get columnlist form database model
	$columnlist = getColumnNames($table);
	
	// read and process CSV file
	reset($table);
	$rowcount = 1;
	$columncount = (array_key_exists('_a', $table['_c']['column'])) ? 1 : count($table['_c']['column']);
	
	$bbbuf = '';
	// get columns form ODCB source by name
	if($prg_option['COLUMN_NAMES']) {
		while (odbc_fetch_row($recordset)) {
			$buf = array();
			foreach ($columnlist as $column) {
				$col = @odbc_result($recordset, $column);
				if ($col === false) {
					echo "\nColumne name '$column' not found in odbc query $sqlfile"; $prg_option['ERR'] = 4;
				}
				$buf[] = $col;
			}
			if ($prg_option['ERR']) { break; }
			$bbbuf = $bbbuf."\n".implode("; ", $buf);
			// write SIARD table
			writeSIARDColumn($siard_handle, $buf, $columncount, $rowcount, $table);
			
			if (fmod($rowcount, $prg_option['PI_COUNT']*10) == 0) { echo chr(46); }
			$rowcount++;
		}
	}
	// get columns form ODCB source by order
	else {
		while (odbc_fetch_into($recordset, $buf)) {
			if(count($buf) < $columncount) {
				echo "\nIncorrect columne count in odbc query $sqlfile"; $prg_option['ERR'] = 4;
				break;
			}
			// write SIARD table
			writeSIARDColumn($siard_handle, $buf, $columncount, $rowcount, $table);
			
			if (fmod($rowcount, $prg_option['PI_COUNT']*10) == 0) { echo chr(46); }
			$rowcount++;
		}
	}


	// write SIARD file XML footer
	writeSIARDFooter($siard_handle);
	
	// update table row counter
	setTableOption($table, 'rowcount', $rowcount-1);

	echo "\n";
	odbc_close($odbc_handle);
	fclose($siard_handle);
}
?>
