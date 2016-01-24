<!DOCTYPE html>
<html lang=en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>403 Forbidden</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=yes, width=device-width">
</head>
<body>
<?php
require_once dirname( __FILE__ ) . '/common.inc.php';
$uri = $_SERVER['HTTP_X_ORIGINAL_URI'];
list( $tool, $maintainers ) = getToolInfo( $uri );
?>
<h1>Forbidden</h1>
<p>The URI you have requested, <a href="<?= $uri ?>"><code><?= $uri ?></code></a>, might exist but the server has been instructed not to let you reach it.</p>
<?php if ( $tool !== false ) { ?>
<h2>If you have reached this page from somewhere else...</h2>
<p>This URI is managed by the <a href="/?tool=<?= urlencode( $tool ) ?>"><code><?= htmlentities( $tool )?></code></a> tool, maintained by <?php printMaintainers( $maintainers ) ?>.</p>
<p>Perhaps this content can only be accessed from the secret underground lair of the maintainers, or the link you've followed doesn't actually lead somewhere useful?</p>
<p>If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above) about the error and how you ended up here.</p>
<h2>If you maintain this tool</h2>
<p>The error might be caused by incorrect permissions, or by the absence of an index file (this webserver does not list directory contents by default).</p>
<?php } else { ?>
<p>Perhaps the webserver has temporarily lost its mind, or the link you've followed doesn't actually lead somewhere useful?</p>
<p>If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
<?php } ?>
</body>
</html>
