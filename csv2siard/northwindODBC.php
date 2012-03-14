<?

$conn=odbc_connect('northwind','','');
if (!$conn) {
	exit("Connection Failed: " . $conn);
}

$sql="SELECT * FROM customers";
$rs=odbc_exec($conn,$sql);
if (!$rs) {
	exit("Error in SQL");
}

echo "CompanyName; ContactName\n";

while (odbc_fetch_row($rs)) {
	$compname=odbc_result($rs,"CompanyName");
	$conname=odbc_result($rs,"ContactName");
	echo "$compname; $conname\n";
}
odbc_close($conn);

?>
