@ECHO OFF
SETLOCAL

@ECHO ON
DEL /Q test.siard
CALL csv2siard.exe
CALL csv2siard.exe gv-model-v8.xml csvdata test.siard
