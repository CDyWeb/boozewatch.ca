<?

class ShippingRateManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("ShippingRate");

		$this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		$this->addFieldConfig("name=rate;type=".CCMSDomainField::FIELDTYPE_CUR.";required=1");
		$this->addFieldConfig("name=tax;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."tax,caption:name,delete:restrict");
		
		$this->setListFields(array("name","rate","tax"));
		$this->setEditFields(array("name","rate","tax"));

		$this->init();
		$this->movable=false;
	}
	
	public function getOrderBy() {
		return "rate";
	}
	
}

// end