<?php
// kompilieren: BAMCOMPILE.EXE -C -E:php_w32api.dll  DLL_test.php 
// Achtung php_w32api.dll in der kompilierten Version nicht mit 'dl' laden
//dl("php_w32api.dll");

$api = new win32;

$api->registerfunction("long testFunction ( ) From DLL_Tutorial.dll");
$api->registerfunction("int Add (int n, int m) From DLL_Tutorial.dll");

$api->testFunction( );

$result = $api->Add(32, 58);
echo "32 + 58 = $result\n";

exit(0);
?>
