<?

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require 'load.inc';

require_once 'system/SystemAllTests.php';
require_once 'libs/LibsAllTests.php';
#require_once 'controller/ControllerAllTests.php';
#require_once 'models/ModelsAllTests.php';
#require_once 'views/ViewsAllTests.php';
require_once 'domain/DomainAllTests.php';
require_once 'domain/ModelAllTests.php';

class AllTests extends UnitTestSuite {

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('CCMS testsuite');
    
    global $argv;
    if (isset($argv[2])) {
      $suite->addTestSuite($argv[2]);
    } else {
      $suite->addTestSuite('SystemAllTests');
      $suite->addTestSuite('LibsAlltests');
      #$suite->addTestSuite('ModelsAlltests');
      #$suite->addTestSuite('ViewsAlltests');
      #$suite->addTestSuite('ControllersAlltests');
      $suite->addTestSuite('DomainAllTests');
      $suite->addTestSuite('ModelAllTests');
    }

		_log("AllTests suites building done, ".$suite->count()." tests found");
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}









//end