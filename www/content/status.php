<?
  function humantime($secs) {
    if($secs < 120)
      return "$secs"."s";
    $secs = (int)$secs;
    $mins = (int)($secs / 60);
    $secs = $secs % 60;
    if($mins < 60)
      return "$mins"."m$secs"."s";
    $hours = (int)($mins / 60);
    $mins = $mins % 60;
    return "$hours"."h$mins"."m";
  }

  function humanmem($megs) {
    if($megs > 1024) {
      $megs = (int)($megs / 102.4);
      $megs /= 10.0;
      return "$megs"."G";
    }
    return "$megs"."M";
  }

  function toarray($XML) {
    $array = array();

    if(is_object($XML)) {
      $XML = get_object_vars($XML);
    }
    if(is_array($XML)) {
      foreach($XML as $i => $val) {
        if(is_object($val) || is_array($val)) {
          $val = toarray($val);
        }
        $array[$i] = $val;
      }
    }
    return $array;
  }

  function mmem($str) {
    if(preg_match("/M$/", $str)) {
      return 0+$str;
    }
    if(preg_match("/G$/", $str)) {
      return 1024*$str;
    }
    return -1;
  }

  $rawjobs = toarray(simplexml_load_string(`PATH=/bin:/usr/bin /usr/bin/qstat -u '*' -r -xml`));
  $rawhosts = toarray(simplexml_load_string(`PATH=/bin:/usr/bin /usr/bin/qhost -F h_vmem -xml`));
  $vmem = toarray(simplexml_load_string(`PATH=/bin:/usr/bin /usr/bin/qstat -F h_vmem -xml`));
  foreach ($vmem['queue_info']['Queue-List'] as $vm) {
	  $server = $vm['name'];
	  $server = substr($server, strpos($server, "@") + 1);
	  $server = substr($server, 0, strpos($server, "."));
	  if ( $server !== false ) {
	      $h_vmem[$server] = $vm['resource'];
	  }
  }
?>
            <h1>Wikimedia Tool Labs</h1>
            <p>This is the web server for the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.</p>

            <h2>Grid Status</h2>

<?
  $jobs = array();
  foreach($rawjobs['queue_info']['job_list'] as $jl) {
    $jobid = $jl['JB_job_number'];
    $job = toarray(simplexml_load_string(`PATH=/bin:/usr/bin /usr/bin/qstat -xml -j $jobid|sed -e 's/JATASK:[^>]*/jatask/g'`));
    $job = $job['djob_info']['element'];
    $j = array();
    $tool = $job['JB_owner'];
    $j['tool'] = preg_replace('/^tools.(.*)$/', "$1", $tool);
    $j['sub'] = $job['JB_submission_time'];
    $j['name'] = $job['JB_job_name'];
    foreach($job['JB_hard_resource_list'] as $rvals) {
      if(!isset($rvals[0])) {
        $rvals = array($rvals);
      }
      foreach($rvals as $rval){
        if($rval['CE_name'] == 'h_vmem') {
          $j['mem_alloc'] = intval($rval['CE_doubleval']/1048576);
        }
      }
    }
    $j['tasks'] = 0;
    $j['mem_used'] = 0;
    $j['mem_max'] = 0;
    $j['cpu'] = 0;
    $j['state'] = $jl['@attributes']['state'];
    $j['start'] = $jl['JAT_start_time'];
    $j['slots'] = $jl['slots'];
    $host = $jl['queue_name'];
    $j['host'] = preg_replace('/^.*@([^\.]*)\..*$/', "$1", $host);
    $j['queue'] = preg_replace('/^(.*)@[^\.]*\..*$/', "$1", $host);
    foreach($job['JB_ja_tasks'] as $task) {
      $j['tasks']++;
      foreach($task['JAT_scaled_usage_list']['scaled'] as $usage) {
        switch($usage['UA_name']) {
        case 'cpu':
          $j['cpu'] += $usage['UA_value'];
          break;
        case 'vmem':
          $j['mem_used'] += intval($usage['UA_value']/1048576);
          break;
        case 'maxvmem':
          if(intval($usage['UA_value']/1048576) > $j['mem_max'])
            $j['mem_max'] = intval($usage['UA_value']/1048576);
          break;
        }
      }
    }
    $jobs[$job['JB_job_number']] = $j;
  }
  foreach($rawhosts['host'] as $hl) {
    $h = array();
    $host = $hl['@attributes']['name'];
    $hname = preg_replace('/^([^\.]*)\..*$/', "$1", $host);
    if($hname === 'global')
      continue;
    $h['arch'] = $hl['hostvalue'][0];
    $h['use'] = $hl['hostvalue'][2] / $hl['hostvalue'][1];
    $h['mem'] = mmem($hl['hostvalue'][4]) / mmem($hl['hostvalue'][3]);
    $hosts[$hname] = $h;
  }
  ksort($hosts);
  ksort($jobs);
  foreach($hosts as $host => $h):
      ?>
            <div class="hostline">
              <span class="hostname"><?= $host ?></span>
              <b>Load:</b> <?= (int)($h['use']*1000)/10 ?>%
              <b>Memory:</b> <?= (int)($h['mem']*1000)/10 ?>%
              <b>Free vmem:</b> <? echo $h_vmem[$host]; ?>
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
      <?
      foreach($jobs as $jobid => $j):
      if($j['host'] != $host)
        continue;
          ?>
                <tr class="jobline-<?= $j['state'] ?>">
                  <td class="jobno"><?= $jobid ?></td>
                  <td class="jobname"><?= htmlspecialchars($j['name']) ?></td>
                  <td class="jobtool"><a href="/?list#toollist-<?= $j['tool'] ?>"><?= $j['tool'] ?></a></td>
                  <td class="jobstate"><?= ucfirst($j['queue']) ?> / <?= ucfirst($j['state']) ?></td>
                  <td class="jobtime"><?= strftime("%F %T", $j['sub']) ?></td>
                  <td class="jobcpu"><?= humantime($j['cpu']) ?></td>
                  <td class="jobvmem">
                    <?= humanmem($j['mem_used']) ?>/<?= humanmem($j['mem_alloc']) ?> <? if($j['mem_max'] > $j['mem_used']): ?>(peak <?= humanmem($j['mem_max']) ?>)<? endif; ?>
                  </td>
                </tr>
      <?
          endforeach;
      ?>
              </tbody>
            </table>
<?
  endforeach;
?>
