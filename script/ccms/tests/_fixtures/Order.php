<?

require 'Product.php';
require 'Customer.php';

$cart=array(
  1=>$fixtures[tbl_name('product')][1],
  2=>$fixtures[tbl_name('product')][2],
  3=>$fixtures[tbl_name('product')][3],
  
  4=>$fixtures[tbl_name('product')][1],
  5=>$fixtures[tbl_name('product')][1],
  6=>$fixtures[tbl_name('product')][1],
 
  7=>$fixtures[tbl_name('product')][2],
  8=>$fixtures[tbl_name('product')][2],
  9=>$fixtures[tbl_name('product')][2],
  
  10=>$fixtures[tbl_name('product')][3],
  11=>$fixtures[tbl_name('product')][3],
  12=>$fixtures[tbl_name('product')][3],
);

$cart[1]['amount']=1;
$cart[2]['amount']=2;
$cart[3]['amount']=3;

$cart[4]['amount']=3;
$cart[5]['amount']=2;
$cart[6]['amount']=1;

$cart[7]['amount']=5;
$cart[8]['amount']=5;
$cart[9]['amount']=5;

$cart[10]['amount']=3;
$cart[11]['amount']=3;
$cart[12]['amount']=3;

$cart[2]['product_size']=1;
$cart[7]['product_size']=2;
$cart[8]['product_size']=3;
$cart[9]['product_size']=3;

$fixtures[tbl_name('order')]=array(
  array(
    'id'=>1,
    'order_id'=>1,
    'uid'=>sha1(1),
    'customer'=>1,
    'customer_details'=>serialize(array()),
    'customer_name'=>'John Smith',
    'cart'=>serialize($cart),
    'printed'=>0,
    'currency'=>'EUR',
    'date_insert'=>'2010-01-01',
    'date_update'=>'2010-01-01',
    'am_subtotal'=>'100.00',
    'am_tax'=>'19.00',
    'am_total'=>'119.00',
  ),
  array(
    'id'=>2,
    'order_id'=>2,
    'uid'=>sha1(2),
    'customer'=>NULL,
    'customer_details'=>serialize(array()),
    'customer_name'=>'John Smith',
    'cart'=>serialize($cart),
    'printed'=>0,
    'currency'=>'EUR',
    'date_insert'=>'2010-02-02',
    'date_update'=>'2010-02-02',
    'am_subtotal'=>'200.00',
    'am_tax'=>'19.00',
    'am_total'=>'219.00',
  ),
  array(
    'id'=>3,
    'order_id'=>3,
    'uid'=>sha1(3),
    'customer'=>1,
    'customer_details'=>serialize(array()),
    'customer_name'=>'John Smith',
    'cart'=>serialize($cart),
    'printed'=>0,
    'currency'=>'EUR',
    'date_insert'=>'2010-03-03',
    'date_update'=>'2010-03-03',
    'am_subtotal'=>'300.00',
    'am_tax'=>'19.00',
    'am_total'=>'319.00',
  ),
);

//end