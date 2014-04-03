<? $uri = $_SERVER['X_ORIGINAL_URI']; ?>
      <h1>No webservice</h1>
      <p>The URI you have requested, <a href="http://tools.wmflabs.org<?= $uri ?>"><code>http://tools.wmflabs.org<?= $uri ?></code></a>,
        is not currently serviced.</p>
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
        <p>That tool might not have a web interface, or it may currently be disabled.</p>
        <p>If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above)
        about the error and how you ended up here.</p>
        <h2>If you maintain this tool</h2>
        <p>You have not enabled a web service for your tool, or it has stopped working because of a fatal error.
        You may wish to check your logs or <a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools/Help#Logs">common causes for errors</a> in the help documentation.</p>
      <? else: ?>
        <h2>If you have reached this page from somewhere else...</h2>
        <p>This URI is not currently part of any tool.</p>
        <p>If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
      <? endif ?>
