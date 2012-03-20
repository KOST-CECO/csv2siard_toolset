@ECHO OFF

DEL /Q *.siard
ECHO --------- %time%
ECHO.
php.exe csv2siard.php sqldata\gv-model-v9.xml sqldata test.siard sqldata\csvtext.prefs
hexdump sqldata\gv_person.sql.txt | grep Martin
hexdump sqldata\gv_person.sqlm.txt | grep Martin

REM csv2siard.exe sqldata\gv-model-v9.xml sqldata_mdb test.siard sqldata\csvtext.prefs
REM php.exe csv2siard.php gv-model-v9.xml csvdata test.siard csvdata\gvtg.prefs
REM csv2siard.exe sqldata\gv-model-v9.xml sqldata test.siard sqldata\gvtg.prefs

REM php.exe csv2siard.php no_db_model csvtest test.siard csvtest\csvtest.prefs
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard csvtest\csvtest.prefs
REM php.exe csv2siard.php gv-model-v9.xml csvdata test.siard csvdata\gvtg.prefs

REM csv2siard.exe gv-model-v9.xml P:\KOST\Pilotloesungen\Gebaeudeversicherung\4_GV-Viewer\csvdata P:\KOST\Pilotloesungen\Gebaeudeversicherung\4_GV-Viewer\test.siard
REM csv2siard.exe gv-model-v9.xml P:\KOST\Pilotloesungen\Gebaeudeversicherung\9_Testdaten\csvdata_TG(vertraulich) P:\KOST\Pilotloesungen\Gebaeudeversicherung\4_GV-Viewer\gvtg(vertraulich).siard
REM csv2siard.exe NO_DB_MODEL P:\KOST\Pilotloesungen\Gebaeudeversicherung\9_Testdaten\csvdata_TG(vertraulich) gvtg(vertraulich).siard
REM csv2siard.exe gv-model-v9.xml P:\KOST\Pilotloesungen\Gebaeudeversicherung\9_Testdaten\csvdata_TG(vertraulich) gvtg(vertraulich).siard
REM csv2siard.exe gv-model-v9.xml csvdata test.siard
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard csvtest/csvtest.prefs
REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard csvtest/csvtest.prefs

REM php.exe csv2siard.php NO_DB_MODEL csvtest test.siard
REM php.exe csv2siard.php datatype-model.xml csvtest test.siard
REM php.exe csv2siard.php gv-model-v9.xml csvdata test.siard
REM php.exe csv2siard.php NO_DB_MODEL csvdata test.siard
REM csv2siard.exe gv-model-v9.xml ../9_Testdaten/csvdata_TG test.siard
REM csv2siard.exe NO_DB_MODEL ../9_Testdaten/csvdata_TG test.siard

@ECHO OFF
ECHO.
ECHO ERRORCODE: %ERRORLEVEL%
if %ERRORLEVEL% == 0 (
	REM SIARD v.1.20 kann in MS Access importieren (Fehler in v.1.26)
	CALL "C:\Software\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.20\bin\SiardEdit.jar"
)
exit /b
