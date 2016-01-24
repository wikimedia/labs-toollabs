<?php
$dr = $_SERVER['DOCUMENT_ROOT'];
$orig = $_SERVER['REQUEST_URI'];
if ( preg_match( '/^\/\.([^?]*)$/', $orig, $m ) ) {
	// Hack for error_document not allowing queries
	$qstring = $m[1];
	$orig = '/';
} elseif ( preg_match( '/^(.*)\?(.*)$/', $orig, $m ) ) {
	$qstring = $m[2];
	$orig = $m[1];
}
$uri = $orig;
$uri = preg_replace( '/^\/admin/', '', $uri );
$uri = preg_replace( "/^(\\$_SERVER[SCRIPT_NAME])+\/?/", '/', $uri );

if ( preg_match( '/^\/([^\/]+)(\/.*)?/', $uri, $m ) ) {
	if ( is_dir( "/data/project/{$m[1]}/public_html" ) ) {
		if ( !isset( $m[2] ) ) {
			$to = "$orig/";
			if ( isset( $qstring ) ) {
				$to .= "?$qstring";
			}
			header( "Location: $to" );
			exit( 0 );
		}
		header( 'HTTP/1.0 503 No Webservice' );
		include 'content/503.php';
		exit( 0 );
	}
}

if ( is_file( "{$dr}{$uri}" ) && is_readable( "{$dr}{$uri}" ) ) {
	$mime = 'text/html';
	if ( preg_match( '/\.(.+)$/', $uri, $m ) ) {
		switch( $m[1] ) {
			case 'png':
				$mime = 'image/png';
				break;
			case 'svg':
				$mime = 'image/svg+xml';
				break;
			case 'ico':
				$mime = 'image/x-icon';
				break;
			case 'css':
				$mime = 'text/css';
				break;
			case 'php':
				$mime = 'application/x-httpd-php-source';
				break;
		}
	}
	header( "Content-Type: $mime" );
	header( "X-Sendfile: {$dr}{$uri}" );
	exit( 0 );
}

if ( $uri != '/' ) {
	header( 'HTTP/1.0 404 Not Found' );
	include 'content/404.php';
	exit( 0 );
}

if ( isset( $qstring ) && $qstring !== '' ) {
	$content = $qstring;
	if ( preg_match( '/^[A-Z]/', $content ) === 1 ) {
		header( 'Location: https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/' . urlencode($content) );
		exit( 0 );
	}
} else {
	$content = 'list';
}

if ( preg_match( '/^([a-z0-9]+)(?:=.*)?$/', $content, $values ) !== 1 ) {
	header( 'HTTP/1.0 404 Not Found' );
	include 'content/404.php';
	exit( 0 );
}

$content = $values[1];
if ( !file_exists( "{$dr}/content/{$content}.php" ) ) {
	header( 'HTTP/1.0 404 Not Found' );
	include 'content/404.php';
	exit( 0 );
}

require_once 'htmlpurifier/library/HTMLPurifier.standalone.php';
$config = HTMLPurifier_Config::createDefault();
$config->set( 'URI.Base', 'https://tools.wmflabs.org' );
$config->set( 'URI.MakeAbsolute', true );
$config->set( 'URI.DisableExternalResources', true );
$purifier = new HTMLPurifier( $config );
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
<meta name="viewport" content="initial-scale=1.0, user-scalable=yes, width=device-width" />
<link rel="StyleSheet" href="/style.css" type="text/css" media="screen" />
<!--[if lt IE 7]><style media="screen" type="text/css"> .col1 { width:100%; } </style> <![endif]-->
<script src="/admin/libs/jquery.js"></script>
<script src="/admin/libs/jquery.tablesorter.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
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
);
</script>
</head>
<body>
<div class="colmask leftmenu"><div class="colright">
<div class="col1wrap"><div class="col1">
<?php include "content/{$content}.php"; ?>
</div></div>
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
</div></div>
</body>
</html>
