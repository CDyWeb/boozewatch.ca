<?

require_once dirname(__FILE__)."/CyaneAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testSiteStats extends UnitTestCase {

	public function __construct() {
		require_once '../cyane/SiteStats.class.php';
    log_message('debug', get_class($this).' UnitTestCase constructed');
    $this->myTable=getConfigitem('database_prefix').'log';
	}
	
	public function test_constructor() {
		$o=new SiteStats(false);
		$this->assertTrue($o instanceof SiteStats);
	}
	
	public function test_visitorInfo() {
		$o=new SiteStats(false);
		$arr=$o->visitorInfo();
		$this->assertTrue(is_array($arr));
		$this->assertTrue(in_array('uri',array_keys($arr)));
	}
	
	public function test_logVisitorInfo() {
		executeSql('TRUNCATE TABLE '.$this->myTable);
		$o=new SiteStats(false);
		$o->logVisitorInfo();
		$this->assertEquals(1,getOneValue('select count(*) from '.$this->myTable));
	}
	
	public function test_cleanUp() {
		executeSql('TRUNCATE TABLE '.$this->myTable);
		$o=new SiteStats(false);
		$o->logVisitorInfo();
		executeSql("update `{$this->myTable}` set dt='2007-07-20'");
		$o->cleanUp();
		$this->assertEquals(0,getOneValue('select count(*) from '.$this->myTable));
	}

}

//end