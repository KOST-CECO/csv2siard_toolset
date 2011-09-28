<?
// Report all PHP errors
// error_reporting(E_ALL);

class Local_file_header {
	var $struct;	// Structure holding Local File Header
	var $header;
	
	function Local_file_header($Filename) {
		$this->struct['Local_file_header_signature'] = pack("V", 0x04034b50);
		$this->struct['Version_needed_to_extract'] = pack("v", 0);
		$this->struct['General_purpose_bit_flag'] = pack("v", 0);
		$this->struct['Compression_method'] = pack("v", 0);
		$this->struct['Last_mod_file_time'] = pack("v", 0);
		$this->struct['Last_mod_file_date'] = pack("v", 0);
		$this->struct['CRC-32'] = pack("v", 0);
		$this->struct['Compressed_size'] = pack("v", 0);
		$this->struct['Uncompressed_size'] = pack("v", 0);
		$this->struct['Filename_length'] = pack("v", strlen($Filename));
		$this->struct['Extra_field_length'] = pack("v", 0);
		$this->struct['Filename'] = $Filename;
		$this->struct['Extra_field'] = '';
		echo $Filename;
	}
	
	function Structur() {
		print_r($this->struct);
		foreach $el in $this->struct {
			$this->header = $this->header.$el;
		}
		return($this->header);
	}
}


// MAIN ------------------------------------------------------------------------

$lfh = new Local_file_header("content/table0/table0.xml");
print_r($lfh->Structur());

?>
