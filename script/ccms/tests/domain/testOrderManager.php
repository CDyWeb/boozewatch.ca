<?

class testOrderManager extends UnitTestCase {

	public function __construct() {
		log_message('debug', get_class($this).' UnitTestCase constructed');
    $this->manager=new OrderManager();
	}
  
  public function setUp() {
    require '_fixtures/Order.php';
    $this->setUpFixtures($fixtures);
  }
  public function tearDown() {
    require '_fixtures/Order.php';
    $this->tearDownFixtures($fixtures);
  }
/**/
	public function test_createTable() {
    executeSql('truncate table `'.tbl_name('order').'`');
    executeSql('drop table `'.tbl_name('voucher').'`');
    executeSql('drop table `'.tbl_name('order_product_log').'`');
    executeSql('drop table `'.tbl_name('order_log').'`');
    executeSql('drop table `'.tbl_name('order').'`');
    $this->manager->createTable();
    $m=getTableArray('describe `'.tbl_name('order').'`','Field');
    $this->assertTrue(!empty($m['id']));
    $m=getTableArray('describe `'.tbl_name('order_log').'`','Field');
    $this->assertTrue(!empty($m['order']));
    $m=getTableArray('describe `'.tbl_name('order_product_log').'`','Field');
    $this->assertTrue(!empty($m['order']));
  }
/**/
	public function test_getOrderBy() {
    $orderBy=$this->manager->getOrderBy();
    $arr=$this->manager->getAllExt(null,array($orderBy));
    $this->assertTrue(count($arr)>0);
  }
/**/
	public function test_getItemName() {
    $order=$this->manager->get(1);
    $this->assertEquals('1',$this->manager->getItemName($order));
  }
/**/
	public function test_orderPayed() {
    file_put_contents($ev=APP_PATH.'frontend/plugin_webshop_on_order_payed.inc','<?= define("on_order_payed_".$id,$id); ?>');
    #--
    $order=$this->manager->get(1);
    $this->manager->orderPayed($order);
    $this->assertTrue(defined('on_order_payed_1'));
    #--
    unlink($ev);
  }
/**/
	public function test_orderCancelled() {
    global $config;
    $config['OrderManager.stock']=false;
    file_put_contents($ev=APP_PATH.'frontend/plugin_webshop_on_order_cancelled.inc','<?= define("on_order_cancelled_".$id,$id); ?>');
    #--
    $order=$this->manager->get(1);
    $this->manager->orderCancelled($order);
    $this->assertTrue(defined('on_order_cancelled_1'));
    #--
    unlink($ev);
    unset($config['OrderManager.stock']);
  }
/**/
	public function test_setStatus() {
    file_put_contents($ev1=APP_PATH.'frontend/plugin_webshop_on_order_payed.inc','<?= define("on_order_payed_".$id,$id); ?>');
    file_put_contents($ev2=APP_PATH.'frontend/plugin_webshop_on_order_cancelled.inc','<?= define("on_order_cancelled_".$id,$id); ?>');
    $productManager=new ProductManager();
    $productSizeManager=new ProductSizeManager();
    $sizeManager=new SizeManager();
    #--
    $this->manager->setStatus(1,'sent');
    $order=$this->manager->get(1);
    $this->assertEquals('sent',$order['status']);
    #--
    $this->manager->setStatus(2,'payed');
    $this->assertTrue(defined('on_order_payed_2'));
    $order=$this->manager->get(2);
    $this->assertEquals('payed',$order['status']);
    #--
    
    $product1_before=$productManager->get(1);
    $product2_before=$productManager->get(2);
    $product3_before=$productManager->get(3);
    $order_before=$this->manager->get(3);

    $cart_before=unserialize($order['cart']);
    $ps2_before=$productSizeManager->get($cart_before[2]['product_size']);
    $ps7_before=$productSizeManager->get($cart_before[7]['product_size']);
    $ps8_before=$productSizeManager->get($cart_before[8]['product_size']);
    $ps9_before=$productSizeManager->get($cart_before[9]['product_size']);
    
    $this->manager->setStatus(3,'cancelled');
    $this->assertTrue(defined('on_order_cancelled_3'));
    
    $order_after=$this->manager->get(3);
    $this->assertEquals('cancelled',$order_after['status']);
    
    $product1_after=$productManager->get(1);
    $product2_after=$productManager->get(2);
    $product3_after=$productManager->get(3);

    $cart_after=unserialize($order_after['cart']);
    $ps2_after=$productSizeManager->get($cart_after[2]['product_size']);
    $ps7_after=$productSizeManager->get($cart_after[7]['product_size']);
    $ps8_after=$productSizeManager->get($cart_after[8]['product_size']);
    $ps9_after=$productSizeManager->get($cart_after[9]['product_size']);

    $this->assertEquals($product1_before['stock']+7 ,$product1_after['stock']);
    $this->assertEquals($product2_before,$product2_after);
    $this->assertEquals($product3_before,$product3_after);
    
    $this->assertEquals($ps2_before['stock']+2,$ps2_after['stock']);
    $this->assertEquals(null,$ps7_after['stock']);
    $this->assertEquals($ps8_before['stock']+10,$ps8_after['stock']);
    $this->assertEquals($ps8_after,$ps9_after);
        
    #--
    unlink($ev1);
    unlink($ev2);
  }
/**/
	public function test_save() {
    
  }
/**/
}
  
//end