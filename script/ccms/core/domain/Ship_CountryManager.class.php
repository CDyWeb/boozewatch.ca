<?

class Ship_CountryManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("Ship_Country");

		$this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		$this->addFieldConfig("name=active;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=1");
		$this->addFieldConfig("name=rate;type=".CCMSDomainField::FIELDTYPE_CUR.";required=1;defaultValue=0");
		$this->addFieldConfig("name=with_tax;type=".CCMSDomainField::FIELDTYPE_BOOL.";required=1;defaultValue=1");
    $this->addFieldConfig("name=free_shipping_offset;type=".CCMSDomainField::FIELDTYPE_CUR.";required=0;defaultValue=0");

		$this->setListFields(array("name","active","with_tax","rate",'free_shipping_offset'));
		$this->setEditFields(array("name","active","with_tax","rate",'free_shipping_offset'));

		$this->init();
	}
	
	public function getOrderBy() {
		return "name";
	}
  
  public function populate() {
    $l=getConfigItem('language');
    switch ($l['base']) {
      case 'nl' :
        executeSql("insert into {$this->getTableName()} set `name`='Nederland', active=1, `rate`=0, `with_tax`=1, `free_shipping_offset`=100");
      break;
      
      default :
        executeSql("insert into {$this->getTableName()} set `name`='Canada', active=1, `rate`=0, `with_tax`=1, `free_shipping_offset`=100");
        executeSql("insert into {$this->getTableName()} set `name`='United States', active=1, `rate`=0, `with_tax`=0, `free_shipping_offset`=100");
    }
  }

	protected function createTable() {
		parent::createTable();
		$this->populate();
	}

}

// end