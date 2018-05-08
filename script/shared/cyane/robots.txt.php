<? 

require dirname(__FILE__).'/../../globals.inc.php';
require 'site_config.inc.php';

if ($_SERVER['HTTP_HOST']!=getConfigItem('host_base') && getConfigItem('robots.deny.wrong.domain',false)) { 
?>
User-agent: *
Disallow: /

<?
  exit();
}

if ($_SERVER['HTTP_HOST']!=getConfigItem('host_base') && getConfigItem('robots.redirect.wrong.domain',false)) { 
  header('Location: '.getConfigItem("url_base").'robots.txt');
  exit();
}

if ($_SERVER['HTTP_HOST']!=getConfigItem('host_base') && getConfigItem('robots.404.wrong.domain',true)) { 
  header("HTTP/1.0 404 Not Found");
  echo '<h1>404 Not Found</h1>';
  exit();
}

?>
Sitemap: <?= getConfigItem("url_base") ?>sitemap.xml
User-agent: *
Disallow: /ccms/
Disallow: /shared/
Disallow: /template/
<?= getConfigItem('robots.disallow','') ?>
