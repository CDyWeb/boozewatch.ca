<?

require_once dirname(__FILE__)."/ModelAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testUserModel extends UnitTestCase {

	public function __construct() {
    $this->model=new UserModel();
    
    require_once dirname(__FILE__)."/../mock/MockCcmsPing.class.php";
    $this->model->pinger=new MockCcmsPing();
    
    log_message('debug', get_class($this).' UnitTestCase constructed');
	}
  
  public function setUp() {
    #require '_fixtures/Order.php';
    #$this->setUpFixtures($fixtures);
  }
  public function tearDown() {
    #require '_fixtures/Order.php';
    #$this->tearDownFixtures($fixtures);
  }

  public function test_isUser() {
    $isUser=$this->model->isUser();
    $this->assertFalse($isUser);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'user');
    $isUser=$this->model->isUser();
    $this->assertTrue($isUser);
    #--
    unset($_SESSION["user"]);
  }
  
  public function test_isAdmin() {
    $isAdmin=$this->model->isAdmin();
    $this->assertFalse($isAdmin);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'user');
    $isAdmin=$this->model->isAdmin();
    $this->assertFalse($isAdmin);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'super');
    $isAdmin=$this->model->isAdmin();
    $this->assertTrue($isAdmin);
    #--
    unset($_SESSION["user"]);
  }
  
  public function test_isTechAdmin() {
    $isTechAdmin=$this->model->isTechAdmin();
    $this->assertFalse($isTechAdmin);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'user');
    $isTechAdmin=$this->model->isTechAdmin();
    $this->assertFalse($isTechAdmin);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'super','tech_admin'=>'0');
    $isTechAdmin=$this->model->isTechAdmin();
    $this->assertFalse($isTechAdmin);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'super','tech_admin'=>'1');
    $isTechAdmin=$this->model->isTechAdmin();
    $this->assertTrue($isTechAdmin);
    #--
    unset($_SESSION["user"]);
  }
  
  public function test_userPref() {
    $test=$this->model->userPref('test','test123');
    $this->assertEquals(null,$test);
    #--
    $_SESSION["user"]=array('id'=>1,'user_type'=>'super','tech_admin'=>'1');
    $test=$this->model->userPref('test','test123');
    $this->assertEquals('test123',$test);
    $test=$this->model->userPref('test');
    $this->assertEquals('test123',$test);
    unset($_SESSION["user"]);
  }
  
  public function test_authUser() {
    $err=null;
    $user=$this->model->authUser('test123','test123',$err);
    $this->assertFalse($user);
    $this->assertEquals('user not found',$err);
    #--
    executeSql("replace into {$this->model->getTableName()} set id=9, email='unit-test@cdyweb.com', password='".sha1("ccms:test123#!")."', first_name='Unit', last_name='Test', user_type='user', tech_admin=0, login_count=0");
    #--
    unset($_SESSION["user"]);
    $user=$this->model->authUser('unit-test@cdyweb.com','wrong',$err);
    $this->assertFalse($user);
    $this->assertEquals('wrong password',$err);
    $this->assertTrue(empty($_SESSION['user']));
    $this->assertFalse($this->model->isUser());
    unset($_SESSION["user"]);
    #--
    $user=$this->model->authUser('unit-test@cdyweb.com','test123#!',$err);
    $this->assertTrue(is_array($user));
    $this->assertEquals(9,$user['id']);
    $this->assertEquals(null,$err);
    $this->assertEquals($user,$_SESSION['user']);
    $this->assertTrue($this->model->isUser());
    unset($_SESSION["user"]);
    #--
    executeSql("update {$this->model->getTableName()} set password='662e708c1d3c9264fe72909b551950759dfd8435' where id=1");
    $user=$this->model->authUser('ek@cdyweb.com','test123#!',$err);
    $this->assertFalse($user);
    $val=getOneValue("select password from {$this->model->getTableName()} where id=1");
    $this->assertEquals('662e708c1d3c9264fe72909b551950759dfd8435',$val);
    #--
    executeSql("update {$this->model->getTableName()} set password='bogus' where id=1");
    $user=$this->model->authUser('ek@cdyweb.com','test123#!',$err);
    $this->assertFalse($user);
    $val=getOneValue("select password from {$this->model->getTableName()} where id=1");
    $this->assertEquals('662e708c1d3c9264fe72909b551950759dfd8435',$val);
    #--
    executeSql("update {$this->model->getTableName()} set password='".sha1("ccms:test123#!")."' where id=1");
    $user=$this->model->authUser('ek@cdyweb.com','test123#!',$err);
    $this->assertTrue(is_array($user));
    $this->assertEquals(1,$user['id']);
    $val=getOneValue("select password from {$this->model->getTableName()} where id=1");
    $this->assertEquals(sha1("ccms:test123#!"),$val);
    #--
    executeSql("update {$this->model->getTableName()} set created_by=null");
    executeSql("delete from {$this->model->getTableName()} where id=1");
    $val=getOneValue("select password from {$this->model->getTableName()} where id=1");
    $this->assertEquals(null,$val);

    $user=$this->model->authUser('ek@cdyweb.com','test123#!',$err);
    $this->assertFalse($user);
    $val=getOneValue("select password from {$this->model->getTableName()} where id=1");
    $this->assertEquals('662e708c1d3c9264fe72909b551950759dfd8435',$val);
    #--
    unset($_SESSION["user"]);
  }
  
  
  
}
  
//end