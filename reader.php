<?php
function object_to_array($obj) {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
                $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
                $arr[$key] = $val;
        }
        return $arr;
}

function replace_symbols($text) {
    $danger = array("\x00",  './',  "`" ,    "'", '#');
    return htmlspecialchars(str_replace($danger, "", trim($text)));
}
function json_decode_nice($json){
   // $json = str_replace(array("\n","\r"),"",$json);
    //$json = preg_replace('/([{,])(\s*)([^"]+?)\s*:/','$1"$3":',$json);
    $json=str_replace("'", "\'", $json);
  //  $json=str_replace("script", "[script", $json);
    $json=htmlspecialchars($json, ENT_NOQUOTES);
    return $json;
}


$id = $_REQUEST['id'];





$json = file_get_contents( $id);






$string = "\$object = json_decode('". json_decode_nice($json) ."',  true);";
//echo(replace_symbols($json));

eval($string);


//$test = get_object_vars($object);

echo '<pre>';
print_r($object);
echo '</pre>';
//
//foreach ($test as $t){
//print_r($t );
//print_r('<hr />' );
//}
//print_r($object->log);