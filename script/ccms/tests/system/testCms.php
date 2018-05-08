<?

class testCms extends UnitTestCase {

	public function __construct() {
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function testTest() {
		$this->assertTrue(defined("BASE_PATH"));
		$this->assertTrue(defined("CMS_PATH"));
		$this->assertTrue(defined("UNIT_TESTING"));
	}
	
	public function testGetResourcePath() {
		touch($path1=CMS_PATH."/../".($fn="test123.test"));
		$this->assertEquals($path1,getResourcePath($fn));

		touch($path2=CMS_PATH."/".($fn="test123.test"));
		$this->assertEquals($path2,getResourcePath($fn));
		$this->assertNotEquals($path2,$path1);

		touch($path3=CMS_PATH."/core/".($fn="test123.test"));
		$this->assertEquals($path3,getResourcePath($fn));
		$this->assertNotEquals($path3,$path2);

		touch($path4=CMS_PATH."/custom/".($fn="test123.test"));
		$this->assertEquals($path4,getResourcePath($fn));
		$this->assertNotEquals($path4,$path3);
		
		$this->assertFalse(getResourcePath("bogus"));

		unlink($path4);
		unlink($path3);
		unlink($path2);
		unlink($path1);
	}
	
	public function testRequire() {
		global $test123;
		
		$fp=fopen($path1=CMS_PATH."/core/".($fn="test123.test.php"),"wb");
		fwrite($fp,'<? global $test123; $test123=true; ');
		fclose($fp);
		_require("test123.test.php");
		$this->assertTrue(isset($test123));
		$this->assertTrue($test123);

		$fp=fopen($path2=CMS_PATH."/custom/".($fn="test123.test.php"),"wb");
		fwrite($fp,'<? global $test123; $test123=false; ');
		fclose($fp);
		_require("test123.test.php");
		$this->assertFalse($test123);

		unlink($path2);
		unlink($path1);

		try {
			_require("AAAAAA");
		} catch (Exception $ex) {
		}
		$this->assertTrue(isset($ex));
		$this->assertTrue($ex instanceof Exception);
	}
	
	public function testAutoload() {
		foreach (array("lib","domain","model","view","view/domain","controller") as $p) {
			$class=ucfirst(preg_replace("#[^\w]#","",$p))."Test123";
			$fn="{$class}.class.php";
			$fp=fopen($path1=CMS_PATH."/core/{$p}/{$fn}","wb");
			fwrite($fp,"<? class {$class} { } ");
			fclose($fp);
			$this->assertTrue(is_object(new $class()));
			unlink($path1);
		}
		
		$res=__autoload("AAAAAA");
		$this->assertEquals(false,$res);
	}

	
}

//end