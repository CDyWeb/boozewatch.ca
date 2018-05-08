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

global $config;
$config = array();

$config["support_email"] = "support@cdyweb.com";

$config["logging_level"] = LOG_LEVEL_TRACE;
$config["logging_dest"] = LOG_DEST_ERROR_LOG | LOG_DEST_FIREPHP; // | LOG_DEST_HTML; // | LOG_DEST_FILE;
$config["logging_sql"] = LOG_LEVEL_TRACE;
$config["logging_file"] = "";

$config["html_template_dir"] = SITE_BASE_PATH."html/";
$config["html_base_href"] = SITE_BASE_URL."html/";
$config["default_favicon"] = "favicon.ico";

if (file_exists($site_config['script_app'].'frontend/config.inc.php')) include $site_config['script_app'].'frontend/config.inc.php';

// end