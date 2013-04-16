<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// process the first CSV line and check and count column names
function processCSVColumnNames($buffer, $file, $table, $input) {
global $prg_option;

	$columnlist = getColumnNames($table);
	$fct = 0;
	// check for column names
	foreach ($columnlist as $name) {
		$colname = trim(@$buffer[$fct]);
		if ($prg_option['CHECK_NAMES'] and strcasecmp($name, $colname) != 0) {
			if ($colname == '') {
				log_echo("\nColumn '$name' in database model is missing in CSV file $file");
			} else {
				log_echo("\nColumn '$name' in database model does not confirm with column '$buffer[$fct]' in CSV file $file");
			}
			$prg_option['ERR'] = 32;
			return(false);
		}
		$fct++;
	}
	
	// check column count
	$buf = array_chunk($input, 1);
	$ict = count($buf)-1;
	$ict = (trim($buf[$ict][0]) != '') ? $ict+1 : $ict;
	if ($fct != $ict) {
		log_echo("\nTo many columns in CSV file $file"); $prg_option['ERR'] = 27; return(false);
	}
	return(true);
}
// -----------------------------------------------------------------------------
// get column names for one table from DB-Model and check SQL-naming convention
// return a list of column names or null
function getColumnNames($table) {
global $prg_option;
	$collist = array();
	$errflag = false;
	
	// check for column names
	$fct = 0;
	foreach ($table['_c']['column'] as $column) {
		if (is_array($column)) {
			// multiple columns or only one column
			$name = (array_key_exists('_a', $column)) ? $column['_a']['name'] : $column['name'];
			$fct++;
			if (!testDBMSNaming($name)) {
				log_echo("\nColumn no $fct '".utf8_decode($name)."' does not confirm with SQL naming convention");
				$errflag = true;
			}
			$collist[] = $name;
		}
	}
	if ($errflag){ $prg_option['ERR'] = 32; return($collist); }
	return($collist);
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
// process a single CSV line and write a <row> into SIARD XML file
function writeSIARDColumn($siardhandle, $buffer, $columcount, $rowcount, &$table){
global $prg_option;

	$columcount = ($columcount > count($buffer)) ? count($buffer) : $columcount;

	fwrite ($siardhandle, "<row>");
	
	// handle each <column> in a CSV line
	for ($i=1; $i <= $columcount; $i++) {
		// multiple columns or only one column
		$column = (array_key_exists($i-1, $table['_c']['column'])) ? $table['_c']['column'][$i-1] : $table['_c']['column'];

		// check for required constraint
		$required = false;
		if (array_key_exists('_a', $column)) {
			$required = (array_key_exists('required', $column['_a'])) ? $column['_a']['required'] : false;
		}
		else {
			$required = (array_key_exists('required', $column)) ? $column['required'] : false;
		}
		if ($required == 'true' and trim($buffer[$i-1] == '')) {
			log_echo("\nRestriction 'field required' is violated in row $rowcount, column $i"); $prg_option['ERR'] = 32;
		}

		if (trim($buffer[$i-1]) != '') {
			$buf = $buffer[$i-1];
			// check field type (type constraint) and convert to XML type (default size 255)
			if (array_key_exists('_a', $column)) {
				$type = $column['_a']['type'];
				$size = (array_key_exists('size', $column['_a'])) ? $column['_a']['size'] : NULL;
				$scale = (array_key_exists('scale', $column['_a'])) ? $column['_a']['scale'] : NULL;
			}
			else {
				$type = $column['type'];
				$size = (array_key_exists('size', $column)) ? $column['size'] : NULL;
				$scale = (array_key_exists('scale', $column)) ? $column['scale'] : NULL;
			}
			$buf = trim($buf);
			// file with EOF = SUB (dec 026 hex 0xA1)
			$buf = rtrim($buf, "\x1A");
			$b = $buf;
			switch ($type) {
				case "TINYINT":
				case "SMALLINT":
				case "INTEGER":
				case "BIGINT":
					// remove decimal zeros (e.g. 127.0)
					$b_ = round($buf);
					if ($b_ == $buf) { $buf = $b_; }
					if (!ctype_digit(ltrim($buf, '-'))) {
						log_echo("\nInteger type convertion failed in row $rowcount, column $i => '$buf'"); $prg_option['ERR'] = 32;
					}
					if (ltrim($buf, '-') > 2147483647) {
						log_echo("\nValue too large for signed integer in row $rowcount, column $i => '$buf'"); $prg_option['ERR'] = 32;
					}
					break;
				case "FLOAT":
				case "REAL":
				case "DOUBLE":
				case "NUMERIC":
				case "DECIMAL":
					$buf = strtr ($buf, ',', '.');
					if (!is_numeric ($buf)) {
						log_echo("\nNumeric type convertion failed in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
					}
					if (!is_null($size)) {
						if (strlen(str_replace('.', '', $buf)) > $size) {
							log_echo("\nField exceeds defined precision: $size in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
						}
						if (!is_null($scale)) {
							$pbuf = explode('.', $buf);
							if (count($pbuf) > 1 and strlen($pbuf[1]) > $scale) {
								log_echo("\nField exceeds defined scale: $scale in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
							}
						}
					}
					break;
				case "DATE":
					// remove decimal zeros from EXCEL(e.g. 127.0)
					if (is_numeric($buf)) {
						$b_ = @round($buf);
						if ($b_ == $buf) { $buf = $b_; }
					}
					$td = convert2XMLdate($buf);
					if (!$td) {
						log_echo("\nDate convertion failed in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
					} else {
						$buf = substr($td['date'], 0, 10);
					}
					break;
				case "TIME":
					$td = convert2XMLdate($buf);
					if (!$td) {
						log_echo("\nTime convertion failed in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
					} else {
						$buf = substr($td['date'], 11);
					}
					break;
				case "TIMESTAMP":
					$td = convert2XMLdate($buf);
					if (!$td) {
						log_echo("\nTimestamp convertion failed in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
					} else {
						$buf = $td['date'].'.000000000Z';
					}
					break;
				case "CHAR":
				case "VARCHAR":
				case "LONGVARCHAR":
				case "CLOB":
					// check field size
					$size = (is_null($size)) ? 255 : $size; // default size
					$plain = ($prg_option['CHARSET'] == 'UTF-8') ? utf8_decode($buf) : $buf;
					if (strlen($plain) > $size) {
						$plain = ansi2ascii($plain);
						log_echo("\nFieldsize exceeds defined char size: $size in row $rowcount, column $i => '$plain'"); $prg_option['ERR'] = 32;
					}
					// convert string to XML characterset utf-8
					if ($prg_option['CHARSET'] == 'ASCII') {					// includes ASCII and OEM
						$buf = utf8_encode(ascii2ansi($buf));
					}
					elseif ($prg_option['CHARSET'] == 'ISO-8859-1') {	// includes ANSI and ISO-8859-1
						$buf = utf8_encode($buf);
					}
					// xml encode
					$buf = xml_encode($buf);
					// encode consecutive spaces
					$buf = xml_white_space($buf);
					break;
				case "BIT":
					$size = (is_null($size)) ? 8 : $size; // default size
					if (strlen($buf) > $size) {
						log_echo("\nFieldsize exceeds defined bit size: $size in row $rowcount, column $i"); $prg_option['ERR'] = 32;
					}
					if ($size > 8) {
						log_echo("\nFieldsize $size exceeds max bit size 8 in row $rowcount, column $i"); $prg_option['ERR'] = 32;
					}
					$buf = bindec($buf); 
					$buf = chr($buf);
					$buf = bin2hex($buf);
					break;
				case "BINARY":
					$size = (is_null($size)) ? 4096 : $size; // default size
					if (strlen($buf) > $size) {
						log_echo("\nFieldsize (".strlen($buf).") exceeds defined size: $size in row $rowcount, column $i"); $prg_option['ERR'] = 32;
					}
					$buf = bin2hex($buf);
					break;
				case "VARBINARY":
				case "LONGVARBINARY":
				case "BLOB":
					$size = (is_null($size)) ? 4096 : $size; // default size
					$bbuf = base64_decode($buf);
					if ($bbuf == FALSE) {
						log_echo("\nBase64 decoding failed in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
						$bbuf = $buf;
					} elseif (strlen($buf) > $size) {
						log_echo("\nFieldsize  (".strlen($buf).") exceeds defined size: $size in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
					}
					$buf = bin2hex($bbuf);
					break;
				case "NULL":
				case "OTHER":
				case "JAVA_OBJECT":
				case "DISTINCT":
				case "STRUCT":
				case "ARRAY":
					log_echo("Data type '$type' not supported in row $rowcount, column $i => '$b'"); $prg_option['ERR'] = 32;
					break;
				case "REF":
					break;
				case "BOOLEANINT":
				case "BOOLEANCHAR":
					$buf = (to_bool($buf)) ? 'true' : 'false';
					break;
				default:
					break;
			}
			
			// write a <column> into SIARD XML file
			if ($required != 'true' and $buf == '') {
				// do not write empty column if not necessary (e.g. date = 0)
			} else {
				$buf = '<c' . $i . '>' . $buf . '</c' . $i . '>';
				fwrite ($siardhandle, $buf);
			}
		}
	}

	fwrite ($siardhandle, "</row>\n");
}
// -----------------------------------------------------------------------------
// write footer for SIARD XML file
function writeSIARDFooter($siardhandle){
	fwrite ($siardhandle, "</table>\n");
	return;
}
// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
// write header for SIARD schema file
function writeSchemaHeader($siardhandle, $tablefolder, &$table) {
global $prg_option;

	$rct = (getTableOption($table, 'rowcount'));
	if ($rct >0) {
		 if (array_key_exists('ROW_COUNT', $prg_option)) {
		 		$occurrence = "minOccurs=\"$rct\" maxOccurs=\"$rct\"";
			}
			// min-maxOccurs= according to eCH SIARD recomondation
			else {
				$occurrence = "minOccurs=\"0\" maxOccurs=\"unbounded\"";
		}
	}
	else {
		$occurrence = "minOccurs=\"0\" maxOccurs=\"unbounded\"";
	}

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
					<xs:element name=\"row\" type=\"rowType\" $occurrence/>
				</xs:sequence>
			</xs:complexType>
		</xs:element>
		<xs:complexType name=\"rowType\">
		<xs:sequence>
	");
	return;
}
// -----------------------------------------------------------------------------
// write content SIARD schema file
function writeSchemaContent($siardhandle, &$table){
	$colcount = 1;

	foreach ($table['_c']['column'] as $column) {
		if (is_array($column)) {
			// multiple columns or only one column
			$type = (array_key_exists('_a', $column)) ? $column['_a']['type'] : $column['type'];
			// write field type
			switch ($type) {
				case "TINYINT":
				case "SMALLINT":
				case "INTEGER":
				case "BIGINT":
					$xstype = 'integer'; break;
				case "FLOAT":
				case "REAL":
				case "DOUBLE":
					$xstype = 'float'; break;
				case "NUMERIC":
				case "DECIMAL":
					$xstype = 'decimal'; break;
				case "DATE":
					$xstype = 'date'; break;
				case "TIME":
					$xstype = 'time'; break;
				case "TIMESTAMP":
					$xstype = 'dateTime'; break;
				case "CHAR":
				case "VARCHAR":
				case "LONGVARCHAR":
				case "CLOB":
					$xstype = 'string'; break;
				case "BIT":
				case "BINARY":
				case "VARBINARY":
				case "LONGVARBINARY":
				case "BLOB":
					$xstype = 'hexBinary'; break;
				case "REF":
					$xstype = 'anyURI'; break;
				case "BOOLEANINT":
				case "BOOLEANCHAR":
					$xstype = 'boolean'; break;
				default:
					$xstype = 'string'; break;
			}
			// write if necessary required constraint
			$required = false;
			if (array_key_exists('_a', $column)) {
				$required = (array_key_exists('required', $column['_a'])) ? $column['_a']['required'] : false;
			}
			else {
				$required = (array_key_exists('required', $column)) ? $column['required'] : false;
			}
			if ($required == 'true') {
				// field required
				fwrite ($siardhandle, "<xs:element name=\"c$colcount\" type=\"xs:$xstype\"/>\n");
			}
			else {
				// field not required
				fwrite ($siardhandle, "<xs:element name=\"c$colcount\" type=\"xs:$xstype\" minOccurs=\"0\"/>\n");
			}
			$colcount++;
		}
	}
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
?>
