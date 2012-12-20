Name "csv2siardGUI"
Icon "csv2siard.ico"
BrandingText "Copyright © KOST/CECO 2012"
OutFile "c2sGUI.exe"
InstallDir $EXEDIR
RequestExecutionLevel user
Caption "$(^Name)"
XPStyle on

;--------------------------------
!define CSV2SIARD       "bin\csv2siard.exe"
!define CSV2SIARDDHELP  "Anwendungshandbuch_v1.8.pdf"
!define GUIFILE         "c2sGUI.ini"
!define PREFFILE        "c2sPREF.ini"
!define SETTINGS        "csv2siard"

;--------------------------------
Var DIALOG
Var PREFS
Var HWND
Var MODEL_SWITCH          ; 1=no_db_model, 2=model
Var COLUMN_NAMES_SWITCH   ; COLUMN_NAMES TRUE / FALSE
Var CHECK_COLUMN_SWITCH   ; CHECK_COLUMN TRUE / FALSE
VAR CSV_FOLDER
Var DB_MODEL
Var PREFS_FILE
Var SIARD_FILE
Var OUT_PATH              ; directory for siard and no_db_model.xml
Var LOG
Var PAGE_NO               ; 2=Dialog, 1= Prefs

;--------------------------------
LicenseData license.txt
Page license
Page Custom ShowPrefs LeavePrefs
Page Custom ShowDialog LeaveDialog

;--------------------------------
!include WinMessages.nsh
!include LogicLib.nsh
!include FileFunc.nsh
!include relGotoPage.nsh
!include getBaseName.nsh
!include strTrim.nsh
!include c2sGUI.nsh
!include c2sPREF.nsh
!include c2sSET.nsh
!include c2sLang.nsh

;--------------------------------
; Functions
Function .onInit
  Call LoadSettings
  InitPluginsDir
  GetTempFileName $DIALOG $PLUGINSDIR
  GetTempFileName $PREFS $PLUGINSDIR
  GetTempFileName $LOG $PLUGINSDIR
  File /oname=$DIALOG ${GUIFILE}
  File /oname=$PREFS ${PREFFILE}
  StrCpy $PAGE_NO 2
  ;StrCpy $MODEL_SWITCH 1
  StrCpy $COLUMN_NAMES_SWITCH "TRUE"
  StrCpy $CHECK_COLUMN_SWITCH "TRUE"
FunctionEnd

;--------------------------------
Section "Install"
SectionEnd
