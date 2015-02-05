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

SET ZIP_HOME=P:\KOST\Dokumentation\11 Technotes\ZIP

REM php ZIP ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
php.exe file_zip.php "%1"


REM ZIP64 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
DEL "%1ZIP64.zip"
java.exe -jar "%ZIP_HOME%\zip64-1.04\lib\zip64.jar" n "%1ZIP64.zip" "%1\*"

REM compare ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
hexdump %1.zip > "%1.zip.hex"
hexdump %1ZIP64.zip > "%1ZIP64.zip.hex"

tkdiff "%1.zip.hex" "%1ZIP64.zip.hex"
