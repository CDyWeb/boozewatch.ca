<?

require_once dirname(__FILE__)."/ClsAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testDefaultHtmlOutput extends UnitTestCase {

	public function __construct() {
    require_once '../../shared/cyane/CcmsLanguage.class.php';
    require_once '../inc/session.inc';
    require_once '../cls/FrontendBase.inc';
    require_once '../cls/Output.inc';
    require_once '../cls/HttpOutput.inc';
    require_once '../cls/DefaultHtmlOutput.inc';
    
    require_once '../tests/mock/FrontendMock.class.php';
    
    $this->frontend=new FrontendMock();
    $this->output=new DefaultHtmlOutput($this->frontend);
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function testConstruct() {
    //noop
	}
	public function testGetDocType() {
    $s=$this->output->getDocType();
    $this->assertTrue(!empty($s));
	}
	public function testSetDocType() {
    $this->output->setDocType('test');
    $this->assertEquals('test',$this->output->getDocType());
	}
	public function testGetContentType() {
    $s=$this->output->getContentType();
    $this->assertTrue(!empty($s));
	}
	public function testSetContentType() {
    $this->output->setContentType('test');
    $this->assertEquals('test',$this->output->getContentType());
	}
	public function testGetLanguage() {
    $s=$this->output->getLanguage();
    $this->assertTrue(!empty($s));
	}
	public function testHtmlHeaders() {
    $this->output->setContentType('testContentType');
    $this->output->getFrontend()->setLanguage('testLanguage');
		$this->output->setHeaders(array());
    $this->output->htmlHeaders();
    $h=$this->output->getHeaders();
    $this->assertTrue(count($h)>=5);
    $this->assertTrue((bool)preg_match('#^Content-Type: testContentType#',$h[0][0]));
    $this->assertTrue((bool)preg_match('#^Content-Language: testLanguage#',$h[1][0]));
	}
	public function testAddToHead() {
		$this->assertTrue(true);
	}
	public function testAddHeadMetaTag() {
		$this->assertTrue(true);
	}
	public function testAddHeadLink() {
		$this->assertTrue(true);
	}
	public function testSetReplacement() {
		$this->assertTrue(true);
	}
	public function testSetCookie() {
		$this->assertTrue(true);
	}
	public function testAppend() {
		$this->assertTrue(true);
	}
	public function testSetContent() {
		$this->assertTrue(true);
	}
	public function testOutput() {
    $this->output->getFrontend()->setLanguage('testLanguage');
    $this->output->setContent('<html><head></head><body></body></html>');
		ob_start();
    $this->output->output();
    $s=ob_get_contents();
    ob_end_clean();
    _log($s);
    $this->assertTrue((bool)preg_match('#xml:lang="testLanguage"#',$s));
	}
}

//end