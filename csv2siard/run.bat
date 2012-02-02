@ECHO OFF
DEL /Q test.siard gvtg.siard gvtg(vertraulich).siard
echo --------- %time%
REM csv2siard.exe NO_DB_MODEL P:\KOST\Pilotloesungen\Gebaeudeversicherung\9_Testdaten\csvdata_TG(vertraulich) gvtg(vertraulich).siard
csv2siard.exe gv-model-v9.xml P:\KOST\Pilotloesungen\Gebaeudeversicherung\9_Testdaten\csvdata_TG(vertraulich) gvtg(vertraulich).siard
REM csv2siard.exe gv-model-v9.xml csvdata gvtg.siard
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard csvtest/csvtest.prefs
REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard csvtest/csvtest.prefs

REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard
REM php.exe csv2siard.php gv-model-v9.xml csvdata test.siard
REM php.exe csv2siard.php NO_DB_MODEL csvdata test.siard
REM csv2siard.exe gv-model-v9.xml ../9_Testdaten/csvdata_TG gvtg.siard
REM csv2siard.exe NO_DB_MODEL ../9_Testdaten/csvdata_TG test.siard
echo --------- %time%
@ECHO OFF
if %ERRORLEVEL% == 0 (
	REM SIARD v.1.20 kann in MS Access importieren (Fehler in v.1.26)
	CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.20\bin\SiardEdit.jar"
)
exit /b

