;--------------------------------

!define COLUMN_NAMES_TRUE_RadioButton      "Field 5"
!define COLUMN_NAMES_FALSE_RadioButton     "Field 4"
!define CHECK_COLUMN_TRUE_RadioButton      "Field 7"
!define CHECK_COLUMN_FALSE_RadioButton     "Field 6"
!define FILE_MASK_Combobox                 "Field 10"
!define CHARSET_Combobox                   "Field 12"
!define DELIMITED_Combobox                 "Field 9"
!define QUOTE_Combobox                     "Field 8"
!define DEFAULT_PREFS_File                 "Field 2"

;--------------------------------
Function ShowPrefs
  ${If} $PAGE_NO == 2
    StrCpy $R9 1
    Call RelGotoPage
  ${Else}
    WriteINIStr $PREFS "${DEFAULT_PREFS_File}" "State" $PREFS_FILE
    InstallOptions::initDialog $PREFS
    Pop $HWND
    ; set button "Back" invisible 
    GetDlgItem $1 $HWNDPARENT 3
    ShowWindow $1 0
    InstallOptions::show
  ${EndIf}
FunctionEnd

Function LeavePrefs
  ${If} $PAGE_NO == 2
    Return
  ${EndIf}

  ReadINIStr $PREFS_FILE $PREFS "${DEFAULT_PREFS_File}" "State"
  ReadINIStr $0 $PREFS "Settings" "State"
  
  ${Switch} "Field $0"
    ${Case} '${COLUMN_NAMES_TRUE_RadioButton}'
      StrCpy $COLUMN_NAMES_SWITCH "TRUE"
      ReadINIStr $1 $PREFS '${COLUMN_NAMES_FALSE_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      Abort
    ${Break}
    
    ${Case} '${COLUMN_NAMES_FALSE_RadioButton}'
      StrCpy $COLUMN_NAMES_SWITCH "FALSE"
      ReadINIStr $1 $PREFS '${COLUMN_NAMES_TRUE_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      Abort
    ${Break}
    
    ${Case} '${CHECK_COLUMN_TRUE_RadioButton}'
      StrCpy $CHECK_COLUMN_SWITCH "TRUE"
      ReadINIStr $1 $PREFS '${CHECK_COLUMN_FALSE_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      Abort
    ${Break}
    
    ${Case} '${CHECK_COLUMN_FALSE_RadioButton}'
      StrCpy $CHECK_COLUMN_SWITCH "FALSE"
      ReadINIStr $1 $PREFS '${CHECK_COLUMN_TRUE_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      Abort
    ${Break}
    
    ${Default}
      Call WritePREFS
    ${Break}
  ${EndSwitch}
FunctionEnd

Function WritePREFS
  ${If} ${FileExists} $PREFS_FILE
    MessageBox MB_YESNO 'Achtung: soll die Präferenzdatei neu geschrieben werden$\n$PREFS_FILE' IDYES overwrite IDNO cancel
cancel:
    Return
overwrite:
  ${EndIf}
  fileOpen $0 "$PREFS_FILE" w
  fileWrite $0 "# Default preferences$\r$\n"
  fileWrite $0 "# FILE_MASK: wild card is replaced with table name$\r$\n"
  fileWrite $0 "#$\r$\n"
  ReadINIStr $1 $PREFS "${FILE_MASK_Combobox}" "State"
  fileWrite $0 "FILE_MASK=$1$\r$\n"
  ReadINIStr $1 $PREFS "${CHARSET_Combobox}" "State"
  fileWrite $0 "CHARSET=$1$\r$\n"
  ReadINIStr $1 $PREFS "${DELIMITED_Combobox}" "State"
  fileWrite $0 "DELIMITED=$1$\r$\n"
  ReadINIStr $1 $PREFS "${QUOTE_Combobox}" "State"
  ${If} $1 == '{none}'
    fileWrite $0 "QUOTE=$\r$\n"
  ${Else}
    fileWrite $0 "QUOTE=$1$\r$\n"
  ${EndIf}
  fileWrite $0 "COLUMN_NAMES=$COLUMN_NAMES_SWITCH$\r$\n"
  fileWrite $0 "CHECK_COLUMN=$CHECK_COLUMN_SWITCH$\r$\n"
  fileWrite $0 "#$\r$\n"
  fileWrite $0 "# TMPDIR=c:\tmp$\r$\n"
  fileClose $0
FunctionEnd

;--------------------------------

