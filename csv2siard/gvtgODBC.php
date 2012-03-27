<?

//SELECT gv_gebaeude.police_nr, gv_gebaeude.zweck_text, gv_person.rolle_text, gv_person.name
//FROM gv_gebaeude INNER JOIN gv_person ON gv_gebaeude.id = gv_person.gebaeude_id;

$conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=P:\KOST\Tools\csv2siard\_workbench\sqldata\gvtg.mdb", '', '');
//$conn = odbc_connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=P:\KOST\Pilotloesungen\Gebaeudeversicherung\06_GV-Viewer\gvtg.mdb", '', '');
//$conn=odbc_connect('gvtg','Admin','');
if (!$conn) {
	exit("Connection Failed: " . $conn);
}

//$sql="SELECT * FROM gv_gebaeude";
$sql="SELECT gv_gebaeude.police_nr, gv_gebaeude.zweck_text, gv_person.rolle_text, gv_person.name FROM gv_gebaeude INNER JOIN gv_person ON gv_gebaeude.id = gv_person.gebaeude_id";
$rs=odbc_exec($conn,$sql);
if (!$rs) {
	exit("Error in SQL");
}

echo "police_nr; zweck_text; rolle_text; name\n";

while (odbc_fetch_row($rs)) {
	$police_nr=odbc_result($rs,"police_nr");
	$zweck_text=odbc_result($rs,"zweck_text");
	$rolle_text=odbc_result($rs,"rolle_text");
	$name=odbc_result($rs,"name");
	echo "$police_nr; $zweck_text; $rolle_text; $name\n";
}
odbc_close($conn);

?>
