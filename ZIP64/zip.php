<?
// Report all PHP errors
error_reporting(E_ALL);
//include 'crc32.php';

/* MS-DOS related filesystems store file attributes (archive, directory, hidden, read-only, system and volume) in one byte
   http://en.wikipedia.org/wiki/File_Allocation_Table#Directory_table
  bit meaning if bit = 1
  --- ---------------------------------------
   7  unused
   6  unused
   5  file has been changed since last backup
   4  entry represents a subdirectory
   3  entry represents a volume label
   2  system file
   1  hidden file
   0  read-only
*/
function packAttribute($filename) {
	$_4 = (is_dir($filename)) ? 1 : 0;
	$_3 = 0;
	$_2 = 0;
	$_1 = 0;
	$_0 = (is_writable($filename)) ? 0 : 1;
	$dosAttribute =( $_4<<4)+($_3<<3)+($_2<<2)+($_1<<1)+$_0;
	return $dosAttribute;
}
/* Thanks to Marcin Szychowski 08-Oct-2009 01:59 
   MS-DOS related filesystems, along with ZIP files, store date and time in four bytes (time: 2 bytes, date: 2 bytes), as described in Wikipedia:
   http://en.wikipedia.org/wiki/File_Allocation_Table#Directory_table 
   bits   contents
     -----  ---------------------
     15-11  hour (0-23)
     10-5   minute (0-59)
     4-0    double seconds (0-29) 
*/
// $ts - standard UNIX timestamp, as returned by mktime()
function packTime($ts){
	$sec=round((('1'.date('s', $ts))-100)/2);
	$min=('1'.date('i', $ts))-100;
	$hour=date('G', $ts);

	$dosTime=($hour<<11)+($min<<5)+$sec;

	$m[0]=$dosTime%256;
	$m[1]=(($dosTime-$m[0])/256)%256;
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

// crc32 file checker using binary crc32sum.exe
function crc32_exe($filename) {
global $prgdir, $prg_option;
	log_echo(($prg_option['VERBOSITY']) ? '>crc32 ' : chr(46));
	$cmdline = 'CALL "'.$prgdir.'/crc32sum.exe" "'.$filename.'" ';
	exec($cmdline, $result, $retval);
//print_r($result);
	list($crc32) = split(' ', $result[0]);
	$crc = hexdec($crc32);
	// convert to long signed: From -2'147'483'648 to 2'147'483'647 or from -(2^31) to 2^31-1
	if ($crc > 2147483647) {
		$crc -= 4294967296;
	}
	return($crc);
}

// get file sizes > 4GB on Windows
function getSize($file) {
	$rfile = realpath($file);
	$size = -1;
	$fsobj = new COM("Scripting.FileSystemObject");
	$f = $fsobj->GetFile($rfile);
	$size = $f->Size;
	return $size;
}

// pack binary 8 Byte long integer
function pack64($sig) {
	$bits=64;
	$bin = base_convert($sig,10,2);
	// pad to 64 bits
	$bin_length = strlen($bin);
	if ($bin_length < $bits) $bin = str_repeat ( "0", $bits-$bin_length).$bin;
	// split to 8 Bytes
	$bsplit = str_split($bin, 8);
	$bout = '';
	for ($i = 7; $i >= 0; $i--) {
		// pack 8 Bytes
		$bout = $bout . pack("C", base_convert($bsplit[$i],2,10));
	}
	return $bout;
}

// Ersatz fnr PHP5 Funktion 'str_split'
// array str_split ( string $string [, int $split_length = 1 ] )
if(!function_exists('str_split')) {
  function str_split($string, $split_length = 1) {
    $array = explode("\r\n", chunk_split($string, $split_length));
    array_pop($array);
    return $array;
  }
}

/* A ZipFile object represents the entiry ZIP file, 
      method addZipFile() adds Files and Folders, 
      closeZipFile() finalizes the ZIP file.
   The DirectoryEntry object represents a ZIP Local file header for a specific file 
   as well as an entry for that file in the ZIP Central directory.
   An object of the derived class DirectoryEnd represents the End of central directory.
   Size and Offset in ZIP file are kept in two STATIC class variables.
   The getter function to the classes returns the binary header or directory entry 
   as string which can be written in the newly created ZIP file.
   
   Usage: 
     $zip = new ZipFile("123.zip");   // create zip file 123.zip
     $zip->addZipFile("test/file-1"); // add file-1 to 123.zip
     $zip->addZipFile("test/file-2"); // add file-2 to 123.zip
     $zip->addZipFile("test");        // add folder test to 123.zip
*    $zip->closeZipFile();            // finalize and close 123.zip
*/
// STATIC class variables (not supportet in PHP 4)
$entries_size = array();	// Filename and size of Central_file_header
$payload_size = array();	// Filename and size of Local_file_header + actual file size
$instance_flag = false;		// Flag indicating that an ZIP object is created

// 4.4.3 version needed to extract (2 bytes)
define('VERSION', 45);				// Version made by 
define('IS_FILE', 10);				// 1.0 - Default value
define('IS_FOLDER', 20);			// 2.0 - File is a folder (directory)
define('IS_ZIP64', 45);				// 4.5 - File uses ZIP64 format extensions
define('MAX_4G', 0xfffffffe);	// Max file size for non ZIP64
define('MAX_2O', 0xfffe);			// Max no of files for non ZIP64

// -----------------------------------------------------------------------------
class ZipFile {
	var $central_dir = ''; 				// Holds consecutive central_directory entries
	var $zipfile = '';						// ZIP file name
	var $fp_zipfile = null;				// ZIP file pointer
	
	function ZipFile($zipfile) {
	global $instance_flag;
		$this->zipfile = $zipfile;
		$this->fp_zipfile = fopen($this->zipfile , 'wb');
		if ($instance_flag === false) { $instance_flag = true; }
		else { die("Only one instance of class ZipFile at the same time"); }
	}
	
	function addZipFile($file) {
	global $prg_option, $entries_size;
		$type = IS_FILE;		// 1.0 - Default value
		if (isset($entries_size) and array_key_exists($file, $entries_size)) {
			// File or folder entry exists
			log_echo(($prg_option['VERBOSITY']) ? "\n  Entry exists: $file " : '');
			return;
		}
		if (is_dir($file)) {
			// Write folder to ZIP file
			$type = IS_FOLDER;
			log_echo(($prg_option['VERBOSITY']) ? "\n  addFolder: $file " : '');
			$fn = new DirectoryEntry("$file/", 0, $type);
			fwrite($this->fp_zipfile, $fn->getLocalFileHeader(0, $type));
		} 
		else {
			// Write file to ZIP file
			$filesize = getSize($file);
			$type = ($filesize > MAX_4G ? IS_ZIP64 : IS_FILE);
			log_echo(($prg_option['VERBOSITY']) ? "\n  addFile:   $file " : '');
			$fn = new DirectoryEntry($file, $filesize, $type);
			fwrite($this->fp_zipfile, $fn->getLocalFileHeader($filesize, $type));
			$this->writePayload($file);
			if ($type==IS_ZIP64) {
				fwrite($this->fp_zipfile, $fn->getDataDescriptor());
			} 
		}
		$this->central_dir = $this->central_dir . $fn->getCentralDirectoryEntry($type);
	}
	
	function closeZipFile() {
	global $instance_flag;
		fwrite($this->fp_zipfile, $this->central_dir);
		$fnd = new DirectoryEnd();
		fwrite($this->fp_zipfile, $fnd->getEndofCentralDirectoryRecord());
		fclose($this->fp_zipfile);
		$instance_flag = false;
	}
	
	// Read file from disk and append on ZIP file
	function writePayload($file) {
	global $prg_option;
		log_echo(($prg_option['VERBOSITY']) ? '>stuff ' : chr(46));
		$fh = fopen($file, 'rb');
		$buffer = '';
		while (!feof($fh)) {
			$buffer = fread($fh, 128000);
			fwrite($this->fp_zipfile, $buffer);
		}
		fclose($fh);
	}
	// Close ZIP file to generate MD5 over allready written part
	function getMD5overPayload() {
		fclose($this->fp_zipfile);
		$md5 = strtoupper(md5_file($this->zipfile));
		$this->fp_zipfile = fopen($this->zipfile, 'ab');
		return($md5);
	}
}

// -----------------------------------------------------------------------------
class DirectoryEntry extends ZipFile{
	var $struct;	// Structure holding Local File Header
	
	function DirectoryEntry($filename, $filesize, $type) {
	global $payload_size;
		$this->struct['Central_file_header_signature'] = pack("V", 0x02014b50);
		$this->struct['Local_file_header_signature']   = pack("V", 0x04034b50);
		$this->struct['Data_descriptor_signature']     = pack("V", 0x08074b50);
		$this->struct['Version_made_by']               = pack("v", VERSION);
		$this->struct['Version_needed_to_extract']     = pack("v", $type);	// 1.0 - default, 2.0 - folder, 4.5 - ZIP64 
		//$this->struct['General_purpose_bit_flag']      = pack("v", ($type==IS_ZIP64 ? 4 : 0));
		$this->struct['General_purpose_bit_flag']      = pack("v", 0);
		$this->struct['Compression_method']            = pack("v", 0);
		$this->struct['Last_mod_file_time']            = packTime(filemtime($filename));
		$this->struct['Last_mod_file_date']            = packDate(filemtime($filename));
		if ($filesize > 100000) {
			$this->struct['CRC-32']                        = pack("V", (is_dir($filename) ? 0 : crc32_exe($filename)));
		} else {
			$this->struct['CRC-32']                        = pack("V", (is_dir($filename) ? 0 : crc32(file_get_contents($filename))));
		}
		//$this->struct['CRC-32']                        = pack("V", (is_dir($filename) ? 0 : crc32_file($filename)));
		$this->struct['Compressed_size']               = pack("V", ($filesize > MAX_4G ? 0xffffffff : $filesize));
		$this->struct['Uncompressed_size']             = pack("V", ($filesize > MAX_4G ? 0xffffffff : $filesize));
		$this->struct['Compressed_size_8byte']         = pack64($filesize);
		$this->struct['Uncompressed_size_8byte']       = pack64($filesize);
		$this->struct['Filename_length']               = pack("v", strlen($filename));
		$this->struct['Extra_field_length']            = pack("v", ($type==IS_ZIP64 ? 20 : 0));
		$this->struct['File_comment_length']           = pack("v", 0);
		$this->struct['Disk_number_start']             = pack("v", 0);
		$this->struct['Internal_file_attributes']      = pack("v", 0);
		$this->struct['External_file_attributes']      = pack("V", packAttribute($filename));
		$this->struct['Rel_offset']                            = array_sum($payload_size);
		$this->struct['Relative_offset_of_local_header']       = pack("V", ($this->struct['Rel_offset'] > MAX_4G ? 0xffffffff : array_sum($payload_size)));
		$this->struct['Relative_offset_of_local_header_8byte'] = pack64($this->struct['Rel_offset']);
		$this->struct['Filename']                      = $filename;
		$this->struct['Extra_field']                   = '';
		$this->struct['File_comment']                  = '';
		$this->struct['Header_id']                     = pack("v", 1);	// Zip64 extended information extra field
		$this->struct['Data_size']                     = pack("v",16);	// Zip64 
	}
	
	function getLocalFileHeader($filesize, $type) {
	global $payload_size;
		$header = $this->struct['Local_file_header_signature'].
							$this->struct['Version_needed_to_extract'].
							$this->struct['General_purpose_bit_flag'] .
							$this->struct['Compression_method'].
							$this->struct['Last_mod_file_time'].
							$this->struct['Last_mod_file_date'].
							$this->struct['CRC-32'].
							$this->struct['Compressed_size'].
							$this->struct['Uncompressed_size'].
							$this->struct['Filename_length'].
							$this->struct['Extra_field_length'].
							$this->struct['Filename'].
							$this->struct['Extra_field'];
		if ($type == IS_ZIP64) {
			$header = $header . 
							$this->struct['Header_id'].
							$this->struct['Data_size'].
							$this->struct['Compressed_size_8byte'].
							$this->struct['Uncompressed_size_8byte'];
		}
		$filename = $this->struct['Filename'];
		$payload_size[$filename] = strlen($header) + $filesize;
		return($header);
	}
	
	function getDataDescriptor() {
	global $payload_size;
		$header = 	$this->struct['Data_descriptor_signature'].
					$this->struct['CRC-32'].
					$this->struct['Compressed_size_8byte'].
					$this->struct['Uncompressed_size_8byte'];
		$filename = $this->struct['Filename'];
		$payload_size[$filename] = $payload_size[$filename] + 24;
		return($header);
	}
	function getCentralDirectoryEntry($type) {
	global $entries_size, $payload_size;
		// ZIP64 Central directory entry extension field
		$entry64 = '';
		if ($type == IS_ZIP64) {
			$entry64 = 
							$this->struct['Compressed_size_8byte'].
							$this->struct['Uncompressed_size_8byte'];
		}
		if ($this->struct['Rel_offset'] > MAX_4G) {
			$entry64 .= $this->struct['Relative_offset_of_local_header_8byte'];
		}
		if (strlen($entry64) > 0) {
			$this->struct['Data_size'] = pack("v", strlen($entry64));
			$entry64 = 
							$this->struct['Header_id'].
							$this->struct['Data_size'].
							$entry64;
		}
		// Central directory entry
		$entry = $this->struct['Central_file_header_signature'].
							$this->struct['Version_made_by'].
							$this->struct['Version_needed_to_extract'].
							$this->struct['General_purpose_bit_flag'].
							$this->struct['Compression_method'].
							$this->struct['Last_mod_file_time'].
							$this->struct['Last_mod_file_date'].
							$this->struct['CRC-32'].
							$this->struct['Compressed_size'].
							$this->struct['Uncompressed_size'].
							$this->struct['Filename_length'].
							// $this->struct['Extra_field_length'].
							// length of ZIP64 extension field & extension field header
							pack("v", strlen($entry64)).
							$this->struct['File_comment_length'].
							$this->struct['Disk_number_start'].
							$this->struct['Internal_file_attributes'].
							$this->struct['External_file_attributes'].
							$this->struct['Relative_offset_of_local_header'].
							$this->struct['Filename'].
							$this->struct['Extra_field'].
							$this->struct['File_comment'];
		// Build Central directory entry 
		$entry .= $entry64;
		$filename = $this->struct['Filename'];
		$entries_size[$filename] = strlen($entry);
		return($entry);
	}
}

// -----------------------------------------------------------------------------
class DirectoryEnd extends ZipFile {
	var $struct;	// Structure holding End of Central Directory Record
	var $struct64;	// Structure holding ZIP64 End of Central Directory Record
	var $structloc;	// ZIP64 end of central directory locator
	
	function DirectoryEnd() {
	global $entries_size, $payload_size;
		$this->struct['End of_central_dir_signature']  = pack("V", 0x06054b50);
		$this->struct['Number_of_this_disk']                              = pack("v", 0);
		$this->struct['Disk_where_central_directory_starts']              = pack("v", 0);
		if (count($entries_size) > MAX_2O) {
			$this->struct['Number_of_central_directory_records_on_this_disk'] = pack("v", 0xffff);
			$this->struct['Total_number_of_central_directory_records']        = pack("v", 0xffff);
		} else {
			$this->struct['Number_of_central_directory_records_on_this_disk'] = pack("v", count($entries_size));
			$this->struct['Total_number_of_central_directory_records']        = pack("v", count($entries_size));
		}
		if (array_sum($entries_size) > MAX_4G) {
			$this->struct['Size_of_central_directory']                        = pack("V", 0xffffffff);
		} else {
			$this->struct['Size_of_central_directory']                        = pack("V", array_sum($entries_size));
		}
		if (array_sum($payload_size) > MAX_4G) {
			$this->struct['Offset_of_start_of_central_directory,_relative_to_start_of_archive'] = pack("V", 0xffffffff);
		} else {
			$this->struct['Offset_of_start_of_central_directory,_relative_to_start_of_archive'] = pack("V", array_sum($payload_size));
		}
		$this->struct['zipfile_comment_length']        = pack("v", 0);
		$this->struct['zipfile_comment']               = '';
		
		$this->struct64['End_of_central_dir_signature']  = pack("V", 0x06064b50);
		$this->struct64['size_of_zip64_end_of_central_directory_record']    = pack64(44);;
		$this->struct64['Version_made_by']                                  = pack("v", VERSION);
		$this->struct64['Version_needed_to_extract']                        = pack("v", IS_ZIP64);
		$this->struct64['Number_of_this_disk']                              = pack("V", 0);
		$this->struct64['Disk_where_central_directory_starts']              = pack("V", 0);
		$this->struct64['Number_of_central_directory_records_on_this_disk'] = pack64(count($entries_size));
		$this->struct64['Total_number_of_central_directory_records']        = pack64(count($entries_size));	
		$this->struct64['Size_of_central_directory']                        = pack64(array_sum($entries_size));
		$this->struct64['Offset_of_start_of_central_directory,_relative_to_start_of_archive'] = pack64(array_sum($payload_size));
		$this->struct64['zip64_extensible_data_sector']                     = '';

		$this->structloc['End_of_central_dir_signature']  = pack("V", 0x07064b50);
		$this->structloc['number_of_the_disk_with_the_start_of_the_zip64_end_ofcentral_directory'] = pack("V", 0);
		$this->structloc['relative_offset_of_the_zip64_end_of_central_directory_record']           = pack64(array_sum($payload_size) + array_sum($entries_size));
		$this->structloc['total_number_of_disks']                                                  = pack("V", 1);
	}

	function getEndofCentralDirectoryRecord() {
	global $entries_size, $payload_size;
		$end = '';
		if ((array_sum($payload_size) > MAX_4G) 
			or (array_sum($entries_size) > MAX_4G)
			or (count($entries_size) > MAX_2O)) {
			// ZIP64 End of Central Directory Record
			$end .= implode($this->struct64);
			// ZIP64 end of central directory locator
			$end .= implode($this->structloc);
		}
		// End of Central Directory Record
		$end .= implode($this->struct);
		return($end);
	}
}
?>
