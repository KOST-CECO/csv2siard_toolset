<?
include 'c2sfunction.php';

$buf = '';

for ($i=0; $i < 256; $i++) {
 $buf = $buf . chr($i);
}
file_put_contents("ascii.txt", $buf);

?>
