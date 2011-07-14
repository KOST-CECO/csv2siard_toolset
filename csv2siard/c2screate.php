<?
// Report all PHP errors
error_reporting(E_ALL);
// -----------------------------------------------------------------------------
// read XML database model into multi-dimensional array
function loadDatabaseModell(&$dbmod) {
global $prg_option;

	$dbmod = xml2ary(file_get_contents($prg_option['DB_MODEL']));
	return;
}
// -----------------------------------------------------------------------------
// create SIARD file header and content in TMP directory
function creatSIARDFolder(&$dbmod) {
global $prg_option, $prgdir, $siard_schema, $static_siard_schema, $siard2html, $static_siard2html;
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
	file_put_contents("$prg_option[SIARD_DIR]/header/metadata.xsd", $static_siard_schema);
	file_put_contents("$prg_option[SIARD_DIR]/header/metadata.xsl", $static_siard2html);
	//copy ("$prgdir/$siard_schema", "$prg_option[SIARD_DIR]/header/metadata.xsd");
	//copy ("$prgdir/$siard2html", "$prg_option[SIARD_DIR]/header/metadata.xsl");

	// Create SIARD content and folder
	mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema", 0777, true);
	
	$dbt = &$dbmod['database']['_c']['table'];
	$tbc = 0;
	// only one table
	if (!array_key_exists('0', $dbt)) {
		mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema/table$tbc", 0777, true);
		setTableOption($dbt,"folder", "table$tbc");
	} 
	// multiple tables
	else {
		reset($dbt);
		while (list($dbno, $tables) = each($dbt)) {
			mkdirPHP4("$prg_option[SIARD_DIR]/content/$defaultschema/table$tbc", 0777, true);
			setTableOption($dbt[$dbno],"folder", "table$tbc");
			$tbc++;
		}
	}
	return;
}
// -----------------------------------------------------------------------------
// read a CSV file and write a SIARD table
function creatSIARDTable(&$table) {
global $prg_option, $prgdir;

	$tablename = $table['_a']['name'];

	// check for CSV file and open it for reading
	$csvfile = $prg_option['CSV_FOLDER'].'/'.preg_replace('/([^\*]*)\*([^\*]*)/i', '${1}'.$tablename.'${2}', $prg_option['FILE_MASK']);
	setTableOption($table, 'localfile', $csvfile);

	if(!is_file($csvfile)) {
		echo "CSV file $csvfile not found\n"; $prg_option['ERR'] = 2; return;
	}
	
	// detect encoding with GNU file-5.03
	$commandline = 'CALL "'.$prgdir.'/file.exe" --mime-encoding -bm "'.$prgdir.'/magic.mgc" '.'"'.$csvfile.'"';
	$encoding = exec($commandline);
	echo "Process table (encoding: $encoding) $tablename .";
	
	$csvhandle = fopen($csvfile, "r");
	if(!$csvhandle) {
		echo "Could not read CSV file $csvfile\n"; $prg_option['ERR'] = 2; return;
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
	
	while (($buf = fgetcsv($csvhandle, $prg_option['MAX_ROWSIZE'], $prg_option['DELIMITED'], $prg_option['QUOTE'])) !== false) {
		if(count($buf) < $columcount) {
			echo "\nIncorrect CSV on line $rowcount in file $csvfile"; $prg_option['ERR'] = 4;
		}
		$b = array_chunk($buf, $columcount); $buffer = $b[0];
		// first row contains column names
		if ($rowcount == 1 and $prg_option['COLUMN_NAMES']) {
			processCSVColumnNames($buffer, $csvfile, $table, $buf);
		}
		// write SIARD table
		else {
			writeSIARDColumn($siardhandle, $buffer, $columcount, $rowcount, $table);
		}
		if (fmod($rowcount, $prg_option['PI_COUNT']) == 0) { echo '.'; }
		$rowcount++;
	}

	// write SIARD file XML footer
	writeSIARDFooter($siardhandle);
	
	// update table row counter
	$rowcount = ($prg_option['COLUMN_NAMES']) ? $rowcount-2 : $rowcount-1;
	setTableOption($table, 'rowcount', $rowcount);

	echo "\n";
	fclose($csvhandle);
	fclose($siardhandle);
}
// -----------------------------------------------------------------------------
// write a SIARD table schema file
function creatSIARDSchema(&$table) {
global $prg_option;

	// open SIARD file for writing
	$tablefolder = getTableOption($table, 'folder');
	$siardschema = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xsd";
	$siardhandle = fopen($siardschema, "w");
	if(!$siardhandle) {
		echo "Could not write SIARD table schema file $siardfile\n"; $prg_option['ERR'] = 8; return;
	}
	
	// write SIARD schema header
	writeSchemaHeader($siardhandle, $tablefolder);

	// write SIARD schema content
	writeSchemaContent($siardhandle, $table);

	// write SIARD schema footer
	writeSchemaFooter($siardhandle);

	fclose($siardhandle);
}

// -----------------------------------------------------------------------------
// validate a SIARD XML file with xmllint
function validateSIARDTable(&$table) {
global $prgdir, $prg_option;
//print_r($table);

	$tablefolder = getTableOption($table, 'folder');
	$tablefile = getTableOption($table, 'file');
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xml";
	$siardschema = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xsd";
	
	validateXML($siardschema, $siardfile, "'$tablefile' cannot be converted to a valid XML file");
}
// -----------------------------------------------------------------------------
// write SIARD metadata XML file
function createSIARDMetadata(&$dbmod) {
global $_SERVER, $prgdir, $prgname, $prg_option, $torque2siard, $static_torque2siard;

	//write torque.v4 XML datamodel
	$siardmetadata = "$prg_option[SIARD_DIR]/header/metadata.xml";
	$siardschema = "$prg_option[SIARD_DIR]/header/metadata.xsd";
	$xmldata = "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n" . ary2xml($dbmod);
	
	//convert torque.v4 XML datamodel to SIARD XML metadata file
	$xh = xslt_create();
	$parameters = array (
		'description'         => $prg_option['DESCRIPTION'],
		'archiver'            => $prg_option['ARCHIVED_BY'],
		'archiverContact'     => $prg_option['CONTACT'],
		'dataOwner'           => $prg_option['OWNER'],
		'dataOriginTimespan'  => $prg_option['TIMESPAN'],
		'producerApplication' => $prgname,
		'archivalDate'        => date("Y-m-d"),
		'messageDigest'       => 'MD5',
		'clientMachine'       => $_SERVER['COMPUTERNAME'],
		'databaseProduct'     => $prg_option['DB_TYPE'],
		'connection'          => 'file://'.$prg_option['CSV_FOLDER'],
		'databaseUser'        => $prg_option['SIARD_USER'],
		'databaseSchema'      => $prg_option['SIARD_SCHEMA']
);
	$arguments = array(
		'/_xml' => $xmldata,
		'/_xsl' => $static_torque2siard
	);
	$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments, $parameters);
	xslt_free($xh);
	if (!file_put_contents($siardmetadata, $result)) {
		echo "Could not write SIARD metadata XML file $siardmetadata\n"; $prg_option['ERR'] = 8; return;
	}

	
	//validate SIARD XML metadata file
	validateXML($siardschema, $siardmetadata, "'metadata.xml' is not a valid XML file");
}
// -----------------------------------------------------------------------------
// create SIARD ZIP file
function createSIARDFile( ) {
global $prgdir, $prg_option;

	//write torque.v4 XML datamodel
	$siarddir = "$prg_option[SIARD_DIR]/*";
	$zipfile = tempnam($prg_option['TMPDIR'], 'siard');
	@unlink($zipfile);
	$zipfile = $zipfile.'.zip';
	
	// create ZIP file
	$commandline = 'CALL "'.$prgdir.'/7z.exe" a -w'.' "'.$zipfile.'" '.' "'.$siarddir.'" ';
	exec($commandline, $result, $retval);
	if ($retval != 0) {
		echo "Temporary ZIP file could not be created: $zipfile"; return(-1);
	}
	// rename ZIP file to SIARD file
	rrmdir($prg_option['SIARD_DIR']);
	rename($zipfile, $prg_option['SIARD_FILE']);
}
?>
