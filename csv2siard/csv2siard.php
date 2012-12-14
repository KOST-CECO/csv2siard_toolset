<?
/*******************************************************************************
Copyright (C) 2009 by 
Koordinationsstelle für dauerhafte Archivierung elektronischer Unterlagen

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*******************************************************************************/

// Report all PHP errors
error_reporting(E_ALL);
//xdebug_start_trace("trace.out");
//phpinfo(INFO_ALL);
// phpinfo(INFO_ENVIRONMENT);

// kompilieren mit bamcompile.exe -c -e:php_xslt.dll  programm.php
// Achtung dlls in der kompilierten Version nicht mit 'dl' laden
dl('php_xslt.dll');
// dl('php_mime_magic.dll');
include 'c2sconfig.php';
include 'c2screate.php';
include 'c2sconvert.php';
include 'c2sfunction.php';
include 'c2sxml.php';
include 'c2snodbmodel.php';
include 'c2schema.php';
include 'c2stimedate.php';
include 'zip.php';
include 'c2odbc.php';
include 'c2snodbodbc.php';

// Versionen Liste
$version = '1.7';		// preference path fixed/non Unicode character/XML schema violation/consecutive spaces
$version = '1.7.1';	// ODBC
$version = '1.7.2';	// ODBC fetch by order and by name
$version = '1.7.3';	// keyword ODBC
$version = '1.7.4';	// Using only one ODBC Connection
$version = '1.7.5';	// Documentation: creating SIARD file using ODBC Connection
$version = '1.7.6';	// UTF-8 Field size corrected
$version = '1.7.7';	// remove BOM from head of UTF-8 file
$version = '1.7.8';	// fix minor MS-Excel problems and finish DOCU
$version = '1.7.9';	// NO_DB_MODEL on ODBC Connection
$version = '1.8';		// Update documentation
$version = '1.8.1';	// SQL error message enhancement
$version = '1.8.2';	// SQL_CUR_USE_ODBC and ODBC section in php.ini
$version = '1.8.3';	// odbcheck added
$version = '1.8.4';	// SQL_CUR_USE_DRIVER statt SQL_CUR_USE_ODBC
$version = '1.8.5';	// table.xsd: minOccurs="0" maxOccurs="unbounded" [-> ROW_COUNT=TRUE]
$version = '1.8.5';	// look for 'preferences.prefs' first in CWD then Install DIR
$version = '1.8.6';	// Option :LOG_FILE=fname for GUI

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
$odbc_handle    = null;										// used if ODBC instead of CSV
$dbmod = array();													//nested array to hold the database model
$logfile = false;

// Error code meaning
//ERR 1: Misssing or false parameters
//ERR 2: CSV file not found
//ERR 2: Could not read CSV file
//ERR 2: No CSV files found with specified file mask
//ERR 4: Incorrect CSV on line N
//ERR 4: To many columns in CSV file
//ERR 8: Could not write SIARD table XML file
//ERR 8: Could not write SIARD table schema file
//ERR 8: Could not write SIARD metadata XML file
//ERR 16: Not a valid database schema according to Torque v4.0
//ERR 16: Could not write database description no_db_model.xml
//ERR 32: Column in database model is missing or not confirm with CSV file
//ERR 32: Column type convertion failed
//ERR 32: Required restriction violated
//ERR 64: SIARD XML Schema Validation failed

$usage ="
       Usage :: $prgname.exe database csvpath siardfile [prefs]
    database :: database description according to torque.v4 XML model or keyword :NO_DB_MODEL
     csvpath :: path where to find csv files or keyword :ODBC
   siardfile :: SIARD file to be created
       prefs :: configuration file (default $prefs)

     version :: $version
";

$disclaimer = "
$prgname v $version, Copyright (C) 2011 Martin Kaiser (KOST-CECO)
This program comes with ABSOLUTELY NO WARRANTY.
This is free software, and you are welcome to redistribute it under certain conditions; 
see GPL-2.0_COPYING.txt for details.
";

// MAIN ------------------------------------------------------------------------
setLogfile();

checkUtils();

readCommandLine();

readPreferences();

checkTMP();

checkProgramOptions();

printDisclaimer();

if ($prg_option['DB_MODEL'] == 'NO_DB_MODEL') {
	// create database model from scratch
	createDBModel();
	log_echo("\n");
}

loadDatabaseModell($dbmod);

creatSIARDFolder($dbmod);

$dbt = &$dbmod['database']['_c']['table'];
// only one table
if (!array_key_exists('0', $dbt)) {
	// no assignment by reference in PHP 4 in foreach loops
	creatSIARDTable($dbt);
	creatSIARDSchema($dbt);
	validateSIARDTable($dbt);
} 
else {
	// multiple tables
	reset($dbt);
	while (list($dbno, $table) = each($dbt)) {
		// no assignment by reference in PHP 4 in foreach loops
		creatSIARDTable($dbt[$dbno]);
		creatSIARDSchema($dbt[$dbno]);
		validateSIARDTable($dbt[$dbno]);
	}
}
createSIARDMetadata($dbmod);
createSIARDFile();

if ($prg_option['ERR'] == 0) {
	log_echo("\nSIARD file created: ".ansi2ascii($prg_option['SIARD_FILE'])."\n");
	log_echo("Conversion completed\n");
}
elseif ($prg_option['UNICODE_EXTENDED'] and $prg_option['ERR'] == 64) {
	log_echo("\nSIARD file with XML ERRORS created: ".ansi2ascii($prg_option['SIARD_FILE'])."\n");
	log_echo("Conversion completed\n");
}
else {
	log_echo("\nNo SIARD file created\n");
	log_echo("Conversion aborted\n");
	@unlink($prg_option['SIARD_FILE']);
}

// clean up tmp-directory, remove PHP program-files
$tmpdir = sys_get_temp_dir();
$handle = opendir($tmpdir);
while (false !== ($file = readdir($handle))) {
	if (preg_match('/^php.+\.tmp$/', $file)) {
		@unlink("$tmpdir/$file");
	}
}
closedir($handle);

exit($prg_option['ERR']);
?>
