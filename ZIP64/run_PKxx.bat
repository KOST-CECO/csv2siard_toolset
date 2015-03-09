@ECHO OFF
SETLOCAL


IF [%1]==[] (
	ECHO usage: %0 ^<zip file^>
	EXIT /B
)

IF NOT EXIST %1 (
	ECHO zip file is missing
	EXIT /B
)

c:\Software\Git\bin\sed.exe "s/PK\x03\x04/PK34/g" "%1" > a
c:\Software\Git\bin\sed.exe "s/PK\x07\x08/PK78/g" a > b
c:\Software\Git\bin\sed.exe "s/PK\x01\x02/PK12/g" b > c
c:\Software\Git\bin\sed.exe "s/PK\x05\x06/PK56/g" c > d
c:\Software\Git\bin\sed.exe "s/PK\x06\x06/PK66/g" d > e
c:\Software\Git\bin\sed.exe "s/PK\x06\x07/PK67/g" e > f

echo PK
strings f | grep PK | wc

echo PK34 - local directory
strings f | grep PK34 | wc

echo PK78 - data descriptor
strings f | grep PK78 | wc

echo PK12 - central directory
strings f | grep PK12 | wc

echo PK66 - ZIP64 end of central directory record
strings f | grep PK66 | wc

echo PK67 - ZIP64 end of central directory locator
strings f | grep PK67 | wc

echo PK56 - end of central directory
strings f | grep PK56 | wc

rm ?
