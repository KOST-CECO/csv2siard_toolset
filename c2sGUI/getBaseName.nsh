Function GetBaseName
    ; This function takes a file name and returns the base name (no extension)
    ; Input is from the top of the stack
    ; Usage example:
    ; push (file name)
    ; call GetBaseName
    ; pop (file name)
   
	Exch $0
	Push $1
	Push $2
	Push $3

	StrCpy $1 0
	StrCpy $3 ''

	FileFunc_GetBaseName_loop:
	IntOp $1 $1 - 1
	StrCpy $2 $0 1 $1
	StrCmp $2 '' FileFunc_GetBaseName_trimpath
	StrCmp $2 '\' FileFunc_GetBaseName_trimpath
	StrCmp $3 'noext' FileFunc_GetBaseName_loop
	StrCmp $2 '.' 0 FileFunc_GetBaseName_loop
	StrCpy $0 $0 $1
	StrCpy $3 'noext'
	StrCpy $1 0
	goto FileFunc_GetBaseName_loop

	FileFunc_GetBaseName_trimpath:
	StrCmp $1 -1 FileFunc_GetBaseName_empty
	IntOp $1 $1 + 1
	StrCpy $0 $0 '' $1
	goto FileFunc_GetBaseName_end

	FileFunc_GetBaseName_empty:
	StrCpy $0 ''

	FileFunc_GetBaseName_end:
	Pop $3
	Pop $2
	Pop $1
	Exch $0
FunctionEnd
