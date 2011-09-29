@ECHO OFF
SETLOCAL

SET TESTZIP=1data.zip
SET REFZIP=1dataWinZip.zip

REM ----------------------------------------------------------------------------
SET ORA_HOME=C:\APPS\ORACLE\ORA92\bin
SET UNIX_HOME=C:\Software\PCUnixUtils
SET DOTNETZIP=N:\KOST\Dokumentation\11 Technotes\ZIP\DotNetZipUtils-v1.8
SET PATH=%UNIX_HOME%;%ORA_HOME%;%DOTNETZIP%;%PATH%

@ECHO OFF
ECHO +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
ECHO ZIP +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
%ORA_HOME%\zip.exe -T -v %TESTZIP%
%ORA_HOME%\zip.exe -T -v %REFZIP%

@ECHO OFF
ECHO +7Z +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
%UNIX_HOME%\7z.exe t %TESTZIP%
%UNIX_HOME%\7z.exe t %REFZIP%

@ECHO OFF
ECHO UNZIP +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
%UNIX_HOME%\unzip.exe -t %TESTZIP%
%UNIX_HOME%\unzip.exe -t %REFZIP%

@ECHO OFF
ECHO DotNet UNZIP ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
@ECHO ON
"%DOTNETZIP%\unzip.exe" -t %TESTZIP%
"%DOTNETZIP%\unzip.exe" -t %REFZIP%

%UNIX_HOME%\hexdump.exe %TESTZIP% > %TESTZIP%.hex
%UNIX_HOME%\hexdump.exe %REFZIP% > %REFZIP%.hex

@ECHO OFF
%UNIX_HOME%\tr.exe " " "\n" < %TESTZIP%.hex > %TESTZIP%.hexline
%UNIX_HOME%\tr.exe " " "\n" < %REFZIP%.hex > %REFZIP%.hexline
%UNIX_HOME%\diff.exe %REFZIP%.hexline %TESTZIP%.hexline > %TESTZIP%.diff

RM %TESTZIP%.hexline
RM %REFZIP%.hexline
