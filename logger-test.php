<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);



$filename_log = 'logs/httpwatch-' . urlencode($url) . '-' . @date('Y-m-d-H-i-s') . '.xml';
$dowatch = true;
$dosyslog = true;
$useprod = true;
$visible = true;


// uncomment this to use dev mode
/////////////dev code  start
//$filename_log = 'logs/1.xml';
//$dowatch = false;
//$dosyslog = false;
$useprod = false;
//$visible = true;
/////////// dev code end

require_once 'Syslog.class.php';


$syslog = new Syslog();


$syslog->simple_test();


//echo "<hr />log file created <a href= 'readerxml.php?id={$filename_log}&url=$url'>$filename_log</a>";
//print_r($data);
//die('zz');
