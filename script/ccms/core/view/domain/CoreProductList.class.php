<?

class CoreProductList extends GenericList {

  public $withCheckboxes=true;
  
  protected function getSizes() {
    if (empty($this->sizes)) $this->sizes=getTableArray('select * from '.tbl_name('size'),'id');
    return $this->sizes;
  }
  
  public function getListValue($manager,$fieldName,$line,$maxlength) {
    if ($fieldName=='stock') {
      if (is_array($line['stock'])) {
        $sizes=$this->getSizes();
        $res=array();
        $alwaysStock=getConfigItem('ProductSizeManager.alwaysStock',false);
        foreach ($line['stock'] as $size=>$stock) {
          if ($alwaysStock && ($stock==0)) continue;
          $res[]=$sizes[$size]['name'].': '.intval($stock);
        }
        $res=implode('<br />',$res);
      } else {
        $res=parent::getListValue($manager,$fieldName,$line,$maxlength);
      }
      if (isset($_GET['change_stock'])) return $res;
      return '<a id="change_stock_'.$line['id'].'" href="'.$_SERVER['_URI'].'?change_stock='.$line['id'].'" class="fancybox" style="text-decoration:underline">'.$res.'</a>';
    }
    if ($fieldName=='_discount') {
      $res='';
      if (!empty($line['discount_absolute'])) $res=$this->formatCurrency($line['discount_absolute']);
      if (!empty($line['discount_percent'])) $res=str_replace('.00','',$line['discount_percent']).' %';
      if (!empty($line['discount_start'])) $res.='<br />'.$this->domainTranslate('Product._list.discount_start').': '.$line['discount_start'];
      if (!empty($line['discount_end'])) $res.='<br />'.$this->domainTranslate('Product._list.discount_end').': '.$line['discount_end'];
      return $res;
    }
    return parent::getListValue($manager,$fieldName,$line,$maxlength);
  }
  
  public function getColumnWidth($manager,$fieldName) {
    if ($fieldName=='stock') return 130;
    return parent::getColumnWidth($manager,$fieldName);
  }

	protected function getRowButtons(CCMSDomainManagerInterface $manager) {
		$res=parent::getRowButtons($manager);
		if ($this->getManager()->isAddable()) array_unshift($res,"duplicate");
		return $res;
	}
	
	protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
    if (getConfigItem('products_multi_cat') && ($btnName=='delete')) {
      if (isset($_GET['unlink']) && ($_GET['unlink']==$line['id'])) {
        $this->getManager()->unlinkMulti($line['id'],$_SESSION['Product.tree_id']);
        redirect_url($_SERVER['_URI']);
        exit();
      }
      if (isset($_SESSION['Product.tree_id']) && ($_SESSION['Product.tree_id']!=$line['tree_id'])) return 
"<td width='20'>
  <a href='{$this->lineUrl}?unlink={$line["id"]}' onclick=\"return window.confirm('{$this->_("Unlink.confirm",str_replace('&quot;','`',htmlentities($this->getModel()->getItemName($line),ENT_QUOTES,'UTF-8')))}')\">
    <img height='16' src='{$this->resources_url('/img/icon/cancel.png')}' alt='' title='{$this->_("Unlink")}' border='0' />
  </a>
</td>";
      $this->lineUrl=$this->uri($line);
      return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
    }
    if ($btnName=='duplicate') {
			return 
"<td width='25'>
	<a href='{$this->uri($line)}?duplicate={$line["id"]}'>
		<img height='16' src='{$this->resources_url('/img/icon/duplicate.png')}' alt='' title='{$this->cmsTranslate("Duplicate")}' border='0' />
	</a>
</td>";
		}
		return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
	}
  
  public function change_stock() {
    if (isset($_GET['change_stock'])) {
      $line=current($this->getManager()->getAllExtLimit(array('id='.intval($_GET['change_stock']))));
      if (empty($line)) die('Product not found: '.intval($_GET['change_stock']));

      if ($_SERVER['REQUEST_METHOD']=='POST') {
        if (isset($_POST['stock'])) $data=array('stock'=>intval($_POST['stock']));
        if (isset($_POST['sizes'])) $data=array('_product_sizes_'=>$_POST['sizes']);
        $err=array();
        $res=$this->getManager()->save($line['id'],$data,$err);
        if (!$res || !empty($err)) {
          echo 'ERROR';
          exit();
        }
        $line=current($this->getManager()->getAllExtLimit(array('id='.intval($_GET['change_stock']))));
        echo $this->getListValue($this->getManager(),'stock',$line,0);
        exit();
      }

      if (is_array($line['stock'])) {
        $sizes=$this->getSizes();
?>

<div style="width:300px;min-height:200px; text-align:center;">
<form>
  <p>
    <b><?= trim($line['sku'].' '.$line['name']) ?></b>
  </p>
  <table>
    <tr><td colspan="2">
      <label><?= $this->domainTranslate('Product.stock') ?></label>
    </td></tr>
<?
  foreach ($line['stock'] as $size=>$stock) {
?>
    <tr>
      <td><?= $sizes[$size]['name'] ?></td>
      <td><input type="text" name="sizes[stock][<?= $size ?>]" value="<?= intval($stock) ?>" /></td>
    </tr>
<?
  }
?>
  </table>
  <p>
    <button type="button" onclick="$.post('<?= $_SERVER['_URI'].'?change_stock='.$line['id'] ?>',$(this.form).serialize(),function(resp) { $('#change_stock_<?= $line['id'] ?>').html(resp); $.fancybox.close() },'html')"><?= $this->_('Save'); ?></button>
    &nbsp;&nbsp;&nbsp;
    <a href="#" onclick="$.fancybox.close(); return false;"><?= $this->_('Cancel'); ?></a>
  </p>
</form>
</div>
<?
        
      } else {
?>

<div style="width:300px;height:200px; text-align:center;">
<form>
  <p>
    <b><?= trim($line['sku'].' '.$line['name']) ?></b>
  </p>
  <p>
    <label><?= $this->domainTranslate('Product.stock') ?></label>
    <input type="text" name="stock" value="<?= $line['stock'] ?>" />
  </p>
  <p>
    <button type="button" onclick="$.post('<?= $_SERVER['_URI'].'?change_stock='.$line['id'] ?>',$(this.form).serialize(),function(resp) { $('#change_stock_<?= $line['id'] ?>').html(resp); $.fancybox.close() },'html')"><?= $this->_('Save'); ?></button>
    &nbsp;&nbsp;&nbsp;
    <a href="#" onclick="$.fancybox.close(); return false;"><?= $this->_('Cancel'); ?></a>
  </p>
</form>
</div>
<?
      }
      exit();
    }
  }
  
  public function outputContent() {
    $this->change_stock();
    parent::outputContent();
  }

}


//end