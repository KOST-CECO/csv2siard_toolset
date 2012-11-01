; The name of the installer
Name "SIARD-Val v0.9"
; Sets the icon of the installer
Icon "val.ico"
; remove the text 'Nullsoft Install System vX.XX' from the installer window 
BrandingText "Copyright © KOST/CECO 2012"
; The file to write
OutFile "SIARDval.exe"
; The default installation directory
; InstallDir $DESKTOP
InstallDir $EXEDIR
; Request application privileges for Windows Vista
RequestExecutionLevel user
; Sets the text for the titlebar of the installer
Caption "$(^Name)"
; Makes the installer controls use the new XP style when running on Windows XP
XPStyle on
;--------------------------------
!include WinMessages.nsh
!include getBaseName.nsh
!include getJavaHome.nsh
!include langSIARDVal.nsh
;----------------------
!define CONFIG        "SIARDVal.conf.xml"
!define CONFIGPATH    "configuration"
!define SIARDVAL      "siard-val.jar"
!define SIARDHELP     "SIARD-Val_Anwendungshandbuch_v0.0.2.pdf"
!define INIFILE       "SIARDval.ini"
!define JAVAPATH      "jre6"
!define WORK          "work"
;--------------------------------
Var DIALOG
Var SIARDFILE
Var HEAPSIZE
Var LOGFILE
VAR JAVA
Var HWND
;--------------------------------
; Pages
LicenseData license.txt
Page license
Page instfiles
Page Custom ShowDialog LeaveDialog
;--------------------------------
; Functions
Function .onInit
  ; Initializes the plug-ins dir ($PLUGINSDIR) if not already initialized
  InitPluginsDir
  ; Assign to the user variable $DIALOG, the name of a temporary file
  GetTempFileName $DIALOG $PLUGINSDIR
  ; Adds file(s) to be extracted to the current output path
  ;   Use /oname=X switch to change the output name
  File /oname=$DIALOG ${INIFILE}
FunctionEnd

Function ShowDialog
  ; Writes entry_name=value into [section_name] of ini file
  WriteINIStr $DIALOG "Settings" "NextButtonText" "${NextButtonText}"
  WriteINIStr $DIALOG "Field 1" "Text" "${Field_1}"
  WriteINIStr $DIALOG "Field 4" "Text" "${Field_4}"
  WriteINIStr $DIALOG "Field 6" "Text" "${Field_6}"
  WriteINIStr $DIALOG "Field 7" "Text" "${Field_7}"
  WriteINIStr $DIALOG "Field 8" "Text" "${Field_8}"
  
  ; Display the validation options dialog
  InstallOptions::initDialog $DIALOG
  Pop $HWND
  
  ; set button "Cancel" active 
  #GetDlgItem $1 $HWNDPARENT 2
  #EnableWindow $1 1
  ; set button "Cancel" invisible 
  GetDlgItem $1 $HWNDPARENT 2
  ShowWindow $1 0
  ; set button "Back" invisible 
  GetDlgItem $1 $HWNDPARENT 3
  ShowWindow $1 0
  ; change button font
  GetDlgItem $1 $HWND 1208
  #CreateFont $R1 "Arial" "8" "600"
	#SendMessage $1 ${WM_SETFONT} $R1 0
  SetCtlColors $1 0x000000 0x05D62A
  ; Display the validation options dialog
  InstallOptions::show
FunctionEnd

Function LeaveDialog
  ; To get the input of the user, read the State value of a Field 
  ReadINIStr $SIARDFILE $DIALOG "Field 3" "State"
  ReadINIStr $HEAPSIZE $DIALOG "Field 5" "State"
  ReadINIStr $0 $DIALOG "Settings" "State"  
  
  ; Help button pressed
  StrCmp "Field $0" "Field 2" show_help 0

  ; Edit button pressed
  StrCmp "Field $0" "Field 6" edit_config 0

  ; Reset button pressed
  StrCmp "Field $0" "Field 7" reset_config 0

  ; Validate button pressed
  StrCmp "Field $0" "Field 8" run_validation 0
  
  ;remove jar, config file and work directory
  delete $TEMP\${SIARDVAL}
  delete $TEMP\${SIARDHELP}
  delete $TEMP\${CONFIGPATH}\${CONFIG}
  rmdir  $TEMP\${CONFIGPATH}
  delete $TEMP\*.siard.validationlog.log
  rmdir /r  $TEMP\${WORK}
  return
  
show_help:
    ExecShell "open" "$TEMP\${SIARDHELP}"
    Abort

run_validation:  
  ; No SIARD file selected
  IfFileExists $SIARDFILE 0 goto_abort
  StrCpy $0 $SIARDFILE "" -6 # = ".siard"
  StrCmp $0 ".siard" 0 goto_abort
  ; SIARD file is not of Type ZIP 
  FileOpen $0 $SIARDFILE r
  FileRead $0 $1 2
  FileClose $0
  StrCmp $1 "PK" 0 goto_abort
  ; run validation
  Call RunJar
  Abort
  
goto_abort:
    MessageBox MB_OK '"$SIARDFILE" ${SIARD_MISSING}'
    ; Abort prevents from leaving the current page
    Abort   
edit_config:
    ExecWait '"notepad.exe" "$TEMP\${CONFIGPATH}\${CONFIG}"'
    Abort
reset_config:
    Call WriteConfig
    Abort
FunctionEnd

Function RunJar
  ; get logfile name
  push $SIARDFILE
  Call GetBaseName
  pop $LOGFILE 
  delete "$TEMP\$LOGFILE.siard.validationlog.log"
  ; Launch java program
  ClearErrors
  ExecWait '"$JAVA\bin\java.exe" -Xmx$HEAPSIZE -jar "$TEMP\${SIARDVAL}" "$SIARDFILE" "$TEMP"'
  IfFileExists "$TEMP\$LOGFILE.siard.validationlog.log" 0 prog_err
  IfErrors goto_err goto_ok
goto_err:
    IfFileExists "$TEMP\$LOGFILE.siard.validationlog.log" 0 prog_err
    ; read logfile in  detail view
    ExecShell "open" "$TEMP\$LOGFILE.siard.validationlog.log"
    return
prog_err:
    MessageBox MB_OK "${PROG_ERR}$\n$JAVA\bin\java.exe"
    return
goto_ok:
  ; validation without error complited    
   MessageBox MB_OK '"$SIARDFILE" ${SIARD_OK}'
FunctionEnd

Function WriteConfig
  #File /oname=$TEMP\${CONFIGPATH}\${CONFIG} ${CONFIG}
  fileOpen $0 $TEMP\${CONFIGPATH}\${CONFIG} w
  fileWrite $0 '<?xml version="1.0"?>$\r$\n'
  fileWrite $0 '<configuration>$\r$\n'
  fileWrite $0 '$\t<pathtoworkdir>$TEMP\${WORK}</pathtoworkdir>$\r$\n'
  fileWrite $0 '$\t<table-rows-limit>20000</table-rows-limit>$\r$\n'
  fileWrite $0 '</configuration>'
  fileClose $0
FunctionEnd
;--------------------------------
; Sections
Section "Install"
  ; looking for java home directory
  push ${JAVAPATH}
  Call getJavaHome
  pop $JAVA
  ; specify jar and config file to go in temp path
  File /oname=$TEMP\${SIARDVAL} ${SIARDVAL}
  File /oname=$TEMP\${SIARDHELP} ${SIARDHELP}
  CreateDirectory $TEMP\${CONFIGPATH}
  IfFileExists $TEMP\${CONFIGPATH}\${CONFIG} inst_end 0
    Call WriteConfig
  DetailPrint "Extract: $TEMP\${CONFIGPATH}\${CONFIG}"
inst_end:
SectionEnd
