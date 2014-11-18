@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%

REM change to local disk -------------------------------------------------------
CD /D %1

REM compile --------------------------------------------------------------------
rm.exe -f out.php main.php null
GREP -v "dl(" csv2siard.php | GREP -v "include " > out.php
cat c2sconfig.php c2screate.php c2sconvert.php c2sfunction.php c2sxml.php c2snodbmodel.php c2schema.php c2stimedate.php zip.php c2odbc.php c2snodbodbc.php out.php >main.php
BAMCOMPILE.EXE -d -e:php_xslt.dll main.php csv2siard.exe

rm.exe -f out.php main.php null
GREP -v "dl(" odbcheck.php | GREP -v "include " > out.php
cat c2sconfig.php c2sfunction.php c2sxml.php c2odbc.php out.php >main.php
BAMCOMPILE.EXE -d -e:php_xslt.dll main.php odbcheck.exe

rm.exe -f out.php main.php null
GREP -v "dl(" csvschema.php | GREP -v "include " > out.php
cat c2sconfig.php c2sfunction.php c2sxml.php c2snodbmodel.php c2schema.php c2stimedate.php c2odbc.php c2snodbodbc.php out.php >main.php
BAMCOMPILE.EXE -d -e:php_xslt.dll main.php csvschema.exe
