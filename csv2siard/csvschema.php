<?
// Report all PHP errors
error_reporting(E_ALL);

// kompilieren mit bamcompile.exe -c -e:php_xslt.dll  programm.php
// Achtung dlls in der kompilierten Version nicht mit 'dl' laden
dl('php_xslt.dll');
// dl('php_mime_magic.dll');
include 'c2sconfig.php';
include 'c2sfunction.php';
include 'c2sxml.php';
include 'c2snodbmodel.php';
include 'c2schema.php';
include 'c2stimedate.php';

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
//loadSchema(); unloadSchema();						// load or unload file based XML schema
$prefs          = 'preferences.prefs';		// Preference file
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
if ($argc == 3) {
	$argc = 5; $argv[4] = $argv[2];
}
else {
	$argc = 5; $argv[4] = $prefs;
}
$argv[2] = $argv[1];
$argv[1] = ':NO_DB_MODEL';
$argv[3] = 'dummy.siard';

// MAIN ------------------------------------------------------------------------
setLogfile();
checkUtils();
readCommandLine();
readPreferences();
checkTMP();
checkProgramOptions();
printDisclaimer();

$prg_option['NO_DB_MODEL'] = $prg_option['TMPDIR'].'/no_db_model.xml';
createDBModel();
log_echo("\n");

unloadSchema();

exit(0);
?>
