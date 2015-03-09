<?
// Report all PHP errors
error_reporting(E_ALL);
include 'zip.php';

// -----------------------------------------------------------------------------
// writes echo to logfile if specified
// newline is replaced bei carriage return and newline (0x0d0a)
function log_echo($logtext) {
global $logfile, $prg_option;
	echo $logtext;
	if ($logfile) {
		$logtext = str_replace ( "\r\n" , "\n"  ,$logtext );
		$logtext = str_replace ( "\n" , "\r\n"  ,$logtext );
		fwrite($logfile, $logtext);
	}
}

// Ersatz fnr PHP5 Funktion List files and directories inside the specified path
// array scandir ( string directory [, int sorting_order [, resource context]] )
// returns an array of files and directories from the directory
function scandirPHP4($dir) {
	$arr = array();

	if ( $handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			$arr[] = $file;
		}
	}
	closedir($handle);
	sort($arr);
	return($arr);
}

// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Global
$prgdir = dirname(realpath($argv[0]));				//Program directory
$prg_option['VERBOSITY'] = true;					// Programm optionen

$ZIP = new ZipFile("test.zip");
$ZIP->addZipFile("65535/xaa");
$ZIP->addZipFile("65535/xaa");
$ZIP->closeZipFile();

/*
$ddir = scandirPHP4("65535");
$cc = count($ddir);
echo "filecount: $cc \n";
*/
exit(0);
?>
