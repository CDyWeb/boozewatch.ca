<?

require 'Tree.php';

$fixtures[tbl_name('product')]=array(
  1=>array(
    'id'=>1,
    'tree_id'=>1101,
    'sku'=>'1',
    'name'=>'prod1',
    'price'=>'1.00',
    'stock'=>1,
  ),
  2=>array(
    'id'=>2,
    'tree_id'=>1101,
    'sku'=>'2',
    'name'=>'prod2',
    'price'=>'2.00',
    'stock'=>null,
  ),
  3=>array(
    'id'=>3,
    'tree_id'=>1101,
    'sku'=>'3',
    'name'=>'prod3',
    'price'=>'3.00',
    'stock'=>null,
  ),
);

require 'ProductSize.php';

//end