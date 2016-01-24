<!DOCTYPE html>
<html lang=en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>404 Not Found</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=yes, width=device-width">
<link rel="StyleSheet" href="/style.css" type="text/css" media="screen">
</head>
<body>
<div class="colmask leftmenu"><div class="colright">
<div class="col1wrap"><div class="col1">
<?php
require_once dirname( __FILE__ ) . '/common.inc.php';
$uri = $_SERVER['HTTP_X_ORIGINAL_URI'];
list( $tool, $maintainers ) = getToolInfo( $uri );
?>
<h1>Four hundred and four!</h1>
<p>The URI you have requested, <code><?= htmlspecialchars( $uri ) ?></code>, doesn't seem to actually exist.</p>
<?php if ( $tool !== false ) { ?>
<h2>If you have reached this page from somewhere else...</h2>
<p>This URI is managed by the <a href="/?tool=<?= urlencode( $tool ) ?>"><code><?= htmlspecialchars( $tool ) ?></code></a> tool, maintained by <?php printMaintainers( $maintainers ) ?>.</p>
<p>Perhaps its files are on vacation, or the link you've followed doesn't actually lead somewhere useful?</p>
<p>You might want to looks at the <a href="/?list">list of tools</a> to find what you were looking for, or one of the links on the sidebar to the left. If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above) about the error and how you ended up here.</p>
<?php } else { ?>
<p>Perhaps the webserver has temporarily lost its mind, or the link you've followed doesn't actually lead somewhere useful?</p>
<p>You might want to looks at the <a href="/?list">list of tools</a> to find what you were looking for, or one of the links on the sidebar to the left. If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
<?php } ?>
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
</body>
</html>
