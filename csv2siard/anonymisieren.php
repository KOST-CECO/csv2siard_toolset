<?php
// process a single CSV line and write a <row> into SIARD XML file
function writeSIARDColumn($siardhandle, $buffer, $columcount, $rowcount, &$table){

				case "CHAR":
				case "VARCHAR":
				case "LONGVARCHAR":
				case "CLOB":
					// Anonymisierung von Testdaten
					if ((preg_match("/[A-Za-z]/", $buf) > 0) and (strlen($buf) > 2)){
						$cryptbuf = '';
						foreach(split(' ', $buf) as $splitbuf) {
							$sl = strlen($splitbuf);
							$cb = substr(crypt($splitbuf, 'gvtg'), 2);
							while (strlen($cb) < $sl) {
								$cb = $cb.$cb;
							}
							$cryptbuf = $cryptbuf.' '.substr($cb, 0, $sl);
						}
						$buf = trim($cryptbuf);
					}
					elseif ( $fname = (array_key_exists('_a', $column)) ? $column['_a']['name'] : $column['name'] and ($fname == 'plz' or $fname == 'gemeinde_bfs')) {
						$plz = 0;
						foreach(str_split($buf) as $z){
							$plz += $z;
						}
						$buf = 9800 + $plz;
					}
					elseif ( $fname = (array_key_exists('_a', $column)) ? $column['_a']['name'] : $column['name'] and ($fname == 'kanton')) {
						$buf = 'XY';
					}
					$buf = xml_encode($buf);
					break;
}
?>
