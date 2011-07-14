@ECHO OFF
SETLOCAL

csv2siard.exe | grep version |  cut -d " " -f 8 >version.tmp
COPY $setversion$ + version.tmp $tmp$.bat > nul
CALL $tmp$.bat
DEL $tmp$.bat
DEL version.tmp

ECHO +++ csv2siard Version %VERSION% +++

SET RUNTIME=.\csv2siard_v.%VERSION%

REM copy -----------------------------------------------------------------------
ECHO .
DEL /Q %RUNTIME%\*
RMDIR %RUNTIME%
MKDIR %RUNTIME%

COPY csv2siard.exe %RUNTIME%
COPY xmllint.exe %RUNTIME%
COPY sablot.dll %RUNTIME%
COPY expat.dll %RUNTIME%
COPY iconv.dll %RUNTIME%
COPY 7z.* %RUNTIME%
COPY _*.x* %RUNTIME%
COPY *.prefs %RUNTIME%

@ECHO ON
CD %RUNTIME%
DEL /Q ..\test.siard
CALL csv2siard.exe
CALL csv2siard.exe ..\table2-model.xml ..\csvdata ..\test.siard
