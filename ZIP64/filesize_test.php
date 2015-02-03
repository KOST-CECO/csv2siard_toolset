<?php
// Report all PHP errors
error_reporting(E_ALL);

function getSize($file) {
    $size = -1;
    $fsobj = new COM("Scripting.FileSystemObject");
    $f = $fsobj->GetFile($file);
    $size = $f->Size;
    return $size;
}

function seekSize($file) {
    $size = -1;
	$a = fopen($file, 'r');
	fseek($a, 0, SEEK_END);
	$size = ftell($a);
	fclose($a);
    return $size;
}

function RealFileSize($file)
{
    $fp = fopen($file, "r");
    $pos = 0;
    $size = 1073741824;
    fseek($fp, 0, SEEK_SET);
    while ($size > 1)
    {
        fseek($fp, $size, SEEK_CUR);

        if (fgetc($fp) === false)
        {
            fseek($fp, -$size, SEEK_CUR);
            $size = (int)($size / 2);
        }
        else
        {
            fseek($fp, -1, SEEK_CUR);
            $pos += $size;
        }
    }

    while (fgetc($fp) !== false)  $pos++;
    fclose($fp);
    return $pos;
}

function file_get_size($file) {
    //open file
    $fh = fopen($file, "r");
    //declare some variables
    $size = "0";
    $char = "";
    //set file pointer to 0; I'm a little bit paranoid, you can remove this
    fseek($fh, 0, SEEK_SET);
    //set multiplicator to zero
    $count = 0;
    while (true) {
        //jump 1 MB forward in file
        fseek($fh, 1048576, SEEK_CUR);
        //check if we actually left the file
        if (($char = fgetc($fh)) !== false) {
            //if not, go on
            $count ++;
        } else {
            //else jump back where we were before leaving and exit loop
            fseek($fh, -1048576, SEEK_CUR);
            break;
        }
    }
    //we could make $count jumps, so the file is at least $count * 1.000001 MB large
    //1048577 because we jump 1 MB and fgetc goes 1 B forward too
    $size = bcmul("1048577", $count);
    //now count the last few bytes; they're always less than 1048576 so it's quite fast
    $fine = 0;
    while(false !== ($char = fgetc($fh))) {
        $fine ++;
    }
    //and add them
    $size = bcadd($size, $fine);
    fclose($fh);
    return $size;
}
// MAIN ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
if (!@is_file($argv[1])) { 
	echo "no file specifide, usage: filesize_test.exe <file>\n"; exit(-1);
}

$fn = str_replace('\\', '/', $argv[1]);
$fn = str_replace('//', '/', $fn);

echo "\nphp filesize()\n";
$fs = filesize($fn);
echo "$fn: $fs\n";

echo "\ngetSize()\n";
$fs = getSize($fn);
echo "$fn: $fs\n";

echo "\nseekSize()\n";
$fs = seekSize($fn);
echo "$fn: $fs\n";

echo "\nfile_get_size()\n";
$fs = file_get_size($fn);
echo "$fn: $fs\n";

echo "\nRealFileSize()\n";
$fs = RealFileSize($fn);
echo "$fn: $fs\n";

exit(0);
?>
