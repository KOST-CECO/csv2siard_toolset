<?
// Report all PHP errors
error_reporting(E_ALL);
include 'c2sconfig.php';
include 'c2sfunction.php';
include 'c2sxml.php';
include 'c2odbc.php';

// Globals
$prefs                    = 'preferences.prefs';		// Preference file
$prg_option['CSV_FOLDER'] = '.';										// default path
$odbc_handle              = null;										// used if ODBC instead of CSV

// Read command line
if (!($argc == 3 or $argc == 2)) {
	echo("
       Usage :: odbcheck.exe sqlfile [prefs]
     sqlfile :: sql select statement or keyword :TABLES
       prefs :: configuration file (default) $prefs");
	exit(1);
}

// Specify preference file
if ($argc == 3) {
	$argc = 5; $argv[4] = $argv[2];
}
else {
	$argc = 5; $argv[4] = $prefs;
}

// Read preference file and open ODBC connection
readPreferences();

echo ansi2ascii(utf8_decode("$prg_option[DB_TYPE]:  "));
$prg_option['CONNECTION'] = ($prg_option['CSV_FOLDER'] == '') ? $prg_option['ODBC_DSN'] : $prg_option['ODBC_DSN'];
echo("$prg_option[CONNECTION]\n");

if (!$odbc_handle) {
	exit("Connection Failed: " . $odbc_handle);
}

// Analyse ODBC tables
$sqlfile = $argv[1];
if(strtoupper($sqlfile) == ':TABLES') {
	$columns = odbc_columns($odbc_handle);
	while ($row = odbc_fetch_array($columns)) {
		$tables[$row['TABLE_NAME']][$row['COLUMN_NAME']] = "$row[TYPE_NAME] ($row[COLUMN_SIZE])";
	}
	print_r($tables);
	exit();
}
// Read and trim SQL file
elseif(!is_file($sqlfile)) {
	exit("SQL specification file '$sqlfile' not found\n");
}
$query = trim(preg_replace('/\s[\s]+/',' ',strtr((file_get_contents($sqlfile)),"\x0A\x0D" , "  ")), '; ');

// Analyse record set
$columns = odbc_columns($odbc_handle);
while ($row = odbc_fetch_array($columns)) {
	if (preg_match('/'.$row['TABLE_NAME'].'/', $query)) {
		$coltype[$row['COLUMN_NAME']] = "$row[TYPE_NAME] ($row[COLUMN_SIZE])";
	}
}
print_r($coltype);

// Open ODBC record set
$recordset = @odbc_exec($odbc_handle, $query);
if (!$recordset) {
	exit(odbc_errormsg() . "\n\n" . "Error in SQL command '$query'\n");
}

// Loop record set
$recordcount = 0;
$row = array();
while ($row = odbc_fetch_array ($recordset)) {
	if ($recordcount == 0) {
		foreach ($row as $key => $value) {
			$header[] = $key;
		}
		echo strtoupper(implode($prg_option['DELIMITED'], $header))."\n";
	}
	echo ansi2ascii(implode($prg_option['DELIMITED'], $row))."\n";
	$recordcount++;
}
echo("\nResult row count: $recordcount\n");
odbc_close($odbc_handle);
exit(0);
?>
