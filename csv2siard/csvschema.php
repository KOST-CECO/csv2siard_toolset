<?
// Report all PHP errors
error_reporting(E_ALL);

// kompilieren mit bamcompile.exe -c -e:php_xslt.dll  programm.php
// Achtung dlls in der kompilierten Version nicht mit 'dl' laden
dl('php_xslt.dll');
// dl('php_mime_magic.dll');
include 'c2sconfig.php';
//include 'c2screate.php';
//include 'c2sconvert.php';
include 'c2sfunction.php';
include 'c2sxml.php';
include 'c2snodbmodel.php';
include 'c2schema.php';
include 'c2stimedate.php';
//include 'zip.php';
include 'c2odbc.php';
include 'c2snodbodbc.php';

// global settings -------------------------------------------------------------
$wdir = getcwd();																		// Arbeitsverzeichnis
$prgname = strtolower(basename($argv[0], '.exe'));	// Programm Name
$prgname = basename($prgname, '.php');							// Programm Name
$prgdir  = realpath(dirname($argv[0]));							//Programmverzeichnis

$prg_option['ERR'] = 0;										// Programm optionen
$torque_schema  = '_torque-4.0.xsd';			// torque.v4 XML database schema
$siard_schema   = '_metadata-1.0.xsd';		// XML schema defines the structure of the metadata.xml in SIARD
$siard2html     = '_metadata-1.0.xsl';		// XS transformation: SIARD metadata.xml to xhtml (no function)
$torque2siard   = '_torque2siard.xsl';		// convert torque.v4 XML datamodel to SIARD XML metadata file
$torque2csvschema  = '_torque2csvschema.xsl';		// convert torque.v4 XML datamodel to SIARD XML metadata file
//loadSchema(); unloadSchema();						// load or unload file based XML schema
$prefs          = 'preferences.prefs';		// Preference file
$schema         = 'schema.ini';						// ODBC schema.ini file
$dbmod = array();													//nested array to hold the database model
$logfile = false;

// Read command line
if (!($argc == 3 or $argc == 2)) {
	log_echo("
       Usage :: csvschema.exe csvpath [prefs]
     csvpath :: path where to find csv files
       prefs :: configuration file (default) $prefs
");
	exit(1);
}

// reorder arguments: [1] => :NO_DB_MODEL [2] => csv folder [3] => dummy.siard [4] => preference file
if ($argc == 2) { $maninput = TRUE; }
if ($argc == 3) {
	$argc = 5; $argv[4] = $argv[2];
}
else {
	$argc = 5; $argv[4] = $prefs;
}
$argv[2] = $argv[1];
$argv[1] = ':NO_DB_MODEL='.sys_get_temp_dir().'/no_db_model.xml';
$argv[3] = 'dummy.siard';

// functions -------------------------------------------------------------------
function getParam($prompt_text, $prompt_array) {
	$stdin = fopen('php://stdin', 'r');
	do { 
		echo "$prompt_text (".implode(', ', $prompt_array)."): ";
		$input = strtoupper(trim(fgets($stdin, 1024)));
		if ($input == '') { $input = $prompt_array[0]; }
	} while(!in_array($input, $prompt_array));
	fclose($stdin);
	return($input);
}
// MAIN ------------------------------------------------------------------------
setLogfile();
checkUtils();
readCommandLine();
readPreferences();
checkTMP();
checkProgramOptions();
printDisclaimer();

// no prefs file -> manual input
if ($maninput) {
	$prg_option['FILE_MASK'] = '*.' . getParam("Specify file mask", array('CSV', 'TXT'));
	$prg_option['CHARSET'] = getParam("Specify character set", array('US-ASCII', 'ASCII', 'OEM', 'ANSI', 'ISO-8859-1', 'UTF-8'));
	$prg_option['DELIMITED'] = getParam("Specify column separator", array(';', '#', '$', 'COMMA', 'TAB'));
	$prg_option['DELIMITED'] = ($prg_option['DELIMITED'] == 'COMMA') ? ","  : $prg_option['DELIMITED'];
	$prg_option['DELIMITED'] = ($prg_option['DELIMITED'] == 'TAB')   ? "\t" : $prg_option['DELIMITED'];
	$prg_option['COLUMN_NAMES'] = ( getParam("Field names in the first row", array('YES', 'NO')) == 'YES') ? true : false;
}
createDBModel();
log_echo("\n");

//convert torque.v4 XML datamodel to ODBC schema.ini file
//$static_torque2csvschema = file_get_contents('_torque2csvschema.xsl');
$no_db_model = file_get_contents($prg_option['NO_DB_MODEL']);

switch ($prg_option['DELIMITED']) {
	case ",":
		$format = "CSVDelimited"; break;
	case "\t":
		$format = "TabDelimited"; break;
	default:
		$format = "Delimited($prg_option[DELIMITED])"; break;
}
$xh = xslt_create();
$parameters = array (
	'file_mask'     => str_replace('.sql', '', (str_replace('?', '', (str_replace('*', '', $prg_option['FILE_MASK']))))),
	'column_names'  => ($prg_option['COLUMN_NAMES']) ? 'True' : 'False',
	'delimited'     => $format,
	'charset'       => ($prg_option['CHARSET'] == 'OEM') ? 'OEM' : 'ANSI'
);
$arguments = array(
	'/_xml' => $no_db_model,
	'/_xsl' => $static_torque2csvschema
);
$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments, $parameters);
xslt_free($xh);
if (!file_put_contents($prg_option['CSV_FOLDER']."/$schema", $result)) {
	log_echo("Could not write schema.ini file $prg_option[CSV_FOLDER]/$schema\n"); $prg_option['ERR'] = 8; return;
}
log_echo("New CSV schema.ini written: $prg_option[CSV_FOLDER]/$schema\n");

exit(0);
?>
