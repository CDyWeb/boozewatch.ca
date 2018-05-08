<?

require_once dirname(__FILE__)."/CyaneAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testValidEmail extends UnitTestCase {

	public function __construct() {
		require_once '../cyane/valid_email.inc.php';
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function testIsValidEmail() {
		$this->assertTrue(isValidEmail("aaa@cyane.nl"));
		$this->assertFalse(isValidEmail("aa.bb@cc@bogus.co.uk"));
	}
}

//end