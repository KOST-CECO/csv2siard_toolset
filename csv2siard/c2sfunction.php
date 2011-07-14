<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// Schema validation with xmllint libxml project http://xmlsoft.org/
function validateXML($schema,$xml, $message) {
global $prg_option, $prgdir;
	if ($prg_option['ERR'] != 0) { return false; }

	$commandline =  'CALL "'.$prgdir.'\\xmllint.exe"'.' -noout -schema '.
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
		@rmdir($dir); 
	}
}
// ----------------------------------------------------------------------------
// Voici donc une fonction PHP qui permet de convertir les fichiers codés en
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
/* Konvertiert gegliederte UTC-Angaben in Unix-Zeit, Parameter und ihre Werte-Bereiche:
 * - jahr [1970..2038]
 * - monat [1..12]
 * - tag [1..31]
 * - stunde [0..23]
 * - minute [0..59]
 * - sekunde [0..59]
 *
 * PHP 4: mktime(hour, minute, second, month, day, year)
 * negative timestamps were not supported under Windows, therefore the range of valid years was limited to 1970 through 2038
*/
function unixTime( $stunde, $minute, $sekunde, $monat, $tag, $jahr ) {
	$tage_bis_monatsanfang = /* ohne Schalttag */
			array(0,31,59,90,120,151,181,212,243,273,304,334);

	$jahre=$jahr-1970;
	$schaltjahre=floor( (($jahr-1)-1968)/4 - (($jahr-1)-1900)/100 + (($jahr-1)-1600)/400 );

	$unix_zeit=$sekunde + 60*$minute + 60*60*$stunde +
			($tage_bis_monatsanfang[$monat-1]+$tag-1)*60*60*24 +
			($jahre*365+$schaltjahre)*60*60*24;
 
	if ( ($monat>2) && ($jahr%4==0 && ($jahr%100!=0 || $jahr%400==0)) ) {
		$unix_zeit+=60*60*24; /* +Schalttag wenn jahr Schaltjahr ist */
	}
	return($unix_zeit);
}
?>
