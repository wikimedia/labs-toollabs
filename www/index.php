<?php
$dr = $_SERVER['DOCUMENT_ROOT'];
$orig = $_SERVER['REQUEST_URI'];
if ( preg_match( '@^/\.([^?]*)$@', $orig, $m ) ) {
	// Hack for error_document not allowing queries
	$qstring = $m[1];
	$orig = '/';
} elseif ( preg_match( '/^(.*)\?(.*)$/', $orig, $m ) ) {
	$qstring = $m[2];
	$orig = $m[1];
}
$uri = $orig;
$uri = preg_replace( '@^/admin@', '', $uri );
$uri = preg_replace( "@^({$_SERVER['SCRIPT_NAME']})+/?@", '/', $uri );

if ( $uri !== '/' ) {
	// Are we handling a request for a static resource?
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
				case 'txt':
					$mime = 'text/plain';
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

	// Check to see if we are handling a bare tool name or acting as the nginx
	// 503 error handler page by checking to see if a tool matches the first
	// part of the URI path.
	if ( preg_match( '@^/(?P<tool>[^/]+)(?P<path>/.*)?@', $uri, $m ) ) {
		if ( is_dir( "/data/project/{$m['tool']}/public_html" ) ) {
			if ( !isset( $m['path'] ) ) {
				// Redirect bare /<toolname> links to /<toolname>/
				$to = "{$orig}/";
				if ( isset( $qstring ) ) {
					$to .= "?{$qstring}";
				}
				header( 'HTTP/1.0 301 Moved Permanently' );
				header( "Location: {$to}" );
				exit( 0 );
			}
			// This endpoint is called as an error hander page by nginx, so
			// *DO NOT* return an actual 503 status code. If you do nginx will
			// think that the error handler itself is broken.
			include 'content/503.php';
			exit( 0 );
		}
	}

	header( 'HTTP/1.0 404 Not Found' );
	include 'content/404.php';
	exit( 0 );
}

// Default action is to serve the list of all tools
$content = 'list';
if ( isset( $qstring ) && $qstring !== '' ) {
	$content = $qstring;
	if ( preg_match( '/^[A-Z]/', $content ) === 1 ) {
		header( 'Location: https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/' . urlencode($content) );
		exit( 0 );
	}
}

if ( preg_match( '/^([a-z0-9]+)(?:=.*)?$/', $content, $values ) !== 1 ) {
	header( 'HTTP/1.0 404 Not Found' );
	include 'content/404.php';
	exit( 0 );
}

// Files that should not be exposed from the content directory
$contentBlacklist = array( 'common.inc', 'htmlpurifier.inc' );
$content = $values[1];
if ( in_array( $content, $contentBlacklist ) ||
	!file_exists( "{$dr}/content/{$content}.php" )
) {
	header( 'HTTP/1.0 404 Not Found' );
	include 'content/404.php';
	exit( 0 );
}

// Make testing error page output easier
$errorPages = array(
	'403' => 'Forbidden',
	'404' => 'Not Found',
	'500' => 'Internal Server Error',
	'503' => 'Service Unavailable',
);
if ( isset( $errorPages[$content] ) ) {
	// Do not set proper HTTP status codes. These pages will be used as error
	// handlers by nginx/apache and thus are expected to return 200 status to
	// the calling server. The upstream server will handle putting the right
	// status code on the page sent to the browser.
	include "content/{$content}.php";
	exit( 0 );
}
?><!DOCTYPE html>
<html>
<head>
<title>Tool Labs</title>
<meta charset="utf-8">
<meta name="title" content="Tool Labs">
<meta name="description" content="This is the Tool Labs project for community-developed tools assisting the Wikimedia projects.">
<meta name="author" content="Wikimedia Foundation">
<meta name="copyright" content="Creative Commons Attribution-Share Alike 3.0">
<meta name="publisher" content="Wikimedia Foundation">
<meta name="language" content="Many">
<meta name="robots" content="index, follow">
<meta name="viewport" content="initial-scale=1.0, user-scalable=yes, width=device-width">
<link rel="StyleSheet" href="/style.css" type="text/css" media="screen">
<!--[if lt IE 7]><style media="screen" type="text/css"> .col1 { width:100%; } </style> <![endif]-->
</head>
<body>
<div class="colmask leftmenu"><div class="colright">
<div class="col1wrap"><div class="col1">
<?php include "content/{$content}.php"; ?>
</div></div>
<div class="col2">
<div id="logo"><a href="/"><img src="/Tool_Labs_logo_thumb.png" width="122" height="138" alt="Wikitech and Wikimedia Labs"></a></div>
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
});
</script>
</body>
</html>
