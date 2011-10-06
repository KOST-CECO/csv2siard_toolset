@ECHO OFF
SETLOCAL

php.exe zip.php 1-4data
REM CALL bamcompile.exe -c zip.php
CALL bamcompile.exe zip.php
CALL zip.exe 1-4data
CALL run_ziptest.bat 1-4data.zip

