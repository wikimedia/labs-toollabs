<h1>Wikimedia Tool Labs</h1>
<p>This is the web server for the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.</p>

<h2>Hosted tools</h2>
<table id="thebigtable" class="tablesorter">
<thead>
<tr>
<th>Tool</th>
<th>Maintainers</th>
<th>Notes</th>
</tr>
</thead>
<tbody>
<?php
require_once dirname( __FILE__ ) . '/htmlpurifier.inc.php';
function describe( $t ) {
	if ( array_key_exists( 'description', $t ) ) {
		global $purifier;
		echo  $purifier->purify( $t['description'] );
		if ( array_key_exists( 'author', $t ) ) {
			echo '<br><i>Author(s): ', $purifier->purify( $t['author'] ), '</i>';
		}
		if ( array_key_exists( 'repository', $t ) ) {
			echo '<br><a href="', htmlspecialchars( $t['repository'] ), '">Source</a>';
		}
	}
}

// Query list of active web services.
$active_proxy = file_get_contents( '/etc/active-proxy' );
$active_proxies_json = file_get_contents( 'http://' . $active_proxy . ':8081/list' );
$tooldyn = array();
if ( $active_proxies_json === false ) {
	error_log( 'Cannot retrieve list of active proxies from http://' . $active_proxy . ':8081/list' );
} else {
	$active_proxies = json_decode( $active_proxies_json, true );
	foreach ( $active_proxies as $key => $value ) {
		if ( array_key_exists( 'status', $value) &&
			$value['status'] == 'active' ) {
			$tooldyn[$key] = 1;
		}
	}
}

$ini = parse_ini_file( '/data/project/admin/replica.my.cnf' );
$db = new mysqli( 'tools.labsdb', $ini['user'], $ini['password'], 'toollabs_p' );

$res = $db->query( 'SELECT * FROM users' );
while ( $row = $res->fetch_assoc() ) {
	$users[$row['name']] = $row;
}
$res->free();

$res = $db->query( 'SELECT * FROM tools ORDER BY name ASC' );
while ( $row = $res->fetch_assoc() ) {
	$tool = $row['name'];
	if ( $row['toolinfo'] != '' &&
		!is_null( json_decode( $row['toolinfo'], true ) )
	) {
		$json = json_decode( $row['toolinfo'], true );
	} else {
		$json = array( 'description' => $row['description'] );
	}

	if ( array_key_exists( 0, $json ) && !array_key_exists( 1, $json ) ) {
		$json = $json[0];
	}
?>
<tr class="tool" id="toollist-<?= htmlspecialchars( $tool ) ?>">
<td class="tool-name">
<?php
	if ( array_key_exists( 'url', $json ) ) {
		echo '<a class="tool-web" href="', htmlspecialchars( $json['url'] ), '">', htmlspecialchars( $tool ), '</a>';
	} elseif ( array_key_exists( $tool, $tooldyn ) &&
		!array_key_exists( 0, $json )
	) {
		echo '<a class="tool-web" href="/', urlencode( $tool ), '/">', htmlspecialchars( $tool ), '</a>';
	} else {
		echo htmlspecialchars( $tool );
	}
?>
<span class="mw-editsection">[<a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaServiceGroup&amp;action=managemembers&amp;projectname=tools&amp;servicegroupname=tools.<?= urlencode( $tool )?>">manage</a> maintainers]</span>
</td>
<td class="tool-maintainers">
<?php
	foreach ( explode (' ', $row['maintainers'] ) as $maint ) {
		if ( array_key_exists( $maint, $users ) ) {
			$maint = $users[$maint]['wikitech'];
?>
	<a href="https://wikitech.wikimedia.org/wiki/User:<?= urlencode( strtr( $maint, ' ', '_' ) ) ?>"><?= htmlspecialchars( ucfirst($maint) ) ?></a>
<?php
		} // end if
	} // end foreach
?>
</td>
<td class="tool-desc">
<?php
	if ( array_key_exists( 1, $json ) ) {
		$first = ' first';
		foreach ( $json as $sub ) {
			echo '<div class="subtool', $first, '"><span class="subtool-name">';
			if ( array_key_exists( 'url', $sub ) ) {
				echo "<a href=\"" . htmlspecialchars( $sub['url'] ) . "\">";
			}
			echo htmlspecialchars( $sub['title'] );
			if ( array_key_exists( 'url', $sub ) ) {
				echo '</a>';
			}
			echo '</span><span class="subtool-desc">';
			describe( $sub );
			echo '</span></div>';
			$first = '';
		}
	} else {
		describe( $json );
	}
?>
</td>
</tr>
<?php
} // end while
$res->free();
?>
</tbody>
</table>
