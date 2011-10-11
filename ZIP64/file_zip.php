<?
// Report all PHP errors
error_reporting(E_ALL);
include 'zip.php';

// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Global
$_prgdir = dirname(realpath($argv[0]));				//Program directory
// -----------------------------------------------------------------------------
function walkDir($name) {
global $ZIP;
	if (is_dir($name)) {
		$dh = opendir($name);
		while (($file = readdir($dh)) !== false) {
			if ($file != "." && $file != "..") {
				walkDir("$name/$file");
			}
		}
		closedir($dh);
	}
	$ZIP->addZipFile($name);
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
