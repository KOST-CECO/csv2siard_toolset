<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
/**
 * Parse a time/date generated with strftime().
 *
 * This function is the same as the original one defined by PHP (Linux/Unix only),
 *  but now you can use it on Windows too.
 *  Limitation : Only this format can be parsed %S, %M, %H, %d, %m, %Y
 * 
 * @author Lionel SAURON
 * @version 1.0
 * @public
 * 
 * @param $sDate(string)	The string to parse (e.g. returned from strftime()).
 * @param $sFormat(string)  The format used in date  (e.g. the same as used in strftime()).
 * @return (array)		  Returns an array with the <code>$sDate</code> parsed, or <code>false</code> on error.
 *
 * This work of Lionel SAURON (http://sauron.lionel.free.fr:80) is licensed under the
 * Creative Commons Attribution 2.0 France License.
 *
 */

if(function_exists("strptime") == false) 
{ 
	function strptime($sDate, $sFormat) 
	{ 
		$aResult = array 
		( 
			'tm_sec'   => 0, 
			'tm_min'   => 0, 
			'tm_hour'  => 0, 
			'tm_mday'  => 1, 
			'tm_mon'   => 0, 
			'tm_year'  => 0, 
			'tm_wday'  => 0, 
			'tm_yday'  => 0, 
			'tm_utc'  => 0, 
			'unparsed' => $sDate, 
		); 
		 
		while($sFormat != "") 
		{ 
			// ===== Search a %x element, Check the static string before the %x ===== 
			$nIdxFound = strpos($sFormat, '%'); 
			if($nIdxFound === false) 
			{ 
				 
				// There is no more format. Check the last static string. 
				$aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate; 
				break; 
			} 
			 
			$sFormatBefore = substr($sFormat, 0, $nIdxFound); 
			$sDateBefore   = substr($sDate,   0, $nIdxFound); 
			 
			if($sFormatBefore != $sDateBefore) break; 
			 
			// ===== Read the value of the %x found ===== 
			$sFormat = substr($sFormat, $nIdxFound); 
			$sDate   = substr($sDate,   $nIdxFound); 
			 
			$aResult['unparsed'] = $sDate; 
			 
			$sFormatCurrent = substr($sFormat, 0, 2); 
			$sFormatAfter   = substr($sFormat, 2); 
			 
			$nValue = -1; 
			$sDateAfter = ""; 
			 
			switch($sFormatCurrent) 
			{ 
				case '%S': // Seconds after the minute (0-59) 
					 
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter); 
					 
					if(($nValue < 0) || ($nValue > 59)) return false; 
					 
					$aResult['tm_sec']  = $nValue; 
					break; 
				 
				// ---------- 
				case '%M': // Minutes after the hour (0-59) 
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter); 
					 
					if(($nValue < 0) || ($nValue > 59)) return false; 
				 
					$aResult['tm_min']  = $nValue; 
					break; 
				 
				// ---------- 
				case '%H': // Hour since midnight (0-23) 
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter); 
					 
					if(($nValue < 0) || ($nValue > 23)) return false; 
				 
					$aResult['tm_hour']  = $nValue; 
					break; 
				 
				// ---------- 
				case '%d': // Day of the month (1-31) 
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter); 
					 
					if(($nValue < 1) || ($nValue > 31)) return false; 
				 
					$aResult['tm_mday']  = $nValue; 
					break; 
				 
				// ---------- 
				case '%m': // Months since January (0-11) 
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter); 
					 
					if(($nValue < 1) || ($nValue > 12)) return false; 
				 
					$aResult['tm_mon']  = ($nValue - 1); 
					break; 
				 
				// ---------- 
				case '%Y': // Years since 1601 
					sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter); 
					 
					if($nValue < 1601) return false; 
				 
					$aResult['tm_year']  = ($nValue - 1900); 
					break; 
				 
				// ---------- 
				default: 
					break 2; // Break Switch and while 
				 
			} // END of case format 
			 
			// ===== Next please ===== 
			$sFormat = $sFormatAfter; 
			$sDate   = $sDateAfter; 
			 
			$aResult['unparsed'] = $sDate; 
			 
		} // END of while($sFormat != "") 
		 
		// ===== Create the other value of the result array ===== 
		$nParsedDateTimestamp = unixTime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'], 
								$aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900); 

		// Before PHP 5.1 return -1 when error 
		if($nParsedDateTimestamp === false) return false; 
		
		$tm = gmtime($nParsedDateTimestamp);
		$aResult['tm_utc'] = $nParsedDateTimestamp;
		$aResult['tm_wday'] = $tm['tm_wday']; // Days since Sunday (0-6) 
		$aResult['tm_yday'] = $tm['tm_yday']; // Days since January 1 (0-365) 

		return $aResult; 
	} // END of function 
	 
} // END of if(function_exists("strptime") == false) 

// -----------------------------------------------------------------------------
// Returns array with [type] of date and [date] in XML format
// empty [date] means convertion failor
function convert2XMLdate($str) {
$retval = array();

	// non-standard: YY MM DD hh II SS like '20080701223807'
	if (is_array($arr = strptime($str, '%Y%m%d%H%M%S')) and $arr['unparsed'] == '') {
		$retval['type'] = "non-standard";
	}
	// XMLRPC (Compact): YY MM DD 't' hh II SS "20080701t223807" or "20080701T093807" 
	elseif (is_array($arr = strptime($str, '%Y%m%dt%H%M%S')) and $arr['unparsed'] == '') {
		$retval['type'] = "XMLRPC (Compact)";
	}
	elseif (is_array($arr = strptime($str, '%Y%m%dT%H%M%S')) and $arr['unparsed'] == '') {
		$retval['type'] = "XMLRPC (Compact)";
	}
	// XMLRPC: YY MM DD "T" hh ":" II ":" SS "20080701T22:38:07", "20080701T9:38:07" 
	elseif (is_array($arr = strptime($str, '%Y%m%dT%H:%M:%S')) and $arr['unparsed'] == '') {
			$retval['type'] = "XMLRPC";
	}
	// EXIF YY ":" MM ":" DD " " HH ":" II ":" SS "2008:08:07 18:11:31" 
	elseif (is_array($arr = strptime($str, '%Y:%m:%d %H:%M:%S')) and $arr['unparsed'] == '') {
		$retval['type'] = "EXIF";
	}
	// MySQL YY "-" MM "-" DD " " HH ":" II ":" SS "2008-08-07 18:11:31" 
	elseif (is_array($arr = strptime($str, '%Y-%m-%d %H:%M:%S')) and $arr['unparsed'] == '') {
		$retval['type'] = "MySQL";
	}
	// WDDX YY "-" mm "-" dd "T" hh ":" ii ":" ss "2008-7-1T9:3:37" 
	elseif (is_array($arr = strptime($str, '%Y-%m-%dT%H:%M:%S')) and $arr['unparsed'] == '') {
		$retval['type'] = "WDDX";
	}
	// ISO 8601/SOAP: YY "-" MM "-" DD "T" HH ":" II ":" SS frac tzcorrection? "2008-07-01T22:35:17.02", "2008-07-01T22:35:17.03+08:00" 
	elseif (is_array($arr = strptime($str, '%Y-%m-%dT%H:%M:%S')) 
			and ($arr['unparsed'] == '' 
				or preg_match('/\.\d\d/', $arr['unparsed'])
				or preg_match('/\.\d\d\+\d\d\:00/', $arr['unparsed']) 
			)) {
		$retval['type'] = "ISO 8601 / SOAP";
	}
	// Common Log Format dd "/" M "/" YY : HH ":" II ":" SS space tzcorrection "10/Oct/2000:13:55:36 -0700" 
	elseif (preg_match('#^\d\d/(jan|feb|mar|apr|may|jun|jul|aug|sep|sept|oct|nov|dec)/\d\d\d\d\:#i', $str)) {
		$str = preg_replace('#^(\d\d)/(jan|feb|mar|apr|may|jun|jul|aug|sep|sept|oct|nov|dec)/(\d\d\d\d)\:(.*)$#i', '${1}-${2}-${3} ${4}', $str);
		$tmp = strtotime($str);
		$retval['date'] = strftime('%Y-%m-%dT%H:%M:%S', $tmp);
		$arr = strptime($retval['date'], '%Y-%m-%dT%H:%M:%S');
		$retval['type'] = "Common Log Format";
	}
	// UNIX date format
	else {
		$tmp = strtotime($str);
		$retval['date'] = strftime('%Y-%m-%dT%H:%M:%S', $tmp);
		$arr = strptime($retval['date'], '%Y-%m-%dT%H:%M:%S');
		$retval['type'] = "UNIX native date format";		
	}
	// prepare output in XML date format
	if (is_array($arr)) {
		$retval['date'] = sprintf('%02d-%02d-%02dT%02d:%02d:%02d', $arr['tm_year']+1900, $arr['tm_mday'], $arr['tm_mon']+1, $arr['tm_hour'], $arr['tm_min'], $arr['tm_sec']);
		$retval['utc'] = $arr['tm_utc'];
		return($retval);
	}
	else {
		return(false);
	}
}
?>
