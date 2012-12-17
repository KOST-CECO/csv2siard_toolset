@ECHO OFF
SETLOCAL
REM SET TMP=E:\bamcompile
SET TEMP=%TMP%

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%

REM compile --------------------------------------------------------------------
rm.exe -f csv2siard.exe odbcheck.exe

rm.exe -f %TMP%\* 2> null
REM GREP -v "dl(" csv2siard.php | GREP -v "include " > out.php
REM cat c2sconfig.php c2screate.php c2sconvert.php c2sfunction.php c2sxml.php c2snodbmodel.php c2schema.php c2stimedate.php zip.php c2odbc.php c2snodbodbc.php out.php >main.php
REM BAMCOMPILE.EXE -d -e:php_xslt.dll main.php csv2siard.exe
GREP -v "dl(" csv2siard.php > main.php
CMD.EXE /C "BAMCOMPILE.EXE csv2siard.bcp"

rm.exe -f %TMP%\* 2> null
REM GREP -v "dl(" odbcheck.php | GREP -v "include " > out.php
REM cat c2sconfig.php c2sfunction.php c2sxml.php c2odbc.php out.php >main.php
REM BAMCOMPILE.EXE -d -e:php_xslt.dll main.php odbcheck.exe
CMD.EXE /C "BAMCOMPILE.EXE odbcheck.bcp"

rm.exe -f out.php main.php null

REM check syntax ---------------------------------------------------------------
@ECHO ON
CALL csv2siard.exe
@IF %ERRORLEVEL% GTR 1 (
	@EXIT /B
)
CALL odbcheck.exe
@IF %ERRORLEVEL% GTR 1 (
	@EXIT /B
)

REM test function --------------------------------------------------------------
@ECHO.
CALL odbcheck.exe odbcsql\anl.sql odbcsql\odbcsql.prefs
@IF %ERRORLEVEL% NEQ 0 (
	PAUSE
	@EXIT /B
)
@ECHO.

@ECHO --------------------------------------------------------------------------
@rm.exe -f /Q *.siard
CALL csv2siard.exe table2-model.xml csvdata test.siard
@IF %ERRORLEVEL% NEQ 0 (
	PAUSE
	@EXIT /B
)
unzip -t test.siard
@ECHO.

@ECHO --------------------------------------------------------------------------
@rm.exe -f /Q *.siard
CALL csv2siard.exe :NO_DB_MODEL csvdata test.siard
@ECHO.

@ECHO --------------------------------------------------------------------------
@rm.exe -f /Q *.siard
CALL csv2siard.exe :NO_DB_MODEL :ODBC test.siard odbcsql\odbcsql.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@rm.exe -f /Q *.siard
CALL csv2siard.exe datatype-model.xml datatype test.siard datatype\datatype.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@rm.exe -f /Q *.siard
CALL csv2siard.exe gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@rm.exe -f /Q *.siard
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
