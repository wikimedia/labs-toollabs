<!DOCTYPE html>
<html lang=en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>403 Forbidden</title>
<meta name="viewport" content="initial-scale=1.0, user-scalable=yes, width=device-width">
</head>
<body>
<?php $uri = $_SERVER['HTTP_X_ORIGINAL_URI']; ?>
      <h1>Forbidden</h1>
      <p>The URI you have requested, <a href="<?= $uri ?>"><code><?= $uri ?></code></a>, might exist but the server has been instructed not to let you reach it.</p>
      <?php $tool = '';
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
      <p>This URI is managed by the <a href="/?tool=<?= $tool ?>"><code><?= $tool?></code></a> tool, maintained by 
      <?php foreach($maintainers as $num => $maint):
           $mu = posix_getpwnam($maint);
           if($mu):
             $wtu = $mu['gecos'];
             ?><A HREF="https://wikitech.wikimedia.org/wiki/User:<?= $wtu ?>"><?= ucfirst($wtu) ?></A><?php
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
      <p>Perhaps this content can only be accessed from the secret underground lair of the maintainers, or the link you've followed doesn't actually lead somewhere useful?</p>
      <p>If you're pretty sure this shouldn't be an error, you may wish to notify the tool's maintainers (above) about the error and how you ended up here.</p>
      <h2>If you maintain this tool</h2>
      <p>The error might be caused by incorrect permissions, or by the absence of an index file (this webserver does not list directory contents by default).</p>
      <?php else: ?>
      <p>Perhaps the webserver has temporarily lost its mind, or the link you've followed doesn't actually lead somewhere useful?</p>
      <p>If you're pretty sure this shouldn't be an error, you may wish to notify the <a href="/?tool=admin">project administrators</a> about the error and how you ended up here.</p>
      <?php endif ?>
</body>
</html>
