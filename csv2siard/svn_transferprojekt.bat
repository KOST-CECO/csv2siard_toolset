@echo off & setlocal enableextensions enabledelayedexpansion

set _source=_SourceSave

:: Create SVN repository
svnadmin create N:/KOST/SVN/Transferprojekt
mkdir csv2siard
copy svn_transferprojekt.bat csv2siard
copy copy_svn-structur.bat csv2siard
svn import csv2siard file:///N:/KOST/SVN/Transferprojekt/csv2siard -m "inital import"
rmdir /s /q csv2siard
svn checkout file:///N:/KOST/SVN/Transferprojekt/csv2siard workbench
pause


:: Loop through source directory 
for /F "tokens=*" %%G in ('DIR /B /AD /O %_source%') do (
rmdir /s /q csv2siard
cd workbench
call copy_svn-structur.bat ..\csv2siard
cd ..

rmdir /s /q workbench
move csv2siard workbench
xcopy %_source%\%%G workbench /E /Q

cd workbench
svn export file:///N:/KOST/SVN/Transferprojekt/csv2siard/copy_svn-structur.bat
svn add *

for /F "delims=_ tokens=2" %%H in ("%%G") do svn commit -m %%H
cd ..
)
rmdir /s /q csv2siard
