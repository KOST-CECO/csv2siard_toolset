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
?>
