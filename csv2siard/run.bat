@ECHO OFF

DEL /Q *.siard
ECHO --------- %time%
ECHO.
REM php.exe csv2siard.php NO_DB_MODEL datatype test.siard datatype\datatype.prefs
php.exe csv2siard.php datatype-utf8.xml datatype test.siard datatype\datatype.prefs
REM php.exe csv2siard.php gv-model-v9.xml odbc test.siard odbcdata\odbcdata.prefs
REM php.exe csv2siard.php gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs
REM php.exe csv2siard.php gv-model-v9.xml csvdata test.siard gvtg.prefs
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard csvtest\csvtest.prefs
REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard csvtest\csvtest.prefs
REM php.exe csv2siard.php datatype-utf8.xml csvtest test.siard csvtest\csvtest.prefs
REM php.exe csv2siard.php NO_DB_MODEL csvdata test.siard
REM php.exe csv2siard.php NO_DB_MODEL odbc test.siard sqldata\sqldata.prefs
REM php.exe csv2siard.php sqldata\gv-model-v9.xml odbc test.siard sqldata\sqldata.prefs
REM php.exe csv2siard.php sqldata\gv-model-v9.xml sqldata test.siard sqldata\sqldata.prefs

@ECHO OFF
ECHO.
ECHO ERRORCODE: %ERRORLEVEL%
if %ERRORLEVEL% == 0 (
	REM SIARD v.1.20 kann in MS Access importieren (Fehler in v.1.26)
	CALL "C:\Software\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.29\bin\SiardEdit.jar"
)
exit /b
