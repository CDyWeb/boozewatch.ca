<?

class BrandManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct('Brand');

		$this->addFieldConfig('name=name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
		$this->addFieldConfig('name=img;type='.CCMSDomainField::FIELDTYPE_IMG.';required=0');
    $this->addFieldConfig('name=link;type='.CCMSDomainField::FIELDTYPE_LINK.';required=0');
		$this->addFieldConfig('name=description;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');

		$this->setListFields(array('name','link','img'));
		$this->setEditFields(array('name','link','img','description'));

		$this->init();
		$this->movable=false;
	}
	
	public function getOrderBy() {
		return 'name';
	}

}

// end