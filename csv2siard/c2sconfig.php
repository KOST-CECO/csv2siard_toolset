<?
// Report all PHP errors
error_reporting(E_ALL);

// Print disclaimer and preferences --------------------------------------------
function printDisclaimer() {
global $prg_option, $prgname, $version, $disclaimer;

	echo $disclaimer;
	
	echo "\nPreferences:\n";
	reset($prg_option);
	while (list($key, $val) = each($prg_option)) {
		$val = ansi2ascii(utf8_decode($val));
		echo "  [$key] => $val\n";
	}
	echo "\n";
}

// read and check command-line arguments ---------------------------------------
function readCommandLine() {
global $argc, $argv, $usage, $wdir, $prgdir, $torque_schema, $static_torque_schema, $prg_option;
	if ($argc < 4) {
		echo $usage; exit(1);
	}
	
	// check database description XML file
	if (strcasecmp($argv[1], 'NO_DB_MODEL') == 0) {
		$prg_option['DB_MODEL'] = 'NO_DB_MODEL';
	}
	else {
		$dbmodel = str_replace('\\', '/', realpath($argv[1]));
		if (!is_file($dbmodel)) {
			echo "Database description $argv[1] not found\n"; exit(1);
		}
		$temp = sys_get_temp_dir();
		file_put_contents("$temp/$torque_schema", $static_torque_schema);
		if (!validateXML("$temp/$torque_schema", $dbmodel, "'$argv[1]' is not a valid database schema according to Torque v4.0")) {
			exit(16);
		}
		unlink("$temp/$torque_schema");
		$prg_option['DB_MODEL'] = $dbmodel;
	}
	// check folder with csv files
	$csvpath = str_replace('\\', '/', realpath($argv[2]));
	if (!is_dir($csvpath)) {
		echo "'$argv[2]' is not a valid path\n"; exit(1);
	}
	$prg_option['CSV_FOLDER'] = $csvpath;
	
	// check for existing SIARD file
	$siardbase = basename($argv[3]);
	$siarddir = realpath(dirname($argv[3]));
	$siardfile = str_replace('\\', '/', "$siarddir/$siardbase");

	if (!is_dir($siarddir)) {
		$siarddir = dirname($argv[3]);
		echo "Folder $siarddir for SIARD file $siardbase is missing\n"; exit(1);
	}
	if (strtoupper(substr($siardfile, -6)) != ".SIARD") {
		echo "SIARD file $argv[3] must have file extension '.siard'\n"; exit(1);
	}
	if (is_file($siardfile)) {
		echo "SIARD file $argv[3] already exists\n"; exit(1);
	}
	$prg_option['SIARD_FILE'] = $siardfile;
}

// read and check preferences --------------------------------------------------
function readPreferences() {
global $argc, $argv, $wdir, $prgdir, $prefs, $prg_option;
	// parameter settings
	// $prg_option['DB_MODEL'];								// database description according to torque.v4 XML model or NO_DB_MODEL
	// $prg_option['CSV_FOLDER'];							// path where to find the csv files
	// $prg_option['SIARD_FILE'];							// SIARD file to be created
	// set default preferences
	$prg_option['DELIMITED'] = ';';						// CSV column separator
	$prg_option['QUOTE'] = '"';								// Optional field Quotation
	$prg_option['COLUMN_NAMES'] = true;				// First row contains column names
	$prg_option['CHECK_NAMES'] = true;				// Check column names with first row
	$prg_option['CHARSET'] = 'ISO-8859-1';		// default character-set (choose: ASCII, OEM, ANSI, ISO-8859-1 and UTF-8)
	$prg_option['FILE_MASK'] = '*.dat';				// Wild card is replaced with table name or converted to tablename
	$prg_option['CHECK_COLUMN'] = true;				// Check column count, not applicable with MS-Exel CSV
	$prg_option['CHECK_DATABASE_INTEGRITY'] = false;	// Not implemented yet
	$prg_option['TMPDIR'] = sys_get_temp_dir();// default temp dir
	$prg_option['PI_COUNT'] = 100;						// progress indicator per line processed
	$prg_option['MAX_ROWSIZE'] = 100000;			// maximal CSV row size
	// Optional content settings
	$prg_option['DESCRIPTION'] = '';					// Database description
	$prg_option['ARCHIVED_BY'] = '';					// Database archived by
	$prg_option['CONTACT'] = '';							// Archivist's contact details
	$prg_option['OWNER'] = '(...)';						// Data owner prior to archiving
	$prg_option['TIMESPAN'] = '(...)';				// Data creation time span
	$prg_option['DB_TYPE'] = 'CSV';						// Type of Database or database product
	$prg_option['SIARD_USER'] = 'admin';			// default user
	$prg_option['SIARD_SCHEMA'] = 'schema0';	// default schema

	// specific preference file
	if ($argc == 5) {
		$prefsfile = str_replace('\\', '/', "$wdir/$argv[4]");
		if (!is_file($prefsfile)) {
			echo "Preference file $prefsfile not found\n"; exit(1);
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
			else { $prg_option[$key] = utf8_encode(utf8_decode($val)); }
		}
	}
}
// check utility programms  ----------------------------------------------------
function checkUtils() {
global $prgdir, $prg_option;
// Libraries missing
	// <eXpat/> the Expat XML Parser http://expat.sourceforge.net and www.sysinternals.com
	if ((@md5_file("$prgdir/expat.dll") != '3e860d331271c23e46efb1ba019701d1')
	// iconv.dll (LGPLed libiconv for Windows NT/2000/XP and Windows 95/98/ME) is a component from the 
	// software libiconv: character set conversion library version 1.9.0 by Free Software Foundation
	or (@md5_file("$prgdir/iconv.dll") != 'e4341fb69cb24cf63e9063f4d8967ebf')
	// The sablot.dll module is utilized by the processor of Sablotron XSLT (Extensible Stylesheet Language (XSL) 
	// Transformations), http://www.gingerall.cz/
	or (@md5_file("$prgdir/sablot.dll") != '89f212d20a8b7b9a30b1e3284627febf')) {
		echo "Some libraries are missing or corrupt\n"; exit(1);
	}
// Programs missing
	// xmllint libxml project http://xmlsoft.org/
	elseif (@md5_file("$prgdir/xmllint.exe") != '5e11a78328e7cde3206f15fb8c79437c'){
		echo "Program xmllint.exe is missing, corrupt or wrong version (libxml version 20630)\n"; exit(1);
	}
	elseif (@md5_file("$prgdir/libxml2.dll") != 'a48f3cbb3f0176e33099274126724ea0'){
		echo "Library libxml2.dll is missing, corrupt or wrong version (libxml version 20630)\n"; exit(1);
	}
	elseif (@md5_file("$prgdir/zlib1.dll") != 'f5b8b7054675d6aaf4ce3e727395f402'){
		echo "Library zlib1.dll is missing, corrupt or wrong version (libxml version 20630)\n"; exit(1);
	}
	// 77-Zip 4.65  Copyright (c) 1999-2009 Igor Pavlov  2009-02-03
	elseif (@md5_file("$prgdir/7z.exe") != '93c7b7a3e3051bbb9630e41425cfdb3c'){
		echo "Program 7z.exe is missing, corrupt or wrong version (7z.exe version 4.65)\n"; exit(1);
	}
	elseif (@md5_file("$prgdir/7z.dll") != 'ca41d56630191e61565a343c59695ca1'){
		echo "Library 7z.dll is missing, corrupt or wrong version (7z.dll version 4.65)\n"; exit(1);
	}
	// GNU file-5.03
	elseif (@md5_file("$prgdir/file.exe") != '0d76b6d325bb9336c6c6a5c220f02c37'){
		echo "file.exe is missing, corrupt or wrong version (GNU file-5.03)\n"; exit(1);
	}
	elseif (@md5_file("$prgdir/magic.mgc") != '1dfd3dfbb62862a93112c02b26e53493'){
		echo "magic.mgc is missing, corrupt or wrong version \n"; exit(1);
	}
	elseif (@md5_file("$prgdir/magic1.dll") != '87307712a13f3282ceb7c5868312cd76'){
		echo "magic1.dll is missing, corrupt or wrong version \n"; exit(1);
	}
	elseif (@md5_file("$prgdir/regex2.dll") != '547c43567ab8c08eb30f6c6bacb479a3'){
		echo "regex2.dll is missing, corrupt or wrong version \n"; exit(1);
	}
}
// check  TMP directory --------------------------------------------------------
function checkTMP() {
global $prg_option;
// TMP directory
	$tmpdir = realpath($prg_option['TMPDIR']);

	if (!is_dir($tmpdir)) {
		$tmpdir = $prg_option['TMPDIR'];
		echo "No valid TMP directory: $tmpdir\n"; exit(1);
	}
	elseif (!@touch("$tmpdir/$prgname.tmp")) {
		echo "You may not have appropriate rights on TMP directory: $tmpdir\n"; exit(1);
	}
	@unlink("$tmpdir/$prgname.tmp");
	$prg_option['TMPDIR'] = str_replace('\\', '/', $tmpdir);
}
// check  TMP directory --------------------------------------------------------
function checkProgramOptions() {
global $prg_option;
	
	$prg_option['CHARSET'] = strtoupper($prg_option['CHARSET']);
	switch ($prg_option['CHARSET']) {
		case "ASCII":
		case "US-ASCII":
		case "OEM":
			$prg_option['CHARSET'] = "ASCII"; break;
		case "ANSI":
		case "ISO-8859-1":
			$prg_option['CHARSET'] = "ISO-8859-1"; break;
		case "UTF-8":
			$prg_option['CHARSET'] = "UTF-8"; break;
		default:
			echo "Only the following character sets are supported: ASCII, OEM, ANSI, ISO-8859-1, and UTF-8\n"; exit(1);
	}
}
?>
