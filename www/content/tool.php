<h1>Wikimedia Tool Labs</h1>
<p>Welcome to the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.</p>
<?php
$tool='';
if ( $_REQUEST['tool'] ) {
	$g = posix_getgrnam( 'tools.' . $_REQUEST['tool'] );
	$u = posix_getpwnam( 'tools.' . $_REQUEST['tool'] );
	if ( $g && $u ) {
		$tool = $_REQUEST['tool'];
		$maintainers = $g['members'];
		$home = $u['dir'];
	}
}
if ( $tool !== '' ) {
	$info = null;

	$ini = parse_ini_file( '/data/project/admin/replica.my.cnf' );
	$db = new mysqli( 'tools.labsdb', $ini['user'], $ini['password'], 'toollabs_p' );
	if ( $db->connect_errno === 0 ) {
		$stmt = $db->prepare( 'SELECT toolinfo FROM tools WHERE name = ?' );
		if ( $stmt !== false ) {
			$stmt->bind_param( 's', $tool );
			if ( $stmt->execute() !== false && $stmt->bind_result( $json ) ) {
				if ( $stmt->fetch() && $json ) {
					$info = json_decode( $json, true );
				}
			}
			$stmt->close();
		}
		$db->close();
	}

	if ( $info === null ) {
		// No toolinfo data found, so make some up
		$info = array(
			'name' => $tool,
			'title' => $tool,
		);
		if ( glob( "{$home}/public_html/index.*" ) ) {
			// If the tool has an index file in their public_html then we
			// assume that the tool has a web UI. Lazier than polling the
			// proxy server.
			$info['url'] = '/' . urlencode( $tool ) . '/';
		}
		if ( is_readable( "{$home}/.description" ) ) {
			$info['description'] = file_get_contents(
				"{$home}/.description", false, null, 0, 2048 );
		}
	}

	if ( isset( $info['name'] ) ) {
		// Info only covers a single tool. Just to make things easier later on
		// let's wrap this in a containing array.
		$info = array( $info );
	}
?>
<h2>Tool account: <?= htmlspecialchars( $tool ) ?></h2>
<table class="tool-info" cols="2" width="95%">
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
</td></tr>
<tr><th>Tool<?= count($info) > 1 ? 's' : '' ?></th><td>
<?php
	$first = ' first';
	foreach ( $info as $toolinfo ) {
		echo '<div class="subtool', $first, '"><span class="subtool-name">';
		if ( isset( $toolinfo['url'] ) ) {
			echo "<a href=\"" . htmlspecialchars( $toolinfo['url'] ) . "\">";
		}
		echo htmlspecialchars( $toolinfo['title'] );
		if ( isset( $toolinfo['url'] ) ) {
			echo '</a>';
		}
		echo '</span><span class="subtool-desc">';
		if ( isset( $toolinfo['description'] ) ) {
			echo  $purifier->purify( $toolinfo['description'] );
			if ( isset( $toolinfo['author'] ) ) {
				echo '<br><i>Author(s): ', $purifier->purify( $toolinfo['author'] ), '</i>';
			}
			if ( isset( $toolinfo['repository'] ) ) {
				echo '<br><a href="', htmlspecialchars( $toolinfo['repository'] ), '">Source</a>';
			}
		}
		echo '</span></div>';
		$first = '';
	}
?>
</td></tr></table>
<?php } else { ?>
<p>No such tool?  Trying to guess, are you?</p>
<?php }
