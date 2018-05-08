<?

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'IncAllTests::main');
}

_log('IncAllTests');

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once dirname(__FILE__).'/../../../shared/tests/UnitTest.php';

$files = UnitTest::files('/test.*\.php/', dirname(__FILE__), true);
foreach($files as $file){
    require_once $file;
}

class IncAllTests extends UnitTestSuite {

	public static function suite() {
		$files = UnitTest::files('/test.*\.php/', dirname(__FILE__));
		$suite = new PHPUnit_Framework_TestSuite('Inc tests');
		foreach($files  as $file){
			$file = str_replace('.php', '', $file);
			if (defined('TEST_ONLY') && (strcasecmp($file,TEST_ONLY)!==0)) continue; 
			$suite->addTestSuite($file);
		}
		log_message("debug","suite building done, ".$suite->count()." tests found");
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'IncAllTests::main') {
    IncAllTests::main();
}


//end