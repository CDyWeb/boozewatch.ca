<?

class NewsletterGroupManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("NewsletterGroup",$this->getTablePrefix()."newsletter_group");

		$this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
    $this->addFieldConfig("name=auto_activate;type=".CCMSDomainField::FIELDTYPE_BOOL.";defaultValue=0;required=1");

		$this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);

		$this->setListFields(array("name","_count"));
		$this->setEditFields(array("name","auto_activate"));

		$this->init();
	}
	
	//@Override
	public function getListData() {
		$result=array_merge(array(
			array(
				'__readonly'=>true,
				'can_move'=>false,
				'id'=>-1,
				'name'=>null,
			)
		),$this->getAll());
		$arr=getTableArray("select newsletter_group as id,count(*) as c from ".$this->getTablePrefix()."newsletter_subscribe group by newsletter_group","id");
		foreach ($result as &$line) {
			$id=$line['id'];
			if ($line['id']==-1) $id="";
			$line['_count']=isset($arr[$id])?$arr[$id]['c']:0;
		}
		return $result;
	}

}

// end