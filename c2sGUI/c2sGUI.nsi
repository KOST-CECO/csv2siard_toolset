Name "csv2siardGUI"
Icon "csv2siard.ico"
BrandingText "Copyright © KOST/CECO 2012"
OutFile "c2sGUI.exe"
InstallDir $EXEDIR
RequestExecutionLevel user
Caption "$(^Name)"
XPStyle on

;--------------------------------
!include WinMessages.nsh
!include LogicLib.nsh
!include c2sLang.nsh

;----------------------
!define INIFILE       "c2sGUI.ini"

!define HELP_Button               "Field 4"
!define CSV_DirReqest             "Field 3"
!define NO_DB_MODEL_RadioButton   "Field 6"
!define MODEL_RadioButton         "Field 5"
!define MODEL_FileReqest          "Field 7"
!define EDIT_Button               "Field 11"
!define CREATE_Button             "Field 10"
!define PREFS_FileReqest          "Field 8"

;--------------------------------
Var DIALOG
Var HWND
Var MODEL_SWITCH  ; 0=not set, 1=no_db_model, 2=model
VAR CSV_FOLDER
Var DB_MODEL
Var PREFS_FILE

;--------------------------------
LicenseData license.txt
Page license
Page Custom ShowDialog LeaveDialog

;--------------------------------
; Functions
Function .onInit
  InitPluginsDir
  GetTempFileName $DIALOG $PLUGINSDIR
  File /oname=$DIALOG ${INIFILE}
  StrCpy $MODEL_SWITCH 0
FunctionEnd

Function ShowDialog
  InstallOptions::initDialog $DIALOG
  Pop $HWND
  InstallOptions::show
FunctionEnd

Function LeaveDialog
  ReadINIStr $CSV_FOLDER $DIALOG "${CSV_DirReqest}" "State"
  ReadINIStr $DB_MODEL $DIALOG "${MODEL_FileReqest}" "State"
  ReadINIStr $PREFS_FILE $DIALOG "${PREFS_FileReqest}" "State"
  ReadINIStr $0 $DIALOG "Settings" "State"  
  ${Switch} "Field $0"
  ${Case} '${NO_DB_MODEL_RadioButton}'
    StrCpy $MODEL_SWITCH 1
    ReadINIStr $1 $DIALOG '${MODEL_RadioButton}' 'HWND'
    SendMessage $1 ${BM_SETCHECK} 0 0
    Abort
    ${Break}
  ${Case} '${MODEL_RadioButton}'
    StrCpy $MODEL_SWITCH 2
    ReadINIStr $1 $DIALOG '${NO_DB_MODEL_RadioButton}' 'HWND'
    SendMessage $1 ${BM_SETCHECK} 0 0
    Abort
    ${Break}
  ${Default}
    Call RunCSV2SIARD
    ${Break}
  ${EndSwitch}
FunctionEnd

Function RunCSV2SIARD
  ${If} ${FileExists} $CSV_FOLDER
    MessageBox MB_OK 'Folder: $CSV_FOLDER $DIALOG'
  ${EndIf}
FunctionEnd
;--------------------------------
Section "Install"
SectionEnd
