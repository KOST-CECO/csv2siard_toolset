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
!include relGotoPage.nsh
!include c2sLang.nsh

;----------------------
!define GUIFILE       "c2sGUI.ini"
!define PREFFILE      "c2sPREF.ini"

!define HELP_Button               "Field 5"
!define CSV_DirReqest             "Field 4"
!define NO_DB_MODEL_RadioButton   "Field 7"
!define DB_MODEL_RadioButton      "Field 6"
!define DB_MODEL_FileRequest       "Field 8"
!define DB_MODEL_File             "Field 2"
!define EDIT_Button               "Field 12"
!define CREATE_Button             "Field 11"
!define PREFS_FileReqest          "Field 9"

;--------------------------------
Var DIALOG
Var PREFS
Var HWND
Var MODEL_SWITCH  ; 1=no_db_model, 2=model
VAR CSV_FOLDER
Var DB_MODEL
Var PREFS_FILE
Var PAGE_NO       ; 2=Dialog, 1= Prefs

;--------------------------------
LicenseData license.txt
Page license
Page Custom ShowPrefs LeavePrefs
Page Custom ShowDialog LeaveDialog

;--------------------------------
; Functions
Function .onInit
  InitPluginsDir
  GetTempFileName $DIALOG $PLUGINSDIR
  GetTempFileName $PREFS $PLUGINSDIR
  File /oname=$DIALOG ${GUIFILE}
  File /oname=$PREFS ${PREFFILE}
  StrCpy $PAGE_NO 2
  StrCpy $MODEL_SWITCH 1
  WriteINIStr $DIALOG "${CSV_DirReqest}" "State" $EXEDIR
FunctionEnd

;--------------------------------
Function ShowPrefs
  ${If} $PAGE_NO == 2
    StrCpy $R9 1
    Call RelGotoPage
  ${Else}
    InstallOptions::initDialog $PREFS
    Pop $HWND
    InstallOptions::show
  ${EndIf}
FunctionEnd

Function LeavePrefs
FunctionEnd

;--------------------------------

Function ShowDialog
  ${If} $MODEL_SWITCH == 1
    WriteINIStr $DIALOG "${DB_MODEL_File}" "Text" '$EXEDIR\\no_db_model.xml'
  ${EndIf}
  InstallOptions::initDialog $DIALOG
  Pop $HWND
  InstallOptions::show
FunctionEnd

Function LeaveDialog
  ReadINIStr $CSV_FOLDER $DIALOG "${CSV_DirReqest}" "State"
  ReadINIStr $DB_MODEL $DIALOG "${DB_MODEL_FileRequest}" "State"
  ReadINIStr $PREFS_FILE $DIALOG "${PREFS_FileReqest}" "State"
  ReadINIStr $0 $DIALOG "Settings" "State"
  
  ${Switch} "Field $0"
    ${Case} '${NO_DB_MODEL_RadioButton}'
      StrCpy $MODEL_SWITCH 1
      ReadINIStr $1 $DIALOG '${DB_MODEL_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      ReadINIStr $1 $DIALOG '${DB_MODEL_FileRequest}' 'HWND'
      SendMessage $1 ${WM_SETTEXT} 1 'STR:'
      ReadINIStr $1 $DIALOG '${DB_MODEL_File}' 'HWND'
      SendMessage $1 ${WM_SETTEXT} 1 'STR:$EXEDIR\no_db_model.xml'
      
      Abort
    ${Break}
    
    ${Case} '${DB_MODEL_RadioButton}'
      StrCpy $MODEL_SWITCH 2
      ReadINIStr $1 $DIALOG '${NO_DB_MODEL_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      ReadINIStr $1 $DIALOG '${DB_MODEL_File}' 'HWND'
      SendMessage $1 ${WM_SETTEXT} 1 'STR:'
      Abort
    ${Break}
    
    ${Case} '${EDIT_Button}'
      ${If} ${FileExists} $PREFS_FILE
        ExecWait '"notepad.exe" "$PREFS_FILE"'
      ${Else}
        MessageBox MB_OK 'Achtung: keine oder keine gültige Präferenzdatei gewählt$\n$PREFS_FILE'
      ${EndIf}
      Abort
    ${Break}
    
    ${Case} '${CREATE_Button}'
      StrCpy $PAGE_NO 1
      StrCpy $R9 -1
      Call RelGotoPage
    ${Break}
      
    ${Default}
      Call RunCSV2SIARD
    ${Break}
  ${EndSwitch}
FunctionEnd

;--------------------------------

Function RunCSV2SIARD
  ${If} ${FileExists} $CSV_FOLDER
    MessageBox MB_OK 'Folder: $CSV_FOLDER $DIALOG'
  ${EndIf}
FunctionEnd
;--------------------------------
Section "Install"
SectionEnd
