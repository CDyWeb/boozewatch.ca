<?

class TreeManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("Tree");

		$this->addFieldConfig("name=active;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=1");
		$this->addFieldConfig("name=parent_id;type=".CCMSDomainField::FIELDTYPE_FK.";required=0;attributes=table:".$this->getTablePrefix()."tree,caption:name,delete:restrict");
		$this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		$this->addFieldData("page",CCMSDomainField::FIELDTYPE_STRING);
		$this->addFieldData("args",CCMSDomainField::FIELDTYPE_STRING);
		$this->addFieldConfig("name=class;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0;translatable=0");
		$this->addFieldData("text",CCMSDomainField::FIELDTYPE_TEXT);
		$this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);
		$this->addFieldConfig("name=user_type;type=".CCMSDomainField::FIELDTYPE_ENUM.";required=1;defaultValue=editor;attributes=user,editor,super");
		
		$this->setListFields(array("name"));
		$this->setEditFields(array("name"));

		$this->init();
		$this->treeInvolved=true;
	}
	
	//@Deprecated
	function toLocale($node) {
		//if (substr($node["name"],0,1)==":") $node["name"]=get Domain Text(substr($node["name"],1),"_title");
		//if (substr($node["name"],0,1)==".") $node["name"]=get Domain Text("Tree".$node["name"]);
		return $node;
	}

	function get($id) {
		$node=parent::get($id);
		return $node; //$this->toLocale($node);
	}
	
	function getRoots() {
		return getTableArray("select * from {$this->getTableName()} where parent_id is null order by orderby");
	}

	function getNewElementName($parent_folder_id) {
		$res=getOneValue("select child_class from {$this->getTableName()} where id={$parent_folder_id}");
		if (!$res) $res=$this->getClassName();
		return $res;
	}
	
	public static function getArg($tree_id,$key) {
		$m=new TreeManager();
		$line=$m->get($tree_id);
		foreach (explode(";",$line["args"]) as $expr) if (preg_match("#^([^:]+):(.*)$#",$expr,$match) && $key==$match[1]) {
			return $match[2];
		}
	}
  
  public function fixtures() {
    $this->populate();
  }
  
  public function populate() {
    $with_shop=getConfigItem('plugin.enable.shop',false)?1:0;
    $with_newsletter=getConfigItem('plugin.enable.newsletter',false)?1:0;
		executeSql("replace into {$this->getTableName()} set id=01, active=1, name='Website'");
		executeSql("replace into {$this->getTableName()} set id=1001, parent_id=01, active=1, name='.MenuTop', class='Page'");
		executeSql("replace into {$this->getTableName()} set id=1002, parent_id=01, active=0, name='.MenuLeft', class='Page'");
		executeSql("replace into {$this->getTableName()} set id=1003, parent_id=01, active=0, name='.MenuRight', class='Page'");
		executeSql("replace into {$this->getTableName()} set id=1004, parent_id=01, active=0, name='.MenuBottom', class='Page'");

		executeSql("replace into {$this->getTableName()} set id=11, active={$with_shop}, name='Shop'");

		executeSql("replace into {$this->getTableName()} set id=91,  user_type='super', active=1, name='Misc'");
		executeSql("replace into {$this->getTableName()} set id=902, user_type='super', parent_id=91, active=1, name=':Settings', page='settings'");
		executeSql("replace into {$this->getTableName()} set id=905, user_type='super', parent_id=91, active=1, name=':User', class='User'");
    executeSql("replace into {$this->getTableName()} set id=909, user_type='super', parent_id=91, active={$with_newsletter}, name='.Newsletter', page='newsletter'");
    
    if ($with_shop) {
      executeSql("
INSERT INTO `{$this->getTableName()}` (`id`, `active`, `parent_id`, `name`, `page`, `args`, `class`, `text`, `orderby`, `user_type`) VALUES
(1100, 1, 11, '.Cat', NULL, NULL, 'Cat', NULL, 99, 'editor'),
(1101, 0, 11, '.Shop', 'shop', NULL, NULL, NULL, 1, 'editor'),
(1102, 1, 11, '.Customers', NULL, NULL, 'Customer', NULL, 2, 'editor'),
(1103, 1, 11, '.Orders', NULL, NULL, 'Order', NULL, 3, 'editor'),
(1104, 1, 11, '.Brand', NULL, NULL, 'Brand', NULL, 4, 'editor'),
(1105, 1, 11, '.Shipping', NULL, NULL, 'ShippingRate', NULL, 5, 'editor'),
(1106, 0, 11, '.Payment', 'shop.payment', NULL, NULL, NULL, 6, 'editor'),
(1107, 1, 11, '.Tax', NULL, NULL, 'Tax', NULL, 7, 'editor'),
(1108, 1, 11, ':Ship_Country', NULL, NULL, 'Ship_Country', NULL, 8, 'editor'),
(1109, 1, 11, ':ProductNotify', NULL, NULL, 'ProductNotify', NULL, 9, 'editor'),
(1110, 1, 11, '.ProductStock', 'shop.stock', NULL, NULL, NULL, 10, 'editor'),
(1111, 0, 11, ':SizeGroup', NULL, NULL, 'SizeGroup', NULL, 11, 'editor'),
(1112, 0, 11, ':Voucher', NULL, NULL, 'Voucher', NULL, 12, 'editor')
"
      );
    }
  }

	function createTable() {
		parent::createTable();
    $this->populate();
	}
	
}

// end