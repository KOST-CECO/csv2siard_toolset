<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
//This is just another simple xml parser 

class Xml
{
    var $tag;
    var $value;
    var $attributes;
    var $next;
}

function xml2array($xml_string)
{
    $Parser = xml_parser_create();
    xml_parser_set_option($Parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($Parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($Parser, $xml_string, $Xml_Values);
    xml_parser_free($Parser);
    $XmlClass = array();
    $LastObj = array();
    $NowObj = &$XmlClass;

    foreach($Xml_Values as $Xml_Key => $Xml_Value)
    {
        $Index = count($NowObj);
        if($Xml_Value["type"] == "complete")
        {
            $NowObj[$Index] = new Xml;
            $NowObj[$Index]->tag = $Xml_Value["tag"];
            $NowObj[$Index]->value = $Xml_Value["value"];
            $NowObj[$Index]->attributes = $Xml_Value["attributes"];
        }
        elseif($Xml_Value["type"] == "open")
        {
            $NowObj[$Index] = new Xml;
            $NowObj[$Index]->tag = $Xml_Value["tag"];
            $NowObj[$Index]->value = $Xml_Value["value"];
            $NowObj[$Index]->attributes = $Xml_Value["attributes"];
            $NowObj[$Index]->next = array();
            $LastObj[count($LastObj)] = &$NowObj;
            $NowObj = &$NowObj[$Index]->next;
        }
        elseif($Xml_Value["type"] == "close")
        {
            $NowObj = &$LastObj[count($LastObj) - 1];
            unset($LastObj[count($LastObj) - 1]);
        }
        
    }

    return $XmlClass;
}

$String = "
<parser>
    <parseur_name>MyParser</parseur_name>
    <category>
        <name>Name 1</name>
        <note>A note 1</note>
    </category>
</parser>
";
$Xml = xml2array($String);

print_r($Xml);
?>

