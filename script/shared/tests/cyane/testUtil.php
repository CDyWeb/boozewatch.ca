<?

require_once dirname(__FILE__)."/CyaneAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testUtil extends UnitTestCase {

	public function __construct() {
		require_once '../cyane/util.inc.php';
    log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function test_w3cDate() {
		$result=w3cDate(time());
		log_message('debug', 'test_w3cDate: '.$result);
		$this->assertEquals(1,preg_match('#^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d[+-]\d\d:\d\d$#',$result));
	}

  //function utf8_ent($s,$style=ENT_QUOTES)
	public function test_utf8_ent() {
    $s='';
    $this->assertEquals('',utf8_ent($s));
    $s='<h1 class="cls">è</h1>';
    $this->assertEquals(htmlentities($s,ENT_QUOTES,"UTF-8"),utf8_ent($s));
	}

	public function test_make_url() {
	}
	public function test_text_limit() {
	}
	public function test_html_limit() {
	}
	
	public function test_getRequestURI() {
		unset($_SERVER["_URI"]);
		$_SERVER["REQUEST_URI"]="/test123/999?456&abc?def";
		$this->assertEquals("/test123/999",getRequestURI());
	}

	public function test_getPermalinkName() {
		$this->assertEquals("aa-bb-cc-dd-eee",getPermalinkName("aa bb[çc&dd'èéë "));
		$this->assertEquals("aa:a!b~bb!c!cc",getPermalinkName(" aa:a b~bb c%cc ",'!'));
		$this->assertEquals("aca-beb-cec",getPermalinkName(utf8_decode(" aça bëb CÈC "),'-','ISO-8859-1'));
	}

	public function test_mb_unserialize() {
		$s=html_entity_decode("&euml;");
		$subject=utf8_encode(serialize(array($s)));
		$result=mb_unserialize($subject);
		$this->assertEquals(utf8_encode($s),$result[0]);
	}

	public function test_getClientIp() {
		global $config;
		unset($config['proxy_ips']);
		
		$this->assertTrue(function_exists('getConfigItem'));
		
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip1='13.4.78.31';
		$_SERVER['REMOTE_ADDR']=$ip1;
		$this->assertEquals($ip1,getClientIp());
		
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip2='10.10.80.29';
		$_SERVER['HTTP_CLIENT_IP']=$ip2;
		$this->assertEquals($ip2,getClientIp());

		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip3='20.7.7.2';
		$_SERVER['HTTP_X_FORWARDED_FOR']=$ip3;
		$this->assertEquals($ip3,getClientIp());

		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']=$ip1;
		$_SERVER['HTTP_CLIENT_IP']=$ip2;
		$this->assertEquals($ip2,getClientIp());
		
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']=$ip1;
		$_SERVER['HTTP_X_FORWARDED_FOR']=$ip3;
		$this->assertEquals($ip1,getClientIp());

		$config['proxy_ips']=$ip1;
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']=$ip1;
		$_SERVER['HTTP_X_FORWARDED_FOR']=$ip3;
		$this->assertEquals($ip3,getClientIp());

		$config['proxy_ips']="{$ip1} {$ip2}";
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']=$ip2;
		$_SERVER['HTTP_X_FORWARDED_FOR']=$ip3;
		$this->assertEquals($ip3,getClientIp());

		$config['proxy_ips']="1.2.3.4 5.6.7.8";
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']=$ip2;
		$_SERVER['HTTP_X_FORWARDED_FOR']=$ip3;
		$this->assertEquals($ip2,getClientIp());
		
		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']='not a valid ip address';
		$this->assertEquals('0.0.0.0',getClientIp());

		unset($_SERVER['_IP']);
		unset($_SERVER['REMOTE_ADDR']);
		unset($_SERVER['HTTP_CLIENT_IP']);
		unset($_SERVER['HTTP_X_FORWARDED_FOR']);
		$_SERVER['REMOTE_ADDR']='1.2.3.456';
		$this->assertEquals('0.0.0.0',getClientIp());
		
		$_SERVER['REMOTE_ADDR']=$_SERVER['_IP']='127.0.0.1';
	}


	public function test_clientIsRobot() {
		$_SERVER["HTTP_USER_AGENT"]="";
		$this->assertFalse(clientIsRobot());
		
		#$_SERVER['REMOTE_ADDR']=$_SERVER['_IP']='65.55.104.58';
		#$this->assertTrue(clientIsRobot());
		
		$_SERVER['REMOTE_ADDR']=$_SERVER['_IP']='127.0.0.1';
		$this->assertFalse(clientIsRobot());
		
		$_SERVER["HTTP_USER_AGENT"]="Bogus";
		$this->assertFalse(clientIsRobot());

		$_SERVER["HTTP_USER_AGENT"]="Bogus Firefox";
		$this->assertFalse(clientIsRobot());

		$_SERVER["HTTP_USER_AGENT"]="Bogus googlebot";
		$this->assertTrue(clientIsRobot());
		
		$_SERVER["HTTP_USER_AGENT"]="";
	}

	public function test_ctype() {
		$this->assertTrue(ctype_alpha("a"));
		$this->assertTrue(ctype_digit("1"));
		$this->assertTrue(ctype_alnum("a1"));
	}
	
	public function test_return_bytes() {
		$this->assertEquals(2048,return_bytes("2k"));
		$this->assertEquals(2048*1024,return_bytes("2m"));
		$this->assertEquals(2048*1024*1024,return_bytes("2g"));
	}
	
	public function test_readdir_ls() {
		$res=readdir_ls(dirname(__FILE__));
		$this->assertTrue(in_array(basename(__FILE__),array_keys($res)));

		try {
			readdir_ls("AAAAAA");
		} catch (Exception $ex) {
		}
		$this->assertTrue(isset($ex));
		$this->assertTrue($ex instanceof Exception);
	}
	
	public function test_read_url() {
		$res=read_url("http://localhost");
		$this->assertTrue(strlen($res)>0);
		
		$res=read_url("http://0.0.0.0/");
		$this->assertTrue($res===false);
	}
	
	public function test_redirect_url() {
		redirect_url("test123",301,true);
		//@todo
		$this->assertTrue(true);
	}
	
	public function test_minutesToSeconds() {
		$this->assertEquals(60*2,minutesToSeconds(2));
	}
	public function test_hoursToSeconds() {
		$this->assertEquals(60*60*2,hoursToSeconds(2));
	}
	public function test_daysToSeconds() {
		$this->assertEquals(24*60*60*2,daysToSeconds(2));
	}
	public function test_weeksToSeconds() {
		$this->assertEquals(7*24*60*60*2,weeksToSeconds(2));
	}
	public function test_yearsToSeconds() {
		$this->assertEquals(365*24*60*60*2,yearsToSeconds(2));
	}
	
}

//end