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

;----------------------
!define INIFILE       "c2sGUI.ini"

;--------------------------------
Var DIALOG
Var HWND

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
FunctionEnd

Function ShowDialog
  InstallOptions::initDialog $DIALOG
  Pop $HWND
  InstallOptions::show
FunctionEnd

Function LeaveDialog
FunctionEnd

;--------------------------------
Section "Install"
SectionEnd
