<?
// Report all PHP errors
error_reporting(E_ALL);

include 'c2sfunction.php';
include 'c2stimedate.php';

// -----------------------------------------------------------------------------
/*
print_r(convert2XMLdate("20080701223517"));
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
print_r(convert2XMLdate("2001-09-09 01:46:40"));

//9. September 2001   01:46:40
echo mktime(01, 46, 40, 9, 9, 2001)."\n";
echo unixTime(1, 46, 40, 9, 9, 2001)."\n";
//1. Januar 1970 00:00:00
echo unixTime(0, 0, 0, 1, 1, 1970)."\n";
//1. Januar 1601 00:00:00
echo unixTime(0, 0, 0, 1, 1, 1601)."\n";

?>
