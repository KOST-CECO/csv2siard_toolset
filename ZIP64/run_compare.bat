@ECHO OFF
SETLOCAL

IF [%1]==[] (
	ECHO usage: %0 ^<folder^>
	EXIT /B
)

IF NOT EXIST %1 (
	ECHO folder is missing
	EXIT /B
)

REM ----------------------------------------------------------------------------
CLS
SET KOST=P:\KOST
IF NOT EXIST %KOST% (
	SET KOST=C:\KOST_local
)
SET ZIP_HOME=%KOST%\Dokumentation\11 Technotes\ZIP

REM php ZIP ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
DEL "%1.zip"
php.exe file_zip.php "%1"
ECHO.
ECHO.

REM ZIP64 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
DEL "%1ZIP64.zip"
java.exe -jar "%ZIP_HOME%\zip64-1.04\lib\zip64.jar" n "%1ZIP64.zip" "%1\*"

REM compare ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
DEL "%1.zip.hex" "%1ZIP64.zip.hex"
hexdump %1.zip > "%1.zip.hex"
hexdump %1ZIP64.zip > "%1ZIP64.zip.hex"

START tkdiff.exe "%1.zip.hex" "%1ZIP64.zip.hex"

REM ziptest ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
CALL run_ziptest.bat %1.zip
