<?

class SizeManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct('Size');
    $this->addFieldConfig("name=sizegroup;type=".CCMSDomainField::FIELDTYPE_FK.";required=1;attributes=table:".$this->getTablePrefix()."sizegroup,caption:name,delete:restrict");
		$this->addFieldConfig('name=name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    $this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);
		
		$this->setListFields(array('name'));
		$this->setEditFields(array('name'));

		$this->init();
    $this->setFilterFieldName("sizegroup");
	}
  
  protected function getItemNameFieldName() {
    return 'name';
  }
  
  public function save($id, $data, &$err) {
    if (is_array($_POST['input_name'])) {
      foreach ($_POST['input_name'] as $n) if (!empty($n)) {
        $data['name']=$n;
        $res=parent::save($id,$data,$err);
      }
    } else {
      $res=parent::save($id,$data,$err);
    }
    return $res;
  }

}

// end