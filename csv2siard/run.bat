@ECHO OFF
DEL /Q test.siard
REM php.exe csv2siard.php gv-model-v8.xml csvdata test.siard
csv2siard.exe gv-model-v8.xml csvdata_originale test.siard
REM csv2siard.exe gv-model-v8.xml csvdata test.siard
REM php.exe csv2siard.php table2-model.xml csvdata test.siard
REM php.exe csv2siard.php minimal2-model.xml csvdata test.siard
REM php.exe csv2siard.php minimal1-model.xml csvdata test.siard
REM php.exe csv2siard.php NO_DB_MODEL gvtest test.siard
REM php.exe csv2siard.php NO_DB_MODEL csvdata test.siard

if "%errorlevel%"=="0" (
	CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Documents and Settings\u1942\applications\siard suite\bin\SiardEdit.jar"
)
