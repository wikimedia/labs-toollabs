<H1>Wikimedia Tool Labs</H1>
Welcome to the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users. On this page you can find a complete list of hosted tools along with any additional information provided by the tool maintainers. Labs users who have been granted access to the Tools project may also create a new tool or add/remove maintainers to or from a tool that they manage.


<h2>Useful links</h2>
<ul>
    <li><a href="https://wikitech.wikimedia.org/wiki/Nova_Resource:Tools">Tools project page on wikitech</a> (find out more about the Tools project)</li>
<br>
    <li><a href="https://wikitech.wikimedia.org/w/index.php?title=Special:UserLogin&returnto=Main+Page&type=signup">Create a Labs account</a> (you must have a Labs account to access the Tools project)</li>
    <li><a href="https://wikitech.wikimedia.org/wiki/Special:NovaKey">Add a public SSH key</a> (you’ll need this to access Labs servers using SSH)</li>
    <li><a href="https://wikitech.wikimedia.org/wiki/Special:FormEdit/Tools_Access_Request">Request access to the Tools project</a> (Join us!)</li>
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
