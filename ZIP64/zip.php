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

/* A DirectoryEntry object represents a ZIP Local file header for a specific file as well as an entry for that file in the ZIP Central directory.
   An object of the derived class DirectoryEnd represents the End of central directory.
   Size and Offset in ZIP file are kept in two STATIC class variables.
   The getter function to the classes returns the binary header or directory entry as string which can be written in the newly created ZIP file.
   Pseudo usage: 
     fn1 = new DirectoryEntry(filename1)
     write fn1->getLocalFileHeader() to file.zip
     write filename1 to file.zip
     fn2 = new DirectoryEntry(filename2)
     write fn2->getLocalFileHeader() to file.zip
     write filename2 to file.zip
            . . .
     write fn1->getCentralDirectoryEntry() to file.zip
     write fn2->getCentralDirectoryEntry() to file.zip
            . . .
     fnd = new DirectoryEnd()
     write fnd->getEndofCentralDirectoryRecord() to file.zip
*/
// STATIC class variables (not supportet in PHP 4)
$_entries_size = array(); // Filename and size of Central_file_header
$_payload_size = array(); // Filename and size of Local_file_header + actual file size

class DirectoryEntry {
	var $struct;	// Structure holding Local File Header
	
	function DirectoryEntry($filename) {
	global $_entries_size, $_payload_size, $_prgdir;
		$this->struct['Central_file_header_signature'] = pack("V", 0x02014b50);
		$this->struct['Local_file_header_signature']   = pack("V", 0x04034b50);
		$this->struct['Version_made_by']               = pack("v", 19);
		$this->struct['Version_needed_to_extract']     = pack("v", 10);
		$this->struct['General_purpose_bit_flag']      = pack("v", 0);
		$this->struct['Compression_method']            = pack("v", 0);
		$this->struct['Last_mod_file_time']            = packTime(filemtime($filename));
		$this->struct['Last_mod_file_date']            = packDate(filemtime($filename));
		if (is_dir($filename)) { 
			$this->struct['CRC-32']                      = pack("V", 0); }
		else {
			$cmdline = 'CALL "'.$_prgdir.'/crc32sum.exe" "'.$filename.'" ';
			exec($cmdline, $result, $retval);
			list($crc32) = split(' ', $result[0]);
			list($crc32) = sscanf($crc32, "%x");
			$this->struct['CRC-32']                      = pack("V", $crc32);
			//$this->struct['CRC-32']                      = pack("V", crc32_file($filename));
			//$this->struct['CRC-32']                      = pack("V", crc32(file_get_contents($filename)));
		}
		$this->struct['Compressed_size']               = pack("V", filesize($filename));
		$this->struct['Uncompressed_size']             = pack("V", filesize($filename));
		$this->struct['Filename_length']               = pack("v", strlen($filename));
		$this->struct['Extra_field_length']            = pack("v", 0);
		$this->struct['File_comment_length']           = pack("v", 0);
		$this->struct['Disk_number_start']             = pack("v", 0);
		$this->struct['Internal_file_attributes']      = pack("v", 0);
		$this->struct['External_file_attributes']      = pack("V", packAttribute($filename));
		$this->struct['Relative_offset_of_local_header'] = pack("V", array_sum($_payload_size));
		$this->struct['Filename']                      = $filename;
		$this->struct['Extra_field']                   = '';
		$this->struct['File_comment']                  = '';
	}
	
	function getLocalFileHeader() {
	global $_payload_size;
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
		$filename = $this->struct['Filename'];
		$_payload_size[$filename] = strlen($header) + filesize($filename);
		return($header);
	}
	
	function getCentralDirectoryEntry() {
	global $_entries_size;
		$entry  = $this->struct['Central_file_header_signature'].
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
							$this->struct['Extra_field_length'].
							$this->struct['File_comment_length'].
							$this->struct['Disk_number_start'].
							$this->struct['Internal_file_attributes'].
							$this->struct['External_file_attributes'].
							$this->struct['Relative_offset_of_local_header'].
							$this->struct['Filename'].
							$this->struct['Extra_field'].
							$this->struct['File_comment'];
		$filename = $this->struct['Filename'];
		$_entries_size[$filename] = strlen($entry);
		return($entry);
	}
}

class DirectoryEnd extends DirectoryEntry {
	var $struct;	// Structure holding Local File Header
	
	function DirectoryEnd() {
	global $_entries_size, $_payload_size;
		$this->struct['End of_central_dir_signature']  = pack("V", 0x06054b50);
		$this->struct['Number_of_this_disk']                              = pack("v", 0);
		$this->struct['Disk_where_central_directory_starts']              = pack("v", 0);
		$this->struct['Number_of_central_directory_records_on_this_disk'] = pack("v", count($_entries_size));
		$this->struct['Total_number_of_central_directory_records']        = pack("v", count($_entries_size));
		$this->struct['Size_of_central_directory']                        = pack("V", array_sum($_entries_size));
		$this->struct['Offset_of_start_of_central_directory,_relative_to_start_of_archive'] = pack("V", array_sum($_payload_size));
		$this->struct['zipfile_comment_length']        = pack("v", 0);
		$this->struct['zipfile_comment']               = '';
	}

	function getEndofCentralDirectoryRecord() {
		$end    = $this->struct['End of_central_dir_signature']  = pack("V", 0x06054b50).
							$this->struct['Number_of_this_disk'].
							$this->struct['Disk_where_central_directory_starts'].
							$this->struct['Number_of_central_directory_records_on_this_disk'].
							$this->struct['Total_number_of_central_directory_records'].
							$this->struct['Size_of_central_directory'].
							$this->struct['Offset_of_start_of_central_directory,_relative_to_start_of_archive'].
							$this->struct['zipfile_comment_length'].
							$this->struct['zipfile_comment'];
		return($end);
	}
}

// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Global
$_prgdir = dirname(realpath($argv[0]));				//Program directory
$_central_dir = ''; // Holds consecutive central_directory entries
// -----------------------------------------------------------------------------
function addFolder($fp, $folder) {
global $_central_dir;
	// loop through folder
	$dh = opendir($folder);
	while (($file = readdir($dh)) !== false) {
		if ($file != "." && $file != "..") {
			if (is_dir("$folder/$file")) {
				addFolder($fp, "$folder/$file");
			}
			else {
				addFile($fp, "$folder/$file");
			}
		}
	}
	closedir($dh);
	// Write folder to ZIP file
	echo "  addFolder: $folder/\n";
	$fn = new DirectoryEntry("$folder/");
	fwrite($fp, $fn->getLocalFileHeader());
	$_central_dir = $_central_dir . $fn->getCentralDirectoryEntry();
}
// -----------------------------------------------------------------------------
function addFile($fp, $file) {
global $_central_dir;
echo "  addFile:   $file\n";
	$fn = new DirectoryEntry($file);
	fwrite($fp, $fn->getLocalFileHeader());
	writePayload($fp, $file);
	$_central_dir = $_central_dir . $fn->getCentralDirectoryEntry();
}
// -----------------------------------------------------------------------------
// Read and write payload file with max. block size (8192) into zip file
function writePayload($fp, $file) {
	$fh = fopen($file, 'rb');
	$buffer = '';
	while (!feof($fh)) {
		$buffer = fread($fh, 8192);
		fwrite($fp, $buffer);
	}
	fclose($fh);
}

// -----------------------------------------------------------------------------
if (!@is_dir($argv[1])) { 
	echo "no directory spezifide, usage: zip.exe <directory>\n"; exit(-1);
}
$startfolder = str_replace('\\', '/', $argv[1]);
$startfolder = str_replace('//', '/', $startfolder);
$startfolder = rtrim($startfolder, '/');

$fp = fopen("$startfolder.zip" , 'wb');
addFolder($fp, $startfolder);

fwrite($fp, $_central_dir);

$fnd = new DirectoryEnd();
fwrite($fp, $fnd->getEndofCentralDirectoryRecord());

fclose($fp);
exit(0);
?>
