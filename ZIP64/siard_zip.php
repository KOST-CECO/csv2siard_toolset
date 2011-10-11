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

chdir ($startfolder);
$ZIP = new ZipFile("../$startfolder.siard");
walkDir("content");

echo "\n\nMD5: ".$ZIP->getMD5overPayload()."\n";

walkDir("header");
$ZIP->closeZipFile();
chdir ("..");

exit(0);
?>
