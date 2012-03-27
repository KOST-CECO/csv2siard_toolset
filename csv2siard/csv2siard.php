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

// Versionen Liste
$version = '0.1';		// Read and check command-line arguments
$version = '0.2';		// Load Datamodel
$version = '0.3';		// Write SIARD XML files
$version = '0.4';		// Write SIARD metadata files
$version = '0.5';		// Read preference settings into metadata.xml
$version = '0.6';		// CSV encoding ISO-8859-1 and UTF-8
$version = '0.7';		// check CSV column names
$version = '0.8';		// necessary exe and dll
$version = '0.9';		// NO_DB_MODEL implementieren
$version = '1.0';		// XSL and XSD include in program source
$version = '1.1';		// convert date/time fields to xs:date
$version = '1.2';		// check column type for NO_DB_MODEL 
$version = '1.3';		// correct field type setting
$version = '1.4';		// fix in NO_DB_MODEL and MS-Excel flavour
$version = '1.5';		// DBMS Naming restriction with NO_DB_MODEL
$version = '1.6';		// SIARD Datei mit MD5 Hash & SUB an Dateiende möglich
$version = '1.6.1';	// detectSUB fix
$version = '1.6.2';	// binary field processing implemented
$version = '1.6.3';	// enhanced xmllint output
$version = '1.6.4';	// date = 0 fixed
$version = '1.7';		// preference path fixed/non Unicode character/XML schema violation/consecutive spaces
$version = '1.7.1';	// ODBC
$version = '1.7.2';	// ODBC fetch by order and by name
$version = '1.7.3';	// keyword ODBC
$version = '1.7.4';	// ODBC Connection nur einmal öffnen


// global settings -------------------------------------------------------------
$wdir = getcwd();																		// Arbeitsverzeichnis
$prgname = strtolower(basename($argv[0], '.exe'));	// Programm Name
$prgname = basename($prgname, '.php');							// Programm Name
$prgdir  = dirname(realpath($argv[0]));							//Programmverzeichnis

$prg_option['ERR'] = 0;										// Programm optionen
$torque_schema  = '_torque-4.0.xsd';			// torque.v4 XML database schema
$siard_schema   = '_metadata-1.0.xsd';		// XML schema defines the structure of the metadata.xml in SIARD
$siard2html     = '_metadata-1.0.xsl';		// XS transformation: SIARD metadata.xml to xhtml (no function)
$torque2siard   = '_torque2siard.xsl';		//convert torque.v4 XML datamodel to SIARD XML metadata file
$prefs          = 'preferences.prefs';		// Preference file
$odbc_handle    = null;										// used if ODBC instead of CSV
$dbmod = array();													//nested array to hold the database model

// Error code meaning
//ERR 1: Misssing or false preferences
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
    database :: database description according to torque.v4 XML model or keyword NO_DB_MODEL
     csvpath :: path where to find csv files or keyword ODBC
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
checkUtils();

readCommandLine();

readPreferences();

checkTMP();

checkProgramOptions();

printDisclaimer();

if ($prg_option['DB_MODEL'] == 'NO_DB_MODEL') {
	// create database model from scratch
	createDBModel();
	echo "\n";
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
	echo "\nSIARD file created: ".ansi2ascii($prg_option['SIARD_FILE'])."\n";
	echo "Conversion completed\n";
}
elseif ($prg_option['UNICODE_EXTENDED'] and $prg_option['ERR'] == 64) {
	echo "\nSIARD file with XML ERRORS created: ".ansi2ascii($prg_option['SIARD_FILE'])."\n";
	echo "Conversion completed\n";
}
else {
	echo "\nNo SIARD file created\n";
	echo "Conversion aborted\n";
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
