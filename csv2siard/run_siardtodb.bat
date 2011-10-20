@ECHO OFF

echo --------- %time%
echo --------- %time% >> run_siardtodb.log

C:\Software\siardsuite_1.20\siardtodb.cmd -d ODBC -n SIARDACCESS -u Administrator -s large.siard -p ""

echo --------- %time%
echo --------- %time% >> run_siardtodb.log

exit /b
