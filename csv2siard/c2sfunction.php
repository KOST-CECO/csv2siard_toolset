<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// detect mime-type and mime-encoding with GNU file-5.03
// return type or encoding according to $type [TYPE or ENCODING, default is TYPE]
function detectMimeType($file, $type = 'TYPE') {
global $prg_option, $prgdir;
$type = ($type == 'TYPE') ? '--mime-type' : '--mime-encoding';

	$cmdline = 'CALL "'.$prgdir.'/file.exe" '.$type.' -bm "'.$prgdir.'/magic.mgc" '.'"'.$file.'"';
	$mime_type = exec($cmdline);
	
	// may be the file ends with EOF = SUB (dec 026 hex 0xA1)
	if ($mime_type == 'application/octet-stream' or $mime_type == 'binary') {
		$fp = fopen($file,'rb');
		fseek($fp, -1, SEEK_END);
		// copy file without SUB into tmpfile
		if (ord(fgetc($fp)) == 26) {
			$tmpfile = tempnam($prg_option['TMPDIR'] , 'sub');
			$tfp = fopen($tmpfile,'wb');
			rewind($fp);
			while (!feof($fp)) {
				$content = fread($fp, 20);
				$content = (feof($fp)) ? rtrim($content, "\x1A") : $content;
				fwrite($tfp, $content);
			}
			fclose($tfp);
			fclose($fp);
			// get mime-type of tmpfile with GNU file-5.03
			$cmdline = 'CALL "'.$prgdir.'/file.exe" '.$type.' -bm "'.$prgdir.'/magic.mgc" '.'"'.$tmpfile.'"';
			$mime_type = exec($cmdline);
			unlink($tmpfile);
			return($mime_type);
		}
		fclose($fp);
	}
	return($mime_type);
}
// -----------------------------------------------------------------------------
// US-ASCII or digit, first character must be a letter, case insensitive, max 30 character
function testDBMSNaming($buf) {
	$ascii = str_split('012345679ABCDEFGHIJKLMNOPQRSTUVWXYZ_');
	
	// max 30 character
	if (strlen($buf) > 30) {
		return(false);
	}	
	//US-ASCII letter or digit
	$strarr = str_split(strtoupper($buf));
	foreach ($strarr as $ch) {
		if (!in_array($ch, $ascii)){
			return(false);
		}
	}
	//first character must be a letter
	if (ord($strarr[0]) < 65 or ord($strarr[0]) > 90) {
		return(false);
	}
	return(true);
}
// -----------------------------------------------------------------------------
// Schema validation with xmllint libxml project http://xmlsoft.org/
function validateXML($schema,$xml, $message) {
global $prg_option, $prgdir;
	if ($prg_option['ERR'] != 0) { return false; }

	$commandline =  'CALL "'.$prgdir.'\\xmllint.exe"'.' -stream -noout -schema '.
									' "'.$schema.'" '.
									' "'.$xml.'" '.
									' 2> '.' "'.$xml.'.out"';
	exec($commandline, $result, $retval);
	if ($retval) {
		$result = file_get_contents("$xml.out");
		$result_array = explode("\n", $result, 2);
		echo "$message\n$result_array[0]\n";
		$prg_option['ERR'] = 64;
		@unlink("$xml.out");
		return(false);
	}
	@unlink("$xml.out");
	return(true);
}
// -----------------------------------------------------------------------------
// Return "/database/table/option@key from XML array"
function getTableOption(&$table,$key) {
	// no options
	if (!array_key_exists('option', $table['_c'])) {
		return(false);
	}
	// one option
	elseif (!array_key_exists('0', $table['_c']['option'])) {
		if ($table['_c']['option']['_a']['key'] == $key) {
			return($table['_c']['option']['_a']['value']);
		}
	}
	// multi options
	else {
		foreach ($table['_c']['option'] as $option) {
			if ($option['_a']['key'] == $key) {
				return($option['_a']['value']);
			}
		}
	}
	return(false);
}
// -----------------------------------------------------------------------------
// Enter "/database/table/option[@key='value'] into XML array"
function setTableOption(&$table,$key, $value) {
	$tail = array();
	// no options, <option> has to be the first element in <table>
	if (!array_key_exists('option', $table['_c'])) {
		//echo "no options\n";
		reset($table['_c']);
		while (list($tableprop, $tbl) = each($table['_c'])) {
			$tail[$tableprop] = $table['_c'][$tableprop];
		}
		reset($tail);
		while (list($tableprop, $tbl) = each($tail)) {
			unset($table['_c'][$tableprop]);;
		}
		
		$table['_c']['option']['_a']['key']= $key;
		$table['_c']['option']['_a']['value']= $value;
		$table['_c']['option']['_v'] = '';
		
		reset($tail);
		while (list($tableprop, $tbl) = each($tail)) {
			$table['_c'][$tableprop] = $tbl;
		}
	}
	// one option
	elseif (!array_key_exists('0', $table['_c']['option'])) {
		//echo "one option\n";
		$table['_c']['option'][0]['_a']['key']= $table['_c']['option']['_a']['key'];
		$table['_c']['option'][0]['_a']['value']= $table['_c']['option']['_a']['value'];
		$table['_c']['option'][0]['_v'] = '';
		$table['_c']['option'][1]['_a']['key']= $key;
		$table['_c']['option'][1]['_a']['value']= $value;
		$table['_c']['option'][1]['_v'] = '';
		unset($table['_c']['option']['_a'], $table['_c']['option']['_v']);
	}
	// multi options
	else {
		$tbc = count ($table['_c']['option']);
		//echo "$tbc options\n";
		$table['_c']['option'][$tbc]['_a']['key']= $key;
		$table['_c']['option'][$tbc]['_a']['value']= $value;
		$table['_c']['option'][$tbc]['_v'] = '';
	}
}
// -----------------------------------------------------------------------------
// Ersatz fnr PHP5 Funktion 'str_split'
// array str_split ( string $string [, int $split_length = 1 ] )
if(!function_exists('str_split')) {
  function str_split($string, $split_length = 1) {
    $array = explode("\r\n", chunk_split($string, $split_length));
    array_pop($array);
    return $array;
  }
}
// -----------------------------------------------------------------------------
// Ersatz fnr PHP5 Funktion 'mkdir'
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
// Ersatz fnr PHP5 Funktion 'sys_get_temp_dir'
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
// ----------------------------------------------------------------------------
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
		@rmdir($dir); 
	}
}
// ----------------------------------------------------------------------------
// Voici donc une fonction PHP qui permet de convertir les fichiers codTs en
// ANSI vers de l'ASCII Par Nicolas Debras
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
		$string = str_replace(chr($asciiarray[$i]), chr($ansiarray[$i]), $string);
		$i++;
	}
	return ($string);
}
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
		$string = str_replace(chr($ansiarray[$i]), chr($asciiarray[$i]), $string);
		$i++;
	}
	return ($string);
}
// ----------------------------------------------------------------------------
// 1. Escape the pattern to make it regex-safe. Wildcards use only * and ?, so the rest of the text has to be converted to literals
// 2. Once escaped, * becomes \* and ? becomes \?, so we have to convert \* and \? to their respective regex equivalents, .* and .?
// 3. For replacement pattern use (.*) and (.?) for backreference ${1}
// 4. Prepend ^ and append $ to specify the beginning and end of the pattern
function Wildcard2Regex($pattern) {
	$pattern = str_replace ('.', '\\.', $pattern);
	$pattern = str_replace ('*', '(.*)', $pattern);
	$pattern = str_replace ('?', '(.?)', $pattern);
	return('^'.$pattern.'$');
}
// ----------------------------------------------------------------------------
/* Checks a variable if it is true or false, humanlike. 
 * We account for values as 'on', '1', '' and so on.    
 * Finally, for some reare occurencies we account with  
 * crazy logic to fit some arrays and objects.          
 *                                                      
 * @author Kim Steinhaug, <kim@steinhaug.com>           
 * @param mixed $var, the variable to check             
*/
function to_bool($var){
	if(is_bool($var)){
		return $var;
	} else if($var === NULL || $var === 'NULL' || $var === 'null'){
		return false;
	} else if(is_string($var)){
		$var = trim($var);
		if($var=='false'){ return false;
		} else if($var=='true'){ return true;
		} else if($var=='no'){ return false;
		} else if($var=='yes'){ return true;
		} else if($var=='off'){ return false;
		} else if($var=='on'){ return true;
		} else if($var==''){ return false;
		} else if(ctype_digit($var)){
			if((int) $var)
				return true;
				else
				return false;
		} else { return true; }
	} else if(ctype_digit((string) $var)){
			if((int) $var)
				return true;
				else
				return false;
	} else if(is_array($var)){
		if(count($var))
			return true;
			else
			return false;
	} else if(is_object($var)){
		return true;// No reason to (bool) an object, we assume OK for crazy logic
	} else {
		return true;// Whatever came though must be something,	OK for crazy logic
	}
}
// ----------------------------------------------------------------------------
/* converts UTC-time to Unix/POSIX time stamp (similar to mktime):
 * - year [1601..2038]
 * - month [1..12]
 * - day [1..31]
 * - hour [0..23]
 * - min [0..59]
 * - sec [0..59]
 *
 * PHP 4: mktime(hour, minute, second, month, day, year)
 * negative timestamps were not supported under Windows, therefore the range of valid years was limited to 1970 through 2038
*/
function unixTime( $hour, $min, $sec, $month, $day, $year ) {
	$day_2_month = /* without leap years */
			array(0,31,59,90,120,151,181,212,243,273,304,334);

	if ($year < 1601 or $year > 2038) { return(false); } 
	elseif ($month < 1 or $month > 12) { return(false); } 
	elseif ($day < 1 or $day > 31) { return(false); } 
	elseif ($hour < 0 or $hour > 23) { return(false); } 
	elseif ($min < 0 or $min > 59) { return(false); } 
	elseif ($sec < 0 or $sec > 59) { return(false); } 

	$years=$year-1970;
	$leap_years=floor( (($year-1)-1968)/4 - (($year-1)-1900)/100 + (($year-1)-1600)/400 );

	$timestamp=$sec + 60*$min + 60*60*$hour +
			($day_2_month[$month-1]+$day-1)*60*60*24 +
			($years*365+$leap_years)*60*60*24;
 
	if ( ($month>2) && ($year%4==0 && ($year%100!=0 || $year%400==0)) ) {
		$timestamp+=60*60*24; /* add leap day when year == leap year */
	}
	return($timestamp);
}

// -----------------------------------------------------------------------------
/* Set the time structure tm_t fields for a unix timestamp 
 * "tm_sec"   Seconds after the minute (0-61) 
 * "tm_min"   Minutes after the hour (0-59) 
 * "tm_hour"  Hour since midnight (0-23) 
 * "tm_mday"  Day of the month (1-31) 
 * "tm_mon"   Months since January (0-11) 
 * "tm_year"  Years since 1900 
 * "tm_wday"  Days since Sunday (0-6) 
 * "tm_yday"  Days since January 1 (0-365)
 * "tm_isdst" Daylight Saving Time flag (0)
 */
function gmtime($time) { 
	$tp = array (
		'tm_sec'   => 0, 
		'tm_min'   => 0, 
		'tm_hour'  => 0, 
		'tm_mday'  => 1, 
		'tm_mon'   => 0, 
		'tm_year'  => 0, 
		'tm_wday'  => 0, 
		'tm_yday'  => 0, 
		'tm_isdst' => 0 );
		
	$day_per_month = array(0,31,59,90,120,151,181,212,243,273,304,334);

	$day = floor($time/(24*60*60)); 
	$secs = $time % (24*60*60); 
	$tp['tm_sec'] = $secs % 60; 
	$mins = floor($secs / 60); 
	$tp['tm_hour'] = floor($mins / 60); 
	$tp['tm_min'] = $mins % 60; 
	$tp['tm_wday'] = ($day + 4) % 7; 
	$year = floor((($day * 4) + 2)/1461); 
	$tp['tm_year'] = $year + 70; 
	$leap = !($tp['tm_year'] & 3); 
	$day -= floor((($year * 1461) + 1) / 4); 
	$tp['tm_yday'] = $day; 
	$day += ($day > 58 + $leap) ? (($leap) ? 1 : 2) : 0; 
	$tp['tm_mon'] = floor((($day * 12) + 6)/367); 
	$tp['tm_mday'] = $day + 1 -  floor((($tp['tm_mon'] * 367) + 5)/12); 
	$tp['tm_isdst'] = 0;
	
	return($tp);
} 
?>
