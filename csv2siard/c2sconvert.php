<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// process the first CSV line (work in progress)
function processCSVColumnNames($buffer, $file, $name, $table) {
global $prg_option;
	// TO BE DONE
	return(true);
}
// -----------------------------------------------------------------------------
// write header for SIARD XML file
function writeSIARDHeader($siardhandle, $tablefolder) {
global $prg_option;

	fwrite ($siardhandle, "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n");
	fwrite ($siardhandle, "<table xsi:schemaLocation=\"http://www.admin.ch/xmlns/siard/1.0/$prg_option[SIARD_SCHEMA]/$tablefolder.xsd $tablefolder.xsd\"
	xmlns=\"http://www.admin.ch/xmlns/siard/1.0/$prg_option[SIARD_SCHEMA]/$tablefolder.xsd\"
	xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
	");
}
// -----------------------------------------------------------------------------
// write header for SIARD XML file
function writeSIARDFooter($siardhandle){
	fwrite ($siardhandle, "</table>\n");
}
// -----------------------------------------------------------------------------
// process a single CSV line and write a into SIARD XML file
function writeSIARDColumn($siardhandle, $buffer, $columcount){
global $prg_option;
	fwrite ($siardhandle, "<row>");
	
	for ($i=1; $i <= $columcount; $i++) {
		if (trim($buffer[$i-1]) != '') {
			$buf = $buffer[$i-1];
			// Characterset encoding TO BE DONE
			if (strcasecmp($prg_option['CHARSET'], 'UTF-8') != 0) {
				$buf = utf8_encode($buf);
			}
			$buf = '<c' . $i . '>' . $buf . '</c' . $i . '>';
			fwrite ($siardhandle, $buf);
		}
	}

	fwrite ($siardhandle, "</row>\n");
}
?>
