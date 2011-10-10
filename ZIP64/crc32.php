<?php
/*
if you are looking for a fast function to hash a file, take a look at
http://www.php.net/manual/en/function.hash-file.php
this is crc32 file checker based on a CRC32 guide
it have performance at ~ 625 KB/s on my 2.2GHz Turion
far slower than hash_file('crc32b','filename.ext')
*/
function crc32_file ($filename)
{
	$f = @fopen($filename,'rb');
	if (!$f) return false;
	
	static $CRC32Table, $Reflect8Table;
	if (!isset($CRC32Table))
	{
		$Polynomial = 0x04c11db7;
		$topBit = 1 << 31;
		 
		for($i = 0; $i < 256; $i++) 
		{ 
			$remainder = $i << 24;
			for ($j = 0; $j < 8; $j++)
			{
				if ($remainder & $topBit)
					$remainder = ($remainder << 1) ^ $Polynomial;
				else $remainder = $remainder << 1;
			}
			
			$CRC32Table[$i] = $remainder;
			
			if (isset($Reflect8Table[$i])) continue;
			$str = str_pad(decbin($i), 8, '0', STR_PAD_LEFT);
			$num = bindec(strrev($str));
			$Reflect8Table[$i] = $num;
			$Reflect8Table[$num] = $i;
		}
	}
	
	$remainder = 0xffffffff;
	while ($data = fread($f,64000))
	{
		$len = strlen($data);
		for ($i = 0; $i < $len; $i++)
		{
			$byte = $Reflect8Table[ord($data[$i])];
			$index = (($remainder >> 24) & 0xff) ^ $byte;
			$crc = $CRC32Table[$index];
			$remainder = ($remainder << 8) ^ $crc;
		}
	}
	
	$str = decbin($remainder);
	$str = str_pad($str, 32, '0', STR_PAD_LEFT);
	$remainder = bindec(strrev($str));
	return $remainder ^ 0xffffffff;
}
?>

