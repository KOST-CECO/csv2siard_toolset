<?
// Report all PHP errors
error_reporting(E_ALL);

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
	$prg_option['DB_SCHEMA'] = $database;

	// check folder with csv files
	$csvpath = str_replace('\\', '/', realpath($argv[2]));
	if (!is_dir($csvpath)) {
		echo "'$argv[2]' is not a valid path\n"; exit(-1);
	}
	$prg_option['CSV_FOLDER'] = $csvpath;
	
	// check for existing SIARD file
	$siardbase = basename($argv[3]);
	$siarddir = realpath(dirname($argv[3]));
	$siardfile = str_replace('\\', '/', "$siarddir/$siardbase");

	if (!is_dir($siarddir)) {
		$siarddir = dirname($argv[3]);
		echo "Folder $siarddir for SIARD file $siardbase is missing\n"; exit(-1);
	}
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
	$prg_option['DELIMITED'] = ';';
	$prg_option['QUOTE'] = '"';
	$prg_option['COLUMN_NAMES'] = true;
	$prg_option['CHARSET'] = 'ISO-8859-1';						// default character-set
	$prg_option['FILE_MASK'] = '*.dat';
	$prg_option['CHECK_FIELD_TYPE'] = false;
	$prg_option['CHECK_DATABASE_INTEGRITY'] = false;
	$prg_option['TMPDIR'] = sys_get_temp_dir();				// default temp dir
	$prg_option['SIARD_USER'] = 'admin';							// default user
	$prg_option['SIARD_SCHEMA'] = 'schema0';					// default schema


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
			if (strcasecmp($val, 'true') == 0) { $prg_option[$key] = true; }
			elseif (strcasecmp($val, 'false') == 0) { $prg_option[$key] = false; }
			elseif ($val == '\t') { $prg_option[$key] = "\t"; }
			else { $prg_option[$key] = $val; }
		}
	}
}

// check utility programms  ----------------------------------------------------
function checkUtils() {
global $prg_option;
// Libraries
	if ((@md5_file("expat.dll") != '3e860d331271c23e46efb1ba019701d1')
	and (@md5_file("iconv.dll") != 'e4341fb69cb24cf63e9063f4d8967ebf')
	and (@md5_file("php_xslt.dll") != 'f172b4d0ee4dbbe2e73d4516729a4cd3')
	and (@md5_file("sablot.dll") != '89f212d20a8b7b9a30b1e3284627febf')) {
		echo "Some libraries are missing or corrupt\n"; exit(-1);
	}
// Programs
	elseif (@md5_file("xmllint.exe") != '5e11a78328e7cde3206f15fb8c79437c'){
		echo "Program xmllint.exe is missing, corrupt or wrong version (libxml version 20630)\n"; exit(-1);
	}
}

// check  TMP directory --------------------------------------------------------
function checkTMP() {
global $prg_option;
// TMP directory
	$tmpdir = realpath($prg_option['TMPDIR']);

	if (!is_dir($tmpdir)) {
		$tmpdir = $prg_option['TMPDIR'];
		echo "No valid TMP directory: $tmpdir\n"; exit(-1);
	}
	elseif (!@touch("$tmpdir/$prgname.tmp")) {
		echo "You may not have appropriate rights on TMP directory: $tmpdir\n"; exit(-1);
	}
	@unlink("$tmpdir/$prgname.tmp");
	$prg_option['TMPDIR'] = str_replace('\\', '/', $tmpdir);
}
?>
