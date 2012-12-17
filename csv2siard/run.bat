@ECHO ON
@DEL /Q *.siard
@ECHO --------- %time% --------- 
php.exe csv2siard.php table2-model.xml csvdata test.siard 
@DEL /Q *.siard
php.exe csv2siard.php table2-model.xml csvdata test.siard :LOG_FILE=log.txt
@DEL /Q *.siard
php.exe csv2siard.php table2-model.xml csvdata test.siard preferences.prefs :LOG_FILE=log.txt
@DEL /Q *.siard
@ECHO --------- %time% --------- 
@REM php.exe csv2siard.php :NO_DB_MODEL :ODBC test.siard odbc.prefs
@REM php.exe csv2siard.php northwind.xml :ODBC test.siard odbc.prefs
@REM php.exe csv2siard.php gv-model-v9.xml :ODBC test.siard odbc.prefs
@REM csv2siard.exe gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs
@REM php.exe csv2siard.php :NO_DB_MODEL :ODBC test.siard odbcsql\odbcsql.prefs
@REM php.exe csv2siard.php :NO_DB_MODEL csvdata test.siard 
@REM php.exe csv2siard.php gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs
@REM csv2siard.exe gv-model-v9.xml odbc test.siard odbcdata\odbcdata.prefs
@REM csv2siard.exe gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs

@ECHO OFF
EXIT /b

ECHO.
ECHO ERRORCODE: %ERRORLEVEL%
if %ERRORLEVEL% == 0 (
	REM SIARD v.1.20 kann in MS Access importieren (Fehler in v.1.26)
	CALL C:\Software\jre6\bin\javaw.exe -jar "C:\Software\siardsuite_1.20\bin\SiardEdit.jar"
	REM CALL C:\Software\jre6\bin\javaw.exe -jar "C:\Software\SIARD Suite-1.44\bin\SiardEdit.jar"
)
EXIT /b
