@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET JAVA_HOME=C:\Software\jdk1.6.0_01
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PERL_HOME=C:\Software\Perl
SET PATH=%UNIX_HOME%;%JAVA_HOME%\2_arcun;%PERL_HOME%\2_arcun;%PATH%

REM compile --------------------------------------------------------------------
GREP -v "dl(" csv2siard.php > main.php
BAMCOMPILE.EXE csv2siard.bcp
DEL main.php

@ECHO ON
DEL /Q test.siard
CALL csv2siard.exe

CALL csv2siard.exe table2-model.xml csvdata test.siard
DEL /Q test.siard

CALL csv2siard.exe NO_DB_MODEL csvdata test.siard

if "%errorlevel%"=="0" (
	CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Documents and Settings\u1942\applications\siard suite\bin\SiardEdit.jar"
)
