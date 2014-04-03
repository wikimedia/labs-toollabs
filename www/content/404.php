<? $uri = $_SERVER['X_ORIGINAL_URI']; ?>
      <h1>Four hundred and four!</h2>
      <p>The URI you have requested, <code>http://tools.wmflabs.org<?= $uri ?></code>, doesn't seem to actually exist.</p>
      <? $tool = '';
         if(preg_match("@^/([^/]+)/@", $uri, $part)) {
           $gr = posix_getgrnam("local-".$part[1]);
           if($gr) {
             $tool = $part[1];
             $maintainers = $gr['members'];
           }
         }
         if($tool != ''):
      ?>
      <h2>If you have reached this page from somewhere else...</h2>
      <p>This URI is managed by the <a href="/?tool=<?= $tool ?>"><code><?= $tool?></code></a> tool, maintained by 
      <? foreach($maintainers as $num => $maint):
           $mu = posix_getpwnam($maint);
           if($mu):
             $wtu = $mu['gecos'];
             ?><A HREF="https://wikitech.wikimedia.org/wiki/User:<?= $wtu ?>"><?= ucfirst($wtu) ?></A><?
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
      <p>Perhaps its files are on vacation, or the link you've followed doesn't actually lead somewhere useful?</p>
      <p>You might want to looks at the <a href="/?list">list of tools</a> to find what you were looking for, or one of the links on the sidebar to the left. If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above) about the error and how you ended up here.</p>
      <? else: ?>
      <p>Perhaps the webserver has temporarily lost its mind, or the link you've followed doesn't actually lead somewhere useful?</p>
      <p>You might want to looks at the <a href="/?list">list of tools</a> to find what you were looking for, or one of the links on the sidebar to the left. If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
      <? endif ?>


