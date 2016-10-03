<?php

class HTTPWatchRedAril {


     var $path = null;
     var $url = null;

    function __construct($config) {

        $this->path = $config['id'];
        $this->url = $config['url'];
        $this->tmpdir = LOGSDIR;
        $this->windir    = realpath($this->tmpdir)     ;


      //  die($this->windir . DIRECTORY_SEPARATOR . $this->path);



        $this->Visible = isset($config['visible']) ? $config['visible'] : true;
        $this->Clear = $config['cookieclear'] == 0;
        $this->ClearCache = $config['cacheclear'] == 0;
//        echo $this->path;
//        echo $this->url;
    }
  
    function watch($type = 'XML') {
        //$type = XML | HAR | CSV



        $browser = new COM("InternetExplorer.Application");
        if (!method_exists($browser, 'Navigate')) {
            throw new Exception('didn\'t create IE obj');
        }

        // hide IE
        $browser->Visible = $this->Visible;

        $controller = new COM("HttpWatch.Controller");
        if (!method_exists($controller, 'IE')) {
            throw new Exception('failed to enable HTTPWatch');
        }
        $plugin = $controller->IE->Attach($browser);
        $plugin->OpenWindow(false);

        //TODO Clear timing
       // if ($this->Clear){
            $plugin->Clear();

            $plugin->ClearCache();
            $plugin->ClearAllCookies();

     //   }

        // start
        $plugin->Record();

        // browse
        //$plugin->GotoUrl('http://' . $url);
        $plugin->GotoUrl($this->url);
        $controller->Wait($plugin, -1);




       // $filename = tempnam('/webserver/tmp', 'htt');

           //  $this->windir
       $FileHandle = fopen( $this->tmpdir . $this->path, 'w') or die("can't open file");
       fclose($FileHandle);

        $exporttype = 'Export' . $type;




        if (!method_exists($plugin->Log, $exporttype)) {
            throw new Exception('Failed to find HTTPWatch method ' . $exporttype);
        }


        try {
            $plugin->Log->$exporttype($this->windir . DIRECTORY_SEPARATOR . $this->path);

        } catch (Exception $e) {
            echo '<h3>ERROR: ',  $e->getMessage(), "</h3>";
        }

        $plugin->Stop();
        $plugin->CloseBrowser();


        return file_get_contents($this->tmpdir . $this->path);
    }


}

