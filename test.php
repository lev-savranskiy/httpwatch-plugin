<?php




try {
    $ourFileName = "/httpwatchlogs/testFile.txt";
    $ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
    fclose($ourFileHandle);
    echo fileperms($ourFileName)  ;

} catch (Exception $e) {
    echo '<h3>ERROR: ',  $e->getMessage(), "</h3>";
}
