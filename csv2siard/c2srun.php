<?
// Report all PHP errors
error_reporting(E_ALL);

// -----------------------------------------------------------------------------
// read XML database model into multi-dimensional array
function loadDatabaseModell(&$dbm) {
global $prg_option, $prgdir, $model2array;

	$xh = xslt_create();
	
	$arguments = array(
		'/_xml' => file_get_contents($prg_option['DB_SCHEMA']),
		'/_xsl' => file_get_contents("$prgdir/$model2array")
	);
	$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
	eval('$dbm = '.$result);
	
	xslt_free($xh);
	return;
}

// -----------------------------------------------------------------------------
// create SIARD file and header in TMP directory
function creatSIARDHeader($dbm) {
global $prg_option, $prgdir;

	$prg_option['SIARD_DIR'] = $prg_option['TMPDIR'].'/'.basename($prg_option['SIARD_FILE']);
	//remove existing directroy and files
	//rmdir("N:/KOST/Projekte/Transferprojekt/4_CSV_2_SIARD/temp/test.siard/header");
	rrmdir("$prg_option[SIARD_DIR]");
	mkdirPHP4("$prg_option[SIARD_DIR]/header", 0777, true);
	
	// for convenience digestType: "(|(MD5|SHA-1).*)" => "(MD5.+|SHA-1.+)*"
	copy ("$prgdir/_metadata.xsd", "$prg_option[SIARD_DIR]/header/metadata.xsd");
	copy ("$prgdir/_metadata.xsl", "$prg_option[SIARD_DIR]/header/metadata.xsl");

	return;
}





// -----------------------------------------------------------------------------
// A simple way to recursively delete a directory that is not empty
function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandirPHP4($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
			} 
		}
		reset($objects);
		rmdir($dir); 
	}
}

?>
