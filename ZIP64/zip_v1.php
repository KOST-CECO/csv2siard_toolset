<?
// Report all PHP errors
// error_reporting(E_ALL);

/* Thanks to Marcin Szychowski 08-Oct-2009 01:59 
   MS-DOS related filesystems, along with ZIP files, store date and time in four bytes (time: 2 bytes, date: 2 bytes), as described in Wikipedia:
   http://en.wikipedia.org/wiki/File_Allocation_Table#Directory_table */
/*   bits   contents
     -----  ---------------------
     15-11  hour (0-23)
     10-5   minute (0-59)
     4-0    double seconds (0-29) 
*/
// $ts - standard UNIX timestamp, as returned by mktime()
function packTime($ts){
$ts = $ts + 17;
for ($i = 0; $i <= 60; $i++) {
	$ts++;
	$s=date('s', $ts);
	$sec=round((('1'.date('s', $ts))-100)/2);
	$newsec = (round((date('s', $ts)-1)/2,0) > 0) ? round((date('s', $ts)-1)/2,0) : 0;
	echo "s:$s - sec:$sec - newsec:$newsec\n";
}

	$min=('1'.date('i', $ts))-100;
	$hour=date('G', $ts);

	$dosTime=($hour<<11)+($min<<5)+$sec;

	$m[0]=$dosTime%256;
	$m[1]=(($dosTime-$m[0])/256)%256;
	print_r($m);
	return sprintf('%c%c', $m[0], $m[1]);
}
/*   bits   contents
     -----  -----------------------------------------------
     15-9   years elapsed since 1980 (0-127)
     8-5    month (1=January, 2=February, ..., 12=December)
     4-0    day (1-31)
*/
// $ts - standard UNIX timestamp, as returned by mktime()
function packDate($ts) {
	$year=date('Y', $ts)-1980;
	$day=date('j', $ts);
	$month=date('n', $ts);

	$dosDate=($year<<9)+($month<<5)+$day;

	$m[0]=$dosDate%256;
	$m[1]=(($dosDate-$m[0])/256)%256;
	return sprintf('%c%c', $m[0], $m[1]);
}

class Local_file_header {
	var $struct;	// Structure holding Local File Header
	
	function Local_file_header($filename) {
		$this->struct['Local_file_header_signature'] = pack("V", 0x04034b50);
		$this->struct['Version_needed_to_extract'] = pack("v", 10);
		$this->struct['General_purpose_bit_flag'] = pack("v", 0);
		$this->struct['Compression_method'] = pack("v", 0);
		
		echo "$filename was last modified: " . date ("F d Y H:i:s.", filemtime($filename));
		
		//echo filemtime($filename).':'.packTime(filemtime($filename))."\n";
		
		$this->struct['Last_mod_file_time'] = packTime(filemtime($filename));
		$this->struct['Last_mod_file_date'] = packDate(filemtime($filename));
		$this->struct['CRC-32'] = pack("V", crc32(file_get_contents($filename)));
		$this->struct['Compressed_size'] = pack("V", filesize($filename));
		$this->struct['Uncompressed_size'] = pack("V", filesize($filename));
		$this->struct['Filename_length'] = pack("v", strlen($filename));
		$this->struct['Extra_field_length'] = pack("v", 0);
		$this->struct['Filename'] = $filename;
		$this->struct['Extra_field'] = '';
	}
	
	function Structure() {
		return($this->struct);
	}	
	
	function Stream() {
		$header = '';
		foreach ($this->struct as $el) {
			$header = $header.$el;
		}
		return($header);
	}
}


// MAIN ------------------------------------------------------------------------

$lfh = new Local_file_header("1-4data/test2.dat");

print_r($lfh->Structure());
echo $lfh->Stream()."\n";

$fp = fopen("out.zip" , 'wb');
fwrite($fp, $lfh->Stream());
fclose($fp);

system("hexdump.exe out.zip");
?>
