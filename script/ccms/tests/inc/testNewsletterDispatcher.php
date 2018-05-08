<?

require_once dirname(__FILE__)."/IncAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testNewsletterDispatcher extends UnitTestCase {

	public function __construct() {
		_require ("inc/NewsletterDispatcher.class.php");
		log_message('debug', get_class($this).' UnitTestCase constructed');
		#--
		$this->newsletter=array('id'=>1,'name'=>'phpunit');
		$this->recipients=array(
			1=>array('id'=>1,'email'=>'phpunit@cyane.nl','hash'=>'abc'),
			2=>array('id'=>2,'email'=>'phpunit@cyane.nl','hash'=>'abd'),
			3=>array('id'=>3,'email'=>'phpunit@cyane.nl','hash'=>'abe'),
			4=>array('id'=>4,'email'=>'phpunit@cyane.nl','hash'=>'abf'),
		);
		#--
	}
	
	public function test_getMailer() {
		$mailer=NewsletterDispatcher::getMailer();
		$this->assertTrue(is_object($mailer));
	}

	public function test_init() {
		global $config;
		$config['NewsletterDispatcher.batchSize']=3;
		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		NewsletterDispatcher::init($this->newsletter,array());
		$this->assertTrue(empty($_SESSION[NewsletterDispatcher::SESS_TO]));
		#--
		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		NewsletterDispatcher::init($this->newsletter,$this->recipients);
		$this->assertFalse(empty($_SESSION[NewsletterDispatcher::SESS_TO]));
		$this->assertTrue(is_object($_SESSION[NewsletterDispatcher::SESS_TO]));
		$this->assertTrue($_SESSION[NewsletterDispatcher::SESS_TO] instanceof NewsletterDispatcher);
		$obj=$_SESSION[NewsletterDispatcher::SESS_TO];
		$this->assertEquals($this->recipients,$obj->getRecipients());
		$this->assertEquals($this->newsletter,$obj->getNewsletter());
		$this->assertEquals(3,$obj->getBatchsize());
	}
	
	public function test_fromSession() {
		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		NewsletterDispatcher::init($this->newsletter,array());
		$obj=NewsletterDispatcher::fromSession();
		$this->assertEquals(null,$obj);
		#--
		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		NewsletterDispatcher::init($this->newsletter,$this->recipients);
		$obj=NewsletterDispatcher::fromSession();
		$this->assertTrue(is_object($obj));
		$this->assertTrue($obj instanceof NewsletterDispatcher);
		$this->assertEquals($this->recipients,$obj->getRecipients());
		$this->assertEquals($this->newsletter,$obj->getNewsletter());		
	}
	
	public function test_toSession() {
		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		NewsletterDispatcher::init($this->newsletter,$this->recipients);
		$obj=NewsletterDispatcher::fromSession();
		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		$obj->toSession();
		$this->assertEquals($obj,$_SESSION[NewsletterDispatcher::SESS_TO]);
	}
	
	public function test_dispatch() {
	
		require_once(SHARED_PATH.'/tests/mock/MockMySqlWrapper.class.php');
		$mysql=MySqlWrapper::getInstance();
		MySqlWrapper::setInstance(new MockMySqlWrapper());

		require_once(SHARED_PATH.'/tests/mock/MockHtmlMimeMail5.class.php');
		$mailer=NewsletterDispatcher::getMailer();
		NewsletterDispatcher::setMailer(new MockHtmlMimeMail5());
	
		global $config;
		$config['NewsletterDispatcher.batchSize']=3;
		
		$_SESSION["user"]["email"]='from@cyane.nl';
		$_SESSION["user"]["first_name"]='John';
		$_SESSION["user"]["last_name"]='Doe';

		unset($_SESSION[NewsletterDispatcher::SESS_TO]);
		NewsletterDispatcher::init($this->newsletter,array());
		$result=NewsletterDispatcher::dispatch();
		$this->assertTrue($result);
		
		#--
		NewsletterDispatcher::init($this->newsletter,array(array('id'=>null,'email'=>'erwin@cyane.nl')));
		$obj=NewsletterDispatcher::fromSession();
		$result=NewsletterDispatcher::dispatch();
		$this->assertTrue($result);
		$this->assertEquals(0,NewsletterDispatcher::$todo);
		$this->assertEquals(0,count($obj->getRecipients()));
		
		#--
		NewsletterDispatcher::init($this->newsletter,$this->recipients);
		$result=NewsletterDispatcher::dispatch();
		$this->assertFalse($result);
		$this->assertEquals(1,NewsletterDispatcher::$todo);
		
		$obj=NewsletterDispatcher::fromSession();
		$this->assertEquals(1,count($obj->getRecipients()));
		$this->assertEquals(current($this->recipients),current($obj->getRecipients()));

		MySqlWrapper::setInstance($mysql);
		NewsletterDispatcher::setMailer($mailer);
	}

}

//end












