<?php 
function mc_parse_xml($filename)
{
    $xml = file_get_contents($filename);
    $p = xml_parser_create();
    xml_parse_into_struct($p, $xml, $values, $index);
    xml_parser_free($p);
    for ($i=0;$i<count($values);$i++) {
        if (isset($values[$i]['attributes'])) {
            $parent = $values[$i]['tag'];
            $keys = array_keys($values[$i]['attributes']);
            for ($z=0;$z<count($keys);$z++)
            {
                $content[$parent][$i][$keys[$z]] = $values[$i]['attributes'][$keys[$z]];
                if (isset($content[$parent][$i]['VALUE'])) $content[$parent][$i]['VALUE'] = $values[$i]['value'];
            }
        }
    }
    foreach ($content as $key => $values) {
        $content[$key] = array_values($content[$key]); 
    }
    if (is_array($content)) return $content;
    else return false;
}

print_r(mc_parse_xml("sample.xml"));
?>
