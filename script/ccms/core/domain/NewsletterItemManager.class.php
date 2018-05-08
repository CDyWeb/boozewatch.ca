<?

class NewsletterItemManager extends CCMSDomainManager {

	function __construct() {
		parent::__construct('NewsletterItem');
		$this->addFieldConfig("name=newsletter;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."newsletter,caption:name,delete:restrict");
		#--
		$this->addFieldConfig("name=type;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1;defaultValue=text");
		$this->addFieldConfig("name=fk;type=".CCMSDomainField::FIELDTYPE_INT.";required=0");
		$this->addFieldConfig("name=title;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=caption;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
		$this->addFieldConfig("name=image;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
		$this->addFieldConfig("name=text;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0");
		#--
		$this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);

		$this->setListFields(array('type','title','image'));
		$this->setEditFields(array('type','fk','title','image','caption','text'));

		$this->init();
		$this->setFilterFieldName("newsletter");
	}
  
	protected function getItemNameFieldName() {
		return 'title';
	}

	/**
	public function save($id, $data, &$err) {
	}
	**/

}

// end