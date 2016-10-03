<html>
<head>
    <title>Data leakage logger</title>
</head>
<body>
<img src="style/Logo-180-64.png" alt=""/>


<?php
require 'JSONParser.class.php';

exec('ipconfig', $catch);

$IPv4 = null;
foreach ($catch as $line) {

    $lines = explode(':', $line);
    if ((strpos($lines[0], 'IPv4')) !== false) {
        //    print_r($lines[0]);
        //    print_r($lines[1]);
        //    print_r('<hr />');
        $IPv4 = trim($lines[1]);
    }
}


if (!$IPv4) {
    echo 'No $IPv4 found';
}


$ini_array = parse_ini_file("_version.ini");

echo '<h3>Data leakage logger at ' . $IPv4 . '. Build ' . $ini_array['version'] . '</h3>';
//echo '<h4><a href="log.txt" target="_blank">See local log</a> </h4>';
//echo '<h4><a href="logger.php?partnerid=p1&cc=0" target="_blank">Run test </a></h4>';

//echo '<div>updated at <span id="last_updated"></span></div>';
//echo '<a href="test.php">test</a> ';




if (!is_file('configpages/' . $IPv4 . '.json')) {
    echo 'No config page found for address ' . $IPv4;


} else {
    $jsonparser = new JSONParser('configpages/' . $IPv4 . '.json');


    $conf = $jsonparser->getconf();
    $url = $conf['pages_arr'][0];

    $screen = 'screenshots/' . md5($url). '.png';


    echo '<iframe src="page.php?partnerid=' . $IPv4 . '" frameborder="1"  width="99%" height="50%" ></iframe>';
   // echo '<iframe src="ImageGrabWindow.php?partnerid=' . $IPv4 . '" frameborder="1"  width="70%" height="240" ></iframe>';
}



?>

<script type="text/javascript">

    function refreshlog() {
        document.getElementById('logframe').src = 'log.txt?ts=' + new Date().getTime();

    }
</script>

<h4>

    <a href="#" onclick="refreshlog(); return false;" target="_blank">Refresh local log</a>
    &bull;

    <a href="../dataleakage-checkout.php" target="_blank">Run SVN update</a>
    &bull;
    <a href="http://depot.redaril.com:8080/browse/DMPPROJECT-2051" target="_blank">Report bug</a>


</h4>

<div style="font-size:10px;  background-color: #ddd; ">
    <iframe src="" frameborder="1" width="99%" height="600" id="logframe"></iframe>

</div>


<script type="text/javascript">
    refreshlog();
</script>
</body>
</html>
