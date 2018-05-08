<?

define('UNIT_TESTING',true);
$_SERVER['WINDIR']=true;

#--
require '../../globals.inc.php';
require '../cyane/site_config.inc.php'; //@todo Dependency Inject
global $config;
$config["logging_dest"] = LOG_DEST_HTML;
$config["logging_level"] = LOG_LEVEL_TRACE;
require '../cyane/logger.inc.php'; //@todo Dependency Inject
require '../cyane/util.inc.php';
require '../cyane/mysql.inc.php';
#--

require 'PHPUnit/autoload.php';
require_once 'UnitTest.php';

//end