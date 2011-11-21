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
	if(!isset($csvfile)) {
		echo "CSV table $tablename not found\n"; $prg_option['ERR'] = 2; return;
	}

	setTableOption($table, 'localfile', xml_encode($csvfile));

	if(!is_file($csvfile)) {
		echo "CSV file $csvfile not found\n"; $prg_option['ERR'] = 2; return;
	}
	
	// detect encoding with GNU file-5.03
	$encoding = detectMimeType($csvfile, 'ENCODING');
	echo "Process table (encoding: $encoding) $tablename ";
	
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
		// file with EOF = SUB (dec 026 hex 0xA1)
		if(count($buf) == 1 and ord($buf[0]) == 26) {
			break;
		}
		elseif(count($buf) < $columcount) {
			if ($prg_option['CHECK_COLUMN']) {
				echo "\nIncorrect CSV on line $rowcount in file $csvfile"; $prg_option['ERR'] = 4;
			}
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
		if (fmod($rowcount, $prg_option['PI_COUNT']*10) == 0) { echo chr(46); }
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
	writeSchemaHeader($siardhandle, $tablefolder, $table);

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

	$tablefolder = getTableOption($table, 'folder');
	$tablefile = getTableOption($table, 'localfile');
	$siardfile = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xml";
	$siardschema = "$prg_option[SIARD_DIR]/content/$prg_option[SIARD_SCHEMA]/$tablefolder/$tablefolder.xsd";
	
	validateXML($siardschema, $siardfile, "'$tablefile' - convertion to XML file failed");
}
// -----------------------------------------------------------------------------
// write SIARD metadata XML file
function createSIARDMetadata(&$dbmod) {
global $_SERVER, $prgdir, $prgname, $version, $prg_option, $torque2siard, $static_torque2siard;

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
		'producerApplication' => "$prgname $version - Convert multiple CSV files to siard file",
		'archivalDate'        => date("Y-m-d"),
		'messageDigest'       => 'MD5',
		'clientMachine'       => $_SERVER['COMPUTERNAME'],
		'databaseProduct'     => $prg_option['DB_TYPE'],
		'connection'          => 'file://'.xml_encode(utf8_encode($prg_option['CSV_FOLDER'])),
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
// Walk through folder and add file to $ZIP file
function walkSIARDDir($ZIP, $name) {
	if (is_dir($name)) {
		$dh = opendir($name);
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != "..") {
				walkSIARDDir(&$ZIP, "$name/$file");
			}
		}
		closedir($dh);
	}
	$ZIP->addZipFile($name);
}

// -----------------------------------------------------------------------------
// create SIARD ZIP file
function createSIARDFile( ) {
global $wdir, $prgdir, $prg_option;

	//write torque.v4 XML datamodel
	$siarddir = "$prg_option[SIARD_DIR]/*";
	$zipfile = tempnam($prg_option['TMPDIR'], 'siard');
	@unlink($zipfile);
	$zipfile = $zipfile.'.zip';
	
	// create ZIP file with MD5 Hash
	echo "ZIP SIARD file ";
	chdir($prg_option['SIARD_DIR']);
	$ZIP = new ZipFile($zipfile);

	// Folder content in ZIP Datei einfügen
	walkSIARDDir(&$ZIP, "content");

	// MD5 berechnen
	$md5 = $ZIP->getMD5overPayload();
	$metadata = file_get_contents("header/metadata.xml");
	$metadata_md5 = preg_replace ('/\<messageDigest\>MD5.*\<\/messageDigest\>/','<messageDigest>MD5'.$md5.'</messageDigest>', $metadata);
	file_put_contents("header/metadata.xml", $metadata_md5);

	// Folder header in ZIP Datei einfügen
	$ZIP->addZipFile("header");
	$ZIP->addZipFile("header/metadata.xsd");
	// $ZIP->addZipFile("header/metadata.xsl");
	$ZIP->addZipFile("header/metadata.xml");
	$ZIP->closeZipFile();
	chdir($wdir);

	// rename ZIP file to SIARD file
	rrmdir($prg_option['SIARD_DIR']);
	rename($zipfile, $prg_option['SIARD_FILE']);
}
?>
