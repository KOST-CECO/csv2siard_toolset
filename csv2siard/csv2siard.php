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
include 'c2sfunction.php';
include 'c2screate.php';
include 'c2sconvert.php';

// Versionen Liste
$version = '0.1';		// Read and check command-line arguments
$version = '0.2';		// Load Datamodel
$version = '0.3';		// Write SIARD XML files

// global settings -------------------------------------------------------------
$wdir = '.'; $wdir = realpath($wdir);								// Arbeitsverzeichnis
$prgname = strtolower(basename($argv[0], '.exe'));	// ProgrammName
$prgname = basename($prgname, '.php');							// ProgrammName
$prgdir = dirname(realpath(dirname($argv[0]).'/'.$prgname.'.exe'));		//Programmverzeichnis

$prg_option['ERR'] = false;													// Programm Optionen
$dbschema = 'database-torque-4-0.xsd';							// torque.v4 schema
$prefs = 'preferences.prefs';												// Preference file
$model2array = 'model2array.xsl';										// Transform XML Database to array
$dbm = array();				//nested array to hold database model and SIARD structure

$usage ="
       Usage :: $prgname.exe database csvpath siardfile prefs
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
loadDatabaseModell($dbm);
creatSIARDFolder($dbm);

print_r($prg_option);
foreach ($dbm['DATABASE_STRUCTURE'] as $db) {
	foreach (array_keys($db) as $table) {
		creatSIARDTable($db[$table], $table, $dbm['SIARD_STRUCTURE'][$prg_option['SIARD_SCHEMA']]);
		exit;
	}
}
exit(0);
?>
