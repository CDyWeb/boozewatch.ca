<?

if (!defined('UNIT_TESTING')) define('UNIT_TESTING', true);
$_SERVER['WINDIR']=true;

/**
#--
require '../../globals.inc.php';
require '../../shared/cyane/site_config.inc.php'; //@todo Dependency Inject
global $config;
$config["logging_dest"] = LOG_DEST_HTML;
$config["logging_level"] = LOG_LEVEL_TRACE;
require '../../shared/cyane/logger.inc.php'; //@todo Dependency Inject
require '../../shared/cyane/util.inc.php';
require '../../shared/cyane/mysql.inc.php';
#--
**/

require '../ccms.inc.php';

require 'PHPUnit/autoload.php';
require_once '../../shared/tests/UnitTest.php';

//end