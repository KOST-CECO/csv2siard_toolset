@ECHO OFF
SETLOCAL

xmllint.exe -noout -schema test\header\metadata.xsd test\header\metadata.xml

