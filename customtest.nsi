RequestExecutionLevel user
outfile 'customtest.exe'
 
!define REQUIRED '30000' ; predefined required space in MBs
 
!include 'FileFunc.nsh'
!Include 'MUI.nsh'
 
!insertmacro GetDrives
!insertmacro DriveSpace
 
!insertmacro MUI_PAGE_WELCOME
page custom CustomCreate
!insertmacro MUI_PAGE_INSTFILES
 
!insertmacro MUI_LANGUAGE "English"
 
Section -
#######
SectionEnd
 
Function CustomCreate
   Push $1 ;park value to the stack
   InstallOptions::initDialog /NOUNLOAD '$PLUGINSDIR\custom.ini'
   Pop $1 ;get dialog HWND
   ReadINIStr $2 "$PLUGINSDIR\custom.ini" "Field 1" "HWND"
   SetCtlColors $2 0x0000FF 0xFFFFFF
   CreateFont $3 "Tahoma" 7 800
   SendMessage $2 ${WM_SETFONT} $3 0
   InstallOptions::show
   Pop $1 ;get button action
   Pop $1 ;get custom.ini full path
   Pop $1 ;get back value from stack
FunctionEnd 
 
Function GetDrivesCallBack
   ${DriveSpace} "$9" "/D=F /S=M" $R0
   StrCpy $9 $9 1
   IntCmp '$R0' '${REQUIRED}' +4 +4 0
   StrCmp '$R2' 'state_ok' +3
   WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'State' 'Drive:  $9     \
   Required Space =  ${REQUIRED} Mbytes      Free Space =  $R0 Mbytes'
   StrCpy '$R2' 'state_ok'
   ReadIniStr '$R1' '$PLUGINSDIR\custom.ini' 'Field 1' 'ListItems'
   StrCmp '$R1' '' 0 addlist
   WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'ListItems' 'Drive:  $9     \
   Required Space =  ${REQUIRED} Mbytes      Free Space =  $R0 Mbytes'
   goto end
  addlist:
   WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'ListItems' '$R1|Drive:  $9     \
   Required Space =  ${REQUIRED} Mbytes      Free Space =  $R0 Mbytes'
  end:
   Push $0
FunctionEnd
 
Function .onInit
   initpluginsdir
   file /oname=$PLUGINSDIR\custom.ini custom.ini
   ${GetDrives} "HDD" GetDrivesCallBack
FunctionEnd
