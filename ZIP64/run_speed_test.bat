@ECHO OFF
SETLOCAL

IF [%1]==[] (
	ECHO usage: %0 "folder"
	EXIT /B
)
IF NOT EXIST %1 (
	ECHO folder "%1" is missing
	EXIT /B
)

SET JAVA_HOME=C:\Software\jdk1.6.0_01
SET UNIX_HOME=C:\Software\PCUnixUtils

SET TESTZIP=%1


@ECHO ON
@ECHO --------------------------------------------------------------------------
DEL /Q %TESTZIP%.zip
@ECHO.| TIME
REM %UNIX_HOME%\7z.exe a -mx0 %TESTZIP%.zip %TESTZIP%
@ECHO.| TIME

@ECHO --------------------------------------------------------------------------
DEL /Q %TESTZIP%.zip
@ECHO.| TIME
zip.exe %TESTZIP%
@ECHO.| TIME

@ECHO --------------------------------------------------------------------------
DEL /Q %TESTZIP%.zip
@ECHO.| TIME
%JAVA_HOME%/bin/java.exe -jar zip64-1.02\lib\zip64.jar n %TESTZIP%.zip %TESTZIP%\*
@ECHO.| TIME
