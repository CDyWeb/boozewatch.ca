<?

class SizeGroupManager extends CCMSDomainManager {

	function __construct() {
	
		parent::__construct('SizeGroup');
		$this->addFieldConfig('name=name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
		
		$this->setListFields(array('name'));
		$this->setEditFields(array('name'));

		$this->init();
    $this->addable = getConfigItem('SizeGroupManager.addable',true);
    $this->editable = getConfigItem('SizeGroupManager.editable',true);
    $this->deletable = getConfigItem('SizeGroupManager.deletable',true);
	}
  
  public function delete($id) {
    $m=new SizeManager();
    $m->deleteWhere(array('`sizegroup`='.$id));
    parent::delete($id);
  }
  
  protected function getItemNameFieldName() {
    return 'name';
  }
  protected function getOrderBy() {
    return 'name';
  }
  
  public function save($id, $data, &$err) {
    $res=parent::save($id,$data,$err);
    if ($res>0) {
      if (isset($this->duplicateFrom)) {
        $sizeManager=new SizeManager();
        $sizes=$sizeManager->getAllExt(array('sizegroup='.$this->duplicateFrom));
        foreach ($sizes as $size) {
          unset($size['id']);
          $size['sizegroup']=$res;
          $sizeManager->save(0, $size, $err);
        }
      }
    }
    return $res;
  }
  
}

// end