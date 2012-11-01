outfile 'CheckboxNotify.exe'
showinstdetails show
licenseData '${NSISDIR}\Docs\makensisw\License.txt'
RequestExecutionLevel user

;----------------------
!include WinMessages.nsh
!include LogicLib.nsh

;----------------------
!define NOCHECKED_TEXT ''

;----------------------
page license
page custom CustomCreate CustomLeave
page instfiles

;----------------------
section -
sectionend

;----------------------
function .onInit
	initpluginsdir
	gettempfilename $0
	rename $0 '$PLUGINSDIR\custom.ini'
	call WriteIni
functionend
 
function CustomCreate
	push $R1 ;park contains of $R1 to the stack
	InstallOptions::InitDialog /NOUNLOAD '$PLUGINSDIR\custom.ini'
	pop $R1 ;$R1 contains the dialog HWND
	GetDlgItem $R0 $HWNDPARENT 1
	EnableWindow $R0 0
	InstallOptions::Show '$PLUGINSDIR\custom.ini'
	pop $R1 ;$R1 contains the pressed button 
	pop $R1 ;$R1 contains the the path to the custom.ini
	pop $R1 ;$R1 got back the value from stack
functionend
 
function CustomLeave
	readinistr $0 '$PLUGINSDIR\custom.ini' 'Settings' 'State'
	${if} $0 == 1
		readinistr $1 '$PLUGINSDIR\custom.ini' 'Field 1' 'Text'
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 5' 'HWND'
		readinistr $3 '$PLUGINSDIR\custom.ini' 'Field 1' 'State'
		${if} $3 == 1
			SendMessage $2 ${WM_SETTEXT} 1 'STR:$1'
			GetDlgItem $R0 $HWNDPARENT 1
			EnableWindow $R0 1
		${else}
			SendMessage $2 ${WM_SETTEXT} 1 'STR:${NOCHECKED_TEXT}'
			GetDlgItem $R0 $HWNDPARENT 1
			EnableWindow $R0 0
		${endif}
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 2' 'HWND'
		SendMessage $2 ${BM_SETCHECK} 0 0
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 3' 'HWND'
		SendMessage $2 ${BM_SETCHECK} 0 0
		abort
	${elseif} $0 == 2
		readinistr $1 '$PLUGINSDIR\custom.ini' 'Field 2' 'Text'
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 5' 'HWND'
		readinistr $3 '$PLUGINSDIR\custom.ini' 'Field 2' 'State'
		${if} $3 == 1
			SendMessage $2 ${WM_SETTEXT} 1 'STR:$1'
			GetDlgItem $R0 $HWNDPARENT 1
			EnableWindow $R0 1
		${else}
			SendMessage $2 ${WM_SETTEXT} 1 'STR:${NOCHECKED_TEXT}'
			GetDlgItem $R0 $HWNDPARENT 1
			EnableWindow $R0 0
		${endif}
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 1' 'HWND'
		SendMessage $2 ${BM_SETCHECK} 0 0
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 3' 'HWND'
		SendMessage $2 ${BM_SETCHECK} 0 0
		abort
	${elseif} $0 == 3
		readinistr $1 '$PLUGINSDIR\custom.ini' 'Field 3' 'Text'
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 5' 'HWND'
		readinistr $3 '$PLUGINSDIR\custom.ini' 'Field 3' 'State'
		${if} $3 == 1
			SendMessage $2 ${WM_SETTEXT} 1 'STR:$1'
			GetDlgItem $R0 $HWNDPARENT 1
			EnableWindow $R0 1
		${else}
			SendMessage $2 ${WM_SETTEXT} 1 'STR:${NOCHECKED_TEXT}'
			GetDlgItem $R0 $HWNDPARENT 1
			EnableWindow $R0 0
		${endif}
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 1' 'HWND'
		SendMessage $2 ${BM_SETCHECK} 0 0
		readinistr $2 '$PLUGINSDIR\custom.ini' 'Field 2' 'HWND'
		SendMessage $2 ${BM_SETCHECK} 0 0
		abort
	${endif}
functionend
 
function WriteIni
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Settings' 'NumFields' '5'
	 
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Type' 'Checkbox'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Left' '2'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Top' '2'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Right' '100'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Bottom' '14'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Text' 'Checkbox one'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'State' '0'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 1' 'Flags' 'NOTIFY'
	 
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Type' 'Checkbox'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Left' '2'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Top' '18'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Right' '100'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Bottom' '32'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Text' 'Checkbox two'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'State' '0'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 2' 'Flags' 'NOTIFY'
	 
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Type' 'Checkbox'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Left' '2'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Top' '36'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Right' '100'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Bottom' '50'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Text' 'Checkbox three'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'State' '0'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 3' 'Flags' 'NOTIFY'
	 
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 4' 'Type' 'GroupBox'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 4' 'Left' '30'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 4' 'Top' '70'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 4' 'Right' '-31'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 4' 'Bottom' '120'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 4' 'Text' 'Select Checkbox'
	 
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 5' 'Type' 'Text'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 5' 'Left' '40'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 5' 'Top' '90'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 5' 'Right' '-41'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 5' 'Bottom' '105'
	WriteIniStr '$PLUGINSDIR\custom.ini' 'Field 5' 'State' '${NOCHECKED_TEXT}'
Functionend
