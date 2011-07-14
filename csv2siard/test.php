<?
// Report all PHP errors
error_reporting(E_ALL);

include 'c2sfunction.php';
include 'c2stimedate.php';
include 'c2snodbmodel.php';
include 'c2sxml.php';

// -----------------------------------------------------------------------------

$xmlin = file_get_contents('in.xml');
$mod = xml2ary($xmlin);
$xmlout = "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n" . ary2xml($mod);
file_put_contents('out.xml', $xmlout);

?>
