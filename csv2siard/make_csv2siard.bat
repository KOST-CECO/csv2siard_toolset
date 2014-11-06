@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET WINDOWS_HOME=C:\Windows\system32
SET PATH=%UNIX_HOME%;%PATH%

csv2siard.exe | grep version |  cut -d " " -f 8 >version.tmp
COPY $setversion$ + version.tmp $tmp$.bat > nul
CALL $tmp$.bat
DEL $tmp$.bat
DEL version.tmp

ECHO +++ csv2siard Version %VERSION% +++

SET RUNTIME=csv2siard_v.%VERSION%
SET WDIR=%CD%

REM copy -----------------------------------------------------------------------
ECHO .
RMDIR /S /Q %TEMP%\%RUNTIME%

MKDIR %TEMP%\%RUNTIME%
MKDIR %TEMP%\%RUNTIME%\bin
MKDIR %TEMP%\%RUNTIME%\source
MKDIR %TEMP%\%RUNTIME%\csvdata
MKDIR %TEMP%\%RUNTIME%\datatype
MKDIR %TEMP%\%RUNTIME%\odbcdata
MKDIR %TEMP%\%RUNTIME%\odbcsql

COPY csv2siard.exe %TEMP%\%RUNTIME%\bin
COPY odbcheck.exe %TEMP%\%RUNTIME%\bin
COPY csvschema.exe %TEMP%\%RUNTIME%\bin

COPY xmllint.exe %TEMP%\%RUNTIME%\bin
COPY iconv.dll %TEMP%\%RUNTIME%\bin
COPY libxml2.dll %TEMP%\%RUNTIME%\bin
COPY zlib1.dll %TEMP%\%RUNTIME%\bin

COPY sablot.dll %TEMP%\%RUNTIME%\bin
COPY expat.dll %TEMP%\%RUNTIME%\bin

COPY crc32sum.exe %TEMP%\%RUNTIME%\bin

COPY file.exe %TEMP%\%RUNTIME%\bin
COPY magic.mgc %TEMP%\%RUNTIME%\bin
COPY magic %TEMP%\%RUNTIME%\bin
COPY magic1.dll %TEMP%\%RUNTIME%\bin
COPY regex2.dll %TEMP%\%RUNTIME%\bin

COPY preferences.prefs %TEMP%\%RUNTIME%
COPY preferences.prefs %TEMP%\%RUNTIME%\bin
COPY GPL-2.0_COPYING.txt %TEMP%\%RUNTIME%\bin

COPY database-torque-4-0.xsd %TEMP%\%RUNTIME%
COPY gv-model-v9.xml %TEMP%\%RUNTIME%
COPY gv-model-nf.xml %TEMP%\%RUNTIME%
COPY datatype-model.xml %TEMP%\%RUNTIME%

COPY Anwendungshandbuch*.pdf %TEMP%\%RUNTIME%

COPY *.php         %TEMP%\%RUNTIME%\source
COPY csv2siard.bcp %TEMP%\%RUNTIME%\source

COPY csvdata\*      %TEMP%\%RUNTIME%\csvdata
COPY datatype\*     %TEMP%\%RUNTIME%\datatype
COPY odbcdata\*     %TEMP%\%RUNTIME%\odbcdata
COPY odbcsql\*      %TEMP%\%RUNTIME%\odbcsql

COPY demo.*     %TEMP%\%RUNTIME%

COPY c2sGUI.exe      %TEMP%\%RUNTIME%

REM test -----------------------------------------------------------------------
CD /D "%TEMP%\%RUNTIME%""
DEL /Q "%TEMP%\%RUNTIME%\test.siard"
SET PATH=

@ECHO ON
CALL bin\csv2siard.exe
CALL bin\csv2siard.exe "%WDIR%\table2-model.xml" "%WDIR%\csvdata" "%TEMP%\%RUNTIME%\test.siard"
DEL /Q "%TEMP%\%RUNTIME%\test.siard"
CALL bin\csv2siard.exe :no_db_model %WDIR%\csvdata %TEMP%\%RUNTIME%\test.siard
DEL %TEMP%\%RUNTIME%\no_db_model.xml

@ECHO OFF
REM zip ------------------------------------------------------------------------
%UNIX_HOME%\7z.exe a -mx9 %TEMP%\%RUNTIME%.zip *
COPY %TEMP%\%RUNTIME%.zip ..\..\..\04_Publikation\%TEMP%\%RUNTIME%.zip
DEL /Q %TEMP%\%RUNTIME%.zip
CD ..
RMDIR /S /Q C:\Software\csv2siard
%WINDOWS_HOME%\xcopy.exe %TEMP%\%RUNTIME% C:\Software\csv2siard /I /S
RMDIR /S /Q %TEMP%\%RUNTIME%

REM MD5 ------------------------------------------------------------------------
%UNIX_HOME%\md5sum.exe %WDIR%\..\..\04_Publikation\%RUNTIME%.zip
