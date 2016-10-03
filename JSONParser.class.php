<?php




class JSONParser
{

    function __construct($filename)
    {
        if ( !$filename){
            die('filename is missing in JSONParser constructor.');
        }

        $this->filename = $filename;

        if ( !is_file($this->filename)){
            die($this->filename . ' is not a file.');
        }


        $this->parsed = json_decode(file_get_contents($this->filename), true);

    }

    function getconf()
    {

        $conf = $this->parsed;

        return array(
            'pages_arr'=> explode(',',  $conf['pages']),
            'exclusions'=>$conf['exclusions'],
            'freq'=>$conf['freq']  ,
            'partner'=>$conf['partner']  ,
            'partnertype'=>$conf['partnertype']
        );

    }


}