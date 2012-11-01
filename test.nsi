RequestExecutionLevel user
;---------------------
;Include Modern UI
 
  !include "MUI.nsh"
  !include "LogicLib.nsh"
;----------------------
;defines for Dialog 1 - test.ini
 
  !define DIALOG1 "test.ini"
 
  !define CHK_BLUE "Field 2"
  !define CHK_RED "Field 3"
  !define CHK_GREEN "Field 4"
  !define CHK_BROWN "Field 5"
  !define CHK_PROXYSETTINGS "Field 6"
  !define GRP_PROXYOPTIONS "Field 7"
  !define LBL_IPADDRESS "Field 8"
  !define TXT_IPADDRESS "Field 9"
  !define LBL_PORT1 "Field 10"
  !define TXT_PORT1 "Field 11"
  !define CHK_ENCRYPTION "Field 12"
  !define DRQ_NSISPATH "Field 14"
  !define CMD_AUTODETECTNSISPATH "Field 15"
;--------------------------------
;General
 
  ;Name and file
  Name "MUI installation validation example"
  OutFile "test.exe"
 
  ;Default installation folder
  InstallDir "$PROGRAMFILES\MUI validate"
 
  ;Get installation folder from registry if available
  InstallDirRegKey HKCU "Software\MUI validate" ""
 
;--------------------------------
;Pages
 
  Page custom InitPage ValidatePage
  !insertmacro MUI_PAGE_DIRECTORY
  !insertmacro MUI_PAGE_INSTFILES
 
;--------------------------------
;Interface Settings
 
  !define MUI_ABORTWARNING
 
;--------------------------------
;Languages
 
  !insertmacro MUI_LANGUAGE "English"
 
;--------------------------------
;Reserve Files
 
  ;If you are using solid compression, files that are required before
  ;the actual installation should be stored first in the data block,
  ;because this will make your installer start faster.
 
  ReserveFile "test.ini"
  !insertmacro MUI_RESERVEFILE_INSTALLOPTIONS
 
;--------------------------------
;Variables
 
  Var "tmp"
 
!include "WordFunc.nsh"
!insertmacro WordReplace
!insertmacro WordFind
 
; Activate a group of controls, depending on the state of one control
; 
; Usage:
; 
; eg. !insertmacro GROUPCONTROLS "${DIALOG1}" "${CHK_PROXYSETTINGS}" "${LBL_IPADDRESS}|${TXT_IPADDRESS}|${LBL_PORT1}|${TXT_PORT1}|${CHK_ENCRYPTION}"
; FILE:          INI-file in $pluginsdir
; SOURCECONTROL: RadioButton, Checkbox
; CONTROLGROUP:  pipe delimited list of controls; ${BUTTON1}|${CHECKBOX}|${TEXTFIELD}
;
; Requires:
;
; !include "WordFunc.nsh"
; !insertmacro WordReplace
; !insertmacro WordFind
;
!macro GROUPCONTROLS FILE SOURCECONTROL CONTROLGROUP
  Push $R0 ;holds element
  Push $R1 ;counter
  Push $R2 ;state of the control
  Push $R3 ;flags of the control / hwnd of the control
 
  !insertmacro MUI_INSTALLOPTIONS_READ $R2 "${FILE}" "${SOURCECONTROL}" "State"
 
  StrCpy $R1 1
  ${Do}
    ClearErrors
    ${WordFind} "${CONTROLGROUP}" "|" "E+$R1" $R0
 
    ${If} ${Errors}
    ${OrIf} $R0 == ""
      ${ExitDo}
    ${EndIf}
 
    ; Put state change in flags of element as well
    !insertmacro MUI_INSTALLOPTIONS_READ $R3 "${FILE}" "$R0" "Flags"
    ${If} "$R2" == "1"
      ${WordReplace} $R3 "DISABLED" "" "+" $R3
       ${WordReplace} $R3 "||" "|" "+" $R3
      !insertmacro MUI_INSTALLOPTIONS_WRITE "${FILE}" "$R0" "Flags" $R3
    ${Else}
      !insertmacro MUI_INSTALLOPTIONS_WRITE "${FILE}" "$R0" "Flags" "$R3|DISABLED"
    ${EndIf}
 
    !insertmacro MUI_INSTALLOPTIONS_READ $R3 "${FILE}" "$R0" "HWND"
    EnableWindow $R3 $R2
 
    IntOp $R1 $R1 + 1
  ${Loop}
 
  Pop $R3
  Pop $R2
  Pop $R1
  Pop $R0
 
!macroend
 
;change text field and put value in ini file
; 
; Usage:
;
;  !insertmacro CHANGETEXTFIELD "${DIALOG1}" "${DRQ_NSISPATH}" $tmp
;
; FILE:    INI-file in $pluginsdir
; ELEMENT: name of the control
; VALUE:   value that should appear in control
;
!macro CHANGETEXTFIELD FILE ELEMENT VALUE
  Push $R0 ; holds value
  !insertmacro MUI_INSTALLOPTIONS_WRITE ${VALUE} "${FILE}" "${ELEMENT}" "State"
  !insertmacro MUI_INSTALLOPTIONS_READ $R0 "${FILE}" "${ELEMENT}" "HWND"
  SendMessage $R0 ${WM_SETTEXT} 0 "STR:${VALUE}"
  Pop $R0
!macroend
 
; checks a group of checkboxes and counts how many of them
; are activated.
;
; Usage:
;
; Create a langstring containing an error message
; eg. LangString TEXT_LIMITATIONSEXEEDED ${LANG_ENGLISH} "Choose either two or three colours!"
;
; eg. !insertmacro CHECKBOXCHECKER "${DIALOG1}" "${CHK_BLUE}|${CHK_RED}|${CHK_GREEN}|${CHK_BROWN}" 2 3
;
; FILE:          INI-file in $pluginsdir
; CONTROLGROUP:  pipe delimited list of controls; ${BUTTON1}|${CHECKBOX}|${TEXTFIELD}
; MIN/MAX:       at least ${MIN} and no more than ${MAX} controls must be in activated state
;
; Requires:
;
; !include "WordFunc.nsh"
; !insertmacro WordFind
;
!macro CHECKBOXCHECKER FILE CONTROLGROUP MIN MAX
 
  Push $R0 ;holds element
  Push $R1 ;counter
  Push $R2 ;count activated elements
  Push $R3 ;state of the control
 
  StrCpy $R1 1
  StrCpy $R2 0
  ${Do}
    ClearErrors
    ${WordFind} "${CONTROLGROUP}" "|" "E+$R1" $R0
 
    ${If} ${Errors}
    ${OrIf} $R0 == ""
      ${ExitDo}
    ${EndIf}
 
    ; Put state change in flags of element as well
    !insertmacro MUI_INSTALLOPTIONS_READ $R3 "${FILE}" "$R0" "State"
    ${If} "$R3" == "1"
      IntOp $R2 $R2 + 1
    ${EndIf}
 
    IntOp $R1 $R1 + 1
  ${Loop}
 
  ${If} $R2 < ${MIN}
  ${OrIf} $R2 > ${MAX}
    MessageBox MB_OK|MB_ICONSTOP "$(TEXT_LIMITATIONSEXCEEDED)"
    Abort
  ${EndIf}
 
  Pop $R3
  Pop $R2
  Pop $R1
  Pop $R0
 
!macroend
 
;--------------------------------
;Installer Sections
 
Section "Dummy Section" SecDummy
 
  SetOutPath "$INSTDIR"
 
  ;ADD YOUR OWN FILES HERE...
 
SectionEnd
 
;--------------------------------
;Installer Functions
 
Function .onInit
 
;  !insertmacro MUI_LANGDLL_DISPLAY
 
FunctionEnd
 
LangString TEXT_IO_TITLE ${LANG_ENGLISH} "Test page"
LangString TEXT_IO_SUBTITLE ${LANG_ENGLISH} "Test some things"
LangString TEXT_LIMITATIONSEXCEEDED ${LANG_ENGLISH} "Choose either two or three colours!"
 
Function InitPage
 
  ;Extract InstallOptions INI files
  !insertmacro MUI_INSTALLOPTIONS_EXTRACT "${DIALOG1}"
 
  !insertmacro MUI_HEADER_TEXT "$(TEXT_IO_TITLE)" "$(TEXT_IO_SUBTITLE)"
  !insertmacro MUI_INSTALLOPTIONS_INITDIALOG "${DIALOG1}"
  !insertmacro GROUPCONTROLS "${DIALOG1}" "${CHK_PROXYSETTINGS}" "${LBL_IPADDRESS}|${TXT_IPADDRESS}|${LBL_PORT1}|${TXT_PORT1}|${CHK_ENCRYPTION}"
  !insertmacro MUI_INSTALLOPTIONS_SHOW  
 
FunctionEnd
 
Function ValidatePage
  ; handle notify event of element
  !insertmacro MUI_INSTALLOPTIONS_READ $tmp "${DIALOG1}" "Settings" "State"  
  ${Switch} "Field $tmp"
    ${Case} "${CHK_PROXYSETTINGS}"
      !insertmacro GROUPCONTROLS "${DIALOG1}" "${CHK_PROXYSETTINGS}" "${LBL_IPADDRESS}|${TXT_IPADDRESS}|${LBL_PORT1}|${TXT_PORT1}|${CHK_ENCRYPTION}"
      Abort
    ${Case} "${CMD_AUTODETECTNSISPATH}"
      ;read registry value and set text field
      ReadRegStr $tmp HKLM "Software\NSIS" ""
      !insertmacro CHANGETEXTFIELD "${DIALOG1}" "${DRQ_NSISPATH}" $tmp
      Abort      
  ${EndSwitch}
 
  ; check if 2 or 3 check boxes are activated
  !insertmacro CHECKBOXCHECKER "${DIALOG1}" "${CHK_BLUE}|${CHK_RED}|${CHK_GREEN}|${CHK_BROWN}" 2 3
 
  ;  check if checkbox is activated
  !insertmacro MUI_INSTALLOPTIONS_READ $tmp "${DIALOG1}" "${CHK_PROXYSETTINGS}" "State"  
  ${If} $tmp == 1
    Var /GLOBAL ip1
    Var /GLOBAL port1
 
    !insertmacro MUI_INSTALLOPTIONS_READ $ip1 "${DIALOG1}" "${TXT_IPADDRESS}" "State"
    Push "$ip1"
    Call ValidateIP
    ${If} ${Errors}
      MessageBox MB_ICONEXCLAMATION "IP not correct! $ip1"
      Abort
    ${EndIf}
 
    !insertmacro MUI_INSTALLOPTIONS_READ $port1 "${DIALOG1}" "${TXT_PORT1}" "State"
    ${If} $port1 < 1024
    ${OrIf} $port1 > 32768
      MessageBox MB_ICONEXCLAMATION "Port not valid!"
      Abort
    ${EndIf}
  ${EndIf}
 
  ; check for file in NSIS' path
  !insertmacro MUI_INSTALLOPTIONS_READ $tmp "${DIALOG1}" "${DRQ_NSISPATH}" "State"
  ${Unless} ${FileExists} "$tmp\makensis.exe"
    MessageBox MB_OK|MB_ICONEXCLAMATION "makensis.exe in NSIS path not found!"
    Abort
  ${EndIf}
 
  ; TODO: save values to ini file/registry...
 
FunctionEnd
 
; http://nsis.sourceforge.net/Validate_IP_function
!include WordFunc.nsh
 
!insertmacro WordFind
!insertmacro StrFilter
Function ValidateIP
 
  Exch $0
  Push $1
  Push $2
 
  ${StrFilter} $0 1 "." "" $1
  ${If} $0 != $1
    # invalid charcaters used
    #   example: a127.0.0.1
    Goto error
  ${EndIf}
 
  ${WordFind} $0 . "#" $1
  ${If} $1 != 4
    # wrong number of numbers
    #   example: 127.0.0.
    Goto error
  ${EndIf}
 
  ${WordFind} $0 . "*" $1
  ${If} $1 != 3
    # wrong number of dots
    #   example: 127.0.0.1.
    Goto error
  ${EndIf}
 
  ${For} $2 1 4
    ${WordFind} $0 . +$2 $1
 
    ${If} $1 > 255
    ${OrIf} $1 < 0
      # invalid number
      #   example: 500.0.0.1
      Goto error
    ${EndIf}
  ${Next}
 
  Pop $2
  Pop $1
  Pop $0
 
  ClearErrors
 
  Return
 
  error:
 
    Pop $2
    Pop $1
    Pop $0
 
    SetErrors
 
FunctionEnd
