<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);


require 'XMLParserlog.class.php';
require 'HTTPWatch.RedArilIE.php';
require 'JSONParser.class.php';


if (!isset($_GET['partnerid']) || !isset($_GET['cc'])) {

    die('Parameters [partnerid] and [cc] not found in URL');

}


$jsonparser = new JSONParser('configpages/' . $_GET['partnerid'] . '.json');


$conf = $jsonparser->getconf();
$url = $conf['pages_arr'][$_GET['cc']];

//print_r($conf);
//die();

$filename_log = 'httpwatch-' . urlencode($url) . '-' . @date('Y-m-d-H-i-s') . '.xml';

//$filename_log = 'logs/temp.xml';
$dowatch = true;
$dosyslog = true;
$useprod = true;
$visible = true;

//     LOGSDIR is IN DISK ROOT!!!

define(LOGSDIR , '/httpwatchlogs/');

if (!is_dir(LOGSDIR)) {
    mkdir(LOGSDIR) or die('cant create LOGSDIR ' . LOGSDIR);
}



// uncomment this to use dev mode
/////////////dev code  start
//$filename_log = 'logs/1.xml';
//$dowatch = false;
//$dosyslog = false;
//$useprod = false;
//$visible = true;
/////////// dev code end


$config = array(
    'id' => $filename_log,
    'partnertype' => $conf['partnertype'],
    'partner' => $conf['partner'],
    'url' => $url,
    'exclusions' => $conf['exclusions'],
    'visible' => $visible,
    'cc' => $_GET['cc'],
    'useprod' => $useprod,
    'dosyslog' => $dosyslog,
    'dowatch' => $dowatch

);

echo "HTTPWatchRedAril started";
$logger = new HTTPWatchRedAril($config);
echo ".";
$parser = new XMLParserLog($config);
echo ".";
//die();




if ($dowatch) {



    $data = $logger->watch();
    echo ".\n\r";

    // save data to xml
//    fwrite($handle = fopen($filename_log, "w"), $data);
//    fclose($handle);
    echo "<p>log created from HTTPWatch file "  . $filename_log .  " </p><p style=\"color:green;\">HTTPWatch USED! </p>";
} else {
    echo "<p>log read from STATIC file"  . $filename_log .  " </p><p style=\"color:red;\">No HTTPWatch USED! </p>";
}
if (!$dosyslog) {
    echo "<p style=\"color:red;\">No SYSLOG USED! </p>";
}

$parser->parselog($filename_log);
echo "log parsed OK\n\r";
echo "exiting...\n\r";



//echo "<hr />log file created <a href= 'readerxml.php?id={$filename_log}&url=$url'>$filename_log</a>";
//print_r($data);
//die('zz');
