@ECHO OFF
SETLOCAL

IF [%1]==[] (
	ECHO usage: %0"zipfile"
	EXIT /B
)
IF NOT EXIST %1 (
	ECHO zipfile "%1" is missing
	EXIT /B
)

SET TESTZIP=%1

REM ----------------------------------------------------------------------------
SET ORA_HOME=C:\APPS\ORACLE\ORA92\bin
SET UNIX_HOME=C:\Software\PCUnixUtils
SET DOTNETZIP=N:\KOST\Dokumentation\11 Technotes\ZIP\DotNetZipUtils-v1.8
SET PATH=%UNIX_HOME%;%ORA_HOME%;%DOTNETZIP%;%PATH%

@ECHO OFF
ECHO .
ECHO ZIP +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
%ORA_HOME%\zip.exe -T -v %TESTZIP%

@ECHO OFF
ECHO +7Z +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
%UNIX_HOME%\7z.exe t %TESTZIP%

@ECHO OFF
ECHO UNZIP +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
%UNIX_HOME%\unzip.exe -t %TESTZIP%

@ECHO OFF
ECHO DotNet UNZIP ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
"%DOTNETZIP%\unzip.exe" -t %TESTZIP%

%UNIX_HOME%\hexdump.exe %TESTZIP% > %TESTZIP%.hex
