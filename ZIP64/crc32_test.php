<?
// Report all PHP errors
error_reporting(E_ALL);

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
	while ($data = fread($f,8192))
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


// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Global
$wdir = '.'; $wdir = realpath($wdir);								// Arbeitsverzeichnis
$prgname = strtolower(basename($argv[0], '.exe'));	// ProgrammName
$prgname = basename($prgname, '.php');							// ProgrammName
$prgdir  = dirname(realpath($argv[0]));							//Programmverzeichnis

// MySQL YY "-" MM "-" DD " " HH ":" II ":" SS "2008-08-07 18:11:31" 
$_timeformat = 'Y-m-d H:i:s';

// -----------------------------------------------------------------------------
if (!@is_file($argv[1])) { 
	echo "no file specifide, usage: CRC-test.exe <file>\n"; exit(-1);
}
$testfile = str_replace('\\', '/', $argv[1]);
$testfile = str_replace('//', '/', $testfile);

// -----------------------------------------------------------------------------
echo date($_timeformat, time())."\n"; $s_time = time();

$pruefsumme = crc32(file_get_contents($testfile));

$e_time = time() - $s_time;
printf("%x", $pruefsumme); printf("\truntime(s): %d\n", $e_time);
echo date($_timeformat, time())."\n\n";

// -----------------------------------------------------------------------------
echo date($_timeformat, time())."\n"; $s_time = time();

$cmdline = 'CALL "'.$prgdir.'/crc32sum.exe" "'.$testfile.'" ';
exec($cmdline, $result, $retval);
list($pruefsumme) = split(' ', $result[0]);

$e_time = time() - $s_time;
printf("%s", $pruefsumme); printf("\truntime(s): %d\n", $e_time);
echo date($_timeformat, time())."\n\n";

// -----------------------------------------------------------------------------
echo date($_timeformat, time())."\n"; $s_time = time();

$pruefsumme = crc32_file($testfile);

$e_time = time() - $s_time;
printf("%x", $pruefsumme); printf("\truntime(s): %d\n", $e_time);
echo date($_timeformat, time())."\n\n";

exit(0);
?>
