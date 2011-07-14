<?
// Report all PHP errors
error_reporting(E_ALL);
//phpinfo(INFO_ALL);
// phpinfo(INFO_ENVIRONMENT);

// kompilieren mit bamcompile.exe -c -e:php_xslt.dll  programm.php
// Achtung dlls in der kompilierten Version nicht mit 'dl' laden
dl('php_xslt.dll');
include 'c2sconfig.php';

// Versionen Liste
$version = '0.1';		// Read and check command-line arguments

// global settings -------------------------------------------------------------
$wdir = '.'; $wdir = realpath($wdir);								// Arbeitsverzeichnis
$prgname = strtolower(basename($argv[0], '.exe'));	// ProgrammName
$prgname = basename($prgname, '.php');							// ProgrammName
$prgdir = dirname(realpath(dirname($argv[0]).'/'.$prgname.'.exe'));		//Programmverzeichnis

$prg_option = array();															// Programm Optionen
$dbschema = 'database-torque-4-0.xsd';							// torque.v4 schema
$prefs = 'preferences.prefs';												// Preference file

$usage ="
       Usage :: $prgname.exe database csvpath siardfile prefs
    database :: database XML description according to torque.v4 schema
     csvpath :: path where to find the csv files
   siardfile :: SIARD file to be created
       prefs :: configuration file (default $prefs)

     version :: $version
";

// -----------------------------------------------------------------------------
// Ersatz für PHP5 Funktion 'file_put_contents'
if (!function_exists('file_put_contents')) {
	function file_put_contents($fname, $data) {
		$f = @fopen($fname, 'w');
		if (!$f) {
			return false;
		} else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

// read and check command-line arguments ---------------------------------------
function readCommandLine() {
global $argc, $argv, $usage, $wdir, $prgdir, $dbschema, $prg_option;
	if ($argc < 4) {
		echo $usage; exit(-1);
	}
	
	// check database description XML file
	$database = str_replace('\\', '/', realpath($argv[1]));
	if (!is_file($database)) {
		echo "Database description $argv[1] not found\n"; exit(-1);
	}
	exec("$prgdir/xmllint.exe -noout -schema $prgdir/$dbschema $database 2>$database.out", $result, $database_retval);
	if ($database_retval) {
		echo "'$argv[1]' does not validate with torque.v4 schema\n"; 
		$result = file_get_contents("$database.out");
		echo $result;
		exit(-1);
	}
	unlink("$database.out");
	$prg_option['XML_DB'] = $database;

	// check folder with csv files
	$csvpath = str_replace('\\', '/', realpath($argv[2]));
	if (!is_dir($csvpath)) {
		echo "'$argv[2]' is not a valid path\n"; exit(-1);
	}
	$prg_option['CSV_FOLDER'] = $csvpath;
	
	// check for existing SIARD file
	$siardfile = str_replace('\\', '/', "$wdir/$argv[3]");
	if (strtoupper(substr($siardfile, -6)) != ".SIARD") {
		echo "SIARD file $argv[3] must have file extension '.siard'\n"; exit(-1);
	}
	if (is_file($siardfile)) {
		echo "SIARD file $argv[3] already exists\n"; exit(-1);
	}
	$prg_option['SIARD_FILE'] = $siardfile;
}

// read and check preferences --------------------------------------------------
function readPreferences() {
global $argc, $argv, $wdir, $prgdir, $prefs, $prg_option;

	// set default preferences
	$prg_option['PREFS']['DELIMITED'] = ';';
	$prg_option['PREFS']['QUOTE'] = '"';
	$prg_option['PREFS']['COLUMN_NAMES'] = true;
	$prg_option['PREFS']['CHARSET'] = 'ISO 8859-1';
	$prg_option['PREFS']['FILE_EXTENSION'] = '.dat';
	$prg_option['PREFS']['CHECK_FIELD_TYPE'] = false;
	$prg_option['PREFS']['CHECK_DATABASE_INTEGRITY'] = false;

	// specific preference file
	if ($argc == 5) {
		$prefsfile = str_replace('\\', '/', "$wdir/$argv[4]");
		if (!is_file($prefsfile)) {
			echo "Preference file $prefsfile not found\n"; exit(-1);
		}
	} 
	// default preference file
	else {
		$prefsfile = str_replace('\\', '/', "$prgdir/$prefs");
		if (!is_file($prefsfile)) {
			echo "No preference file found, default settings are used\n"; return;
		}
	}
	// read preference file and set preferences
	$prefs = file($prefsfile, 'FILE_IGNORE_NEW_LINES' | 'FILE_SKIP_EMPTY_LINES');
	foreach ($prefs as $pref) {
		if (substr($pref, 0, 1) != '#') {
			$key = trim(strtok($pref, "=#"));
			$val = trim(strtok("=#"));
			if (strcasecmp($val, 'true') == 0) { $prg_option['PREFS'][$key] = true; }
			elseif (strcasecmp($val, 'false') == 0) { $prg_option['PREFS'][$key] = false; }
			elseif ($val == '\t') { $prg_option['PREFS'][$key] = "\t"; }
			else { $prg_option['PREFS'][$key] = $val; }
		}
	}
}

// check utility programms -----------------------------------------------------
function checkUtils() {
// Libraries prüfen
	if ((@md5_file("expat.dll") != '3e860d331271c23e46efb1ba019701d1')
	and (@md5_file("iconv.dll") != 'e4341fb69cb24cf63e9063f4d8967ebf')
	and (@md5_file("php_xslt.dll") != 'f172b4d0ee4dbbe2e73d4516729a4cd3')
	and (@md5_file("sablot.dll") != '89f212d20a8b7b9a30b1e3284627febf')) {
		processMessage("Some libraries are missing or corrupt", true); exit(-1);
	}
	elseif (@md5_file("xmllint.exe") != '5e11a78328e7cde3206f15fb8c79437c'){
		processMessage("Program xmllint.exe is missing, corrupt or wrong version (libxml version 20630)", true); exit(-1);
	}
}

// MAIN ------------------------------------------------------------------------
checkUtils();

readCommandLine();

readPreferences();

print_r($prg_option);


exit(0);

?>
