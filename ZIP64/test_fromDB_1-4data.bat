REM Reihenfolge der Dateien in fromDB_testdata.siard ermitteln
REM ----------------------------------------------------------
strings fromDB_1-4data.siard | egrep "content|header" | grep -v "<" | grep -v "contents" | head -n 18

REM Mögliche Concatenationen nach Reihenfolge in SIARD file oder metadata.xml
REM -------------------------------------------------------------------------
cat fromDB_1-4data/content/schema0/table3/table3.xsd fromDB_1-4data/content/schema0/table3/table3.xml fromDB_1-4data/content/schema0/table2/table2.xsd fromDB_1-4data/content/schema0/table2/table2.xml fromDB_1-4data/content/schema0/table1/table1.xsd fromDB_1-4data/content/schema0/table1/table1.xml fromDB_1-4data/content/schema0/table0/table0.xsd fromDB_1-4data/content/schema0/table0/table0.xml fromDB_1-4data/header/metadata.xsd fromDB_1-4data/header/metadata.xsl | md5sum 

cat fromDB_1-4data/content/schema0/table3/table3.xsd fromDB_1-4data/content/schema0/table3/table3.xml fromDB_1-4data/content/schema0/table2/table2.xsd fromDB_1-4data/content/schema0/table2/table2.xml fromDB_1-4data/content/schema0/table1/table1.xsd fromDB_1-4data/content/schema0/table1/table1.xml fromDB_1-4data/content/schema0/table0/table0.xsd fromDB_1-4data/content/schema0/table0/table0.xml fromDB_1-4data/header/metadata.xsd | md5sum 

cat fromDB_1-4data/content/schema0/table3/table3.xsd fromDB_1-4data/content/schema0/table3/table3.xml fromDB_1-4data/content/schema0/table2/table2.xsd fromDB_1-4data/content/schema0/table2/table2.xml fromDB_1-4data/content/schema0/table1/table1.xsd fromDB_1-4data/content/schema0/table1/table1.xml fromDB_1-4data/content/schema0/table0/table0.xsd fromDB_1-4data/content/schema0/table0/table0.xml | md5sum 

cat fromDB_1-4data/content/schema0/table3/table3.xml fromDB_1-4data/content/schema0/table2/table2.xml fromDB_1-4data/content/schema0/table1/table1.xml fromDB_1-4data/content/schema0/table0/table0.xml | md5sum 

cat fromDB_1-4data/content/schema0/table0/table0.xml fromDB_1-4data/content/schema0/table1/table1.xml fromDB_1-4data/content/schema0/table2/table2.xml fromDB_1-4data/content/schema0/table3/table3.xml | md5sum 

REM MD5 direkt über ZIP mit ZIP Header
REM ----------------------------------
head -c 91398 fromDB_1-4data.siard | md5sum

head -c 91374 fromDB_1-4data.siard | md5sum
