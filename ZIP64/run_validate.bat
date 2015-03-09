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

REM compare ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
DEL "%1.zip.hex" 

tail1k.exe "%1.zip" > "%1.zip.1k"

hexdump %1.zip.1k > "%1.zip.hex"

DEL "%1.zip.1k" 

CALL "%1.zip.hex"

REM ziptest ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
CALL run_ziptest.bat %1.zip
