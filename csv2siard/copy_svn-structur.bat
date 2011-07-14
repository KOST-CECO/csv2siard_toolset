@echo off & setlocal enableextensions enabledelayedexpansion
:: The script will copy all .svn folder and subfolder in a new folder as an empty
:: SVN Project structur, wich kann be filled with projectfiles

:: Check new location path for empty svn structure
if [%1]==[] (
	echo New location for empty svn structure is missing
	echo usage: %0 path
	exit /b
)
if exist %1 (
	echo path '%1' for empty svn structure is allready existing
	exit /b
)
set _newpath=%1

:: looking for working directory
set s="%CD%"

:: Get the length of the working directory assuming a max of 255
set _charcount=0
for /l %%c in (0,1,255) do (
set si=!s:~%%c!
if defined si set /a _charcount+=1)
if %_charcount% EQU 256 set _charcount=0
set /a _dirlength=%_charcount%-1

:: Loop through directory tree, looking for '.svn'
for /F "tokens=*" %%G in ('DIR /B /AD /S *.svn*') DO (

	call :copyit %%G
)
goto:eof

:: Copy found '.svn' directory to new location
:copyit
	set _var=%1
	call set _targetpath=%%_var:~%_dirlength%%%
	echo XCOPY   %_var%   %_newpath%\%_targetpath%  /E /H /I /Q
	xcopy "%_var%" "%_newpath%\%_targetpath%" /E /H /I /Q
	goto :eof
