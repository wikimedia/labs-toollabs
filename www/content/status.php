<?php
function humantime( $secs ) {
	if ( $secs < 120 ) {
		return "{$secs}s";
	}
	$secs = (int) $secs;
	$mins = (int) ( $secs / 60 );
	$secs = $secs % 60;
	if ( $mins < 60 ) {
		return "{$mins}m{$secs}s";
	}
	$hours = (int) ( $mins / 60 );
	$mins = $mins % 60;
	return "{$hours}h{$mins}m";
}

function humanmem( $megs ) {
	if ( $megs > 1024)  {
		$megs = (int) ( $megs / 102.4 );
		$megs /= 10.0;
		return "{$megs}G";
	}
	$megs = (int) ( $megs * 10 );
	$megs /= 10.0;
	return "{$megs}M";
}

function mmem( $str ) {
	$suffix = substr( $str, -1 );
	if ( $suffix === 'M' ) {
		return 0 + $str;
	}
	if ( $suffix === 'G' ) {
		return 1024 * $str;
	}
	return -1;
}

function array_get( $arr, $key, $default = '' ) {
	return array_key_exists( $key, $arr ) ? $arr[$key] : $default;
}

$raw = shell_exec( "PATH=/bin:/usr/bin qstat -xml -j '*'|sed -e 's/JATASK:[^>]*/jatask/g'|iconv -f utf8 -t utf8 -c" );
$xml = simplexml_load_string( $raw );
unset( $raw );

foreach ( $xml->djob_info->element as $xjob ) {
	$job = array(
		'num'    => (string) $xjob->JB_job_number,
		'name'   => (string) $xjob->JB_job_name,
		'submit' => (string) $xjob->JB_submission_time,
		'owner'  => (string) $xjob->JB_owner,
		'tool'   => preg_replace( '/^tools\.(.*)$/', '$1', (string) $xjob->JB_owner ),
	);
	if ( $xjob->JB_hard_queue_list ) {
		$job['queue'] = (string) $xjob->JB_hard_queue_list->destin_ident_list->QR_name;
	} else {
		$job['queue'] = '(manual)';
	}
	foreach ( $xjob->JB_hard_resource_list->qstat_l_requests as $lreq ) {
		if ( $lreq->CE_name === 'h_vmem' ) {
			$job['h_vmem'] = (int) $lreq->CE_doubleval;
		}
	}
	if ( $xjob->JB_ja_tasks->jatask &&
		$xjob->JB_ja_tasks->jatask->JAT_scaled_usage_list
	) {
		foreach ( $xjob->JB_ja_tasks->jatask->JAT_scaled_usage_list->scaled as $usage ) {
			$job[(string) $usage->UA_name] = (int) $usage->UA_value;
		}
	}
	if ( $xjob->JB_ja_tasks->ulong_sublist &&
		$xjob->JB_ja_tasks->ulong_sublist->JAT_scaled_usage_list
	) {
		foreach ( $xjob->JB_ja_tasks->ulong_sublist->JAT_scaled_usage_list->scaled as $usage ) {
			$job[(string) $usage->UA_name] = (int) $usage->UA_value;
		}
	}
	$jobs[$job['num']] = $job;
}
unset( $xml );

$raw = shell_exec( "PATH=/bin:/usr/bin qhost -xml -j -F h_vmem|iconv -f utf8 -t utf8 -c" );
$xml = simplexml_load_string( $raw );
unset( $raw );

foreach ( $xml->host as $xhost ) {
	$hname = preg_replace( '/^([^\.]*)\..*/', '$1', (string) $xhost->attributes()->name );
	if ( $hname !== 'global' ) {
		$host = array(
			'name'   => $hname,
			'h_vmem' => mmem( (string) $xhost->resourcevalue ) * 1024 * 1024,
			'jobs'   => array(),
		);
		foreach ( $xhost->job as $xjob ) {
			$jid = (int) $xjob->attributes()->name;
			$job = array();
			foreach ( $xjob->jobvalue as $jv ) {
				$job[(string) $jv->attributes()->name] = (string) $jv;
			}
			$rawState = array_get( $job, 'job_state' );
			$jobs[$jid]['state'] = $rawState;
			if ( stristr( $rawState, 'R' ) !== false ) {
				$jobs[$jid]['state'] = 'Running';
			}
			if ( stristr( $rawState, 's' ) !== false ) {
				$jobs[$jid]['state'] = 'Suspended';
			}
			if ( stristr( $rawState, 'd' ) !== false ) {
				$jobs[$jid]['state'] = 'Deleting';
			}
			$jobs[$jid]['host'] = $hname;
			$jobs[$jid]['priority'] = array_get( $job, 'priority' );
			$host['jobs'][] = $jid;
		}
		foreach ( $xhost->hostvalue as $hv ) {
			$host[(string) $hv->attributes()->name] = (string) $hv;
		}
		$host['mem'] = mmem( array_get( $host, 'mem_used', '0M' ) ) / mmem( array_get( $host, 'mem_total', '1M' ) );
		$hosts[$hname] = $host;
	}
}
?>
<h1>Wikimedia Tool Labs</h1>
<p>This is the web server for the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.</p>

<h2>Grid Status</h2>
<?php
ksort( $hosts );
ksort( $jobs );

foreach ( $hosts as $host => $h ) {
	$hvmem = array_get( $h, 'h_vmem', 0 );
	foreach ( $h['jobs'] as $jn ) {
		$hvmem -=  array_get( $jobs[$jn], 'h_vmem', 0 );
	}
	$hvmem = (int) ( $hvmem / 1024 / 1024 );
	if ( $hvmem < 0 ) {
		$hvmem = 0;
	}
?>
<div class="hostline">
<span class="hostname"><?= htmlspecialchars( $host ) ?></span>
<b>Load:</b> <?= (int) ( array_get( $h, 'load_avg', 0 ) * 1000 ) / ( array_get( $h, 'num_proc', 1 ) * 10 ) ?>%
<b>Memory:</b> <?= (int) ( array_get( $h, 'mem', 0 ) * 1000 ) / 10 ?>%
<?php if ( array_get( $h, 'h_vmem', 0 ) > 0 ) { ?>
<b>Free vmem:</b> <?= humanmem( $hvmem ); ?>
<?php } ?>
</div>
<table class="hostjobs tablesorter">
<thead>
<tr>
<th>No.</th>
<th>Name</th>
<th>Tool</th>
<th>State</th>
<th>Time</th>
<th>CPU</th>
<th>VMEM</th>
</tr>
</thead>
<tbody>
<?php
	foreach ( $jobs as $jobid => $j ) {
		if(!array_key_exists('host', $j) || $j['host'] != $host) {
			continue;
		}
?>
<tr class="jobline-<?= htmlspecialchars( array_get( $j, 'state' ) ) ?>">
<td class="jobno"><?= $jobid ?></td>
<td class="jobname"><?= htmlspecialchars( array_get( $j, 'name' ) ) ?></td>
<td class="jobtool"><a href="/?tool=<?= urlencode( array_get( $j, 'tool' ) ) ?>"><?= htmlspecialchars( array_get( $j, 'tool' ) ) ?></a></td>
<td class="jobstate"><?= htmlspecialchars( ucfirst( array_get( $j, 'queue' ) ) ) ?> / <?= htmlspecialchars( ucfirst( array_get( $j, 'state' ) ) ) ?></td>
<td class="jobtime"><?= strftime( '%F %T', array_get( $j, 'submit', 0 ) ) ?></td>
<td class="jobcpu"><?= array_key_exists('cpu', $j) ? humantime( $j['cpu'] ) : 'n/a' ?></td>
<td class="jobvmem">
<?= array_key_exists('vmem', $j) ? sprintf( '%d/%d', humanmem( $j['vmem'] / 1024 / 1024 ), humanmem( array_get( $j, 'h_vmem', 0 ) / 1024 / 1024 ) ) : 'n/a' ?>
<?php
		if ( array_key_exists('maxvmem', $j) &&
			$j['maxvmem'] > array_get( $j, 'vmem', 0 ) * 1.02
		) {
?>
(peak <?= humanmem( $j['maxvmem'] / 1024 / 1024 ) ?>)
<?php } // end if mexvmem ?>
</td>
</tr>
<?php } // end foreach job ?>
</tbody>
</table>
<?php } // end foreach host ?>
