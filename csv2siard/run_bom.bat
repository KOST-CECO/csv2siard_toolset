@ECHO OFF
SETLOCAL

REM settings -------------------------------------------------------------------
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PATH=%UNIX_HOME%;%PATH%

DEL *.siard

REM compile --------------------------------------------------------------------
php.exe csv2siard.php "..\..\09_Testdaten\UTF-8 BOM Testpaket\Model3.xml" "..\..\09_Testdaten\UTF-8 BOM Testpaket" bom.siard "..\..\09_Testdaten\UTF-8 BOM Testpaket\Prefs3-1.txt"
ECHO.
C:\Software\Git\bin\unzip.exe -p bom.siard | grep Rudolf
