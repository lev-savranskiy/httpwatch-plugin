<?php

class HTTPWatchRedAril {


     var $path = null;
     var $url = null;

    function __construct($config) {

        $this->path = $config['path'];
        $this->url = $config['url'];
        $this->id = $config['id'];



        $this->Visible = isset($config['visible']) ? $config['visible'] : true;
        $this->Clear = $config['cookieclear'] == 0;
        $this->ClearCache = $config['cacheclear'] == 0;
//        echo $this->path;
//        echo $this->url;
    }
  
    function watch($type = 'XML') {
        //$type = XML | HAR | CSV




       // $browser->Visible = $this->Visible;

        $this->controller = new COM("HttpWatch.Controller");
        if (!method_exists( $this->controller, 'Firefox')) {
            throw new Exception('failed to enable HTTPWatch');
        }
        $plugin = $this->controller->Firefox->New();
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
        $this->controller->Wait($plugin, -1);


       // $filename = "logs/" . $this->id;

        $FileHandle = fopen($this->id, 'w') or die("can't open file");



        //$filename = tempnam('/tmp', 'htt');

        $exporttype = 'Export' . $type;



        if (!method_exists($plugin->Log, $exporttype)) {
            throw new Exception('Failed to find HTTPWatch method ' . $exporttype);
        }


        $plugin->Log->$exporttype($FileHandle);
        $plugin->Stop();
        $plugin->CloseBrowser();



        return file_get_contents($filename);
    }


}

