Name "csv2siardGUI"
Icon "csv2siard.ico"
BrandingText "Copyright © KOST/CECO 2012"
OutFile "c2sGUI.exe"
InstallDir $EXEDIR
RequestExecutionLevel user
Caption "$(^Name)"
XPStyle on

;----------------------
!define GUIFILE       "c2sGUI.ini"
!define PREFFILE      "c2sPREF.ini"

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
!include WinMessages.nsh
!include LogicLib.nsh
!include relGotoPage.nsh
!include c2sGUI.nsh
!include c2sPREF.nsh
!include c2sLang.nsh

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
Function RunCSV2SIARD
  ${If} ${FileExists} $CSV_FOLDER
    MessageBox MB_OK 'Folder: $CSV_FOLDER $DIALOG'
  ${EndIf}
FunctionEnd

;--------------------------------
Section "Install"
SectionEnd
