<?

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once '../../shared/tests/UnitTest.php';
require_once '../../shared/tests/UnitTestCase.php';
require_once '../../shared/tests/UnitTestSuite.php';

#--
require 'mock/CcmsSessionHandler.class.php';

define('HTMLMIMEMAIL5_TYPE','mock-db');

require_once dirname(__FILE__).'/../inc/ccms.inc'; //@todo Dependency Inject
require_once dirname(__FILE__).'/../inc/frontend.inc'; //@todo Dependency Inject

$config["logging_dest"] = LOG_DEST_HTML | LOG_DEST_FILE;
$config["logging_level"] = LOG_LEVEL_TRACE;
$config["logging_file"] = 't.txt';
$config["logging_sql_only_select"] = true;
$site_config['database_prefix']='test_';
file_put_contents($config["logging_file"],'');
#--

#require_once 'cls/ClsAllTests.php';
#require_once 'inc/IncAllTests.php';
require_once 'plugins/PluginsAllTests.php';

class AllTests extends UnitTestSuite {

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('CCMS testsuite');

		//$suite->addTestSuite('ClsAllTests');
		//$suite->addTestSuite('IncAllTests');
    $suite->addTestSuite('PluginsAllTests');

		log_message("debug","suite building done, ".$suite->count()." tests found");
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}









//end