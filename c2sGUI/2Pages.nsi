Name "2Pages"
OutFile "2Pages.exe"
RequestExecutionLevel user

!include LogicLib.nsh
!include relGotoPage.nsh

;--------------------------------
VAR PAGE_NO

;--------------------------------
Page custom ShowFirstPage LeaveFirstPage
Page Custom ShowSecondPage LeaveSecondPage

;--------------------------------
Function .onInit
  StrCpy $PAGE_NO 0 ;This is the first page
FunctionEnd

Function ShowFirstPage
  ${If} $PAGE_NO == 0
    StrCpy $PAGE_NO 1 ;This is the first page
    StrCpy $R9 1
    Call RelGotoPage
  ${Else}
    InstallOptions::dialog "$EXEDIR\firstPage.ini"
    Pop $0
  ${EndIf}
FunctionEnd

Function LeaveFirstPage
FunctionEnd

Function ShowSecondPage 
  StrCpy $PAGE_NO 2 ;This is the second page
  InstallOptions::dialog "$EXEDIR\secondPage.ini"
  Pop $0
FunctionEnd
 
Function LeaveSecondPage
  StrCpy $R9 -1
  Call RelGotoPage
FunctionEnd

;--------------------------------
Section "myIOPage"
SectionEnd
