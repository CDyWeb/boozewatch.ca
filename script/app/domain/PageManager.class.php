<?

class CustomPageManager extends PageManager {

  function __construct() {
		//$this->addFieldConfig('name=img;type='.CCMSDomainField::FIELDTYPE_IMG.';required=0;');
    parent::__construct();
    $this->setEditFields(array('active','parent_id','page_type','attributes','name','meta_title','meta_description','uri','text')); //'img','text'));
    /*
    if ($this->getFilter()=='1003') {
      $this->setEditFields(array('active','parent_id','page_type','attributes','name','meta_title','meta_description','uri','img','text'));
      $this->setListFields(array('name','img'));
    }
    */
  }

  public $translate=array(
    //'Page.img'=>'Sidebar image',
  );
  
}

//end