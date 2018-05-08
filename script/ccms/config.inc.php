<?php
/*
------------------------------------------------------------

	CDyWeb CMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2010 CDyWeb / Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cdyweb.com
------------------------------------------------------------
*/

global $config;
$config = array();

$config["support_email"] = "support@cdyweb.com";

$config["database_auto_create"] = true;
$config["database_auto_update"] = true;

$config["base_uri"] = "ccms";
$config["base_url"] = SITE_BASE_URL.$config["base_uri"];
$config["shared_url"] = SITE_BASE_URL.'shared';
$config["resources_url"] = SITE_BASE_URL.'shared/ccms';

if (isset($_SERVER['WINDIR'])) {
  $config["logging_level"] = LOG_LEVEL_TRACE;
  $config["logging_dest"] = LOG_DEST_ERROR_LOG | LOG_DEST_FIREPHP; // | LOG_DEST_HTML; // | LOG_DEST_FILE;
  $config["logging_sql"] = LOG_LEVEL_TRACE;
  $config["logging_file"] = "";
} else {
  $config["logging_level"] = LOG_LEVEL_NONE;
  $config["logging_dest"] = 0;
  $config["logging_sql"] = 0;
  $config["logging_file"] = "";
}

if (isset($_GET['system_lang'])) {
  setcookie('ccms_system_lang', $_COOKIE['ccms_system_lang']=$_GET['system_lang'], time()+(60*60*24*999), '/ccms/');
}
if (!isset($_COOKIE['ccms_system_lang']) || isset($_GET['resetlang'])) {
  if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && preg_match('#^(nl-nl|nl,)#i',$_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
    setcookie('ccms_system_lang', $_COOKIE['ccms_system_lang']='nl_NL', time()+(60*60*24*999), '/ccms/');
  } else {
    setcookie('ccms_system_lang', $_COOKIE['ccms_system_lang']='en_US', time()+(60*60*24*999), '/ccms/');
  }
}
$config['system_lang']=$_COOKIE['ccms_system_lang'];

$config["plugin.enable.shop"] = false;

if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('#MSIE [5678]#',$_SERVER['HTTP_USER_AGENT'])) $config['jquery.jqupload']=false;

if (file_exists("custom/config.inc.php")) include "custom/config.inc.php";

// end