<?
// Report all PHP errors
error_reporting(E_ALL);
include 'zip.php';

// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Global
$prgdir = dirname(realpath($argv[0]));				//Program directory
$prg_option['VERBOSITY'] = true;					// Programm optionen
$countit = 0;
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
// -----------------------------------------------------------------------------
function walkDir($name) {
global $ZIP, $countit;
//	echo "\n addZipFile: $name " . $countit++;
	$ZIP->addZipFile($name);
	if (is_dir($name)) {
		$dh = opendir($name);
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != "..") {
				walkDir("$name/$file");
			}
		}
		closedir($dh);
	}
}

// -----------------------------------------------------------------------------
if (!@is_dir($argv[1])) { 
	echo "no directory spezifide, usage: zip.exe <directory>\n"; exit(-1);
}
$startfolder = str_replace('\\', '/', $argv[1]);
$startfolder = str_replace('//', '/', $startfolder);
$startfolder = rtrim($startfolder, '/');

$ZIP = new ZipFile("$startfolder.zip");
walkDir($startfolder);
$ZIP->closeZipFile();

exit(0);
?>
