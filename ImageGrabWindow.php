<?php
require 'JSONParser.class.php';
ini_set ('user_agent', $_SERVER['HTTP_USER_AGENT']);
$partnerid = $_REQUEST['partnerid'];
$jsonparser = new JSONParser('configpages/' . $partnerid . '.json');


$conf = $jsonparser->getconf();
$url = $conf['pages_arr'][0];
//$url = 'http://www.icrossing.com/about-icrossing-interactive-agency';


echo file_get_contents($url);
die();

$file = 'screenshots/' . md5($url). '.png';


    if (!is_file($file)) {

       // echo  '$screen not found';

        $Browser = new COM('InternetExplorer.Application');
        $Browserhandle = $Browser->HWND;
        $Browser->Visible = true;
        $Browser->Fullscreen = true;
        $Browser->Navigate($url);

        while ($Browser->Busy) {
            com_message_pump(20000);
        }
        $img = imagegrabwindow($Browserhandle, 0);
        $Browser->Quit();
        imagepng($img, $file);
    }


       echo "<img src='$file'  width='300' height='220'/>";

