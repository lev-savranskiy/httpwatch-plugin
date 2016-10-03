<html>
<head>
    <title>Data leakage logger</title>
</head>
<body>



<img src="style/Logo-180-64.png" alt="" />
<?php
$ini_array = parse_ini_file("_version.ini");

echo '<h3>Data leakage logger. Version ' .$ini_array['version']  . '</h3>';
//echo '<h4><a href="log.txt" target="_blank">See local log</a> </h4>';
//echo '<h4><a href="logger.php?partnerid=p1&cc=0" target="_blank">Run test </a></h4>';
echo '<div>started at ' . @date('Y-m-d H:i:s') . '</div>';
//echo '<div>updated at <span id="last_updated"></span></div>';
//echo '<a href="test.php">test</a> ';

$pages = 0;

foreach (glob("configpages/*.json") as $filename) {
    // partners configs
    $pages++;
    $partnerid =  str_replace(array('configpages/' , '.json') , '' ,$filename);
    echo '<iframe src="page.php?partnerid=' .$partnerid .'" frameborder="1"  width="32%" height="200" ></iframe>';
}


if (!$pages){
    echo 'No config pages found.';
}

?>

<script type="text/javascript">

  function  refreshlog(){
      document.getElementById('logframe').src = 'log.txt?ts=' + new Date().getTime();

  }
</script>

<h4>

    <a href="#" onclick="refreshlog(); return false;" target="_blank">Refresh local log</a>
    &bull;
    <a href="http://depot.redaril.com:8080/browse/DMPPROJECT-2051" target="_blank">Report bug</a>


</h4>

<div style="font-size:10px;  background-color: #ddd; ">
    <iframe src="log.txt" frameborder="1"  width="99%" height="600" id="logframe"  ></iframe>

</div>


<script type="text/javascript">
    refreshlog();
</script>
</body>
</html>
