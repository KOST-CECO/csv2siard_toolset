@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%

REM compile --------------------------------------------------------------------
DEL csv2siard.exe main.php out.php
GREP -v "dl(" csv2siard.php | GREP -v "include " > out.php

CAT out.php c2sfunction.php c2stimedate.php c2sxml.php c2sconfig.php c2screate.php c2sconvert.php c2snodbmodel.php c2schema.php zip.php c2odbc.php c2snodbodbc.php >main.php
BAMCOMPILE.EXE -d -e:php_xslt.dll main.php csv2siard.exe

REM DEL csv2siard.exe odbcheck.exe main.php
REM GREP -v "dl(" csv2siard.php > main.php
REM SLEEP 5
REM CALL BAMCOMPILE.EXE csv2siard.bcp

CALL BAMCOMPILE.EXE odbcheck.bcp

REM check syntax ---------------------------------------------------------------
CALL csv2siard.exe
IF %ERRORLEVEL% NEQ 1 (
	EXIT /B
)
CALL odbcheck.exe
IF %ERRORLEVEL% NEQ 1 (
	EXIT /B
)
REM test function --------------------------------------------------------------
@ECHO ON
ECHO.
@ECHO --------------------------------------------------------------------------
CALL odbcheck.exe odbcsql\anl.sql odbcsql\odbcsql.prefs
ECHO.@ECHO --------------------------------------------------------------------------
@DEL /Q *.siard
CALL csv2siard.exe table2-model.xml csvdata test.siard
unzip -t test.siard
ECHO.
@ECHO --------------------------------------------------------------------------
@DEL /Q *.siard
CALL csv2siard.exe :NO_DB_MODEL csvdata test.siard
ECHO.
@ECHO --------------------------------------------------------------------------
@DEL /Q *.siard
CALL csv2siard.exe :NO_DB_MODEL :ODBC test.siard odbcsql\odbcsql.prefs
ECHO.
@ECHO --------------------------------------------------------------------------
@DEL /Q *.siard
CALL csv2siard.exe datatype-model.xml datatype test.siard datatype\datatype.prefs
ECHO.
@ECHO --------------------------------------------------------------------------
@DEL /Q *.siard
CALL csv2siard.exe gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs
ECHO.
@ECHO --------------------------------------------------------------------------
@DEL /Q *.siard
CALL csv2siard.exe gv-model-v9.xml csvdata test.siard

@ECHO OFF
if %ERRORLEVEL% == 0 (
	REM CALL C:\Software\jre6\bin\javaw.exe -jar "C:\Software\siardsuite_1.20\bin\SiardEdit.jar"
	REM CALL "C:\Software\jre6\bin\javaw.exe" -jar "C:\Software\siardsuite_1.44\bin\SiardEdit.jar"
)
@ECHO --------------------------------------------------------------------------
java.exe -Xmx128m -jar siard-val.jar test.siard C:\TEMP
IF %ERRORLEVEL% NEQ  0 (
	notepad.exe  C:\TEMP\test.siard.validationlog.log
)
