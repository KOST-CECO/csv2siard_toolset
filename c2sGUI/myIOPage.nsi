Name "myIOPage"
OutFile "myIOPage.exe"
RequestExecutionLevel user

!include relGotoPage.nsh

;--------------------------------
Page custom CustomPage
Page components Components_PreFunction
Page directory Directory_PreFunction

;--------------------------------
Function CustomPage
  StrCpy $R8 1 ;This is the first page
  InstallOptions::dialog "$EXEDIR\myIOPage.ini"
  Pop $0
FunctionEnd
 
Function Components_PreFunction
  StrCpy $R8 2 ;This is the second page
FunctionEnd
 
Function Directory_PreFunction
  StrCpy $R8 3 ;This is the third page
FunctionEnd
 
Function .onUserAbort
  StrCmp $R8 1 0 End ;Compare the variable with the
                     ;page index of your choice
    StrCpy $R9 1
    Call RelGotoPage
    Abort
  End:
FunctionEnd

;--------------------------------
Section "myIOPage"
SectionEnd
