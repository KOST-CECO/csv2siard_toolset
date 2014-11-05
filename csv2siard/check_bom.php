<?
error_reporting(E_ALL);

include 'c2sfunction.php';

$row = 1;
if (($handle = fopen("bom.csv", "r")) !== FALSE) {
    check4BOM($handle);
    
    while (($data = fgetcsv($handle, 1000, ';', '"')) !== FALSE) {
        $num = count($data);
        echo "\n$num Felder in Zeile $row:\n";
        $row++;
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "\n";
        }
    }
    fclose($handle);
}
?> 
