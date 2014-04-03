<H1>Wikimedia Tool Labs</H1>
Welcome to the Tool Labs project, the home of community-maintained external tools supporting Wikimedia projects and their users.
  <? $tool='';
     $g = posix_getgrnam("tools." . $_REQUEST['tool']);
     $u = posix_getpwnam("tools." . $_REQUEST['tool']);
     if($g and $u) {
       $tool = $_REQUEST['tool'];
       $maintainers = $g['members'];
       $home = $u['dir'];
     }
     if($tool != ''):
  ?>
  <H2>Tool details</H2>
  <TABLE CLASS="tool-info" COLS=2 WIDTH="95%">
    <TR><TH class="tool-name"><?
      echo $tool;
      if(array_key_exists(0, glob("$home/public_html/index.*")))
        print "<br/><a href=\"/$tool/\">(Web interface)</a>";
    ?></TH><TD></TD></TR>
      <TR><TH>Description</TH>
        <TD><?
        if(is_readable("$home/.description")) {
          $desc = file_get_contents("$home/.description", false, NULL, 0, 2048);
          print  $purifier->purify($desc);
        }
      ?></TD></TR>
      <TR><TH>Maintainers</TH><TD><?
        foreach($maintainers as $maint):
          $mu = posix_getpwnam($maint);
          if($mu):
            $wtu = $mu['gecos'];
            ?><A HREF="https://wikitech.wikimedia.org/wiki/User:<?= $wtu ?>"><?= ucfirst($wtu) ?></A> <?
          else:
            echo ucfirst($maint), " ";
          endif;
        endforeach;
      ?><TD>
      </TD></TR>
  </TABLE>
  <? else: ?>
  No such tool?  Trying to guess, are you?
  <? endif;
