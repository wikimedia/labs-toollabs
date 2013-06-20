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
<HTML>
  <HEAD>
    <TITLE>Tool Labs</TITLE>
    <LINK rel="StyleSheet" href="/style.css" type="text/css" media=screen>
    <META charset="utf-8">
    <META name="title" content="Tool Labs">
    <META name="description" content="This is the Tool Labs project for community-developed tools assisting the Wikimedia projects.">
    <META name="author" content="Wikimedia Foundation">
    <META name="copyright" content="Creative Commons Attribution-Share Alike 3.0">
    <META name="publisher" content="Wikimedia Foundation">
    <META name="language" content="Many">
    <META name="robots" content="index, follow">
  </HEAD>
  <BODY>
    <TABLE border="0" cellpadding="1em"><TR>
    <TD valign="top">
      <A HREF="/">
        <IMG SRC="https://wikitech.wikimedia.org/w/images/c/cf/Labslogo_thumb.png" ALT="Wikitech and Wikimedia Labs">
      </A>
      <DIV CLASS="sidebar">
        <HR>
        <A HREF="/?list">Tools</A>
        <A HREF="/?status">Status</A>
        <A HREF="/?Privacy">Privacy policy</A>
        <HR>
        Maintainers:
        <A HREF="/?Help">Help</A>
        <A HREF="/?Rules">Rules</A>
        <HR>
      </DIV>
    </TD><TD class="content"><?
      include "content/$content.php";
    ?></TD>
  </BODY>
</HTML>

