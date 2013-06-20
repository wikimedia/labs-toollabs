<H1>Wikimedia Tool Labs</H1>
Welcome to the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.

<h2>Useful links</h2>
<ul>
    <li><a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools">main page on wikitech</a></li>
<br>
    <li><a href="https://wikitech.wikimedia.org/w/index.php?title=Special:UserLogin&returnto=Main+Page&type=signup">create a new user</a></li>
    <li><a href="https://wikitech.wikimedia.org/wiki/Special:NovaKey">add a public SSH key</a></li>
    <li><a href="https://wikitech.wikimedia.org/wiki/Special:FormEdit/Tools_Access_Request">Request access to the project</a></li>
</ul>

<h2><span class="mw-headline" style="display: inline;">Hosted tools</span>
     <span class="mw-editsection">[<a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaProject&action=addservicegroup&projectname=tools">create new tool</a>]</span>
</h2>
  <TABLE CLASS="tool-list" COLS=3>
<?  $users = shell_exec("/usr/bin/getent group|/bin/grep ^local-");
    foreach(split("\n", $users) as $ln) {
      $fields = split(":", $ln);
      if(array_key_exists(3, $fields)) {
        list($user, $pass, $gid, $members) = $fields;
        $u = posix_getpwuid($gid);
        $home = $u['dir'];
        $indices = glob("$home/public_html/index.*");
        $user = preg_replace("/^local-/", '', $user);
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
    <TR class="tool" id="<?= $tool ?>"><TD class="tool-name"><?
      if(array_key_exists('uri', $t)) {
        print "<a class=\"tool-web\" href=\"" . $t['uri'] . "\">$tool</a>";
      } else {
        print $tool;
      }
?>
      <span class="mw-editsection" style="display:block;">[
<a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaServiceGroup&action=addmember&projectname=tools&servicegroupname=local-<?=$tool?>">add</a> / 
<a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaServiceGroup&action=deletemember&projectname=tools&servicegroupname=local-<?=$tool?>">remove</a> maintainers]</span>
    </TD>
      <TD class="tool-maintainers"><?
        foreach($t['maints'] as $maint):
          ?><A HREF="https://wikitech.wikimedia.org/wiki/User:<?= $maint ?>"><?= ucfirst($maint) ?></A><?
        endforeach;
?>
</TD>
      <TD class="tool-desc"><?
        if(is_readable($t['home']."/.description")) {
          $desc = file_get_contents($t['home']."/.description", false, NULL, 0, 2048);
          print  $purifier->purify($desc);
        }
      ?></TD>
    </TR>
<?  endforeach;
?>
  </TABLE>
