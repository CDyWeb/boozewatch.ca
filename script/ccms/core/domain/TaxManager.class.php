<?

class TaxManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct("Tax");

		$this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
		$this->addFieldConfig("name=percent;type=".CCMSDomainField::FIELDTYPE_PERCENT.";required=1");
		
		$this->setListFields(array("name","percent"));
		$this->setEditFields(array("name","percent"));

		$this->init();
	}
  
  public function populate() {
    $l=getConfigItem('language');
    switch ($l['base']) {
      case 'nl' :
        executeSql("insert into {$this->getTableName()} set `name`='BTW 21%', `percent`=21");
        executeSql("insert into {$this->getTableName()} set `name`='BTW 6%', `percent`=6");
        executeSql("insert into {$this->getTableName()} set `name`='BTW vrij', `percent`=0");
      break;
      
      default :
        executeSql("insert into {$this->getTableName()} set `name`='HST 13%', `percent`=13");
        executeSql("insert into {$this->getTableName()} set `name`='No Tax', `percent`=0");
    }
  }

	protected function createTable() {
		parent::createTable();
		$this->populate();
	}
	
}

// end