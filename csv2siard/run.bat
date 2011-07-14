@ECHO OFF
DEL /Q test.siard
php.exe csv2siard.php gv-model-v8.xml csvdata test.siard
REM csv2siard.exe gv-model-v8.xml csvdata test.siard
REM php.exe csv2siard.php table2-model.xml csvdata test.siard
REM php.exe csv2siard.php minimal2-model.xml csvdata test.siard
REM php.exe csv2siard.php minimal1-model.xml csvdata test.siard
REM php.exe csv2siard.php NO_DB_MODEL gvtest test.siard

CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Documents and Settings\u1942\applications\siard suite\bin\SiardEdit.jar"
