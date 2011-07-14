@ECHO OFF
SETLOCAL

SET APP=..\runAPP
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
CALL csv2siard.exe
CALL csv2siard.exe gv-model-v8.xml csvdata test.siard

PAUSE
runtime_csv2siard.bat
