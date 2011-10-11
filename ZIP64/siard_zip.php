<?
// Report all PHP errors
error_reporting(E_ALL);
include 'zip.php';

// -----------------------------------------------------------------------------
// Ersatz fnr PHP5 Funktion 'file_put_contents'
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

// Folder content in ZIP Datei einfgen
walkDir("content");

// MD5 berechnen
$md5 = $ZIP->getMD5overPayload();
$metadata = file_get_contents("header/metadata.xml");
$metadata_md5 = preg_replace ('/\<messageDigest\>MD5.*\<\/messageDigest\>/','<messageDigest>MD5'.$md5.'</messageDigest>', $metadata);
file_put_contents("header/metadata.xml", $metadata_md5);

// Folder header in ZIP Datei einfgen
$ZIP->addZipFile("header");
$ZIP->addZipFile("header/metadata.xsd");
// $ZIP->addZipFile("header/metadata.xsl");
$ZIP->addZipFile("header/metadata.xml");
$ZIP->closeZipFile();

chdir ("..");

exit(0);
?>
