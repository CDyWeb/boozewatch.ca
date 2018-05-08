<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 108 $
$LastChangedDate: 2009-05-24 17:33:49 +0200 (zo, 24 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

function _log($s,$level=LOG_LEVEL_DEBUG,$html_format="<!-- {t}: %s -->") {

	global $config;
	if ($level>$config["logging_level"]) return;

	if (is_array($s)) $s=print_r($s,true);
	if (is_object($s)) $s=print_r($s,true);
	if (($config["logging_dest"]&LOG_DEST_HTML)==LOG_DEST_HTML) echo sprintf(str_replace("{t}",date("r"),$html_format),$s)."\n";
	if (($config["logging_dest"]&LOG_DEST_ERROR_LOG)==LOG_DEST_ERROR_LOG) error_log(@$_SERVER["HTTP_HOST"].": ".$s);
	if (($config["logging_dest"]&LOG_DEST_FILE)==LOG_DEST_FILE && $config["logging_file"]) {
    $log_file = $config["logging_file"];
		if($fp = @fopen($log_file,'ab')) {
			$now = date("d-M-y H:i:s");
			fwrite($fp,"{$now} - {$s}\n");
			fclose($fp);
		} else {
      throw new Exception($log_file);
    }
	}
	if (($config["logging_dest"]&LOG_DEST_FIREPHP)==LOG_DEST_FIREPHP and !headers_sent()) {
		if (!defined("FIREPHP_LOADED")) {
			require dirname(__FILE__).'/../FirePHPCore/FirePHP.class.php';
			define("FIREPHP_LOADED",true);
		}
		if (isset($_SERVER["HTTP_USER_AGENT"]) && preg_match("# CDyWeb#",$_SERVER["HTTP_USER_AGENT"])) {
      $firephp = FirePHP::getInstance(true);
			$firephp->log($s);
		}
	}
	return $s;
}

function log_in_js($s,$level=LOG_LEVEL_DEBUG) {

	global $config;
	if ($level<$config["logging_level"]) return;

	if (is_array($s)) $s=print_r($s,true);
	if (is_object($s)) $s=print_r($s,true);
	if ($config["logging_dest"]&LOG_DEST_HTML==LOG_DEST_HTML) echo sprintf("/* %s */\n",$s);

}

function log_message($level,$msg,Array $subst=null) {
	if ($subst) foreach ($subst as $k=>$s) {
		if (is_array($s)) $s=print_r($s,true);
		if (is_object($s)) $s=print_r($s,true);
		$msg=str_replace("{$k}",$s,$msg);
	}
	switch (strtolower($level)) {
		case "debug" : return _log($msg,LOG_LEVEL_DEBUG);
		case "trace" : return _log($msg,LOG_LEVEL_TRACE);
		case "warn" : return _log($msg,LOG_LEVEL_WARN);
		case "error" : return _log($msg,LOG_LEVEL_ERROR);
		return _log($msg,LOG_LEVEL_INFO);
	}
}

//end