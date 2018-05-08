<?require_once dirname(__FILE__)."/CyaneAllTests.php";echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;class testMysql extends UnitTestCase {	public function __construct() {		require_once '../cyane/mysql.inc.php';    log_message('debug', get_class($this).' UnitTestCase constructed');	}		public function testSql_log() {		sql_log("abc");		//@Todo		$this->assertTrue(true);	}		public function testDb_query() {		try {			$res=db_query("select now()");		} catch (Exception $ex) {		}		$this->assertTrue(!isset($ex));		$this->assertTrue(is_resource($res));				try {			$res=db_query("select '");		} catch (Exception $ex2) {		}		$this->assertTrue(isset($ex2));		$this->assertTrue($ex2 instanceof Exception);	}		public function testExecuteSql() {		global $insertedId;		$insertedId=-1;		$res=executeSql("select now()");		$this->assertEquals(1,$res);		$this->assertNotEquals($insertedId,-1);				try {			$res=executeSql("select '");		} catch (Exception $ex) {		}		$this->assertTrue(isset($ex));		$this->assertTrue($ex instanceof Exception);	}	public function testExecutePSql() {		$res=executePSql("select :s1, :s2, :s1, now()",array("s1"=>"s1","s2"=>"s2"));	}		public function testExecuteTransSql() {		$trans=array("select now()","select now()");		$res=executeTransSql($trans);		$this->assertTrue($res===true);	}		public function testExecuteTableSql() {		$res=executeTableSql("show tables");		$this->assertTrue(is_resource($res));	}	public function testGetOneValue() {		$res=getOneValue("show tables");		$this->assertTrue(is_string($res));	}		public function testGetOneRow() {		$res=getOneRow("show tables");		$this->assertTrue(is_array($res));	}		public function testGetTableArray() {		$res=getTableArray("show tables");		$this->assertTrue(is_array($res));		$this->assertTrue(count($res)>1);	}	}//end