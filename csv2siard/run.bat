@ECHO OFF
DEL /Q test.siard
echo --------- %time%
csv2siard.exe datatype-model.xml csvtest test.siard csvtest/csvtest.prefs
REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard csvtest/csvtest.prefs

REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard
REM php.exe csv2siard.php gv-model-v8.xml csvdata test.siard
REM php.exe csv2siard.php NO_DB_MODEL csvdata test.siard
REM csv2siard.exe gv-model-v8.xml ../9_Testdaten/csvdata_TG gvtg.siard
REM csv2siard.exe NO_DB_MODEL ../9_Testdaten/csvdata_TG test.siard
echo --------- %time%
@ECHO OFF
if %ERRORLEVEL% == 0 (
	CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.26\bin\SiardEdit.jar"
)
exit /b

