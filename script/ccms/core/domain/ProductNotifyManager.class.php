<?

class ProductNotifyManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("ProductNotify");

		$this->addFieldConfig("name=email;type=".CCMSDomainField::FIELDTYPE_EMAIL.";required=1");
		$this->addFieldConfig("name=product;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."product,caption:name,delete:cascade");
		$this->addFieldConfig("name=lang;type=".CCMSDomainField::FIELDTYPE_STRING.";length=5;required=1;defaultValue=en-US");
		
		$this->setListFields(array("product","email"));
		$this->setEditFields(array("product","email"));

		$this->init();
		$this->movable=false;
	}
	
	public function getItemName($line) {
		return $line["email"];
	}
	
	public function getOrderBy() {
		return "email";
	}
	
}

// end