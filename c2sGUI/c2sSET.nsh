;--------------------------------
Function SaveSettings
  ${IfNot} ${FileExists} $LOCALAPPDATA\${SETTINGS}
    CreateDirectory $LOCALAPPDATA\${SETTINGS}
  ${EndIf}
  fileOpen $0 "$LOCALAPPDATA\${SETTINGS}\${SETTINGS}.prefs" w
    fileWrite $0 "$CSV_FOLDER$\n"
    fileWrite $0 "$PREFS_FILE$\n"
    fileWrite $0 "$MODEL_SWITCH$\n"
    fileWrite $0 "$DB_MODEL$\n"
  fileClose $0
FunctionEnd

;--------------------------------
Function LoadSettings
  ${If} ${FileExists} $LOCALAPPDATA\${SETTINGS}\${SETTINGS}.prefs
    fileOpen $0 "$LOCALAPPDATA\${SETTINGS}\${SETTINGS}.prefs" r
      fileRead $0 $R0
      ${StrTrimNewLines} $CSV_FOLDER $R0
      fileRead $0 $R0
      ${StrTrimNewLines} $PREFS_FILE $R0
      fileRead $0 $R0
      ${StrTrimNewLines} $MODEL_SWITCH $R0
      fileRead $0 $R0
      ${StrTrimNewLines} $DB_MODEL $R0
      StrCpy $OUT_PATH $CSV_FOLDER
    fileClose $0
  ${Else}
    ; initial settings
    StrCpy $CSV_FOLDER "$EXEDIR\csvdata"
    StrCpy $PREFS_FILE "$EXEDIR\preferences.prefs"
    StrCpy $MODEL_SWITCH 1
    StrCpy $DB_MODEL ''
    StrCpy $OUT_PATH $DESKTOP
  ${EndIf}
FunctionEnd
