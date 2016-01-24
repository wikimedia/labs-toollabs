<!DOCTYPE html>
<html lang=en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>500 Internal Server Error</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=yes, width=device-width">
</head>
<body>
<?php
require_once dirname( __FILE__ ) . '/common.inc.php';
$uri = $_SERVER['HTTP_X_ORIGINAL_URI'];
list( $tool, $maintainers ) = getToolInfo( $uri );
?>
<h1>Internal error</h1>
<p>The URI you have requested, <a href="<?= htmlspecialchars( $uri ) ?>"><code><?= htmlspecialchars( $uri ) ?></code></a>, appears to be non-functional at this time.</p>
<?php if ( $tool !== false ) { ?>
<h2>If you have reached this page from somewhere else...</h2>
<p>This URI is part of the <a href="/?tool=<?= urlencode( $tool ) ?>"><code><?= htmlspecialchars( $tool ) ?></code></a> tool, maintained by <?php printMaintainers( $maintainers ) ?>.</p>
<p>Perhaps its magical script elves are temporarily ill, or the link you've followed doesn't actually lead somewhere useful?<p>
<p>If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above) about the error and how you ended up here.</p>
<h2>If you maintain this tool</h2>
<p>The error might be caused by incorrect permission, or by an error in the script or CGI that was meant to execute here.  You may wish to check your logs or <a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/Help#Logs">common causes for errors</a> in the help documentation.</p>
<?php } else { ?>
<p>Perhaps the webserver has temporarily lost its mind, or the link you've followed doesn't actually lead somewhere useful?</p>
<p>If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
<?php } ?>
</body>
</html>
