@ECHO OFF

echo --------- %time%
echo --------- %time% >> run_siardfromdb.log

C:\Software\siardsuite_1.20\siardfromdb.cmd -d ODBC -n SIARDACCESS -u Administrator -s s_large.siard -p ""

echo --------- %time%
echo --------- %time% >> run_siardfromdb.log

exit /b
