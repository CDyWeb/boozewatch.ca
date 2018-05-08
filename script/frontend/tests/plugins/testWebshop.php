<?

require_once dirname(__FILE__)."/PluginsAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

if (!function_exists('setting')) {
  function setting($k) { return null; }
}

class testWebshop extends UnitTestCase {

	public function __construct() {
		log_message('debug', get_class($this).' UnitTestCase constructed');
    global $config,$site_config;
    $config['webshop.payment_methods']='in_advance,ideal';
    $config['webshop.ideal.impl']='rabo_lite';
    $site_config['domain']='localhost';
    $site_config['host_base']=$site_config['domain'];
    $site_config['url_server']='http://localhost';
    
    $this->lang_prefix='';
    if (isset($site_config['language']['selector']) && ($site_config['language']['selector']=='uri-prefix')) $this->lang_prefix='/'.$site_config['language']['base'];
    
  }
  
  public function setUp() {
    require '_fixtures/Page.php';
    require '_meta/Page.php';
    require '_fixtures/Order.php';
    require '_meta/Order.php';
    $this->setUpFixtures($fixtures,$meta);
  }
  
  public function clear_env($withSession) {
    $_POST=array();
    $_GET=array();
    $_COOKIE=array();
    $_REQUEST=array();
    $_SERVER["REQUEST_METHOD"]='GET';
    $_SERVER["REMOTE_ADDR"]='127.0.0.1';
    $_SERVER["HTTP_USER_AGENT"]='PHPUnit';
    if ($withSession) $_SESSION=array();
    $this->frontend=new Frontend();
  }

  public function tearDown() {
    require '_fixtures/Page.php';
    $this->tearDownFixtures($fixtures);
  }
/**
	public function test_Cart() {
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['clear']=$_REQUEST['clear']='1';
    $this->frontend->render();
    $this->assertFalse(empty($_SESSION["webshop"]));
    $webshop=unserialize($_SESSION["webshop"]);
    $this->assertTrue($webshop instanceof Webshop);

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();
    $this->assertFalse(empty($_SESSION["webshop"]));
    $webshop=unserialize($_SESSION["webshop"]);
    $this->assertTrue($webshop instanceof Webshop);
    $cart=$webshop->getCart();
    $this->assertFalse(empty($cart));
    $this->assertTrue($cart instanceof Cart);
    $products=$cart->getProducts();
    $this->assertTrue(is_array($products));
    $this->assertEquals(count($products),1);
    $product=$cart->getProduct(0);
    $this->assertFalse(empty($product));
    $this->assertTrue($product instanceof Product);
    $this->assertEquals($product->id,1);
  }
/**
  public function test_Account() {
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->assertFalse(is_customer());

    $this->clear_env(false);
    $_SERVER["REQUEST_METHOD"]='POST';
    $_POST['login']='test@test.com';
    $_POST['password']='test';
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->assertTrue(is_customer());
  }
/**
  public function test_Payment() {
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_METHOD"]='POST';
    $_POST['login']='test@test.com';
    $_POST['password']='test';
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();
    $this->assertTrue(is_customer());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $this->frontend->render();
    $webshop=unserialize($_SESSION["webshop"]);
    $order=$webshop->getOrder();
    $this->assertEquals('in_advance',$order->getPayment());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $_GET['payment_method']=$_REQUEST['payment_method']='bogus';
    $this->frontend->render();
    $webshop=unserialize($_SESSION["webshop"]);
    $order=$webshop->getOrder();
    $this->assertEquals('in_advance',$order->getPayment());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $_GET['payment_method']=$_REQUEST['payment_method']='ideal';
    $this->frontend->render();
    $webshop=unserialize($_SESSION["webshop"]);
    $order=$webshop->getOrder();
    $this->assertEquals('ideal',$order->getPayment());
  }

/**
  public function test_Order() {
    executeSql('TRUNCATE TABLE `test_order`');
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_METHOD"]='POST';
    $_POST['login']='test@test.com';
    $_POST['password']='test';
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();
    $this->assertTrue(is_customer());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $_REQUEST["_place_order"]=$_GET["_place_order"]=1;
    $this->frontend->render();
    
    $webshop=unserialize($_SESSION["webshop"]);
    $cart=$webshop->getCart();
    $products=$cart->getProductsToArray();

    $arr=getTableArray('select * from test_order');
    $this->assertTrue(count($arr)==1);
    $line=current($arr);
    $this->assertFalse(empty($line['cart']));
    $this->assertEquals(serialize($products),$line['cart']);
  }
/**
  public function test_Pay_iDEAL_click() {
    executeSql('TRUNCATE TABLE `test_order`');
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_METHOD"]='POST';
    $_POST['login']='test@test.com';
    $_POST['password']='test';
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();
    $this->assertTrue(is_customer());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $_GET['payment_method']=$_REQUEST['payment_method']='ideal';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $_REQUEST["_place_order"]=$_GET["_place_order"]=1;
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/pay';
    $this->frontend->render();
    
    $arr=getTableArray('select * from test_order');
    $line=current($arr);
    $this->assertEquals('ideal',$line['payment']);
    $this->assertEquals('new',$line['status']);
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/ideal/success.html';
    $this->frontend->render();

    $arr=getTableArray('select * from test_order');
    $line=current($arr);
    $this->assertEquals('ideal',$line['payment']);
    $this->assertEquals('in_process',$line['status']);
  }
/**/
  public function test_Pay_iDEAL_notify() {
    executeSql('TRUNCATE TABLE `test_order`');
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_METHOD"]='POST';
    $_POST['login']='test@test.com';
    $_POST['password']='test';
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();
    $this->assertTrue(is_customer());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $_GET['payment_method']=$_REQUEST['payment_method']='ideal';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $_REQUEST["_place_order"]=$_GET["_place_order"]=1;
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/pay';
    $this->frontend->render();
    
    $arr=getTableArray('select * from test_order');
    $line=current($arr);
    $this->assertEquals('ideal',$line['payment']);
    $this->assertEquals('new',$line['status']);
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/ideal/notify.html';
    global $notify_xml;
    $notify_xml=<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Notification xmlns="http://www.idealdesk.com/Message" version="1.1.0">
<createDateTimeStamp>20090427235541</createDateTimeStamp>
  <transactionID>0050000013465160</transactionID>
  <purchaseID>1</purchaseID>
  <status>Success</status>
</Notification>
XML;
    $this->frontend->render();

    $arr=getTableArray('select * from test_order');
    $line=current($arr);
    $this->assertEquals('ideal',$line['payment']);
    $this->assertEquals('payed',$line['status']);
  }
/**
  public function test_Pay_in_advance() {
    executeSql('TRUNCATE TABLE `test_order`');
    $this->clear_env(true);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop';
    $_GET['add']=$_REQUEST['add']='1';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_METHOD"]='POST';
    $_POST['login']='test@test.com';
    $_POST['password']='test';
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/account';
    $this->frontend->render();
    $this->assertTrue(is_customer());
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/payment';
    $_GET['payment_method']=$_REQUEST['payment_method']='in_advance';
    $this->frontend->render();

    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/order';
    $_REQUEST["_place_order"]=$_GET["_place_order"]=1;
    $this->frontend->render();
    
    $this->clear_env(false);
    $_SERVER["REQUEST_URI"]=$_SERVER["_URI"]=$this->lang_prefix.'/shop/pay';
    $this->frontend->render();
  }

/**
  public function test_Confirm() {
  }
/**/
}

//end