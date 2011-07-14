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
include 'c2sconfig.php';
include 'c2screate.php';
include 'c2sconvert.php';
include 'c2sfunction.php';
include 'c2sxml.php';

// Versionen Liste
$version = '0.1';		// Read and check command-line arguments
$version = '0.2';		// Load Datamodel
$version = '0.3';		// Write SIARD XML files
$version = '0.4';		// Write SIARD metadata files
$version = '0.5';		// Read preference settings into metadata.xml
$version = '0.6';		// CSV encoding ISO-8859-1 and UTF-8

// global settings -------------------------------------------------------------
$wdir = '.'; $wdir = realpath($wdir);								// Arbeitsverzeichnis
$prgname = strtolower(basename($argv[0], '.exe'));	// ProgrammName
$prgname = basename($prgname, '.php');							// ProgrammName
$prgdir  = dirname(realpath(dirname($argv[0]).'/'.$prgname.'.exe'));		//Programmverzeichnis

$prg_option['ERR'] = 0;															// Programm optionen
$torqueschema   = '_database-torque-4-0.xsd';				// torque.v4 XML database schema
$siard_schema   = '_metadata.xsd';			// XML schema defines the structure of the metadata.xml in SIARD
$siard2html     = '_metadata.xsl';			// XS transformation: SIARD metadata.xml to xhtml (no function)
$torque2siard   = '_torque2siard.xsl';	//convert torque.v4 XML datamodel to SIARD XML metadata file
$prefs          = 'preferences.prefs';	// Preference file
$dbmod = array();												//nested array to hold the database model

$usage ="
       Usage :: $prgname.exe database csvpath siardfile [prefs]
    database :: database XML description according to torque.v4 schema
     csvpath :: path where to find the csv files
   siardfile :: SIARD file to be created
       prefs :: configuration file (default $prefs)

     version :: $version
";
// MAIN ------------------------------------------------------------------------
checkUtils();
readCommandLine();
readPreferences();
checkTMP();
checkProgramOptions();
loadDatabaseModell($dbmod);
creatSIARDFolder($dbmod);

// Print options
reset($prg_option);
while (list($key, $val) = each($prg_option)) {
	$val = ansi2ascii(utf8_decode($val));
	echo "  [$key] => $val\n";
}

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
	echo "\nSIARD file created: $prg_option[SIARD_FILE]\n";
	exit(0);
}
else {
	echo "\nNo valid SIARD file created\n";
	@unlink($prg_option['SIARD_FILE']);
	exit($prg_option['ERR']);
}
?>
