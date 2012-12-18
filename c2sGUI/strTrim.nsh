;-------------------------------------------------------------------------------
;  Example 
;  ${StrTrimNewLines} $0 "This is just an example$\r$\n$\r$\n"
;  $0 = "This is just an example"
;-------------------------------------------------------------------------------

!define StrTrimNewLines "!insertmacro StrTrimNewLines"
 
!macro StrTrimNewLines ResultVar String
  Push "${String}"
  Call StrTrimNewLines
  Pop "${ResultVar}"
!macroend
 
Function StrTrimNewLines
/*After this point:
  ------------------------------------------
  $R0 = String (input)
  $R1 = TrimCounter (temp)
  $R2 = Temp (temp)*/
 
  ;Get input from user
  Exch $R0
  Push $R1
  Push $R2
 
  ;Initialize trim counter
  StrCpy $R1 0
 
  loop:
  ;Subtract to get "String"'s last characters
  IntOp $R1 $R1 - 1
 
  ;Verify if they are either $\r or $\n
  StrCpy $R2 $R0 1 $R1
  ${If} $R2 == `$\r`
  ${OrIf} $R2 == `$\n`
    Goto loop
  ${EndIf}
 
  ;Trim characters (if needed)
  IntOp $R1 $R1 + 1
  ${If} $R1 < 0
    StrCpy $R0 $R0 $R1
  ${EndIf}
 
/*After this point:
  ------------------------------------------
  $R0 = ResultVar (output)*/
 
  ;Return output to user
  Pop $R2
  Pop $R1
  Exch $R0
FunctionEnd
