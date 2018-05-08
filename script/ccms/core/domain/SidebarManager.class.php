<?

class SidebarManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct('Sidebar');

		$this->addFieldConfig('name=active;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=1');
		$this->addFieldConfig('name=name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
		$this->addFieldConfig('name=sidebar_type;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
		$this->addFieldConfig('name=attributes;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0;');
		$this->addFieldConfig('name=text;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');
		
		$this->addFieldData('can_edit',CCMSDomainField::FIELDTYPE_BOOL,1,true);
		$this->addFieldData('can_delete',CCMSDomainField::FIELDTYPE_BOOL,1,true);
		$this->addFieldData('can_move',CCMSDomainField::FIELDTYPE_BOOL,1,true);
		
		$this->addFieldData('orderby',CCMSDomainField::FIELDTYPE_ORDERINDEX);

		$this->setListFields(array('name','active'));
		$this->setEditFields(array('active','sidebar_type','name','text'));
    $this->setTranslateFields(array('name','text'));

		$this->init();
	}
  
	function getSettingsFile($line) {
		if (empty($line["sidebar_type"])) return null;

    $name=$line["sidebar_type"];
		$fn=APP_PATH."sidebar/{$name}/settings.inc.php";
		if (file_exists($fn)) return $fn;

		return null;
	}

}

// end