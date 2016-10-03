

<html>
<head>
    <script type="text/javascript">

        var conf = <? require   'configpages/' . $_GET['partnerid'] . '.json'; ?>;
        var partner = conf.partner;
        var partnerid = '<? echo $_GET['partnerid'] ?>';
        var pages_arr = conf.pages.split(',');
        var pages_count = pages_arr.length;
        var freq = conf.freq;
        var partnertype = conf.partnertype;

    </script>
</head>

<body>

<div style="font-size: 11px; height: 30%">
    <h4><a href="logger.php?partnerid=<? echo $_GET['partnerid'] ?>&cc=0" target="_blank">Run manual test</a></h4>
    <div>Partner: <span id="partner"></span></div>
    <div>Type: <span id="partnertype"></span></div>
    <div>Pages to watch: <span id="pages_count"></span></div>
    <div>Times loaded: <span id="total"></span></div>
    <div>Frequency: <span id="freq"></span> s</div>
    <div>Started at <?= @date('Y-m-d H:i:s') ?></div>
    <div>Last update: <span id="last_updated"></span></div>
    <div>Last request: <span id="last_requested"></span></div>
</div>

<div style="float:left; margin:  5px  0  0 5px; width: 95%; height: 70% ">
<h4>Current call</h4>
<iframe  src="" id="logger-frame" style="" width="100%" height ="70%"></iframe>
</div>
<script type="text/javascript">
    function DateString(d) {
        function pad(n) {
            return n < 10 ? '0' + n : n
        }

        return d.getUTCFullYear() + '-'
                + pad(d.getUTCMonth() + 1) + '-'
                + pad(d.getUTCDate()) + ' '
              //  + pad(d.getUTCHours() - d.getTimezoneOffset() / 60) + ':'
             // WE ARE IN LONDON!!!
                + pad(d.getUTCHours()) + ':'
                + pad(d.getUTCMinutes()) + ':'
                + pad(d.getUTCSeconds())
    }


    document.getElementById('pages_count').innerHTML = pages_count;
    document.getElementById('partner').innerHTML = partner;
    document.getElementById('freq').innerHTML = freq / 1000;
    document.getElementById('partnertype').innerHTML = partnertype;


    var c = 0;
    var cc = 0;
    //console.log(window);
    function timedCount() {

       c++;

        setTimeout("timedCount()", freq);
        //   console.log( top.window.document.getElementById('last_updated'));
        // top.window.document.getElementById('last_updated').innerHTML= DateString(new Date());
        document.getElementById('last_updated').innerHTML = DateString(new Date());
        document.getElementById('total').innerHTML = c;


        if (cc > pages_count - 1) {
            cc = 0;
        }

        var loggerpath = 'logger.php?partnerid=' + partnerid + '&cc=' + cc + '&ts=' + new Date().getTime();


//        var img = new Image();
//        img.src = loggerpath;
//        delete img;
         document.getElementById("logger-frame").setAttribute("src", loggerpath);

        document.getElementById('last_requested').innerHTML = loggerpath;

        cc++;
    }

    timedCount();
</script>
</body>
</html>