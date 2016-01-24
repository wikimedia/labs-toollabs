<h1>Wikimedia Tool Labs</h1>
<p>Welcome to the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.</p>
<?php
$tool='';
$g = posix_getgrnam( 'tools.' . $_REQUEST['tool'] );
$u = posix_getpwnam( 'tools.' . $_REQUEST['tool'] );
if ( $g && $u ) {
	$tool = $_REQUEST['tool'];
	$maintainers = $g['members'];
	$home = $u['dir'];
}
if ( $tool !== '' ) {
?>
<h2>Tool details</h2>
<table class="tool-info" cols="2" width="95%">
<tr><th class="tool-name">
<?php
	echo htmlspecialchars( $tool );
	if ( array_key_exists( 0, glob( "{$home}/public_html/index.*" ) ) ) {
		echo '<br><span class="mw-editsection"><a href="/', urlencode( $tool ), '/">(Web interface)</a></span>';
	}
?>
</th><td></td></tr>
<tr><th>Description</th>
<td>
<?php
	if ( is_readable( "{$home}/.description" ) ) {
		$desc = file_get_contents( "{$home}/.description", false, null, 0, 2048 );
		print  $purifier->purify( $desc );
	}
?>
</td></tr>
<tr><th>Maintainers</th><td>
<?php
	foreach ( $maintainers as $maint ) {
		$mu = posix_getpwnam( $maint );
		if ( $mu ) {
			$wtu = $mu['gecos'];
?>
	<a href="https://wikitech.wikimedia.org/wiki/User:<?= urlencode( $wtu ) ?>"><?= htmlspecialchars( ucfirst( $wtu ) ) ?></a>
<?php
		} else {
			echo htmlspecialchars( ucfirst( $maint ) ), ' ';
		}
	}
?>
</td></tr></table>
<?php } else { ?>
<p>No such tool?  Trying to guess, are you?</p>
<?php }
