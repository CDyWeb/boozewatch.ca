<?php
/*
------------------------------------------------------------

  CDyWeb CMS

Copyright (c) 2006-2010 CDyWeb / Cyane Dynamic Web Solutions
IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
WITHOUT THE PERMISION OF THE AUTHOR.    

Info? Mail to ccms@cdyweb.com
------------------------------------------------------------
*/

require 'inc/hooks.inc';
#if (file_exists('custom/hooks.php')) require 'custom/hooks.php';
if (file_exists('../app/frontend/hooks.inc')) require '../app/frontend/hooks.inc';

hook('index','start');

require 'inc/ccms.inc';

hook('index','loaded_framework');

require 'inc/frontend.inc';
$frontend_cls='Frontend';

hook('index','loaded_frontend');

if (file_exists(getConfigItem('script_app').'frontend/frontend.inc')) require getConfigItem('script_app').'frontend/frontend.inc';

hook('index','loaded_myfrontend');

$frontend=new $frontend_cls();

hook('index','constructed');

if (isset($_SERVER['WINDIR'])) $frontend->render();
else try {
  $frontend->render();
} catch (Exception $ex) {
  header('HTTP/1.0 500 Internal error');
  echo 'An internal error occured. Please contact your site administrator.';
  log_message('error',$ex);
  if (!defined('PHPUnit_MAIN_METHOD') && (ini_get("smtp_port") || ini_get("sendmail_path"))) {
    mail(getConfigItem('support_email','support@cdyweb.com'),'frontend failure',print_r($ex,true),"From: {$_SERVER["HTTP_HOST"]} <info@{$_SERVER["HTTP_HOST"]}>");
  }
}

hook('index','finish');

_log(__FILE__ . ' is done');
db_disconnect();

hook('index','end');

//end