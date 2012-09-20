<?

$fp = fopen('php://stderr', 'w');
$now = date("H:i:s"); fputs($fp, "$now\n");
$start = time();

fputs($fp, "open ODBC connection\n");
//$conn=odbc_connect('Driver={Microsoft Access Text Driver (*.txt, *.csv)};Dbq=./odbcdata', 'Admin' ,'' , SQL_CUR_USE_ODBC);
$conn=odbc_connect('Driver={Microsoft Access Text Driver (*.txt, *.csv)};Dbq=./odbcdata', 'Admin' ,'');
if (!$conn) {
	exit("Connection Failed: " . $conn);
}

//$sql = "SELECT * FROM SPO.csv";
$sql = "
SELECT 
	SchaetzPosId AS id, 
	SchaetzPosSchaetzungId AS Schaetzung_id, 
	SchaetzPosArtDt AS art, 
	SchaetzPosText AS [text], 
	SchaetzPosVolumen AS volumen, 
	SchaetzPosVersWert AS versicherungswert, 
	SchaetzPosLaenge AS laenge, 
	SchaetzPosBreite AS breite, 
	SchaetzPosHoehe AS hoehe, 
	SchaetzPosAbnutzProz AS abnutzung, 
	SchaetzPosErfassWert AS erfasster_wert 
FROM spo.csv 
ORDER  BY 
		SchaetzPosSchaetzungId, 
		SchaetzPosGebaeudeId, 
		SchaetzPosSortierung, 
		SchaetzPosSortierung2;
";

$sql = "SELECT * FROM SPO.csv";

fputs($fp, "open ODBC recordset\n");
$rcount = 0;
$rs = odbc_exec($conn, $sql);
if (!$rs) {
	exit("Error in SQL");
}

//echo "id; Schaetzung_id; art; text; versicherungswert\n";
echo "SchaetzPosId; SchaetzPosSchaetzungId; SchaetzPosGebaeudeId; SchaetzPosArtDt; SchaetzPosText\n";
while (odbc_fetch_row($rs)) {
	$rcount++;
	/*
	$f0 = odbc_result($rs,"id");
	$f1 = odbc_result($rs,"Schaetzung_id");
	$f2 = odbc_result($rs,"art");
	$f3 = odbc_result($rs,"text");
	$f4 = odbc_result($rs,"versicherungswert");
	echo "$f0; $f1; $f2; $f3; $f4\n";
	*/
	$f0 = odbc_result($rs,"SchaetzPosId");
	$f1 = odbc_result($rs,"SchaetzPosSchaetzungId");
	$f2 = odbc_result($rs,"SchaetzPosGebaeudeId");
	$f3 = odbc_result($rs,"SchaetzPosArtDt");
	$f4 = odbc_result($rs,"SchaetzPosText");
	if ($rcount % 10000 == 0) {
		fputs($fp, "records written: $rcount\n");
	}
}
fputs($fp, "total records written: $rcount\n");
fputs($fp, "close ODBC connection\n");
odbc_close($conn);

$ex = time() - $start;
fputs($fp, "execution time: $ex sec\n");
$now = date("H:i:s"); fputs($fp, "$now\n");
fclose($fp);
?>
