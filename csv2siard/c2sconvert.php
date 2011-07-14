<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// process the first CSV line and check column names
function processCSVColumnNames($buffer, $file, $name, $table) {
global $prg_option;
	// check column names *** TO BE DONE ***
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
	return;
}
// -----------------------------------------------------------------------------
// write footer for SIARD XML file
function writeSIARDFooter($siardhandle){
	fwrite ($siardhandle, "</table>\n");
	return;
}
// -----------------------------------------------------------------------------
// process a single CSV line and write a <row> into SIARD XML file
function writeSIARDColumn($siardhandle, $buffer, $columcount, $table){
global $prg_option;
	fwrite ($siardhandle, "<row>");
	
	for ($i=1; $i <= $columcount; $i++) {
		if (trim($buffer[$i-1]) != '') {
			$buf = $buffer[$i-1];
			// type checking *** TO BE DONE ***
			switch ($prg_option['CHARSET']) {
				case "ASCII":
					$buf = utf8_encode(ascii2ansi($buf)); break;
				case "ISO-8859-1":
					$buf = utf8_encode($buf); break;
				case "UTF-8":
					break;
			}

			//if (strcasecmp($prg_option['CHARSET'], 'UTF-8') != 0) {
			//	$buf = utf8_encode($buf);
			//}
			
			// write a <column> into SIARD XML file
			$buf = '<c' . $i . '>' . $buf . '</c' . $i . '>';
			fwrite ($siardhandle, $buf);
		}
	}

	fwrite ($siardhandle, "</row>\n");
}
// -----------------------------------------------------------------------------
// write header for SIARD schema file
function writeSchemaHeader($siardhandle, $tablefolder) {
global $prg_option;

	fwrite ($siardhandle, "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n");
	fwrite ($siardhandle, "
		<xs:schema targetNamespace=\"http://www.admin.ch/xmlns/siard/1.0/$prg_option[SIARD_SCHEMA]/$tablefolder.xsd\"
			xmlns:xs=\"http://www.w3.org/2001/XMLSchema\"
			xmlns=\"http://www.admin.ch/xmlns/siard/1.0/$prg_option[SIARD_SCHEMA]/$tablefolder.xsd\"
			elementFormDefault=\"qualified\"
			attributeFormDefault=\"unqualified\">
		<xs:element name=\"table\">
			<xs:complexType>
				<xs:sequence>
					<xs:element name=\"row\" type=\"rowType\" minOccurs=\"0\" maxOccurs=\"unbounded\"/>
				</xs:sequence>
			</xs:complexType>
		</xs:element>
		<xs:complexType name=\"rowType\">
		<xs:sequence>
	");
	return;
}
// -----------------------------------------------------------------------------
// write footer for SIARD schema file
function writeSchemaFooter($siardhandle){
	fwrite ($siardhandle, "
				</xs:sequence>
			</xs:complexType>
		</xs:schema>");
	return;
}
// -----------------------------------------------------------------------------
// write content SIARD schema file
function writeSchemacontent($siardhandle, &$table){
	$colcount = 1;
	foreach ($table['_c']['column'] as $column) {
		if (is_array($column)) {
			// Convert database type to xml type ***TO BE DONE***
			// multiple columns or only one column
			$type = (array_key_exists('_a', $column)) ? $column['_a']['type'] : $column['type'];
			switch ($type) {
				case "BIGINT":
					$xstype = 'integer'; break;
				case "DOUBLE":
					$xstype = 'double'; break;
				case "INTEGER":
					$xstype = 'integer'; break;
				case "VARCHAR":
					$xstype = 'string'; break;
				default:
					$xstype = 'string'; break;
			}
			fwrite ($siardhandle, "<xs:element name=\"c$colcount\" type=\"xs:$xstype\" minOccurs=\"0\"/>\n");
			$colcount++;
		}
	}
	return;
}
// -----------------------------------------------------------------------------
// convert array to xml string
function array2xml(&$xmlary){
	$xml = '';
	if (is_array($xmlary)) {
		reset($xmlary);
		while (list($name, $ary) = each($xmlary)) {
			$xml = $xml . "<$name>" . array2xml($ary) . "</$name>\n";
		}
	}
	else {
		$xml = $xmlary;
	}
	return($xml);
}
?>
