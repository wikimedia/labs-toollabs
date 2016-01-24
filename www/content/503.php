<!DOCTYPE html>
<html lang=en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>503 Service Unavailable</title>
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
<h1>No webservice</h1>
<p>The URI you have requested, <a href="<?= htmlspecialchars( $uri ) ?>"><code><?= htmlspecialchars( $uri ) ?></code></a>, is not currently serviced.</p>
<?php if ( $tool !== false ) { ?>
<h2>If you have reached this page from somewhere else...</h2>
<p>This URI is part of the <a href="/?tool=<?= urlencode( $tool ) ?>"><code><?= htmlspecialchars( $tool )?></code></a> tool, maintained by <?php printMaintainers( $maintainers ) ?>.</p>
<p>That tool might not have a web interface, or it may currently be disabled.</p>
<p>If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above) about the error and how you ended up here.</p>
<h2>If you maintain this tool</h2>
<p>You have not enabled a web service for your tool, or it has stopped working because of a fatal error. You may wish to check your logs or <a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/Help#Logs">common causes for errors</a> in the help documentation.</p>
<?php
} else {
?>
<h2>If you have reached this page from somewhere else...</h2>
<p>This URI is not currently part of any tool.</p>
<p>If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
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
