<?

require_once dirname(__FILE__)."/ClsAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testRouter extends UnitTestCase {

	public function __construct() {
		require_once '../cls/FrontendBase.inc';
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function testXxx() {
		$this->assertTrue(true);
	}
}

//end