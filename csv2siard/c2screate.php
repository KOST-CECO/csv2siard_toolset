<?
// Report all PHP errors
error_reporting(E_ALL);
// -----------------------------------------------------------------------------
// read XML database model into multi-dimensional array
function loadDatabaseModell(&$dbm) {
global $prg_option, $prgdir, $model2array;

	$xh = xslt_create();
	
	$arguments = array(
		'/_xml' => file_get_contents($prg_option['DB_SCHEMA']),
		'/_xsl' => file_get_contents("$prgdir/$model2array")
	);
	$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
	eval('$dbm = '.$result);

	xslt_free($xh);
	return;
}
// -----------------------------------------------------------------------------
// create SIARD file header and content in TMP directory
function creatSIARDFolder(&$dbm) {
global $prg_option, $prgdir;
$defaultschema = $prg_option['SIARD_SCHEMA'];
$folderstructur ="
    ├───header
    │       metadata.xsd
    │       metadata.xsl
    │       metadata.xml
    └───content
        └───schema0
            ├───table0
            │       table0.xsd
            │       table0.xml
            └───table1
                    table1.xsd
                    table1.xml
";
	// Create temporary SIARD folder
	$prg_option['SIARD_DIR'] = $prg_option['TMPDIR'].'/'.basename($prg_option['SIARD_FILE']);
	rrmdir("$prg_option[SIARD_DIR]");
	
	// Create SIARD header
	mkdirPHP4("$prg_option[SIARD_DIR]/header", 0777, true);
	// for convenience digestType: "(|(MD5|SHA-1).*)" => "(MD5.+|SHA-1.+)*"
	copy ("$prgdir/_metadata.xsd", "$prg_option[SIARD_DIR]/header/metadata.xsd");
	copy ("$prgdir/_metadata.xsl", "$prg_option[SIARD_DIR]/header/metadata.xsl");

	// Create SIARD content and folder
	mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema", 0777, true);
	$siardstructur = array();

	foreach ($dbm as $dbname => $tables) {
		$tbc = 0;
		foreach (array_keys($tables) as $tablename) {
			mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema/table$tbc", 0777, true);
			$dbm[$dbname][$tablename]['$$$_folder_name'] = "table$tbc";
			$tbc++;
		}
	}
	return;
}
// -----------------------------------------------------------------------------
// read CSV Files according DB Modell and create SIARD tables
function processDatabaseModell(&$dbm) {
	foreach ($dbm as $dbname => $tables) {
		foreach ($tables as $tablename => $table) {
			//No assignment of foreach arrays by reference in PHP 4
			creatSIARDTable($dbm[$dbname][$tablename], $tablename);
			creatSIARDSchema($dbm[$dbname][$tablename], $tablename);
		}
	}
}
// -----------------------------------------------------------------------------
// read a CSV file and write a SIARD table
function creatSIARDTable(&$table, $tablename) {
global $prg_option;
echo "Process table $tablename...\n";
	// check for CSV file and open it for reading
	$csvfile = $prg_option['CSV_FOLDER'].'/'.preg_replace('/([^\*]*)\*([^\*]*)/i', '${1}'.$tablename.'${2}', $prg_option['FILE_MASK']);
	if(!is_file($csvfile)) {
		echo "CSV file $csvfile not found\n"; $prg_option['ERR'] = true; return;
	}
	$csvhandle = fopen($csvfile, "r");
	if(!$csvhandle) {
		echo "Could not read CSV file $csvfile\n"; $prg_option['ERR'] = true; return;
	}
	
	// open SIARD file for writing
	$tablefolder = $table['$$$_folder_name'];
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xml";
	$siardhandle = fopen($siardfile, "w");
	if(!$siardhandle) {
		echo "Could not open SIARD xml file $siardfile\n"; $prg_option['ERR'] = true; return;
	}
	
	// write SIARD file XML header
	writeSIARDHeader($siardhandle, $tablefolder);
	
	// read and process CSV file
	$rowcount = 1;
	$columcount = count($table);
	while (($buf = fgetcsv($csvhandle, 100000, $prg_option['DELIMITED'], $prg_option['QUOTE'])) !== false) {
		if(count($buf) < $columcount) {
			echo "Incorrect CSV on line $rowcount in file $csvfile\n"; $prg_option['ERR'] = true;
		}
		$b = array_chunk($buf, $columcount); $buffer = $b[0];
		// first row contains column names
		if ($rowcount == 1 and $prg_option['COLUMN_NAMES']) {
			processCSVColumnNames($buffer, $csvfile, $tablename, $table);
		}
		else {
			writeSIARDColumn($siardhandle, $buffer, $columcount);
		}
		$rowcount++;
	}

	// write SIARD file XML footer
	writeSIARDFooter($siardhandle);
	
	// update table row counter
	$table['$$$_row_count'] = ($prg_option['COLUMN_NAMES']) ? $rowcount-2 : $rowcount-1;
//print_r($table);

	fclose($csvhandle);
	fclose($siardhandle);
}
// -----------------------------------------------------------------------------
// write a SIARD Schema file
function creatSIARDSchema(&$table, $tablename) {
global $prg_option;
	// open SIARD file for writing
	$tablefolder = $table['$$$_folder_name'];
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xsd";
	$siardhandle = fopen($siardfile, "w");
	if(!$siardhandle) {
		echo "Could not open SIARD schema file $siardfile\n"; $prg_option['ERR'] = true; return;
	}
	
	// write SIARD schema header
	writeSchemaHeader($siardhandle, $tablefolder);



	// write SIARD schema footer
	writeSchemaFooter($siardhandle);

	fclose($siardhandle);
}
?>
