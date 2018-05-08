<?

class CustomCustomerManager extends CustomerManager {

  function __construct() {
    $this->addFieldConfig('name=fb_id;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0;');
    $this->addFieldConfig('name=fb_data;type='.CCMSDomainField::FIELDTYPE_SIMPLETEXT.';required=0;');
    $this->addFieldConfig('name=location;type='.CCMSDomainField::FIELDTYPE_SIMPLETEXT.';required=0;');
    $this->addFieldConfig('name=stores;type='.CCMSDomainField::FIELDTYPE_SIMPLETEXT.';required=0;');

    parent::__construct();
    
    $this->setListFields(array('__NAME','__facebook'));
    
		$this->setEditFields(array(
			"first_name",
			"last_name",
			"email",
			"password",
			"tel1",
			"tel2",
			"tel3",
			"adr1_address1","adr1_address2","adr1_city","adr1_state","adr1_zip","adr1_country",
		));
    
  }
  
  public $translate=array(
    'Customer.__facebook'=>'Facebook',
  );

}

//end
