<?
// Report all PHP errors

error_reporting(E_ALL);

// -----------------------------------------------------------------------------
function pack_64bit($big) {
	// sample 4.23 GB = 4552192788
	$left  = 0xffffffff00000000;
	$right = 0x00000000ffffffff;
	
	$l = ($big & $left) >>32;
	$r = $big & $right;

	$good = pack('NN', $l, $r); 
	return $good;
}

// -----------------------------------------------------------------------------
// signed integer to binary
function si2bin($si, $bits=32)
{
    if ($si >= -pow(2,$bits-1) and $si <= pow(2,$bits-1) )
    {
        if ($si >= 0) // positive or zero
        {
            $bin = base_convert($si,10,2);
            // pad to $bits bit
            $bin_length = strlen($bin);
            if ($bin_length < $bits) $bin = str_repeat ( "0", $bits-$bin_length).$bin;
        }
        else // negative
        {
            $si = -$si-pow(2,$bits);
            $bin = base_convert($si,10,2);
            $bin_length = strlen($bin);
            if ($bin_length > $bits) $bin = str_repeat ( "1", $bits-$bin_length).$bin;
        }
        return $bin;
    }
}

// -----------------------------------------------------------------------------
// binary to signed integer
function bin2si($bin,$bits=32)
{
    if (strlen($bin)==$bits)
    {
        if (substr($bin,0,1) == 0) // positive or zero
        {
            $si = base_convert($bin,2,10);
        }
        else // negative
        {
            $si = base_convert($bin,2,10);
            $si = -(pow(2,$bits)-$si);
        }
        return $si;
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
function pack64show($sig) {
	echo "$sig\n";
	$bits=64;
	$bin = base_convert($sig,10,2);
	// pad to $bits bit
	$bin_length = strlen($bin);
	if ($bin_length < $bits) $bin = str_repeat ( "0", $bits-$bin_length).$bin;
	echo "$bin\n";
	$bsplit = str_split($bin, 8);
	echo "$bsplit[0]\n";
	echo "        $bsplit[1]\n";
	echo "                $bsplit[2]\n";
	echo "                        $bsplit[3]\n";
	echo "                                $bsplit[4]\n";
	echo "                                        $bsplit[5]\n";
	echo "                                                $bsplit[6]\n";
	echo "                                                        $bsplit[7]\n";
	
	$out = '';
	for ($i = 7; $i >= 0; $i--) {
		$hex = base_convert($bsplit[$i],2,16);
		if (strlen($hex) == 1) {
			$hex = "0".$hex;
		}
		$out = $out . pack("C", base_convert($bsplit[$i],2,10));
		echo "0x$hex = " . base_convert($bsplit[$i],2,10) . "\n";
	}
	return $out;
}

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

// ++++++++++++++++++++++++++++++++++++++++++++++++++++
$fp = fopen("test.txt" , 'wb');
echo "4552192788\n\n";

//echo pack_64bit(4552192788);
//echo "\n\n"; 

//echo si2bin(4552192788, 64);
//echo "\n\n";

$b = pack64(4552192788);

fwrite($fp, $b);
fclose($fp);
?>
