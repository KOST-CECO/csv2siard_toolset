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
REM COPY php4ts.dll %RUNTIME%

COPY xmllint.exe %RUNTIME%
COPY iconv.dll %RUNTIME%
COPY libxml2.dll %RUNTIME%
COPY zlib1.dll %RUNTIME%

REM COPY php_xslt.dll %RUNTIME%
COPY sablot.dll %RUNTIME%
COPY expat.dll %RUNTIME%

COPY 7z.exe %RUNTIME%
COPY 7z.dll %RUNTIME%
REM COPY gdiplus.dll %RUNTIME%

COPY file.exe %RUNTIME%
COPY magic.mgc %RUNTIME%
COPY magic1.dll %RUNTIME%
COPY regex2.dll %RUNTIME%

REM COPY _*.xsd %RUNTIME%
REM COPY _*.xsl %RUNTIME%
COPY database-torque-4-0.xsd %RUNTIME%

COPY preferences.prefs %RUNTIME%
COPY gv-model-v8.xml %RUNTIME%

COPY GPL-2.0_COPYING.txt %RUNTIME%

REM test -----------------------------------------------------------------------
CD %RUNTIME%
DEL /Q ..\test.siard
SET PATH=

@ECHO ON
CALL csv2siard.exe
CALL csv2siard.exe ..\table2-model.xml ..\csvdata ..\test.siard

@ECHO OFF
REM zip ------------------------------------------------------------------------
CD ..
7z.exe a -mx9 %RUNTIME%.zip %RUNTIME%
