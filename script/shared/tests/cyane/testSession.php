<?

require_once dirname(__FILE__)."/CyaneAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testSession extends UnitTestCase {

	public function __construct() {
		require_once '../cyane/CcmsSessionHandler.class.php';
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function testAdapterIp() {
    require 'mock/MockSessionAdapter.class.php';
    $a=new MockSessionAdapter();
    
    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='127.0.0.1';
    $v=$a->validateIp('123.123.123.123');
    $this->assertFalse($v);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='123.123.123.123';
    $v=$a->validateIp('127.0.0.1');
    $this->assertFalse($v);
    
    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='127.0.0.1';
    $v=$a->validateIp('127.0.0.2');
    $this->assertTrue($v);

    #--

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='192.168.1.1';
    $v=$a->validateIp('123.123.123.123');
    $this->assertFalse($v);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='123.123.123.123';
    $v=$a->validateIp('192.168.1.1');
    $this->assertFalse($v);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='192.168.100.123';
    $v=$a->validateIp('192.168.1.1');
    $this->assertTrue($v);
    
    #--

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='10.0.1.1';
    $v=$a->validateIp('123.123.123.123');
    $this->assertFalse($v);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='123.123.123.123';
    $v=$a->validateIp('10.0.1.1');
    $this->assertFalse($v);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='10.0.100.123';
    $v=$a->validateIp('10.0.1.1');
    $this->assertTrue($v);
    
    #--
    
    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='123.123.123.123';
    $v=$a->validateIp('8.8.8.8');
    $this->assertFalse($v);
    
    unset($_SERVER["_IP"]);
    unset($_SERVER["REMOTE_ADDR"]);

	}
  
  public function testCache() {
  
    $a=new CcmsSession_ObjectCache();
    $a->open('','unittest');
    $a->initialize();
    
    $r=$a->read('test');
    $this->assertEquals(null,$r);

    $a->write('test','value1');
    $r=$a->read('test');
    $this->assertEquals('value1',$r);
    
    $a->destroy('test');
    $r=$a->read('test');
    $this->assertEquals(null,$r);
    
    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='123.123.123.123';
    $a->write('test','value2');
    $r=$a->read('test');
    $this->assertEquals('value2',$r);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='8.8.8.8';
    $r=$a->read('test');
    $this->assertEquals(null,$r);
    
    unset($_SERVER["_IP"]);
    unset($_SERVER["REMOTE_ADDR"]);

  }
  
  public function testDb() {
  
    $a=new CcmsSession_Db();
    $a->open('','unittest');
    $a->initialize();
    
    $r=$a->read('test');
    $this->assertEquals(null,$r);

    $a->write('test','value1');
    $r=$a->read('test');
    $this->assertEquals('value1',$r);
    
    $a->destroy('test');
    $r=$a->read('test');
    $this->assertEquals(null,$r);
    
    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='123.123.123.123';
    $a->write('test','value2');
    $r=$a->read('test');
    $this->assertEquals('value2',$r);

    $_SERVER["_IP"]=$_SERVER['REMOTE_ADDR']='8.8.8.8';
    $r=$a->read('test');
    $this->assertEquals(null,$r);
    
    unset($_SERVER["_IP"]);
    unset($_SERVER["REMOTE_ADDR"]);

  }
  
  public function testCacheHandler() {
    $a=CcmsSessionHandler::$adapter=new CcmsSession_ObjectCache();
    CcmsSessionHandler::open('','unittest');
    CcmsSessionHandler::$is_new=false;
    CcmsSessionHandler::$is_initialized=false;
    
    $r=CcmsSessionHandler::read('test');
    $this->assertEquals(null,$r);

    CcmsSessionHandler::write('test','value1');
    $r=CcmsSessionHandler::read('test');
    $this->assertEquals('value1',$r);
    
    CcmsSessionHandler::destroy('test');
    $r=CcmsSessionHandler::read('test');
    $this->assertEquals(null,$r);
  }
  
  public function testDbHandler() {
    $a=CcmsSessionHandler::$adapter=new CcmsSession_Db();
    CcmsSessionHandler::open('','unittest');
    CcmsSessionHandler::$is_new=false;
    CcmsSessionHandler::$is_initialized=false;
    
    $r=CcmsSessionHandler::read('test');
    $this->assertEquals(null,$r);

    CcmsSessionHandler::write('test','value1');
    $r=CcmsSessionHandler::read('test');
    $this->assertEquals('value1',$r);
    
    CcmsSessionHandler::destroy('test');
    $r=CcmsSessionHandler::read('test');
    $this->assertEquals(null,$r);
  }

}

//end








