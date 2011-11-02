@ECHO OFF
SETLOCAL

php.exe file_zip.php 1-4data
@ECHO .

RMDIR /S /Q tmp
MKDIR tmp
COPY /Y zip.php tmp
COPY /Y file_zip.php tmp

bamcompile.exe -c tmp file_zip.php zip.exe

CALL zip.exe 1-4data

CALL run_ziptest.bat 1-4data.zip

