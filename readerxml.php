<?php

require_once 'XMLParser.class.php';
require_once 'XMLParserlog.class.php';

//print_r($_SERVER);
//$file = file_get_contents($id);

$object = new XMLParserLog($_REQUEST);
$object->parselog();

