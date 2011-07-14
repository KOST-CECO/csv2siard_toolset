@ECHO ON
DEL /Q test.siard
php.exe csv2siard.php table2-model.xml csvdata test.siard
CALL "C:\Program Files\Java\jre6\bin\javaw.exe" -jar "C:\Documents and Settings\u1942\applications\siard suite\bin\SiardEdit.jar"
