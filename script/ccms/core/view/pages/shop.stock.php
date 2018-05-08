<?

$controller=CCMSController::getInstance();
$view=$controller->getView();
$view->addToPagePath("<a href='{$_SERVER["_URI"]}'>".$view->domainTranslate("Tree.ProductStock")."</a>");

class MyList extends CoreProductList {

  protected function getWidth() { return '100%';	}

  public $withCheckboxes=true;
  
  public function outputStyles() {
    parent::outputStyles();
?>
  #pagination A.normal {
    margin:2px;
    padding:3px;
  }
  #pagination A.normal:hover, #pagination A.current {
    background-color:#CCC;
    margin:2px;
    padding:3px;
  }
<?
  }

	public function startList() {
		
    $editor=new CoreProductEditor($this->model);
    $arr=$editor->getTreeOptions($this->model->getDomainManager()->getField('tree_id'));
    if (!isset($_SESSION['shop.stock.filter_brand'])) $_SESSION['shop.stock.filter_brand']=0;
    if (!isset($_SESSION['shop.stock.filter_tree_id'])) $_SESSION['shop.stock.filter_tree_id']=0;
    if (!isset($_SESSION['shop.stock.limit'])) $_SESSION['shop.stock.limit']=10;
    if (!isset($_SESSION['shop.stock.nav'])) $_SESSION['shop.stock.nav']=1;
    
    $nav=$_SESSION['shop.stock.nav'];
    $limit=max(1,intval($_SESSION['shop.stock.limit']));
    $count=$this->model->getDomainManager()->count;
    $pages=ceil($count/$limit);
    
    $searched=isset($_SESSION["ShopStock.last_search"])?$_SESSION["ShopStock.last_search"]:null;

?>
<div style="margin:1em">
<form action="<?= $_SERVER["_URI"] ?>">
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
  <td width="33%" valign="top">
    <p>
      <label style="width:100px;float:left" for="_cat"><?= $this->domainTranslate("Cat") ?> : </label>
      <select id="_cat" onchange="window.location.href='<?= $_SERVER['_URI'] ?>?tree_id='+this.value"><option></option><? 
      foreach ($arr as $option_id=>$option) {
        ?><option <? if ($_SESSION['shop.stock.filter_tree_id']==$option_id) echo 'selected="selected"'; ?> value="<?= $option_id ?>"><?= $option ?></option><?
      } 
    ?></select>
    </p>
    <p>
      <label style="width:100px;float:left" for="_brand"><?= $this->domainTranslate("Brand") ?> : </label>
      <select id="_brand" onchange="window.location.href='<?= $_SERVER['_URI'] ?>?brand='+this.value"><option></option><? 
        foreach (getTableArray('select * from '.tbl_name('brand').' order by name','id') as $option_id=>$option) { 
          ?><option <? if ($_SESSION['shop.stock.filter_brand']==$option_id) echo 'selected="selected"'; ?> value="<?= $option_id ?>"><?= $option['name'] ?></option><? 
        } 
      ?></select>
    </p>
  </td><td width="33%" align="center" valign="top">
    <label for="_search"><?= $this->cmsTranslate("Search") ?> : </label>
    <input type="text" name="_search" value="<?= utf8_ent($searched) ?>" />
    <input type="submit" name="_find" value="<?= $this->cmsTranslate("search") ?>" onclick="return this.form._search.value!=''" />
<? if (!empty($searched)) { ?>
<p><?= $this->cmsTranslate("searched_for",array('',$searched,$count,$_SERVER["_URI"]."?clear")) ?></p>
<? } ?>
  </td><td width="33%" align="right" valign="top">
    <label for="_pag"><?= $this->cmsTranslate("Pagination") ?> : </label>
    <select id="_pag" onchange="window.location.href='<?= $_SERVER['_URI'] ?>?limit='+this.value">
      <? foreach (array(10,50,100) as $n) echo '<option value="'.$n.'" '.($n==$_SESSION['shop.stock.limit']?'selected="selected"':'').'>'.$n.'</option>'; ?>
    </select>
  </td>
</tr><tr>
  <td valign="top">
    <p><a href="<?= $_SERVER['_URI']?>?edit=many&all"><?= $this->cmsTranslate("page.shop.stock.edit_all") ?></a></p>
  </td><td align="center" valign="bottom">
    <!-- -->
  </td><td align="right" valign="bottom" id="pagination"><p>
    <? if ($nav>1) { ?><a href="<?= $_SERVER['_URI'] ?>?nav=<?= $nav-1 ?>">&laquo;&laquo;</a><? } ?>
<?
for ($i=1;$i<$pages+1;$i++) {
  $diff=min($i-1,abs($i-$nav),$pages-$i);
  if ($diff>3) continue;
  if ($diff==3) {
    echo '..';
    continue;
  }
  ?><a href="<?= $_SERVER['_URI'] ?>?nav=<?= $i ?>" <? if ($nav==$i) echo 'class="current"'; else echo 'class="normal"' ?>><?= $i ?></a><? 
} ?>
    <? if ($nav<$pages) { ?><a href="<?= $_SERVER['_URI'] ?>?nav=<?= $nav+1 ?>">&raquo;&raquo;</a><? } ?>
  </p></td>
</tr>
</table>
</form>
</div>
<?
		parent::startList();
	}
	
	public function getTableHtml($data=null,$plain=false) {
		if (isset($this->data)) $data=$this->data;
		return parent::getTableHtml($data,$plain);
	}
  
	public function outputContent() {
    #--
    $this->change_stock();
    #--
		echo "<style type='text/css'>";
		$this->outputStyles();
		echo "</style>";

    $where=array();
    if ($_SESSION['shop.stock.filter_tree_id']) $where[]='`tree_id`='.$_SESSION['shop.stock.filter_tree_id']; 
    if ($_SESSION['shop.stock.filter_brand']) $where[]='`brand`='.$_SESSION['shop.stock.filter_brand']; 
    
		$q=null;
		if (isset($_GET["_search"])) $q=$_SESSION["ShopStock.last_search"]=trim(strip_tags($_GET["_search"]));
		else if (isset($_GET["clear"])) unset($_SESSION["ShopStock.last_search"]);
		else if (isset($_SESSION["ShopStock.last_search"])) $q=$_SESSION["ShopStock.last_search"];
    if (!empty($q)) {
			$where[]=$this->getManager()->searchWhere($q);
    }
    $_SESSION['edit.many.where']=$where;
    $data=$this->getManager()->getStockList($where);

		$this->startList();
    $this->outputFancybox();
		$this->outputDescription();
		$this->outputTable($data);
		$this->endList();
	}
	
	protected function uri($line=null) {
    $tree_id=$_SESSION["shop.stock.tree_id"];
    if (empty($tree_id)) throw new Exception('?');
    $cls=getOneValue('select `class` from '.tbl_name('tree').' where id='.$tree_id);
    if (empty($cls)) {
      $cls='Product';
      executeSql('update '.tbl_name('tree').' set `class`=\''.$cls.'\' where id='.$tree_id);
    }
    return $this->base_url()."/page/{$tree_id}/productstock.html";
	}

}

$model=new CCMSManagedModel("ProductManager");
$model->getDomainManager()->setAddable(false);
$model->getDomainManager()->setMovable(false);

if (isset($_GET["_search"])) $_SESSION['shop.stock.filter_tree_id']=$_SESSION['shop.stock.filter_brand']=0;
if (isset($_GET["_search"]) || isset($_GET['tree_id'])) unset($_SESSION['shop.stock.nav']);

if (isset($_GET['tree_id'])) $_SESSION['shop.stock.filter_tree_id']=intval($_GET['tree_id']); 
else if (!isset($_SESSION['shop.stock.filter_tree_id'])) $_SESSION['shop.stock.filter_tree_id']=0; 

if (isset($_GET['brand'])) $_SESSION['shop.stock.filter_brand']=intval($_GET['brand']); 
else if (!isset($_SESSION['shop.stock.filter_brand'])) $_SESSION['shop.stock.filter_brand']=0;

if (isset($_GET['limit'])) $_SESSION['shop.stock.limit']=max(1,intval($_GET['limit'])); else if (!isset($_SESSION['shop.stock.limit'])) $_SESSION['shop.stock.limit']=10;
if (isset($_GET['nav'])) $_SESSION['shop.stock.nav']=max(1,intval($_GET['nav'])); else if (!isset($_SESSION['shop.stock.nav'])) $_SESSION['shop.stock.nav']=1;
$model->getDomainManager()->setAllLimit(array($_SESSION['shop.stock.limit']*($_SESSION['shop.stock.nav']-1),$_SESSION['shop.stock.limit']));

$model->getDomainManager()->setListFields(getConfigItem('shop.stock.fields',array('tree_id','sku','name','brand','price','_discount','stock','img')));
$list=new MyList($model);
$list->outputContent();

//end