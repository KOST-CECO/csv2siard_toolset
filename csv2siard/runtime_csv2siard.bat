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
DEL /Q %RUNTIME%\bin\*
DEL /Q %RUNTIME%\source\*
DEL /Q %RUNTIME%\*
DEL /Q %RUNTIME%.zip
RMDIR %RUNTIME%\bin
RMDIR %RUNTIME%\source
RMDIR %RUNTIME%\csvdata
RMDIR %RUNTIME%

MKDIR %RUNTIME%
MKDIR %RUNTIME%\bin
MKDIR %RUNTIME%\source
MKDIR %RUNTIME%\csvdata

COPY csv2siard.exe %RUNTIME%\bin

COPY xmllint.exe %RUNTIME%\bin
COPY iconv.dll %RUNTIME%\bin
COPY libxml2.dll %RUNTIME%\bin
COPY zlib1.dll %RUNTIME%\bin

COPY sablot.dll %RUNTIME%\bin
COPY expat.dll %RUNTIME%\bin

COPY 7z.exe %RUNTIME%\bin
COPY 7z.dll %RUNTIME%\bin

COPY file.exe %RUNTIME%\bin
COPY magic.mgc %RUNTIME%\bin
COPY magic.mime %RUNTIME%\bin
COPY magic1.dll %RUNTIME%\bin
COPY regex2.dll %RUNTIME%\bin

COPY preferences.prefs %RUNTIME%\bin
COPY GPL-2.0_COPYING.txt %RUNTIME%\bin

COPY database-torque-4-0.xsd %RUNTIME%
COPY gv-model-v8.xml %RUNTIME%

COPY Anwendungshandbuch*.pdf %RUNTIME%

COPY *.php %RUNTIME%\source
COPY csv2siard.bcp %RUNTIME%\source

COPY csvdata\* %RUNTIME%\csvdata

REM test -----------------------------------------------------------------------
CD %RUNTIME%\bin
DEL /Q ..\..\test.siard
SET PATH=

@ECHO ON
CALL csv2siard.exe
CALL csv2siard.exe ..\..\table2-model.xml ..\..\csvdata ..\..\test.siard

@ECHO OFF
REM zip ------------------------------------------------------------------------
CD ..
bin\7z.exe a -mx9 %RUNTIME%.zip *
COPY %RUNTIME%.zip ..\%RUNTIME%.zip
DEL /Q %RUNTIME%.zip
