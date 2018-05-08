<?

require 'Settings.php';
require 'Aliasdomain.php';
require 'Tree.php';

$fixtures[tbl_name('page')]=array(
  array(
    'id'=>1,
    'tree_id'=>1001,
    'name'=>'home',
  ),
  array(
    'id'=>2,
    'tree_id'=>1001,
    'name'=>'account',
    'page_type'=>'plugin',
    'attributes'=>'customer',
  ),
  array(
    'id'=>3,
    'tree_id'=>1001,
    'name'=>'photos',
    'page_type'=>'plugin',
    'attributes'=>'gallery',
  ),
  array(
    'id'=>4,
    'tree_id'=>1001,
    'name'=>'contact',
    'page_type'=>'plugin',
    'attributes'=>'mailform',
  ),
  array(
    'id'=>5,
    'tree_id'=>1001,
    'name'=>'blog',
    'page_type'=>'plugin',
    'attributes'=>'news',
  ),
  array(
    'id'=>6,
    'tree_id'=>1001,
    'name'=>'collection',
    'page_type'=>'plugin',
    'attributes'=>'products',
  ),
  array(
    'id'=>7,
    'tree_id'=>1001,
    'name'=>'newsletter',
    'page_type'=>'plugin',
    'attributes'=>'newsletter',
  ),
  array(
    'id'=>8,
    'tree_id'=>1001,
    'name'=>'shop',
    'page_type'=>'plugin',
    'attributes'=>'webshop',
  ),
);

//end