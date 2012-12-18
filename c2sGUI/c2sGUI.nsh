;----------------------
!define HELP_Button               "Field 5"
!define CSV_DirReqest             "Field 4"
!define NO_DB_MODEL_RadioButton   "Field 7"
!define DB_MODEL_RadioButton      "Field 6"
!define DB_MODEL_FileRequest      "Field 8"
!define DB_MODEL_File             "Field 2"
!define EDIT_Button               "Field 12"
!define CREATE_Button             "Field 11"
!define PREFS_FileReqest          "Field 9"

;--------------------------------
Function ShowDialog
  ${If} $MODEL_SWITCH == 1
    WriteINIStr $DIALOG "${DB_MODEL_File}" "Text" '$EXEDIR\\no_db_model.xml'
  ${EndIf}
  InstallOptions::initDialog $DIALOG
  Pop $HWND
  ; set button "Back" invisible 
  GetDlgItem $1 $HWNDPARENT 3
  ShowWindow $1 0
  InstallOptions::show
FunctionEnd

;--------------------------------
Function LeaveDialog
  ReadINIStr $PREFS_FILE $DIALOG "${PREFS_FileReqest}" "State"
  ReadINIStr $0 $DIALOG "Settings" "State"
  
  ${Switch} "Field $0"
    ${Case} '${NO_DB_MODEL_RadioButton}'
      StrCpy $MODEL_SWITCH 1
      ReadINIStr $1 $DIALOG '${DB_MODEL_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      ReadINIStr $1 $DIALOG '${DB_MODEL_FileRequest}' 'HWND'
      SendMessage $1 ${WM_SETTEXT} 1 'STR:'
      ReadINIStr $1 $DIALOG '${DB_MODEL_File}' 'HWND'
      SendMessage $1 ${WM_SETTEXT} 1 'STR:$EXEDIR\no_db_model.xml'
      
      Abort
    ${Break}
    
    ${Case} '${DB_MODEL_RadioButton}'
      StrCpy $MODEL_SWITCH 2
      ReadINIStr $1 $DIALOG '${NO_DB_MODEL_RadioButton}' 'HWND'
      SendMessage $1 ${BM_SETCHECK} 0 0
      ReadINIStr $1 $DIALOG '${DB_MODEL_File}' 'HWND'
      SendMessage $1 ${WM_SETTEXT} 1 'STR:'
      Abort
    ${Break}
    
    ${Case} '${EDIT_Button}'
      ${If} ${FileExists} $PREFS_FILE
        ExecWait '"notepad.exe" "$PREFS_FILE"'
      ${Else}
        MessageBox MB_OK 'Achtung: keine oder keine gültige Präferenzdatei gewählt$\n$PREFS_FILE'
      ${EndIf}
      Abort
    ${Break}
    
    ${Case} '${CREATE_Button}'
      StrCpy $PAGE_NO 1
      StrCpy $R9 -1
      Call RelGotoPage
    ${Break}
    
    ${Case} '${HELP_Button}'
      ExecShell "open" "$EXEDIR\${CSV2SIARDDHELP}"
      Abort
    ${Break}
    
    ${Default}
      Call RunCSV2SIARD
      Abort
    ${Break}
  ${EndSwitch}
FunctionEnd

;--------------------------------
Function RunCSV2SIARD
  ReadINIStr $CSV_FOLDER $DIALOG "${CSV_DirReqest}" "State"
  ${IfNot} ${FileExists} $CSV_FOLDER
    MessageBox MB_OK "Achtung: das gewählte CSV Verzeichnis existiert nicht$\n$CSV_FOLDER"
    Abort
  ${EndIf}
  
  ${If} $MODEL_SWITCH == 1
    StrCpy $DB_MODEL ':NO_DB_MODEL'
  ${Else}
    ReadINIStr $DB_MODEL $DIALOG "${DB_MODEL_FileRequest}" "State"
    ${IfNot} ${FileExists} $DB_MODEL
      MessageBox MB_OK "Achtung: das ausgewählte Daten Modell existiert nicht$\n$DB_MODEL"
      Abort
    ${EndIf}
  ${EndIf}
  
  ${IfNot} ${FileExists} $PREFS_FILE
    MessageBox MB_OK 'Achtung: keine oder keine gültige Präferenzdatei gewählt$\n$PREFS_FILE'
    Abort
  ${EndIf}

  Push $CSV_FOLDER
  Call GetBaseName
  Pop $0
  StrCpy $SIARD_FILE "$EXEDIR\$0.siard"
  ${If} ${FileExists} $SIARD_FILE
    MessageBox MB_OKCANCEL 'Achtung: soll die folgende SIARD Datei überschrieben werden?$\n"$SIARD_FILE"' IDOK overwrite IDCANCEL cancel
cancel:
    Abort
overwrite:
    Delete "$SIARD_FILE"
  ${EndIf}
  
  ExecWait '"${CSV2SIARD}" "$DB_MODEL" "$CSV_FOLDER" "$SIARD_FILE" "$PREFS_FILE" ":LOG_FILE=$LOG"' $0
  ${If} $0 == 0
    MessageBox MB_OK 'Die folgende SIARD Datei wurde erfolgreich angelegt:$\n"$SIARD_FILE"';
  ${Else}
    MessageBox MB_OK 'Achtung: ein Fehler ist aufgetreten'
  ${EndIf}
  ExecWait '"notepad.exe" "$LOG"'
FunctionEnd

;--------------------------------
