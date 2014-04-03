<?
    $why = '';
    $content = $_SERVER['QUERY_STRING'];
    if(preg_match("/^[A-Z]/", $content) === 1) {
      header("Location: https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/" . urlencode($content));
      exit;
    }
    if($content == '') {
      $why = 'no content';
      $content = 'list';
    }
    if(preg_match("/^([a-z0-9]+)(=(.*))?$/", $content, $values) !== 1) {
      $why = "not valid: $content";
      $content = "404";
    }
    $content = $values[1];
    $param = $values[3];
    if(!file_exists("$_SERVER[DOCUMENT_ROOT]/content/$content.php")) {
      $why = "unreadable: $content";
      $content = "404";
    }

    require_once 'htmlpurifier/library/HTMLPurifier.standalone.php';
    $config = HTMLPurifier_Config::createDefault();
    $config->set('URI.Base', 'http://tools.wmflabs.org');
    $config->set('URI.MakeAbsolute', true);
    $config->set('URI.DisableExternalResources', true);
    $purifier = new HTMLPurifier($config);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Tool Labs</title>
    <meta charset="utf-8" />
    <meta name="title" content="Tool Labs" />
    <meta name="description" content="This is the Tool Labs project for community-developed tools assisting the Wikimedia projects." />
    <meta name="author" content="Wikimedia Foundation" />
    <meta name="copyright" content="Creative Commons Attribution-Share Alike 3.0" />
    <meta name="publisher" content="Wikimedia Foundation" />
    <meta name="language" content="Many" />
    <meta name="robots" content="index, follow" />
    <link rel="StyleSheet" href="style.css" type="text/css" media="screen" />
    <!--[if lt IE 7]>
    <style media="screen" type="text/css">
    .col1 {
      width:100%;
    }
    </style>
    <![endif]-->
    <script src="/libs/jquery.js"></script>
    <script src="/libs/jquery.tablesorter.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() 
          { 
            $(".tablesorter").tablesorter({
                sortList: [[0,0]],
                // initialize zebra striping of the table
                widgets: ["zebra"],
                // change the default striping class names
                // updated in v2.1 to use widgetOptions.zebra = ["even", "odd"]
                // widgetZebra: { css: [ "normal-row", "alt-row" ] } still works
                widgetOptions : {
                  zebra : [ "normal-row", "alt-row" ]
                }
              });
          } 
      );   
    </script>
  </head>
  <body>
    <div class="colmask leftmenu">
      <div class="colright">
        <div class="col1wrap">
          <div class="col1">
    <?
      include "content/$content.php";
    ?>
          </div>
        </div>
        <div class="col2">
          <div id="logo"><a href="/"><img src="/Tool_Labs_logo_thumb.png" width="122" height="138" alt="Wikitech and Wikimedia Labs" /></a></div>

          <ul>
            <li><a href="/?list">Tools</a></li>
            <li><a href="/?status">Status</a></li>
            <li><a href="/?Privacy">Privacy policy</a></li>
          </ul>
          <em>Maintainers:</em>
          <ul>
            <li><a href="/?Help">Help</a></li>
            <li><a href="/?Rules">Rules</a></li>
          </ul>
        </div>
      </div>
    </div>
  </body>
</html>

