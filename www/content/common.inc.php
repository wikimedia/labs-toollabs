<?php

/**
 * Get information about a tool based on the given URI.
 *
 * @param string $uri
 * @return array Tool name and list of maintainers. Tool name will be false if
 *   URI does not correspond to a known tool
 */
function getToolInfo( $uri ) {
	if ( preg_match( '@^/([^/]+)/@', $uri, $part ) ) {
		$gr = posix_getgrnam( 'tools.' . $part[1] );
		if ( $gr ) {
			return array( $part[1], $gr['members'] );
		}
	}
	return array( false, array() );
}

/**
 * Print a nicely formatted list of maintainers.
 *
 * @param array $maintainers List of usernames
 */
function printMaintainers( $maintainers ) {
	$numMaintainers = count( $maintainers );
	foreach ( $maintainers as $num => $maint ) {
		$mu = posix_getpwnam( $maint );
		if ( $mu ) {
			$wtu = $mu['gecos'];
			echo '<a href="https://wikitech.wikimedia.org/wiki/User:';
			echo urlencode( str_replace( ' ', '_', $wtu ) ), '">', htmlspecialchars( ucfirst($wtu) );
			echo '</a>';
		} else {
			echo htmlspecialchars( ucfirst( $maint ) );
		}
		if ( $num < $numMaintainers - 1 ) {
			if ( $num == $numMaintainers - 2 ) {
				if ( $num === 0 ) {
					echo ' and ';
				} else {
					echo ', and ';
				}
			} else {
				echo ', ';
			}
		}
	}
}
