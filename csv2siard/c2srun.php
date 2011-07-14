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
	eval('$dbm[\'DATABASE_STRUCTURE\'] = '.$result);
	
	xslt_free($xh);
	return;
}

// -----------------------------------------------------------------------------
// create SIARD file header and content in TMP directory
function creatSIARDStructur(&$dbm) {
global $prg_option, $prgdir;
$folderstructur ="
    ├───header
    │       metadata.xsd
    │       metadata.xsl
    │       metadata.xml
    └───content
        └───schema0
            ├───table0
            │       table0.xsd
            │       table0.xml
            └───table1
                    table1.xsd
                    table1.xml
";
	// Create temporary SIARD folder
	$prg_option['SIARD_DIR'] = $prg_option['TMPDIR'].'/'.basename($prg_option['SIARD_FILE']);
	rrmdir("$prg_option[SIARD_DIR]");
	
	// Create SIARD header
	mkdirPHP4("$prg_option[SIARD_DIR]/header", 0777, true);
	// for convenience digestType: "(|(MD5|SHA-1).*)" => "(MD5.+|SHA-1.+)*"
	copy ("$prgdir/_metadata.xsd", "$prg_option[SIARD_DIR]/header/metadata.xsd");
	copy ("$prgdir/_metadata.xsl", "$prg_option[SIARD_DIR]/header/metadata.xsl");

	// Create SIARD content
	mkdirPHP4("$prg_option[SIARD_DIR]/content/schema0", 0777, true);
	$siardstructur = array();
	foreach ($dbm['DATABASE_STRUCTURE'] as $db) {
		$tbc = 0;
		foreach (array_keys($db) as $table) {
			mkdirPHP4("$prg_option[SIARD_DIR]/content/schema0/table$tbc", 0777, true);
			$siardstructur['schema0']["table$tbc"] = $table;
			$tbc++;
		}
	}
	$dbm['SIARD_STRUCTURE'] = $siardstructur;
	return;
}

?>
