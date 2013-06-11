@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%

REM compile --------------------------------------------------------------------
C:\Software\xampp\php\php4\php.exe %1

PAUSE
