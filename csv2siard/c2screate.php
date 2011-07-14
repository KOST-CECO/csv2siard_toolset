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
	eval('$dbm[\'DATABASE_STRUCTURE\'] = '.$result);
	
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

	// Create SIARD content
	mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema", 0777, true);
	$siardstructur = array();
	foreach ($dbm['DATABASE_STRUCTURE'] as $db) {
		$tbc = 0;
		foreach (array_keys($db) as $table) {
			mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema/table$tbc", 0777, true);
			$siardstructur[$defaultschema]["table$tbc"] = $table;
			$tbc++;
		}
	}
	$dbm['SIARD_STRUCTURE'] = $siardstructur;
	return;
}
// -----------------------------------------------------------------------------
// read CSV file and write SIARD table
function creatSIARDTable(&$table, $tablename, &$siardstructur) {
global $prg_option;

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
	$tablefolder = array_search($tablename, $siardstructur);
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xml";
	$siardhandle = fopen($siardfile, "w");
	if(!$siardhandle) {
		echo "Could not open SIARD file $siardfile\n"; $prg_option['ERR'] = true; return;
	}
	
	// write SIARD file XML header
	writeSIARDHeader($siardhandle, $tablefolder);
	
	// read and process CSV file
	$linecount = 1;
	$columcount = count($table);
	while (($buf = fgetcsv($csvhandle, 100000, $prg_option['DELIMITED'], $prg_option['QUOTE'])) !== false) {
		if(count($buf) < $columcount) {
			echo "Incorrect CSV on line $linecount in file $csvfile\n"; $prg_option['ERR'] = true;
		}
		$b = array_chunk($buf, $columcount); $buffer = $b[0];
		// first row contains column names
		if ($linecount == 1 and $prg_option['COLUMN_NAMES'] === true) {
			processCSVColumnNames($buffer, $csvfile, $tablename, $table);
		}
		else {
			writeSIARDColumn($siardhandle, $buffer, $columcount);
		}
		$linecount++;
	}

	// write SIARD file XML footer
	writeSIARDFooter($siardhandle);
	
	// update table linecounter
	print_r($dbm['SIARD_STRUCTURE']);
	
	fclose($csvhandle);
	fclose($siardhandle);
}
?>
