@ECHO OFF
SETLOCAL

IF [%1]==[] (
	ECHO usage: %0 ^<zipfile^> ^<folder^>
	EXIT /B
)

IF NOT EXIST %2 (
	ECHO folder or file to add is missing
	EXIT /B
)


REM ----------------------------------------------------------------------------
SET KOST=P:\KOST
IF NOT EXIST %KOST% (
	SET KOST=C:\KOST_local
)
SET ZIP_HOME=%KOST%\Dokumentation\11 Technotes\ZIP\

REM ZIP64 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
java.exe -jar "%ZIP_HOME%\zip64-1.04\lib\zip64.jar" n %1 %2

