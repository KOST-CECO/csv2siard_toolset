<?
// Report all PHP errors
error_reporting(E_ALL);

include("crc32.php");

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
echo "crc32sum.exe\n";
echo date($_timeformat, time())."\n"; $s_time = time();

$cmdline = 'CALL "'.$prgdir.'/crc32sum.exe" "'.$testfile.'" ';
exec($cmdline, $result, $retval);
list($pruefsumme) = split(' ', $result[0]);

$e_time = time() - $s_time;
printf("%s", $pruefsumme); printf("\truntime(s): %d\n", $e_time);
echo date($_timeformat, time())."\n\n";

// -----------------------------------------------------------------------------
echo "php crc32_file()\n";
echo date($_timeformat, time())."\n"; $s_time = time();

$pruefsumme = crc32_file($testfile);

$e_time = time() - $s_time;
printf("%x", $pruefsumme); printf("\truntime(s): %d\n", $e_time);
echo date($_timeformat, time())."\n\n";

// -----------------------------------------------------------------------------
echo "php crc32()\n";
echo date($_timeformat, time())."\n"; $s_time = time();

$pruefsumme = crc32(file_get_contents($testfile));

$e_time = time() - $s_time;
printf("%x", $pruefsumme); printf("\truntime(s): %d\n", $e_time);
echo date($_timeformat, time())."\n\n";

exit(0);
?>
