@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%
REM SET TMP=C:\TEMP

REM remove bamcompile tmp files ------------------------------------------------
DEL /F /Q %TMP%\*.tmp 2> null

REM get new temp dir -----------------------------------------------------------
:GETTEMPNAME
SET TMPFILE=%TMP%\tmpdir-%RANDOM%-%TIME:~6,5%
if exist "%TMPFILE%" GOTO :GETTEMPNAME 
MKDIR "%TMPFILE%"

REM bamcompile on local disk-------------------------------------------------------
DEL /F csv2siard.exe odbcheck.exe
COPY *.php "%TMPFILE%"
COPY php_xslt.dll "%TMPFILE%"

CALL compile_it.bat "%TMPFILE%"

COPY "%TMPFILE%\*.exe" .
RMDIR /S /Q "%TMPFILE%"

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
CALL csvschema.exe
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
@ECHO.
CALL odbcheck.exe "SELECT * FROM gv_list.csv;" odbcsql\odbcsql.prefs | tail -n 21 | sort | wc
CALL odbcheck.exe "SELECT * FROM gv_list.csv;" odbcsql\odbcsql.prefs | tail -n 21 | sort -u | wc
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
CALL csv2siard.exe table2-model.xml csvdata test.siard
@IF %ERRORLEVEL% NEQ 0 (
	PAUSE
	@EXIT /B
)
unzip -t test.siard
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard log.txt
CALL csv2siard.exe gv-model-v9.xml csvdata test.siard :LOG_FILE=log.txt
pr.exe -n -l 1 log.txt
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
CALL csv2siard.exe :NO_DB_MODEL csvdata test.siard
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
CALL csv2siard.exe :NO_DB_MODEL :ODBC test.siard odbcsql\odbcsql.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
CALL csv2siard.exe datatype\datatype-model.xml datatype test.siard datatype\datatype.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
CALL csv2siard.exe gv-model-nf.xml odbcsql test.siard odbcsql\odbcsql.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
CALL csv2siard.exe datatype\datatype-utf8-odbc.xml :ODBC test.siard datatype\datatype_utf8.prefs
@ECHO.

@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
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
@ECHO --------------------------------------------------------------------------
@DEL /F *.siard
@DEL /F no_db_model.xml
@DEL /F log.txt
