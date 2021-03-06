<?php 
error_reporting(E_ALL);

/*
PHP: XML to Array and backwards:
Here the XML with PHP solution: XML->Array and Array->XML. Work with it as with usual array.
Sources are here:
http://mysrc.blogspot.com/2007/02/php-xml-to-array-and-backwards.html

Format XML->Array
_c - children
_v - value
_a - attributes

*/
// -----------------------------------------------------------------------------
// XML to Array
function xml2ary(&$string) {
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parse_into_struct($parser, $string, $vals, $index);
	xml_parser_free($parser);

	$mnary=array();
	$ary=&$mnary;
	foreach ($vals as $r) {
		$t=$r['tag'];
		if ($r['type']=='open') {
			if (isset($ary[$t])) {
				if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
				$cv=&$ary[$t][count($ary[$t])-1];
			} else $cv=&$ary[$t];
			if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
			$cv['_c']=array();
			$cv['_c']['_p']=&$ary;
			$ary=&$cv['_c'];

		} elseif ($r['type']=='complete') {
			if (isset($ary[$t])) { // same as open
				if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
				$cv=&$ary[$t][count($ary[$t])-1];
			} else $cv=&$ary[$t];
			if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
			$cv['_v']=(isset($r['value']) ? $r['value'] : '');

		} elseif ($r['type']=='close') {
			$ary=&$ary['_p'];
		}
	}	
	
	_del_p($mnary);
	return $mnary;
}

// _Internal: Remove recursion in result array
function _del_p(&$ary) {
	foreach ($ary as $k=>$v) {
		if ($k==='_p') unset($ary[$k]);
		elseif (is_array($ary[$k])) _del_p($ary[$k]);
	}
}
// -----------------------------------------------------------------------------
// Array to XML
function ary2xml($cary, $d=0, $forcetag='') {
	$res=array();
	foreach ($cary as $tag=>$r) {
		if (isset($r[0])) {
			$res[]=ary2xml($r, $d, $tag);
		} else {
			if ($forcetag) $tag=$forcetag;
			$sp=str_repeat("\t", $d);
			$res[]="$sp<$tag";
			if (isset($r['_a'])) {foreach ($r['_a'] as $at=>$av) $res[]=" $at=\"".xml_encode($av)."\"";}
			$res[]=">".((isset($r['_c'])) ? "\n" : '');
			if (isset($r['_c'])) $res[]=ary2xml($r['_c'], $d+1);
			elseif (isset($r['_v'])) $res[]=xml_encode($r['_v']);
			$res[]=(isset($r['_c']) ? $sp : '')."</$tag>\n";
		}
		
	}
	return implode('', $res);
}
// -----------------------------------------------------------------------------
// Insert element into array
function ins2ary(&$ary, $element, $pos) {
	$ar1=array_slice($ary, 0, $pos); $ar1[]=$element;
	$ary=array_merge($ar1, array_slice($ary, $pos));
}
// -----------------------------------------------------------------------------
// encode string with xml entities: < &lt;  > &gt;  & &amp;  " &quot;  ' &apos;
function xml_encode($buf) {
global $prg_option;
	// Convert special characters to XML entities
	$buf = htmlspecialchars($buf, ENT_QUOTES);

	// use escaped Unicode encodings for non printable character (\u00xx)
	if ($prg_option['UNICODE_EXTENDED']) {
		$out = '';
		for ( $i=0; $i < strlen( $buf ); ){
			$val = $buf[$i++];
			$ordval = ord($val);
			if ($ordval <= 8
						or ($ordval >= 14 and $ordval <= 31)
						// string is allready converted to UTF-8
						// or ($ordval >= 92)
						// or ($ordval >= 127 and $ordval <= 159)
					) {
				$out = $out.'\u'. sprintf("%04x", $ordval);
				// $out = $out.'&#'. sprintf("%04d", $ordval).';';
			}
			else {
				$out = $out.$val;
			}
		}
		return($out);
	} 
	else {
		return($buf);
	}
}

// -----------------------------------------------------------------------------
// encode consecutive white space with Unicode \u0020
function xml_white_space($buf) {
	$buf = str_replace ('  ', ' \u0020', $buf);
	$buf = str_replace ('\u0020 \u0020', '\u0020\u0020\u0020', $buf);
	return($buf);
}
?>
