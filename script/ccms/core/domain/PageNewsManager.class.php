<?

class PageNewsManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct('PageNews');

		$this->addFieldConfig('name=page_id;type='.CCMSDomainField::FIELDTYPE_FK.';required=1;attributes=table:'.$this->getTablePrefix().'page,caption:name,where:attributes=\'news\',delete:cascade');
		$this->addFieldConfig('name=user_id;type='.CCMSDomainField::FIELDTYPE_FK.';required=0;attributes=table:'.$this->getTablePrefix().'user,caption:email,delete:set null');
		$this->addFieldConfig('name=title;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
		$this->addFieldConfig('name=pubdate;type='.CCMSDomainField::FIELDTYPE_DATE.';required=1');
		$this->addFieldConfig('name=description;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0;attributes=height=200px');
		$this->addFieldConfig('name=enclosure;type='.CCMSDomainField::FIELDTYPE_IMG.';required=0');
		$this->addFieldConfig('name=link;type='.CCMSDomainField::FIELDTYPE_LINK.';required=0');
		$this->addFieldConfig('name=is_home;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=0;defaultValue=1');
		$this->addFieldConfig('name=is_hot;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=0;defaultValue=1');
    
    $this->addFieldConfig('name=product_id;type='.CCMSDomainField::FIELDTYPE_FK.';required=0;attributes=table:'.$this->getTablePrefix().'product,caption:name,delete:cascade');

		$this->setListFields(array('pubdate','title'));
		$this->setEditFields($editFields=getConfigItem('PageNewsManager.editFields',array('title','description','pubdate','enclosure','link')));
    $this->setTranslateFields(array('title','description'));

    if (!empty($_SESSION['PageNews.page_id'])) $this->setFilterFieldName('page_id');
    else $this->setEditFields(array_merge(array('page_id'),$editFields));

		$this->init();
		$this->movable=false;
	}
	
	public function getItemName($arg) {
		if (is_int($arg)) $arg=$this->get($arg);
    if (isset($arg["title"])) return $arg["title"];
		return $arg["id"];
	}
	
	public function getOrderBy() {
		return "pubdate desc";
	}

  protected function extraSetSqlInsert() {
    return ", user_id=".$_SESSION["user"]["id"];
  }
	
	public function save($id, $data, &$err) {
		$res=parent::save($id,$data,$err);
		if ($res) {
			if (isset($data['page_id'])) $page=$data['page_id'];
      else $page=$this->getFilter();
			executeSql("update ".$this->getTablePrefix()."page set lastmod=now() where id={$page}");
		}
		return $res;
	}

}

// end