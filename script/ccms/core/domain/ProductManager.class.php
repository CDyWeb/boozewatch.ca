<?

/*
CREATE TABLE IF NOT EXISTS `ccms_product_cat` (
  `product_id` int(11) NOT NULL,
  `tree_id` int(11) NOT NULL,
  `orderby` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`tree_id`),
  KEY `orderby` (`orderby`),
  KEY `tree_id` (`tree_id`)
) ENGINE=InnoDB;

ALTER TABLE `ccms_product_cat`
  ADD CONSTRAINT `ccms_product_cat_ibfk_2` FOREIGN KEY (`tree_id`) REFERENCES `ccms_tree` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ccms_product_cat_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `ccms_product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  
*/

class ProductManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("Product");

		$this->addFieldConfig("name=active;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=1");
		$this->addFieldConfig("name=tree_id;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."tree,caption:name,delete:restrict,where:page is NULL and class='Product',orderby:orderby");
		$this->addFieldConfig("name=sku;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		$this->addFieldConfig("name=brand;type=".CCMSDomainField::FIELDTYPE_FK.";required=0;attributes=table:".$this->getTablePrefix()."brand,caption:name,delete:set null");
		$this->addFieldConfig("name=price;type=".CCMSDomainField::FIELDTYPE_CUR.";required=1");
		$this->addFieldConfig("name=tax;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;defaultValue=1;attributes=table:".$this->getTablePrefix()."tax,caption:name,delete:restrict");
		$this->addFieldConfig("name=discount_absolute;type=".CCMSDomainField::FIELDTYPE_CUR.";required=0;");
		$this->addFieldConfig("name=discount_percent;type=".CCMSDomainField::FIELDTYPE_CUR.";required=0;");
		$this->addFieldConfig("name=discount_start;type=".CCMSDomainField::FIELDTYPE_DATE.";required=0;");
		$this->addFieldConfig("name=discount_end;type=".CCMSDomainField::FIELDTYPE_DATE.";required=0;");
		$this->addFieldConfig("name=shippingrate;type=".CCMSDomainField::FIELDTYPE_FK.";required=0;attributes=table:".$this->getTablePrefix()."shippingrate,caption:name,delete:set null");
		$this->addFieldConfig("name=description;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0");
    $this->addFieldConfig("name=keywords;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=img;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
		$this->addFieldConfig("name=subimg1;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
		$this->addFieldConfig("name=subimg2;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
		$this->addFieldConfig("name=subimg3;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
		
		$this->addFieldConfig("name=option1;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=option2;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=option3;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=option4;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		
		$this->addFieldConfig("name=is_new;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=1");
		$this->addFieldConfig("name=is_hot;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=0");
		$this->addFieldConfig("name=is_home;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=0");
		
		$this->addFieldConfig("name=stock;type=".CCMSDomainField::FIELDTYPE_INT.";required=0;defaultValue=0");
		$this->addFieldConfig("name=units;type=".CCMSDomainField::FIELDTYPE_ENUM.";required=1;attributes=piece;defaultValue=piece");
		$this->addFieldConfig("name=quantity;type=".CCMSDomainField::FIELDTYPE_FLOAT.";required=1;defaultValue=1");
		$this->addFieldConfig("name=deliveryperiod;type=".CCMSDomainField::FIELDTYPE_INT.";required=0;defaultValue=10");
		$this->addFieldConfig("name=no_stock_action;type=".CCMSDomainField::FIELDTYPE_ENUM.";required=1;attributes=ignore,notify,hide;defaultValue=notify");

		$this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);

		$this->setListFields(array('sku','name','brand','price','_discount','stock','img'));
		$this->setEditFields(array(
			'active','tree_id','sku','name','keywords','brand','price','option1','option2','option3','tax',
			'discount_absolute','discount_percent','discount_start','discount_end',
			'shippingrate','units','quantity','stock','_product_sizes_','no_stock_action','deliveryperiod',
			'img','subimg1','subimg2','subimg3',
			'is_new','is_hot','is_home',
			'description'
		));
    
    $this->setTranslateFields(array('name','description'));

		$this->init();
		$this->setFilterFieldName("tree_id");
	}
  
  public function is_multi_cat_enabled() {
    return getConfigItem('products_multi_cat');
  }

  public function getAll() {
    if (!$this->is_multi_cat_enabled()) return parent::getAll();
    #--
    $where=null;
    $filter=$this->getFilter();
    $sql='select p.* from '.tbl_name('product').' p left join '.tbl_name('product_cat').' c on p.id=c.product_id and c.tree_id='.intval($filter).' where p.tree_id='.intval($filter).' or c.tree_id='.intval($filter).' order by c.orderby, p.orderby';
    $res=getTableArray($sql);
    return $this->getAllStock($res,null);
  }
  
  public function orderby($orderby) {
    if (!$this->is_multi_cat_enabled()) return parent::orderby($orderby);
    #--
    $filter=$this->getFilter();
    foreach (array_values($orderby) as $i=>$id) {
      $sql='replace into '.tbl_name('product_cat').' set tree_id='.intval($filter).', product_id='.$id.', orderby='.$i;
      executeSql($sql);
    }
  }
  
  public function unlinkMulti($product_id,$tree_id) {
    executeSql('delete from '.tbl_name('product_cat').' where tree_id='.intval($tree_id).' and product_id='.intval($product_id));
  }
	
	public function on_restock($id,$data) {
		if ($data["no_stock_action"]=="notify") {
			$email=getTableArray("select * from ".tbl_name("productnotify")." where product={$id}");
			if (count($email)>0) { 
				if (!function_exists("static_translate")) require_once getConfigItem('script_base').'shared/cyane/static_translate.inc.php';
				if (!class_exists("HtmlMimeMail5")) require_once getConfigItem('script_base').'shared/cyane/HtmlMimeMail5.class.php';
				$args=$data;
				$args["id"]=$id;
				foreach ($email as $line) {
					$return_path=$from=SettingsManager::setting("company_email",null);
					if (!$from) $return_path=$from="info@".getConfigItem("domain");
					$from_name=SettingsManager::setting("company_name",null);
					if (!$from_name) $from_name=getConfigItem("domain");
					$from='"'.$from_name.'" <'.$from.'>';
					$args["email"]=$to=$line["email"];
					$subject=static_translate($line["lang"],"ProductManager.on_restock.notify.subject",$args);
					$msg=static_translate($line["lang"],"ProductManager.on_restock.notify.msg",$args);
					$headers=null;
					HtmlMimeMail5::mail($from,$to,$subject,$msg,$headers,$return_path);
				}
			}
		}
		executeSql("delete from ".tbl_name("productnotify")." where product={$id}");
	}
  
  public function getProductSizeManager() {
    if (!isset($this->productSizeManager)) {
      $this->productSizeManager=new ProductSizeManager();
      if (class_exists('CustomProductSizeManager')) $this->productSizeManager=new CustomProductSizeManager();
    }
    return $this->productSizeManager;
  }
  
  public function valueFromPost($fieldName, $value, array &$err) {
    if ($fieldName=='_product_sizes_') {
      return $this->getProductSizeManager()->valuesFromProduct();
    }
    return parent::valueFromPost($fieldName, $value, $err);
  }
  
  protected function fieldFromInput($fieldName, &$res, &$err, $src, $lcode=null) {
    parent::fieldFromInput($fieldName, $res, $err, $src, $lcode);
    if ($fieldName=='tree_id') {
      if (isset($src['_multi_cat_']) && is_array($src['_multi_cat_'])) $res['_multi_cat_']=$src['_multi_cat_'];
    }
  }
	
	public function save($id, $data, &$err) {
		$old_line=$id>0?$this->get($id):null;
    #--
    $product_sizes=isset($data['_product_sizes_'])?$data['_product_sizes_']:null;
    unset($data['_product_sizes_']);
    #--
    $multi_cat=isset($data['_multi_cat_'])?$data['_multi_cat_']:null;
    unset($data['_multi_cat_']);
    #--
    $res=parent::save($id,$data,$err);
    if ($res) {
      #--
      if (!empty($product_sizes)) {
        $this->getProductSizeManager()->saveFromProduct($res,$product_sizes);
      }
      if (!$id && $this->is_multi_cat_enabled()) {
        if ($this->nextOrderbyPositive) $orderby = 1+intval(getOneValue('select max(orderby) from '.tbl_name('product_cat').' where tree_id='.intval($data['tree_id'])));
        else $orderby = intval(getOneValue('select min(orderby) from '.tbl_name('product_cat').' where tree_id='.intval($data['tree_id'])))-1;
        executeSql('replace into '.tbl_name('product_cat').' set orderby='.$orderby.', product_id='.intval($res).', tree_id='.intval($data['tree_id']));
      }
      if (!empty($multi_cat)) {
        $exists=array_keys(getTableArray('select tree_id from '.tbl_name('product_cat').' where product_id='.intval($res),'tree_id'));
        foreach (array_diff($exists,$multi_cat) as $tree_id) if (!isset($data['tree_id']) || ($data['tree_id']!=$tree_id)) executeSql('delete from '.tbl_name('product_cat').' where product_id='.intval($res).' and tree_id='.intval($tree_id));
        foreach (array_diff($multi_cat,$exists) as $tree_id) if (!isset($data['tree_id']) || ($data['tree_id']!=$tree_id)) executeSql('replace into '.tbl_name('product_cat').' set orderby=99999, product_id='.intval($res).', tree_id='.intval($tree_id));
      }
      #--
      if ($old_line && ($old_line["id"]==$res) && isset($data["no_stock_action"])) {
        if (($old_line["stock"]<1) && ($data["stock"]>0)) $this->on_restock($id,$data);
      }
      #--
    }
    return $res;
	}
  
  public function searchWhere($q) {
    $q=db_escape($q);
    return "((name like '%{$q}%') or (sku like '%{$q}%') or (description like '%{$q}%'))";
  }

	public function doSearch($token, $orderby=null, $limit=null) {
    $where=array($this->searchWhere($token));
		return $this->getStockList($where,$orderby,$limit);
	}
	
	public function getStockList($where=null, $orderby=null, $limit=null) {
    if (empty($where)) $where=array();
    #--
    if (count($where)==0) {
      $where[]="tree_id in (select id from ".tbl_name("tree")." where page is null and class='Product')";
      if (empty($orderby)) $orderby=array("tree_id","orderby");
    } else {
      if (empty($orderby)) $orderby=array($this->getOrderBy());
    }
    #--
		$res=$this->getAllExtLimit($where,$orderby,$limit);
    $this->count=$this->getAllExtCount($where);
    return $res;
	}

  public function getAllExtLimit(array $where=null, array $orderby=null, array $limit=null, $select="*", $idcol=null) {
    $res=parent::getAllExtLimit($where, $orderby, $limit, $select, $idcol);
    if (empty($select) || ($select=='*')) {
      return $this->getAllStock($res,$where);
    }
    return $res;
  }
  
  public function getAllStock($res,$where) {
    $sm=new ProductSizeManager();
    $sizes=$sm->getAllExt(array('`active`=1','product in ('.implode(' ',$this->getAllExtQuery('id',$where)).')'));
    if (count($sizes)>0) {
      foreach ($res as $i=>$line) {
        foreach ($sizes as $size) if ($size['product']==$line['id']) {
          if (!is_array($res[$i]['stock'])) $res[$i]['stock']=array();
          $res[$i]['stock'][$size['size']]=$size['stock'];
        }
      }
    }
    return $res;
  }
	
	#--
  //@Override
  //check dependencies
  protected function checkMeta($create=false,$update=true) {
    if (!isset($_SESSION["meta.checked.{$this->tableName}"])) {
      CCMSManagedModel::getManager("BrandManager");
      CCMSManagedModel::getManager("TaxManager");
      CCMSManagedModel::getManager("ShippingRateManager");
      CCMSManagedModel::getManager("TreeManager");
      CCMSManagedModel::getManager("SizeGroupManager");
      CCMSManagedModel::getManager("SizeManager");
    }

    parent::checkMeta($create,$update);

    if (!isset($_SESSION["meta.checked.{$this->tableName}"])) {
      CCMSManagedModel::getManager("ProductSizeManager");
      CCMSManagedModel::getManager("ProductNotifyManager");
    }
  }

}

// end
