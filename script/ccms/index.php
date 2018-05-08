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

require "ccms.inc.php";

#--
if (!isset($_SESSION['ccms.svn.info'])) {
  if (file_exists($fn=getConfigItem('script_base').'shared/svn.info.txt')) {
    $info=unserialize(file_get_contents($fn));
    foreach ($info as $i=>$s) if (preg_match('#^(.*):(.*)$#U',$s,$match)) $info[trim($match[1])]=trim($match[2]);
  } else {
    $info['Revision']='dev';
  }
  $_SESSION["ccms.svn.info"]=$info;
}
_require ('model/UserModel.class.php');
#--

_log("request from {$_SERVER["REMOTE_ADDR"]} for {$_SERVER["REQUEST_URI"]}",LOG_LEVEL_TRACE);
if (getConfigItem('require_https') && !isset($_SERVER["HTTPS"])) {
  //header('Location: https://'.$_SERVER['HTTP_HOST'].'/ccms/',true,303);
  echo '<a href="https://'.$_SERVER['HTTP_HOST'].'/ccms/">Enter the SSL-secured site</a>';
  exit();
}

if ((!isset($public_page) || !$public_page) && !UserModel::isUser()) {
	_log("not logged in and no public page > show login",LOG_LEVEL_TRACE);
	$config['database_auto_enabled']=false;
	_require ("controller/login.php");
  session_write_close();
	exit();
}

if(isset($_GET["logout"])) {
	_log("logout from _GET",LOG_LEVEL_TRACE);
	$_SESSION = array();
	session_write_close();
	redirect_url($config["base_url"]);
	//@mysql_close();
	exit();
}

$controller="frameset";
$url_param="";
$pattern="#{$site_config["rel_base"]}{$config["base_uri"]}/([\w\._-]+)(|/.*)\.\w+$#i";
$uri=getRequestURI();
log_message("debug","matching {$uri} to {$pattern}");

if (preg_match($pattern,$uri,$match)) {
	$controller=$match[1];
	$url_param=$match[2];
	log_message("debug","controller {$controller} url_param {$url_param}");
} else {
	log_message("debug","no match...");
}
$controllerClass=ucfirst($controller)."Controller";
_require ("controller/{$controller}.php");

log_message("debug", __FILE__ . ":showing {$controller} by {$controllerClass}");

log_message("trace", __FILE__ . ":creating {$controllerClass} ({$url_param})");
$ctrlObj=new $controllerClass($url_param);
log_message("trace", __FILE__ . ":invoke {$controllerClass} ({$url_param})");
$ctrlObj->invoke($url_param);

log_message("trace", __FILE__ . ":session_write_close");
session_write_close();

//@mysql_close();
_log("done.",LOG_LEVEL_TRACE);
// end