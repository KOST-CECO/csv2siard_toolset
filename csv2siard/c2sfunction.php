<?
// Report all PHP errors
error_reporting(E_ALL);
// -----------------------------------------------------------------------------
// Ersatz für PHP5 Funktion 'str_split'
// array str_split ( string $string [, int $split_length = 1 ] )
if(!function_exists('str_split')) {
  function str_split($string, $split_length = 1) {
    $array = explode("\r\n", chunk_split($string, $split_length));
    array_pop($array);
    return $array;
  }
}
// -----------------------------------------------------------------------------
// Ersatz für PHP5 Funktion 'mkdir'
// bool mkdirPHP4 ( string $pathname , int $mode = 0777 , bool $recursive = true )
function mkdirPHP4($pathname, $mode, $recursive) {
	if ($recursive === false) {
		return(@mkdir($pathname, $mode));
	}
	elseif (is_dir($pathname)) { 
		return(true);
	}
	else {
		mkdirPHP4(preg_replace('#/[^/]+$#', '', $pathname), $mode, $recursive);
		return(@mkdir($pathname, $mode));
	}
	return(false);
}
// -----------------------------------------------------------------------------
// Ersatz für PHP5 Funktion 'sys_get_temp_dir'
if ( !function_exists('sys_get_temp_dir')) {
	function sys_get_temp_dir() {
		if( $temp=getenv('TMP') )			return $temp;
		if( $temp=getenv('TEMP') )		return $temp;
		if( $temp=getenv('TMPDIR') )	return $temp;
		$temp=tempnam(__FILE__,'');
		if (file_exists($temp)) {
			unlink($temp);
			return dirname($temp);
		}
		return null;
	}
}
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
// ----------------------------------------------------------------------------
// Ersatz für PHP5 Funktion List files and directories inside the specified path
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
// -----------------------------------------------------------------------------
// A simple way to recursively delete a directory that is not empty
function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandirPHP4($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
			} 
		}
		reset($objects);
		rmdir($dir); 
	}
}
// ----------------------------------------------------------------------------
// Voici donc une fonction PHP qui permet de convertir les fichiers codés en
// ANSI vers de l'ASCII Par Nicolas Debras
function ansi2ascii($string) {
	$asciiarray[] = 142; $ansiarray[] = 196;
	$asciiarray[] = 143; $ansiarray[] = 197;
	$asciiarray[] = 146; $ansiarray[] = 198;
	$asciiarray[] = 128; $ansiarray[] = 199;
	$asciiarray[] = 144; $ansiarray[] = 201;
	$asciiarray[] = 165; $ansiarray[] = 209;
	$asciiarray[] = 153; $ansiarray[] = 214;
	$asciiarray[] = 154; $ansiarray[] = 220;
	$asciiarray[] = 133; $ansiarray[] = 224;
	$asciiarray[] = 160; $ansiarray[] = 225;
	$asciiarray[] = 131; $ansiarray[] = 226;
	$asciiarray[] = 132; $ansiarray[] = 228;
	$asciiarray[] = 134; $ansiarray[] = 229;
	$asciiarray[] = 145; $ansiarray[] = 230;
	$asciiarray[] = 135; $ansiarray[] = 231;
	$asciiarray[] = 138; $ansiarray[] = 232;
	$asciiarray[] = 130; $ansiarray[] = 233;
	$asciiarray[] = 136; $ansiarray[] = 234;
	$asciiarray[] = 137; $ansiarray[] = 235;
	$asciiarray[] = 141; $ansiarray[] = 236;
	$asciiarray[] = 161; $ansiarray[] = 237;
	$asciiarray[] = 140; $ansiarray[] = 238;
	$asciiarray[] = 139; $ansiarray[] = 239;
	$asciiarray[] = 164; $ansiarray[] = 241;
	$asciiarray[] = 149; $ansiarray[] = 242;
	$asciiarray[] = 162; $ansiarray[] = 243;
	$asciiarray[] = 147; $ansiarray[] = 244;
	$asciiarray[] = 148; $ansiarray[] = 246;
	$asciiarray[] = 151; $ansiarray[] = 249;
	$asciiarray[] = 163; $ansiarray[] = 250;
	$asciiarray[] = 150; $ansiarray[] = 251;
	$asciiarray[] = 129; $ansiarray[] = 252;
	$asciiarray[] = 225; $ansiarray[] = 223;

	$i = 0;
	while ($i < sizeof ($asciiarray)){
		$string = str_replace(chr($asciiarray[$i]), chr($ansiarray[$i]), $string);
		$i++;
	}
	return ($string);
}
function ascii2ansi($string) {
	$asciiarray[] = 142; $ansiarray[] = 196;
	$asciiarray[] = 143; $ansiarray[] = 197;
	$asciiarray[] = 146; $ansiarray[] = 198;
	$asciiarray[] = 128; $ansiarray[] = 199;
	$asciiarray[] = 144; $ansiarray[] = 201;
	$asciiarray[] = 165; $ansiarray[] = 209;
	$asciiarray[] = 153; $ansiarray[] = 214;
	$asciiarray[] = 154; $ansiarray[] = 220;
	$asciiarray[] = 133; $ansiarray[] = 224;
	$asciiarray[] = 160; $ansiarray[] = 225;
	$asciiarray[] = 131; $ansiarray[] = 226;
	$asciiarray[] = 132; $ansiarray[] = 228;
	$asciiarray[] = 134; $ansiarray[] = 229;
	$asciiarray[] = 145; $ansiarray[] = 230;
	$asciiarray[] = 135; $ansiarray[] = 231;
	$asciiarray[] = 138; $ansiarray[] = 232;
	$asciiarray[] = 130; $ansiarray[] = 233;
	$asciiarray[] = 136; $ansiarray[] = 234;
	$asciiarray[] = 137; $ansiarray[] = 235;
	$asciiarray[] = 141; $ansiarray[] = 236;
	$asciiarray[] = 161; $ansiarray[] = 237;
	$asciiarray[] = 140; $ansiarray[] = 238;
	$asciiarray[] = 139; $ansiarray[] = 239;
	$asciiarray[] = 164; $ansiarray[] = 241;
	$asciiarray[] = 149; $ansiarray[] = 242;
	$asciiarray[] = 162; $ansiarray[] = 243;
	$asciiarray[] = 147; $ansiarray[] = 244;
	$asciiarray[] = 148; $ansiarray[] = 246;
	$asciiarray[] = 151; $ansiarray[] = 249;
	$asciiarray[] = 163; $ansiarray[] = 250;
	$asciiarray[] = 150; $ansiarray[] = 251;
	$asciiarray[] = 129; $ansiarray[] = 252;
	$asciiarray[] = 225; $ansiarray[] = 223;

	$i = 0;
	while ($i < sizeof ($asciiarray)){
		$string = str_replace(chr($ansiarray[$i]), chr($asciiarray[$i]), $string);
		$i++;
	}
	return ($string);
}
?>
