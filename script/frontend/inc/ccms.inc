<?php
/*
------------------------------------------------------------

  CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
WITHOUT THE PERMISION OF THE AUTHOR.    

Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

$_SERVER["_URI"]=preg_replace("#\?.*$#","",isset($_SERVER["REQUEST_URI"])?$_SERVER["REQUEST_URI"]:$_SERVER["REQUEST_URI"]='');

$my_dir=dirname(__FILE__);
define("BASE_PATH",realpath($my_dir.'/../..'));
define("FRONTEND_PATH",realpath($my_dir.'/..'));
define("SHARED_PATH",BASE_PATH.'/shared');

require BASE_PATH."/globals.inc.php";
require FRONTEND_PATH."/config.inc.php";
require SHARED_PATH."/cyane/site_config.inc.php";
require SHARED_PATH."/cyane/mysql.inc.php";
require SHARED_PATH."/cyane/logger.inc.php";
require SHARED_PATH."/cyane/util.inc.php";

require SHARED_PATH.'/ezc/ezc.inc.php';
global $ezcAutoload;
$ezcAutoload=new CcmsEzcAutoload();

function __autoload($classname) {
  if (file_exists($fn=FRONTEND_PATH."/cls/{$classname}.inc")) { require $fn; return; }
  if (file_exists($fn=SHARED_PATH."/cyane/{$classname}.class.php")) { require $fn; return; }
  global $ezcAutoload;
  $ezcAutoload->autoload($classname);
}

require FRONTEND_PATH."/inc/session.inc";
require FRONTEND_PATH."/inc/locale.inc";

hook('framework','loaded');

function setting($key,$default=null) {
  global $settings;
  if (!$settings) {
    $settings=array();
    foreach (getTableArray("select * from `".tbl_name("settings")."`","key") as $k=>$line) {
      $settings[$k]=$line["str"].$line["txt"];
    }
    //_log(__FILE__ .":settings: ".print_r($settings,true));
  }
  if (!isset($settings[$key])) {
    _log(__FILE__ .":settings: {$key} is not set, returning default - {$default}");
    return $default;
  }
  _log(__FILE__ .":settings: {$key} - {$settings[$key]}");
  return $settings[$key];
}

function set_setting($key,$value) {
  $field="`str`";
  if (strlen($value)>250) $field="`str`=NULL, `txt`";
  executeSql("replace into `".tbl_name("settings")."` set `key`='".db_escape($key)."', {$field}='".db_escape($value)."'");
  if (class_exists('CcmsObjectCache')) {
    $cache=CcmsObjectCache::getInstance();
    $cache->delete(getConfigItem('domain').':settings');
  }
  global $settings;
  $settings[$key]=$value;
}

//end