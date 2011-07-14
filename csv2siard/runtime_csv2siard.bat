@ECHO OFF
SETLOCAL

SET RUNTIME=.\runtime

REM settings -------------------------------------------------------------------
SET JAVA_HOME=C:\Software\jdk1.6.0_01
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PERL_HOME=C:\Software\Perl
SET PATH=%UNIX_HOME%;%JAVA_HOME%\2_arcun;%PERL_HOME%\2_arcun;%PATH%

REM copy -----------------------------------------------------------------------
ECHO .
REM DEL /Q %RUNTIME%
COPY csv2siard.exe %RUNTIME%
COPY xmllint.exe %RUNTIME%
COPY sablot.dll %RUNTIME%
COPY expat.dll %RUNTIME%
COPY icon.dll %RUNTIME%
COPY _*.x* %RUNTIME%
COPY *.prefs %RUNTIME%

PAUSE
