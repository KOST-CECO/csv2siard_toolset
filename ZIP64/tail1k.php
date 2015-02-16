<?
// Report all PHP errors
error_reporting(E_ALL);

// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if (!@is_file($argv[1])) { 
	echo "No input file spezifide, usage: tail1k.exe <file>\n"; exit(-1);
}

$fh = fopen($argv[1], 'rb');
while (!feof($fh)) {
	$buffer = '';
	$buffer = fread($fh, 128000);
	$robin  = $buffer;
}
fclose($fh);

$buffer = $robin . $buffer;

echo substr($buffer, -1000);
exit(0)

?>
