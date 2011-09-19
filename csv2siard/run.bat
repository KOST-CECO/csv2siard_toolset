@ECHO OFF
DEL /Q test.siard
echo --------- %time%

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

echo --------- %time%
