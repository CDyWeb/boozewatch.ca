<?

require 'Settings.php';
require 'Aliasdomain.php';

$fixtures[tbl_name('tree')]=array(
  array(
    'id'=>1,
    'name'=>'Website',
  ),
  array(
    'id'=>11,
    'name'=>'Shop',
    'parent_id'=>1,
  ),
  array(
    'id'=>91,
    'name'=>'Misc',
    'parent_id'=>1,
  ),
  array(
    'id'=>902,
    'name'=>'Settings',
    'parent_id'=>91,
  ),
  array(
    'id'=>905,
    'name'=>'Users',
    'parent_id'=>91,
  ),
  array(
    'id'=>1001,
    'name'=>'Menu top',
    'parent_id'=>1,
  ),
  array(
    'id'=>1002,
    'name'=>'Menu left',
    'parent_id'=>1,
  ),
  array(
    'id'=>1003,
    'name'=>'Menu right',
    'parent_id'=>1,
  ),
  array(
    'id'=>1004,
    'name'=>'Menu bottom',
    'parent_id'=>1,
  ),
);

//end