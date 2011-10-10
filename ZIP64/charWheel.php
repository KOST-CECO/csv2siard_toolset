<?php

dl("php_hash.dll");


	print "  ";
  $pmt = array('-', '\\', '|', '/');
  for( $i = 0; $i <10; $i ++ ){
      sleep(1);
      print $pmt[$i % 4] . chr(8);
      flush();
  }
?>
