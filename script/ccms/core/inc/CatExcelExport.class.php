<?

class CatExcelExport extends CCMSDomainExcelExport {

  protected function recursiveTreeIds($node,&$result) {
    $result[$node['id']]=$node['id'];
    if (!empty($node['_children'])) foreach ($node['_children'] as $child) $this->recursiveTreeIds($child,$result);
  }
  
  public function getProducts($all) {
    $sql='select * from '.tbl_name('product').' where `active`=1 and tree_id in ('.implode(',',$all).')';
    return getTableArray($sql);
  }
  public function getProductSizes($all) {
    $sql='select * from '.tbl_name('productsize').' where active=1 and product in (select id from '.tbl_name('product').' where `active`=1 and tree_id in ('.implode(',',$all).'))';
    $productsize=array();
    foreach (getTableArray($sql) as $line) {
      $productsize[$line['product']][$line['size']]=$line;
    }
    return $productsize;
  }

  public function export(array $ids=null, array $options=null) {
    if (empty($ids)) return;
    
    $all=array();
    foreach ($ids as $id) {
      $all[$id]=$id;
      $tree=$this->manager->getCatTree($id);
      if (!empty($tree)) $this->recursiveTreeIds($tree,$all);
    }
    if (count($all)==0) return;

    $prod=$this->getProducts($all);
    $productsize=$this->getProductSizes($all);

    $fields=getConfigItem('CatExcelExport.fields',array(
//'active',
'tree_id',
'sku',
'name',
'brand',
'price',
//'option1',
//'option2',
//'option3',
'tax',
//'discount_absolute',
//'discount_percent',
//'discount_start',
//'discount_end',
//'shippingrate',
//'units',
//'quantity',
'stock',
//'_product_sizes_',
//'no_stock_action',
//'deliveryperiod',
//'img',
//'subimg1',
//'subimg2',
//'subimg3',
//'is_new',
//'is_hot',
//'is_home',
//'description'
    ));

    $productModel=new CCMSManagedModel('ProductManager');
    $productManager=$productModel->getDomainManager();
    $list=new CoreGenericList($productModel);

    if (!isset($_SERVER['WINDIR'])) {
      header('Pragma: public');
      header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');                  // Date in the past   
      header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
      header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
      header('Pragma: no-cache');
      header('Expires: 0');
      header('Content-Transfer-Encoding: none');
      header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
      header('Content-type: application/x-msexcel');                    // This should work for the rest
      header('Content-Disposition: attachment; filename="export.xls"'); 
    }

?>
<table>
  <tr>
    <? foreach ($fields as $fieldName) { ?><th><?= $list->domainTranslate($productModel->getName(),$fieldName); ?></th><? } ?>
  </tr>
<? 
foreach ($prod as $line) { 
  if (empty($productsize[$line['id']])) {
?>
  <tr>
    <? foreach ($fields as $fieldName) { ?><td><?= $list->getListValue($productManager,$fieldName,$line,null) ?></td><? } ?>
  </tr>
<? 
  } else {
    foreach ($productsize[$line['id']] as $size) {
      if (!empty($size['sku'])) $line['sku']=$size['sku'];
      if (!empty($size['price'])) $line['price']=$size['price'];
      if (!empty($size['stock'])) $line['stock']=$size['stock'];
?>
  <tr>
    <? foreach ($fields as $fieldName) { ?><td><?= $list->getListValue($productManager,$fieldName,$line,null) ?></td><? } ?>
  </tr>
<? 
    }
  }
}
?>
</table>
<?
    
    //@mysql_close();
    exit();    
  }

}

//end