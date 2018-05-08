<?

require_once dirname(__FILE__)."/PluginsAllTests.php";
require_once dirname(__FILE__)."/../mock/MockSetting.php";

if (!class_exists('Page')) {
  class Page {
    public $id=1;
  }
}

class testMailform extends UnitTestCase {

	public function __construct() {
    $this->page=new Page();
		log_message('debug', get_class($this).' UnitTestCase constructed');
    global $site_config;
    $site_config['mock-db-mailer']=true;
	}

  public function uri() {
    return '/';
  }
	
  public function testValidation() {
    require '../plugins/mailform/settings.inc.php';
    $_POST['text']='test <a href="#"> test';
		require '../plugins/mailform/post.inc';
    $this->assertFalse($ok);
    $this->assertTrue(isset($retry_uri));
    $this->assertEquals('/?_input_text=invalid',$retry_uri);
    
    unset($retry_uri);
    unset($ok);
    $_POST['text']='test test';
    global $config;
    $config['plugin.mailform.captcha']='cpt';
		require '../plugins/mailform/post.inc';
    $this->assertFalse($ok);
    $this->assertTrue(isset($retry_uri));
    $this->assertEquals('/?_input_text=test+test',$retry_uri);
    
    unset($retry_uri);
    unset($ok);
    $_POST['cpt']='test123';
		require '../plugins/mailform/post.inc';
    $this->assertFalse($ok);
    $this->assertTrue(isset($retry_uri));
    $this->assertEquals('/?_input_text=test+test',$retry_uri);

    unset($retry_uri);
    unset($ok);
    $_COOKIE['cpt']='foo';
		require '../plugins/mailform/post.inc';
    $this->assertFalse($ok);
    $this->assertTrue(isset($retry_uri));
    $this->assertEquals('/?_input_text=test+test',$retry_uri);
    
    unset($retry_uri);
    unset($ok);
    $_COOKIE['security_code']=sha1('s3cr3t:'.$_SERVER['HTTP_HOST'].':test123:cdyweb');
		require '../plugins/mailform/post.inc';
    $this->assertTrue($ok);
    $this->assertFalse(isset($retry_uri));
  }
  
	public function testPost() {
    require '../plugins/mailform/settings.inc.php';
    
    global $site_config;
    $site_config['mock-db-mailer']=false;
    
    file_put_contents(dirname(__FILE__).'/upload.txt','test123');
    $_FILES=array(
      'upload'=>array(
        'name'=>'upload.txt',
        'tmp_name'=>dirname(__FILE__).'/upload.txt',
      )
    );

		require '../plugins/mailform/post.inc';
    $this->assertTrue(isset($redirect));
    $this->assertEquals('/?fail',$redirect);
    echo $_SESSION['plugin.mailform.error'];

    global $site_config;
    $site_config['mock-db-mailer']=true;

		require '../plugins/mailform/post.inc';
    $this->assertTrue(isset($redirect));
    $this->assertEquals('/?ok',$redirect);
    #echo $_SESSION['plugin.mailform.error'];    
	}
  
}

//end