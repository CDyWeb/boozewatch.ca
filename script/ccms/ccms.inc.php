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

ini_set('newrelic.enabled',false);

$mydir=dirname(__FILE__);
define('BASE_PATH',realpath($mydir.'/..'));
define('CMS_PATH',$mydir);
define('SHARED_PATH',BASE_PATH.'/shared');
define('PLUGIN_PATH',SHARED_PATH.'/plugins');

require_once BASE_PATH.'/globals.inc.php';
require_once CMS_PATH.'/config.inc.php';
require_once CMS_PATH.'/core/inc/autoload.inc';

define('APP_PATH',$site_config['script_app']);

_require ('site_config.inc.php');
_require ('mysql.inc.php');
_require ('logger.inc.php');
_require ('util.inc.php');
_require ('inc/session.inc.php');
_require ('inc/error_handler.inc.php');
_require ('inc/lang.inc.php');
