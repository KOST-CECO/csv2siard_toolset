@ECHO OFF
DEL /Q test.siard
echo --------- %time%
php.exe csv2siard.php gv-model-v8.xml csvdata test.siard

REM php.exe csv2siard.php NO_DB_MODEL csvdata test.siard
REM csv2siard.exe gv-model-v8.xml ../9_Testdaten/csvdata_TG test.siard
REM csv2siard.exe NO_DB_MODEL ../9_Testdaten/csvdata_TG test.siard
echo --------- %time%
CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.26\bin\SiardEdit.jar"
exit /b

REM php.exe csv2siard.exe gv-model-v8.xml csvdata test.siard

csv2siard.exe gv-model-v8.xml    csvdata test.siard
del /q test.siard

csv2siard.exe table1-model.xml   csvdata test.siard
del /q test.siard

csv2siard.exe table2-model.xml   csvdata test.siard
del /q test.siard

csv2siard.exe minimal-model.xml  ../9_Testdaten/csvtest test.siard
del /q test.siard

csv2siard.exe minimal1-model.xml ../9_Testdaten/csvtest test.siard
del /q test.siard

csv2siard.exe minimal2-model.xml ../9_Testdaten/csvtest test.siard
del /q test.siard

REM csv2siard.exe gv-model-v8.xml    ../9_Testdaten/csvdata_TG test.siard
del /q test.siard


csv2siard.exe NO_DB_MODEL   csvdata test.siard
del /q test.siard

csv2siard.exe NO_DB_MODEL  ../9_Testdaten/csvtest test.siard
del /q test.siard

REM csv2siard.exe NO_DB_MODEL  ../9_Testdaten/csvdata_TG test.siard
del /q test.siard

csv2siard.exe NO_DB_MODEL  ../9_Testdaten/test_StAZH test.siard
del /q test.siard

echo --------- %time%
