            <h1>Wikimedia Tool Labs</h1>
            <p>Welcome to the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users. On this page you can find a complete list of hosted tools along with any additional information provided by the tool maintainers. Labs users who have been granted access to the Tools project may also create a new tool or add/remove maintainers to or from a tool that they manage.</p>

            <h2>Useful links</h2>
            <ul>
                <li><a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools">Tools project page on wikitech</a> (find out more about the Tools project)</li>
                <li><a href="https://wikitech.wikimedia.org/w/index.php?title=Special:UserLogin&returnto=Main+Page&type=signup">Create a Labs account</a> (you must have a Labs account to access the Tools project)</li>
                <li><a href="https://wikitech.wikimedia.org/wiki/Special:NovaKey">Add a public SSH key</a> (you’ll need this to access Labs servers using SSH)</li>
                <li><a href="https://wikitech.wikimedia.org/wiki/Special:FormEdit/Tools_Access_Request">Request access to the Tools project</a> (Join us!)</li>
                <li><a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaServiceGroup&action=addservicegroup&projectname=tools">Create New Tool</a></li>
            </ul>

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
<?  $users = shell_exec("/usr/bin/getent group|/bin/grep ^tools.");
    foreach(split("\n", $users) as $ln) {
      $fields = split(":", $ln);
      if(array_key_exists(3, $fields)) {
        list($user, $pass, $gid, $members) = $fields;
        $u = posix_getpwuid($gid);
        $home = $u['dir'];
        $indices = glob("$home/public_html/index.*");
        $user = preg_replace("/^tools./", '', $user);
        $tool = array( 'home' => $home );
        $tool['maints'] = array();
        foreach(split(",", $members) as $uid) {
          $u = posix_getpwnam($uid);
          $tool['maints'][] = $u['gecos'];
        }
        if(array_key_exists(0, $indices))
          $tool['uri'] = "/$user/";
        if(is_dir("$home/public_html"))
          $tools[$user] = $tool;
      }
    }
    ksort($tools);
    foreach($tools as $tool => $t): ?>
                <tr class="tool" id="toollist-<?= $tool ?>">
                  <td class="tool-name"><?
      if(array_key_exists('uri', $t)) {
        print "<a class=\"tool-web\" href=\"" . $t['uri'] . "\">$tool</a>";
      } else {
        print $tool;
      }
?>
                      <span class="mw-editsection">
                        [<a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaServiceGroup&action=managemembers&projectname=tools&servicegroupname=tools.<?=$tool?>">manage</a> maintainers]
                      </span>
                  </td>
                  <td class="tool-maintainers"><?
        foreach($t['maints'] as $maint):
          ?><a href="https://wikitech.wikimedia.org/wiki/User:<?= $maint ?>"><?= ucfirst($maint) ?></a><?
        endforeach;
?></td>

                  <td class="tool-desc"><?
        if(is_readable($t['home']."/.description")) {
          $desc = file_get_contents($t['home']."/.description", false, NULL, 0, 2048);
          print  $purifier->purify($desc);
        }
      ?></td>
                </tr>
<?  endforeach; 
?>
              </tbody>
            </table>
