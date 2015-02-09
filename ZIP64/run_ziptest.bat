@ECHO OFF
SETLOCAL

IF [%1]==[] (
	ECHO usage: %0zipfile
	EXIT /B
)
IF NOT EXIST %1 (
	ECHO zipfile %1 is missing
	EXIT /B
)

SET TESTZIP=%1

CALL shortNamePath.cmd %1  >shortNamePath.tmp
COPY $shortNamePath$ + shortNamePath.tmp $tmp$.bat > nul
CALL $tmp$.bat
DEL $tmp$.bat
DEL shortNamePath.tmp

SET TESTZIP_SHORTNAME=%SHORTNAME%

REM ----------------------------------------------------------------------------
SET KOST=P:\KOST
IF NOT EXIST %KOST% (
	SET KOST=C:\KOST_local
)
SET ZIP_HOME=%KOST%\Dokumentation\11 Technotes\ZIP\CLI_progs

@ECHO OFF
CLS
ECHO.
REM ECHO +7Z +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
REM @ECHO ON
REM "%ZIP_HOME%\7z.exe" t %TESTZIP%
REM 
REM @ECHO OFF
REM ECHO.
ECHO DotNet UNZIP ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
"%ZIP_HOME%\unzip.exe" -t %TESTZIP%

@ECHO OFF
ECHO.
ECHO ZIP-Info ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
"%ZIP_HOME%\zipinfo.exe" -t -l %TESTZIP%

@ECHO OFF
REM ECHO.
REM ECHO PKZip +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
REM @ECHO ON
REM "%ZIP_HOME%\pkzip.exe" -v %TESTZIP_SHORTNAME%

@ECHO OFF
ECHO.
ECHO www.info-zip.org +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
"c:\Software\Git\bin\unzip.exe" -t %TESTZIP%

@ECHO OFF
ECHO.
ECHO ZIP64 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
java.exe -jar "%KOST%\Dokumentation\11 Technotes\ZIP\zip64-1.04\lib\zip64.jar" l %TESTZIP%

@ECHO OFF
REM %UNIX_HOME%\hexdump.exe" %TESTZIP% > %TESTZIP%.hex
