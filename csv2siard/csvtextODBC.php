<?

//SELECT gv_gebaeude.police_nr, gv_gebaeude.zweck_text, gv_person.rolle_text, gv_person.name
//FROM gv_gebaeude INNER JOIN gv_person ON gv_gebaeude.id = gv_person.gebaeude_id;


$conn=odbc_connect('csvtext','','');
if (!$conn) {
	exit("Connection Failed: " . $conn);
}

//$sql="SELECT * FROM gv_gebaeude.csv";
$sql="SELECT gv_gebaeude.police_nr, gv_gebaeude.zweck_text, gv_person.rolle_text, gv_person.name FROM gv_gebaeude.csv AS gv_gebaeude INNER JOIN gv_person.csv AS gv_person ON gv_gebaeude.id = gv_person.gebaeude_id";
$rs=odbc_exec($conn,$sql);
if (!$rs) {
	exit("Error in SQL");
}

echo "police_nr; zweck_text; rolle_text; name\n";

while (odbc_fetch_row($rs)) {
	$police_nr=odbc_result($rs,"police_nr");
	$zweck_text=odbc_result($rs,"zweck_text");
	//echo "$police_nr; $zweck_text\n";
	$rolle_text=odbc_result($rs,"rolle_text");
	$name=odbc_result($rs,"name");
	echo "$police_nr; $zweck_text; $rolle_text; $name\n";
}
odbc_close($conn);

?>
