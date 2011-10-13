@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%

REM compile --------------------------------------------------------------------
GREP -v "dl(" csv2siard.php > main.php
BAMCOMPILE.EXE csv2siard.bcp
DEL main.php

REM check syntax ---------------------------------------------------------------
CALL csv2siard.exe
IF %ERRORLEVEL% GTR 1 (
	EXIT /B
)
REM test function --------------------------------------------------------------
@ECHO ON
DEL /Q test.siard
CALL csv2siard.exe table2-model.xml csvdata test.siard
unzip -t test.siard

DEL /Q test.siard
CALL csv2siard.exe NO_DB_MODEL csvdata test.siard
@ECHO OFF
if %ERRORLEVEL% == 0 (
	CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.26\bin\SiardEdit.jar"
)
