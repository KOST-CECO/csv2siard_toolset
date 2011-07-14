<?
// Report all PHP errors
error_reporting(E_ALL);

include 'c2sfunction.php';
include 'c2stimedate.php';
include 'c2snodbmodel.php';

// -----------------------------------------------------------------------------
echo guessDataType("12345")."\n";
echo guessDataType("123.45")."\n";
echo guessDataType("Tel 118")."\n";
echo guessDataType("1956-05-09 12:23")."\n";
