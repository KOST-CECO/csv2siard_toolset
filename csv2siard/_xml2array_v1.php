<?
// Report all PHP errors
error_reporting(E_ALL);

function array2xml($xmlary){ 
  $o=''; 
  foreach($xmlary as $tag ){ 
    if($tag['tag'] == 'textarea' && !isset($tag['value'])){ 
      //fake a value so it won't self close 
      $tag['value']=''; 
    } 
    //tab space: 
    $t = ''; 
    for($i=1; $i < $tag['level'];$i++){ 
      $t.="\t"; 
    } 
    switch($tag['type']){ 
      case 'complete': 
      case 'open': 
        $o.=$t.'<'.$tag['tag']; 
        if(isset($tag['attributes'])){ 
          foreach($tag['attributes'] as $attr=>$aval){ 
            $o.=' '.$attr.'="'.$aval.'"'; 
          }//foreach 
        }//attributes 
        if($tag['type'] == 'complete'){ 
          if(!isset($tag['value'])){ 
            $o .= ' />'."\n"; 
          } else { 
            $o .= '>'."\n".$t.$tag['value']."\n".$t.'</'.$tag['tag'].'>'."\n"; 
          } 
        }else{ 
          $o .= '>'."\n"; 
        } 
        break; 
      case 'close': 
        $o .= $t.'</'.$tag['tag'].'>'."\n"; 
        break; 
      case 'cdata': 
        $o .= $t.$tag['value']."\n"; 
        break; 
    }//switch 
  }//foreach 
  print_r($o);
}
?>

