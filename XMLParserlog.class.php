<?php


date_default_timezone_set('Europe/London');

require_once 'XMLParser.class.php';
require_once 'Syslog.class.php';


class XMLParserLog extends XMLParser
{


    function __construct($config = null)
    {


        $ini_array = parse_ini_file("_version.ini");

        $this->version = $ini_array['version'] or die(" ERROR: Cant read ini version.ini");
        $this->props_count = $ini_array['props_count'] or die(" ERROR: Cant read ini version.ini");

             // count of props array http://depot.redaril.com:8096/display/dmpproject/Client+Side+Emulator

        $this->tab = "\t";
        $this->tmpdir = LOGSDIR;
        $this->br = "<br />";
        $this->nl = "\n\r";
        $this->path = $this->tmpdir .$config['id'];


        $this->dosyslog = $config['dosyslog'];
        $this->dowatch = $config['dowatch'];
        $this->useprod = $config['useprod'];

        // QA
        $this->syslogserver = 'tcp://10.50.150.130';

        if ($this->dowatch && $this->useprod) {

            //PROD
            $this->syslogserver = 'tcp://10.50.150.77';
        }


        $this->timeinit = time();
        $this->timeinitGMT = date('Y-m-d H:i:s');


        $this->page = $config['url'];
        $this->partner = $config['partner'];
        $this->partnertype = $config['partnertype'];
        $this->page_parts = parse_url($this->page);
        $this->page_host = $this->page_parts['host'];


        $this->processPrefix = 'dataleakage';
        $this->processDelimeter = 'ZZ';

        //echo $this->processPrefix . $this->processDelimeter. $this->version;


        $this->informpath = 'http://wap7.ru/folio/httpwatch/informer.php?data=';
        //$this->whitelist = '(static.ak.fbcdn.net|youtube.com|redaril.com|inc.com|facebook.com|myspacecdn.com|myspace.com|bing.com|atdmt.com|abmr.net)';
        $this->whitelist = $config['exclusions'];

        $this->subject = "Error in data leakage file " . $this->path;
        //  $this->body = "Error in data leakage file <a href='http://downloads.jancoo.spb.ru/httpwatch/" . $this->path ."'>" . $this->path . "</a>";
        $this->body = "Error in XMLParserLog. File  uploaded to http://downloads.wap7.ru/httpwatch/" . $this->timeinitGMT . $this->path . $this->nl;

        $syslog = new Syslog();
        $syslog->SetFacility(16);
        $syslog->SetSeverity(6);
        $syslog->SetHostname('WINDOWSDESKTOP');
        //$syslog->SetFqdn('myserver.mydomain.net');
        //$syslog->SetIpFrom('192.168.0.1');
        $syslog->SetProcess($this->processPrefix . $this->processDelimeter . $this->version);

        $syslog->SetServer($this->syslogserver);
        $syslog->SetPort('514');
        $this->syslog = $syslog;
        //        echo $this->path;
        //        echo $this->url;
    }

    /**
     * @function shows formatted code
     * @param $code
     * @return void
     */
    function  code($code)
    {
        echo ('<pre>');
        print_r($code);
        echo ('</pre>');
    }

    /***
     * @function writes to log
     * @param $data
     * @return
     */
    function write_to_log($data)
    {

        $log = 'log.txt';
        $back = @file_get_contents($log);
        $lines_count = count(file($log));


        //echo $lines_count;


        $file = fopen($log, "w");


        flock($file, LOCK_EX);

        if ($lines_count < 400) {
            // ADD TO old records
            fputs($file, $data . $this->nl . $back);
        } else {
            // DELETE old records
            fputs($file, $data);
        }


        fflush($file);
        flock($file, LOCK_UN);
        fclose($file);

        echo "LOG: " . strlen($data) . " bytes ";





        //return;

        if ($this->dosyslog) {
            $this->write_to_syslog($data);
        }else{
            $this->code($data);
        }


    }




    /***
     * @function writes to native php syslog
     * @param $data
     * @return
     */
    function write_to_syslog($data)
    {


//        openlog("dataleakage", LOG_PID, LOG_USER);
//        syslog(LOG_WARNING, $data);
//        closelog();

        $exploded = explode($this->nl, $data);
        echo "RECORDS : " . count($exploded);



        foreach ($exploded as $k=>$e) {
           // $this->syslog->SetContent($k . ' ' . $e);
            $this->syslog->SetContent($e);
            $this->code($this->syslog->Send());
        }


    }


    function destroy_source()
    {

        // delete XML if not static used

        if ($this->dowatch) {
            unlink($this->path) or die('cant delete file');
            echo "<div>log file deleted successfully</div><hr />";
        }

    }

    /***
     * @function call page to do  mail alert
     * @param $line
     * @return void
     */
    function informer($line)
    {

        file_get_contents($this->informpath . $this->timeinitGMT . $this->path . '&line=' . $line);

        //  if ($_SERVER['HTTP_HOST'] == 'wap7.loc') {


        $file = $this->path;
        $remote_file = '/public_html/httpwatch/' . $this->timeinitGMT .$this->path;
        $conn_id = ftp_connect('srv701.infobox.ru');

        // turn passive mode on
        // ftp_pasv($conn_id, true);


        ftp_login($conn_id, 's604', 'x1bjVZzEMu');

        if (ftp_put($conn_id, $remote_file, $file, FTP_BINARY)) {
            echo "$file upload OK" . $this->br;
        } else {
            echo "$file upload error" . $this->br;
        }

        ftp_close($conn_id);

        $this->destroy_source();


        echo ("<p style=\"color:red;\">" . $this->body . $this->br . " File " . get_called_class() . " Line " . $line . "</p>");


    }

    /***
     * @function find url by regexp in white list
     * @param $needle
     * @param $regexp
     * @return bool
     */

    function findbyregexp($needle, $regexp)
    {

        return (bool)preg_match_all($regexp, $needle, $matches) === true;
    }

    /***
     * @function log parser itself
     * @return void
     */

    function parselog()
    {


        $result = "";

        $output = $this->parse($this->path);


        //
        //                 $this->code($output);
        //                 die(0);

        // $i = 0;

        if (!is_array($output[0]['child'])) {

            echo "parselog error for " . $this->path;
            $this->informer(__LINE__);
            die();

        }

        foreach ($output[0]['child'] as $k => $v) {


            $url = $v['attrs']['URL'];


            $url_parts = parse_url($url);
            $url_host = $url_parts['host'];

            // check whitelist
            // todo white list from config
            if ($this->findbyregexp($url_host, $this->whitelist)) {
                continue;
            }

            //  $this->code($v['child']);
            $found = 0;
            foreach ($v['child'] as $k1 => $v1) {


                $d = array();

                if ($v1['name'] == 'REQUEST' || $v1['name'] == 'RESPONSE' || $v1['name'] == 'TIME') {
                    //  echo $v1['name'] . $this->nl;
                    //     $i++;
                    /*
                   Data Leakage log structure is as follows:

                   Data Leakage log structure is as follows:

                   Created Date Time (format YYYY-MM-DD HH24:MI:SS)
                   Client Type (Advertiser or Publisher)
                   Client Name - based on sampling html page
                   GUID [unique id of request] , md5(ts + url)
                   Time Stamp (in seconds)
                   Page/Tag Domain (from sampling html page)
                   Page/Tag URL (from sampling html page)
                   Request URL (REQUEST)
                   Request Domain (REQUEST)
                   Referer URL (REQUEST)
                   Referer Domain (REQUEST)
                   Content Type (RESPONSE)
                   Cookie Name (REQUEST)
                   Cookie Expiration (REQUEST)
                   Total request/response time


                    */


                    //0
                    //timeinitGMT
                    $d[0] = $this->timeinitGMT;


                    //1
                    //partnertype
                    $d[1] = $this->partnertype;

                    //2
                    //'partner'
                    $d[2] = $this->partner;

                    //3
                    //'guid'
                    $d[3] = md5($this->timeinit . $this->page);

                    //4
                    //'ts'
                    $d[4] = $this->timeinit;

                    //5
                    //'page_host'
                    $d[5] = $this->page_host;


                    //6
                    //'page'
                    $d[6] = $this->page;


                    //7
                    // 'url'
                    $d[7] = $url;

                    //8
                    //'url_host'
                    $d[8] = $url_host;


                    $d[9] =  'null';
                    $d[10] =  'null';


                    if ($v1['name'] == 'TIME') {

                        $found++;
                        $time = $v1['content'];

                    }

                    if ($v1['name'] == 'REQUEST') {
                        $found++;

                        $headers = $v1['child']['2'];
                        //$this->code($headers);

                        $c = count($headers['child']);


                        /*
                         *
                        Request URL( REQUEST);
                         Request domain( REQUEST);
                         Referer URL( REQUEST);
                         Referer domain( REQUEST);
                         */

                        $this_referer = null;
                        $this_referer_host = null;

                        while ($c--) {
                            //$this->code($headers['child'][$c]);
                            $name = $headers['child'][$c]['attrs']['NAME'];
                            $content = $headers['child'][$c]['content'];
                            if ($name == 'Referer') {
                                $this_referer = $content;
                                $url_parts = parse_url($this_referer);
                                $this_referer_host = $url_parts['host'];
                                //echo $name . ' is ' .$content . '<br />';
                            }

                        }

                        //9
                        //'this_referer'




                    }



                    if ($v1['name'] == 'RESPONSE') {

                        $found++;
                        $cookies_arr = $v1['child']['1'];
                        $headers = $v1['child']['2'];


                        $c = count($cookies_arr['child']);

                        $hcount = count($headers['child']);


                        //'Content-Type'
                        $d[11] = 'null';
                        while ($hcount--) {
                            //11
                            if ($headers['child'][$hcount]['attrs']['NAME'] == 'Content-Type') {
                                $d[11] = $headers['child'][$hcount]['content'];
                            }

                        }
                        // die();


                        // sen all cookies if cookies array found
                        if ($c) {
                            while ($c--) {
                                // $this->code($headers['child'][$c]);

                                $d[9] = $this_referer ? $this_referer : 'null';

                                     //10
                                 $d[10] = $this_referer_host ? $this_referer_host : 'null';
                                //12
                                //'cookie_name'
                                $d[12] = $cookies_arr['child'][$c]['attrs']['NAME']? $cookies_arr['child'][$c]['attrs']['NAME'] : 'null';

                                //13
                                //'cookie_expires'
                                $d[13] = isset($cookies_arr['child'][$c]['attrs']['EXPIRES']) ? $cookies_arr['child'][$c]['attrs']['EXPIRES'] : 'null';

                                //14
                                //'time'
                                $d[14] = $time;

                                ksort($d);

//
//                                $this->code($d);
//                                $this->code('<hr/>');
//                                echo count($d);

                                if (count($d) == $this->props_count) {

                                    $result .= implode($d, $this->tab) . $this->nl;
                                    unset($d[12]);
                                    unset($d[13]);

                                } else {
                                    $this->informer(__LINE__);
                                    die();
                                }


                            }

                        } else {
                            $d[9] = $this_referer ? $this_referer : 'null';

                                 //10
                                 //'this_referer_host'
                                 $d[10] = $this_referer_host ? $this_referer_host : 'null';


                            // add 'null' cookies  vars for no cookies case
                            //12
                            $d[12] = 'null';
                            //13
                            $d[13] = 'null';
                            //14
                            $d[14] = $time;

                            ksort($d);


//                            $this->code($d);
//                            $this->code('<hr/>');
//                            echo count($d);





                            if (count($d) == $this->props_count) {

                                $result .= implode($d, $this->tab) . $this->nl;

                            } else {
                                $this->informer(__LINE__);
                                die();
                            }

                        }

                    }

                }
            }
        }

       $this->destroy_source();

        $this->write_to_log($result);


    }


}