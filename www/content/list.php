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
<?
  function describe($t) {
    if(array_key_exists('description', $t)) {
      global $purifier;
      print  $purifier->purify($t['description']);
      if(array_key_exists('author', $t))
        print "<BR/><I>Author(s): " . $purifier->purify($t['author']) . "</I>";
      if(array_key_exists('repository', $t))
        print "<BR/><a href=\"" . htmlspecialchars($t['repository']) . "\">Source</a>";
    }
  }

  $dyn = fopen("/data/project/.system/dynamic", "r");
  while(!feof($dyn)) {
    list($tname, $where) = fscanf($dyn, "%s %s\n");
    $tooldyn{$tname} = 1;
  }
  fclose($dyn);

  $ini = parse_ini_file("/data/project/admin/replica.my.cnf");
  $db = new mysqli("tools.labsdb", $ini['user'], $ini['password'], "toollabs_p");
  
  $res = $db->query("SELECT * FROM users");
  while($row = $res->fetch_assoc()) {
    $users[$row['name']] = $row;
  }
  $res->free();
  
  $res = $db->query("SELECT * FROM tools ORDER BY name ASC");
  while($row = $res->fetch_assoc()):

    $tool = $row['name'];
    if($row['toolinfo'] != '') {
        $json = json_decode($row['toolinfo'], true);
    } else {
        $json = array(
            "description" => $row['description'],
        );
    }

    if(array_key_exists(0, $json) && !array_key_exists(1, $json)) {
        $json = $json[0];
    }
?>
                <tr class="tool" id="toollist-<?= $tool ?>">
                  <td class="tool-name"><?

      if(array_key_exists('url', $json)) {
        print "<a class=\"tool-web\" href=\"" . $json['url'] . "\">$tool</a>";
      } elseif(array_key_exists($tool, $tooldyn) && !array_key_exists(0, $json)) {
        print "<a class=\"tool-web\" href=\"/$tool/\">$tool</a>";
      } else {
        print $tool;
      }

?>
                      <span class="mw-editsection">
                        [<a href="https://wikitech.wikimedia.org/w/index.php?title=Special:NovaServiceGroup&action=managemembers&projectname=tools&servicegroupname=tools.<?=$tool?>">manage</a> maintainers]
                      </span>
                  </td>
                  <td class="tool-maintainers"><?
        foreach(explode(' ', $row['maintainers']) as $maint):
          if(array_key_exists($maint, $users)):
            $maint = htmlspecialchars($users[$maint]['wikitech']);
            ?><a href="https://wikitech.wikimedia.org/wiki/User:<?= $maint ?>"><?= ucfirst($maint) ?></a><?
          endif;
        endforeach;
?></td>
                  <td class="tool-desc"><?
        if(array_key_exists(1, $json)) {
            $first = " first";
            foreach($json as $sub) {
              echo "<div class=\"subtool$first\"><span class=\"subtool-name\">";
              if(array_key_exists('url', $sub))
                echo "<a href=\"" . htmlspecialchars($sub['url']) . "\">";
              echo htmlspecialchars($sub['title']);
              if(array_key_exists('url', $sub))
                echo "</a>";
              echo "</span><span class=\"subtool-desc\">";
              describe($sub);
              echo "</span></div>";
              $first = '';
            }
        } else {
          describe($json);
        }
      ?></td>
                </tr>
<?  endwhile;
    $res->free();
?>
              </tbody>
            </table>
