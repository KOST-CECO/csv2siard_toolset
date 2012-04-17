<?

//SELECT gv_gebaeude.police_nr, gv_gebaeude.zweck_text, gv_person.rolle_text, gv_person.name
//FROM gv_gebaeude INNER JOIN gv_person ON gv_gebaeude.id = gv_person.gebaeude_id;

$conn=odbc_connect('Driver={Microsoft Excel Driver (*.xls)};Dbq=C:/TEMP/gvtg2.xls' , '', '');
//$conn=odbc_connect('Driver={Microsoft Excel Driver (*.xls)};DriverId=790;Dbq=C:/TEMP/gvtg2.xls;DefaultDir=C:/TEMP/' , '', '');
//$conn=odbc_connect('Driver={Microsoft Excel Driver (*.xls)};DriverId=790;Dbq=C:/TEMP/gvtg2.xls;DefaultDir=C:/TEMP/','','');
//$conn=odbc_connect('Driver={Microsoft Excel Driver (*.xls)};DriverId=790;Dbq=P:\KOST\Tools\csv2siard\_workbench\gvtg.xls;DefaultDir=P:\KOST\Tools\csv2siard\_workbench' , '', '');
//$conn=odbc_connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=P:\KOST\Tools\csv2siard\_workbench\gvtg.mdb','','');
//$conn=odbc_connect('Driver={Microsoft Access Text Driver (*.txt, *.csv)};Dbq=P:\KOST\Tools\csv2siard\_workbench\csvtext','','');
//$conn=odbc_connect('csvtext','','');
if (!$conn) {
        exit("Connection Failed: " . $conn);
}

echo "--------------------------------------------------------------------------------------------\n";

   $result = odbc_tables($conn);
   odbc_result_all($result);

$rs=odbc_exec($conn,'SELECT * FROM [gv_anlage$]');
if (!$rs) {
        exit("Error in SQL");
}

while( $row = odbc_fetch_array($rs) ) { 
    print_r($row); 
}
exit;


echo "--------------------------------------------------------------------------------------------\n";
$tabs = odbc_tables($conn);
$tables = array();
while (odbc_fetch_row($tabs)){
    if (odbc_result($tabs,"TABLE_TYPE")=="TABLE") {
        $table_name = odbc_result($tabs,"TABLE_NAME");
        $tables["{$table_name}"] = array();
        $cols = odbc_exec($conn,'select * from `'.$table_name.'` where 1=2');  // we don't want content
      $ncols = odbc_num_fields($cols);
        for ($n=1; $n<=$ncols; $n++) {
            $field_name = odbc_field_name($cols, $n);
            $tables["{$table_name}"]["{$field_name}"]['len'] = odbc_field_len($cols, $n);
            $tables["{$table_name}"]["{$field_name}"]['type'] = odbc_field_type($cols, $n);
        }
    }
}
print_r($tables);
echo "--------------------------------------------------------------------------------------------\n";

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
