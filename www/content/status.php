<?php
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
    $megs = (int)($megs*10);
    $megs /= 10.0;
    return "$megs"."M";
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

  $raw = `PATH=/bin:/usr/bin qstat -xml -j '*'|sed -e 's/JATASK:[^>]*/jatask/g'`;
  $xml = simplexml_load_string($raw);
  unset($raw);

  foreach($xml->djob_info->element as $xjob) {
      $job = array(
          'num'    => (string)$xjob->JB_job_number,
          'name'   => (string)$xjob->JB_job_name,
          'submit' => (string)$xjob->JB_submission_time,
          'owner'  => (string)$xjob->JB_owner,
          'tool'   => preg_replace('/^tools\.(.*)$/', '$1', (string)$xjob->JB_owner),
          'queue'  => (string)$xjob->JB_hard_queue_list->destin_ident_list->QR_name,
      );
      if($job['queue'] == '')
          $job['queue'] = '(manual)';
      foreach($xjob->JB_hard_resource_list->qstat_l_requests as $lreq) {
          if($lreq->CE_name == 'h_vmem')
              $job['h_vmem'] = (int)$lreq->CE_doubleval;
      }
      foreach($xjob->JB_ja_tasks->jatask->JAT_scaled_usage_list->scaled as $usage) {
          $job[(string)$usage->UA_name] = (int)$usage->UA_value;
      }
      foreach($xjob->JB_ja_tasks->ulong_sublist->JAT_scaled_usage_list->scaled as $usage) {
          $job[(string)$usage->UA_name] = (int)$usage->UA_value;
      }
      $jobs[$job['num']] = $job;
  }
  unset($xml);

  $raw = `PATH=/bin:/usr/bin qhost -xml -j -F h_vmem`;
  $xml = simplexml_load_string($raw);
  unset($raw);

  foreach($xml->host as $xhost) {
      $hname = preg_replace('/^([^\.]*)\..*/', '$1', (string)$xhost->attributes()->name);
      if($hname != 'global') {
          $host = array(
              'name'        => $hname,
              'h_vmem'      => mmem((string)$xhost->resourcevalue)*1024*1024,
              'jobs'        => array(),
          );
          foreach($xhost->job as $xjob) {
              $jid = (int)$xjob->attributes()->name;
              $job = array();
              foreach($xjob->jobvalue as $jv) {
                  $job[(string)$jv->attributes()->name] = (string)$jv;
              }
              $jobs[$jid]['state'] = $job['job_state'];
              if(stristr($job['job_state'], 'R') !== false)
                  $jobs[$jid]['state'] = 'Running';
              if(stristr($job['job_state'], 's') !== false)
                  $jobs[$jid]['state'] = 'Suspended';
              if(stristr($job['job_state'], 'd') !== false)
                  $jobs[$jid]['state'] = 'Deleting';
              $jobs[$jid]['host'] = $hname;
              $jobs[$jid]['priority'] = $job['priority'];
              $host['jobs'][] = $jid;
          }
          foreach($xhost->hostvalue as $hv) {
             $host[(string)$hv->attributes()->name] = (string)$hv;
          }
          $host['mem'] = mmem($host['mem_used'])/mmem($host['mem_total']);
          $hosts[$hname] = $host;
      }
  }

?>
            <h1>Wikimedia Tool Labs</h1>
            <p>This is the web server for the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.</p>

            <h2>Grid Status</h2>
<?php

  ksort($hosts);
  ksort($jobs);

  foreach($hosts as $host => $h):
      $hvmem = $h['h_vmem'];
      foreach($h['jobs'] as $jn) {
          $hvmem -= $jobs[$jn]['h_vmem'];
      }
      $hvmem = (int)($hvmem/(1024*1024));
      if($hvmem < 0)
          $hvmem = 0;
      ?>
            <div class="hostline">
              <span class="hostname"><?= $host ?></span>
              <b>Load:</b> <?= (int)($h['load_avg']*1000)/($h['num_proc']*10) ?>%
              <b>Memory:</b> <?= (int)($h['mem']*1000)/10 ?>%
              <?php if($h['h_vmem'] > 0): ?>
                  <b>Free vmem:</b> <?php echo humanmem($hvmem); ?>
              <?php endif; ?>
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
      foreach($jobs as $jobid => $j):
      if($j['host'] != $host)
        continue;
          ?>
                <tr class="jobline-<?= $j['state'] ?>">
                  <td class="jobno"><?= $jobid ?></td>
                  <td class="jobname"><?= htmlspecialchars($j['name']) ?></td>
                  <td class="jobtool"><a href="/?tool=<?= $j['tool'] ?>"><?= $j['tool'] ?></a></td>
                  <td class="jobstate"><?= ucfirst($j['queue']) ?> / <?= ucfirst($j['state']) ?></td>
                  <td class="jobtime"><?= strftime("%F %T", $j['submit']) ?></td>
                  <td class="jobcpu"><?= humantime($j['cpu']) ?></td>
                  <td class="jobvmem">
                    <?= humanmem($j['vmem']/(1024*1024)) ?>/<?= humanmem($j['h_vmem']/(1024*1024)) ?> <?php if($j['maxvmem'] > $j['vmem']*1.02): ?>(peak <?= humanmem($j['maxvmem']/(1024*1024)) ?>)<?php endif; ?>
                  </td>
                </tr>
      <?php
          endforeach;
      ?>
              </tbody>
            </table>
<?php
  endforeach;
?>
