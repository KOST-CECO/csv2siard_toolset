@ECHO OFF
SETLOCAL

SET SOURCESAVE=..\_SourceSave

REM settings -------------------------------------------------------------------
SET JAVA_HOME=C:\Software\jdk1.6.0_01
SET UNIX_HOME=C:\Software\PCUnixUtils
SET PERL_HOME=C:\Software\Perl
SET PATH=%UNIX_HOME%;%JAVA_HOME%\2_arcun;%PERL_HOME%\2_arcun;%PATH%

REM timestamp ------------------------------------------------------------------
FOR /F "delims=. tokens=3" %%g IN ('DATE /T') DO SET _timestamp=%%g
FOR /F "delims=. tokens=2" %%g IN ('DATE /T') DO SET _timestamp=%_timestamp%%%g
FOR /F "delims=. tokens=1" %%g IN ('DATE /T') DO SET _timestamp=%_timestamp%%%g
SET _timestamp=%_timestamp%_%time%
SET _timestamp=%_timestamp::=%
SET _timestamp=%_timestamp: =%
REM ECHO timestamp: %_timestamp%

REM backup ---------------------------------------------------------------------
ECHO .
ECHO Directory %SOURCESAVE%\source_%_timestamp% created
MKDIR %SOURCESAVE%\source_%_timestamp%
COPY *.php %SOURCESAVE%\source_%_timestamp%\
COPY *.bcp %SOURCESAVE%\source_%_timestamp%\
COPY *.xml %SOURCESAVE%\source_%_timestamp%\
COPY *.xsd %SOURCESAVE%\source_%_timestamp%\
COPY *.xsl %SOURCESAVE%\source_%_timestamp%\
COPY *.css %SOURCESAVE%\source_%_timestamp%\
COPY *.bat %SOURCESAVE%\source_%_timestamp%\
COPY *.ini %SOURCESAVE%\source_%_timestamp%\
COPY *.prefs %SOURCESAVE%\source_%_timestamp%\
COPY Anwendungshandbuch* %SOURCESAVE%\source_%_timestamp%\
