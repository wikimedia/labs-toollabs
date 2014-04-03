<? $uri = $_SERVER['HTTP_X_ORIGINAL_URI']; ?>
      <h1>Internal error</h1>
      <p>The URI you have requested, <a href="<?= $uri ?>"><code><?= $uri ?></code></a>,
      appears to be non-functional at this time.</p>
      <? $tool = '';
         if(preg_match("@^/([^/]+)/@", $uri, $part)) {
           $gr = posix_getgrnam("tools.".$part[1]);
           if($gr) {
             $tool = $part[1];
             $maintainers = $gr['members'];
           }
         }
         if($tool != ''):
      ?>
        <h2>If you have reached this page from somewhere else...</h2>
        <p>This URI is part of the <a href="/?tool=<?= $tool ?>"><code><?= $tool?></code></a> tool, maintained by 
        <? foreach($maintainers as $num => $maint):
             $mu = posix_getpwnam($maint);
             if($mu):
               $wtu = $mu['gecos'];
               ?><a href="https://wikitech.wikimedia.org/wiki/User:<?= $wtu ?>"><?= ucfirst($wtu) ?></A><?
             else:
               echo ucfirst($maint);
             endif;
             if($num < count($maintainers)-1) {
               if($num == count($maintainers)-2) {
                 if($num == 0)
                   print " and ";
                 else
                   print ", and ";
               } else
                 print ", ";
             }
             endforeach;
        ?>.</p>
        <p>Perhaps its magical script elves are temporarily ill, or the link you've followed doesn't actually lead
        somewhere useful?<p>
        <p>If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above)
        about the error and how you ended up here.</p>
        <h2>If you maintain this tool</h2>
        <p>The error might be caused by incorrect permission, or by an error in the script or CGI that was meant
        to execute here.  You may wish to check your logs or <a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/Help#Logs">common causes for errors</a> in the help documentation.</p>
      <? else: ?>
        <p>Perhaps the webserver has temporarily lost its mind, or the link you've followed doesn't actually lead
        somewhere useful?</p>
        <p>If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
      <? endif ?>
