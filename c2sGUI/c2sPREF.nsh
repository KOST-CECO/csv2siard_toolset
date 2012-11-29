;--------------------------------


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

