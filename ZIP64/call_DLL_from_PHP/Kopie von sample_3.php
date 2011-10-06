<?php
if (!dl("php_w32api.dll")) {
  echo "Unable to load php_w32api.dll";
  exit;
}

$api = new win32;

/*
     BOOL GetUserName(
        LPTSTR lpBuffer,    // address of name buffer
        LPDWORD nSize       // address of size of name buffer
     );
    Returns the current thread's username
    "&" passes argument as "refrence" not as "copy"
*/
$api->registerfunction("long GetUserName (string &a, int &b) From advapi32.dll");

/*
    DWORD GetTickCount(VOID)
    Returns the ms the OS is running
*/
$api->registerfunction("long GetTickCount () From Kernel32.dll");

$len = 255;                   // set the length your variable should have
$name = str_repeat("\0", $len); // prepare an empty string
if ($api->GetUserName($name, $len) == 0)
{
    die("failed");
}

if (!($time = $api->GetTickCount()))
{
    die("failed");
}

echo "Username: $name\nSystemtime: $time\n";

?>
