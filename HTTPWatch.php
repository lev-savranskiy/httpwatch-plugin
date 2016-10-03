<?php

class HTTPWatch {

  /**
   * The HTTPWatch COM controller
   */
  var $controller;

  /**
   * HTTPWatch plugin object
   */
  var $watch;

  var $entries = array();
  var $summary = array();

  /**
   * Whether the raw request/response body should be returned
   * in data methods such as getEntries()
   * Default is TRUE, meaning skip these raw streams
   */
  var $skipStreams = true;

  /**
   * the API config is generated by dumpapi.php()
   * so better not touch manually, because it will be overwritten.
   *
   * The values explained:
   * + 1 means scalar
   *   e.g. URL of an entry
   * + 2 means value will become unix timestamp
   * + string means an object of class named after the string
   *   e.g. The Response member of the Entry object is an
   *        object of class Response
   * + array means a list of values where each value is of class
   *   named after the single array value
   *   e.g. array('Warning') means the member is a list of
   *        Warning objects
   */
  var $api;
  var $paidproperties;

  static $apipath = '';

  var $hasRestrictedURLs = 'dunno';

  function __construct($brow = 'ie', $config = array('empty_cache' => true)) {



    $apipath = HTTPWatch::$apipath;
    if (!$apipath) {
      $apipath = dirname(__FILE__) . '/HTTPWatchAPI.php';
    }

    // populate API
    if (file_exists($apipath)) {
      require $apipath;
      $this->api = $api;
      $this->paidproperties = $paidproperties;
    }


    $this->controller = new COM("HttpWatch.Controller");
    if(!method_exists($this->controller, 'IE')) {
      throw new Exception('failed to start HTTPWatch');
    }

    $plug =& $this->watch;
    if ($brow === 'ie') {
      // start IE
      $browser = new COM("InternetExplorer.Application");
      if(!method_exists($browser, 'Navigate')) {
        throw new Exception('didn\'t create IE obj');
      }
      $browser->Visible = true;
      $plug = $this->controller->IE->Attach($browser);
    } else {
      $plug = $this->controller->Firefox->New();
    }

    // no filtering
    $plug->Log->EnableFilter(false);

    // clear log and cache
    $plug->Clear();
    if ($config['empty_cache']) {
      $plug->ClearCache();
    }

  }

  function go($url) {
    $this->watch->Record();
    $this->watch->GotoUrl($url);
    $this->controller->Wait($this->watch, -1);
    $this->watch->Stop();
  }


  function done() {
    $this->watch->CloseBrowser();
  }

  function getEntries() {
    if ($this->entries) {
      return $this->entries;
    }
    $this->entries = array();
    $plug =& $this->watch;
    $entlog = $plug->Log->Entries;

    $this->entries = $this->populate($entlog, 'Entry', true);

    return $this->entries;
  }

  function getSummary() {
    if ($this->summary) {
      return $this->summary;
    }
    $this->summary = $this->populate(
      $this->watch->Log->Entries->Summary,
      "Summary"
    );
    return $this->summary;
  }

  /**
   * Helper. Recursively goes through the properties
   * of a VARIANT object $o of class $classname.
   * $islist should be TRUE if $o is a collection/list
   * and provides `Count` and Item()
   *
   * @return array of properties
   */
  function populate($o, $classname, $islist = false) {

    if (!is_array($this->api)) {
      return false;
    }
    $count = $islist ? $o->Count : 1;
    $populateme = array();

    for ($i = 0; $i < $count; $i++) {
      $item = $islist ? $o->Item($i) : $o;

      $populateme[$i] = array();
      foreach($this->api[$classname] as $prop => $value) {
        // skip restricted properties in the basic (free) HTTPWatch edition
        if ($classname === 'Entry') {
          if ($item->isRestrictedURL && $this->paidproperties[$prop]) {
            continue;
          }
        } else if ($this->hasRestrictions() && $this->paidproperties[$prop]) {
          continue;
        }

        if (is_array($value)) {
          $populateme[$i][$prop] = $this->populate($item->$prop, $value[0], true);
        } else if (is_string($value)) {
          $populateme[$i][$prop] = $this->populate($item->$prop, $value, false);
        } else if ($value === 1) {

          $val = $item->$prop;

          if (gettype($val) === "object") {
            $type = variant_get_type($val);
            if ($type === 8209) {
              $val = $this->getStream($val);
            }
            if ($type === VT_DATE ) {
              $val = variant_date_to_timestamp($val);
            }
          }
          $populateme[$i][$prop] = $val;
        }
      }
    }

    return $islist ? $populateme : $populateme[0];

  }


  function getStream($val) {
    if ($this->skipStreams) {
      return "[BYTESTREAM]";
    }
    $chr = '';
    foreach ($val as $byte) {
      $chr .= chr($byte);
    }
    return $chr;
  }

  function hasRestrictions() {
    if (is_bool($this->hasRestrictedURLs)) {
      return $this->hasRestrictedURLs;
    }
    $entries = $this->watch->Log->Entries;
    $max = $entries->Count;
    for($i = 0; $i < $max; $i++) {
      $e = $entries->Item($i);
      if ($e->IsRestrictedURL) {
        $this->hasRestrictedURLs = true;
        return true;
      }
    }
    $this->hasRestrictedURLs = false;
    return false;
  }

  /**
   * If no filename is passed returns the HAR JSON
   */
  function toHAR($filename = false) {
    if ($filename) {
      $this->watch->Log->ExportHAR($filename);
      return file_exists($filename);
    }

    $filename = tempnam('/tmp', 'watchmenowimgoindown');
    $this->watch->Log->ExportHAR($filename);
    return file_get_contents($filename);
  }

}

?>