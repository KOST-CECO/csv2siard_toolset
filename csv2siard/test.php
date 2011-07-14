<?
// Report all PHP errors
error_reporting(E_ALL);

function _bool($var){
	if(is_bool($var)){
		return $var;
	} else if($var === NULL || $var === 'NULL' || $var === 'null'){
		return false;
	} else if(is_string($var)){
		$var = trim($var);
		if($var=='false'){ return false;
		} else if($var=='true'){ return true;
		} else if($var=='no'){ return false;
		} else if($var=='yes'){ return true;
		} else if($var=='off'){ return false;
		} else if($var=='on'){ return true;
		} else if($var==''){ return false;
		} else if(ctype_digit($var)){
			if((int) $var)
				return true;
				else
				return false;
		} else { return true; }
	} else if(ctype_digit((string) $var)){
			if((int) $var)
				return true;
				else
				return false;
	} else if(is_array($var)){
		if(count($var))
			return true;
			else
			return false;
	} else if(is_object($var)){
		return true;// No reason to (bool) an object, we assume OK for crazy logic
	} else {
		return true;// Whatever came though must be something,	OK for crazy logic
	}
}

include 'c2sfunction.php';
include 'c2stimedate.php';

// -----------------------------------------------------------------------------
/*
print_r(check4Date("20080701223517"));
print_r(convert2XMLdate("20080701"));
print_r(convert2XMLdate("20080701t223517"));
print_r(convert2XMLdate("20080701T223517"));
print_r(convert2XMLdate("20080701T22:35:17"));
print_r(convert2XMLdate("2008-07-01T22:35:17.02"));
print_r(convert2XMLdate("2008-07-01T22:35:17.03+08:00"));
print_r(convert2XMLdate("2008:08:07 18:11:31"));
print_r(convert2XMLdate("2008-08-07 18:11:31"));
print_r(convert2XMLdate('13-NOV-92'));
print_r(convert2XMLdate('2008-7-1T9:3:37'));
echo "-------------------------------------------------";
print_r(convert2XMLdate("now"));
print_r(convert2XMLdate("10 September 2000"));
print_r(convert2XMLdate("26-Oct 0010 12:00:00 +0100"));
print_r(convert2XMLdate("10/Oct/2000:13:55:36 -0700"));
print_r(convert2XMLdate("10 Oct 2000 13:55:36 -0700"));
print_r(convert2XMLdate("+1 day"));
print_r(convert2XMLdate("+1 week"));
print_r(convert2XMLdate("+1 week 2 days 4 hours 2 seconds"));
print_r(convert2XMLdate("next Thursday"));
print_r(convert2XMLdate("last Monday"));
echo "-------------------------------------------------";
print_r(convert2XMLdate("0"));
print_r(convert2XMLdate("120.50"));

*/
print_r(convert2XMLdate("1988-05-09"));

if(_bool('0')){ echo 'true'; } else { echo 'false'; }

?>
