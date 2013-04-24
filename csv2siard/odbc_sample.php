<?

//SQL_CUR_USE_IF_NEEDED,SQL_CUR_USE_ODBC ,SQL_CUR_USE_DRIVER SQL_CUR_DEFAULT 
//$odbc_handle = odbc_connect("Driver={Microsoft Access Text Driver (*.txt, *.csv)};Dbq=./testdata", '', '', SQL_CUR_USE_ODBC );
$odbc_handle = odbc_connect("Driver={Microsoft Access Text Driver (*.txt, *.csv)};Dbq=./testdata", '', '', SQL_CUR_USE_DRIVER );
if (!$odbc_handle) {
        exit("Connection Failed: " . $odbc_handle);
}

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
ORDER BY 
		SchaetzPosSchaetzungId, 
		SchaetzPosSortierung, 
		SchaetzPosSortierung2; ";

$recordset = odbc_exec($odbc_handle, $sql);
if (!$recordset) {
        exit("Error in SQL");
}

$recordcount = 0;
$row = array();
while ($row = odbc_fetch_array ($recordset)) {
	if ($recordcount == 0) {
		foreach ($row as $key => $value) {
			$header[] = $key;
		}
		echo strtoupper(implode(';', $header))."\n";
	}
	echo implode(';', $row)."\n";
	$recordcount++;
}
echo "\nResult row count: $recordcount\n";
odbc_close($odbc_handle);
?>
