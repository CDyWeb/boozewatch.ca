<?

class SliderManager extends CCMSDomainManager {

  function __construct() {
  
    parent::__construct("Slider");

    $this->addFieldConfig("name=title;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=enclosure;type=".CCMSDomainField::FIELDTYPE_IMG.";required=0");
    $this->addFieldConfig("name=visible;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");

    $this->addFieldData("orderby",CCMSDomainField::FIELDTYPE_ORDERINDEX);

    $this->setListFields(array("title","enclosure"));
    $this->setEditFields(array('title','enclosure','visible'));

    $this->init();
  }
  
  public function getItemName($line) {
    if (isset($line["title"])) return $line["title"];
    return $line["id"];
  }
  
  public function save($id, $data, &$err) {
    $enclosure=null;
    if (!empty($data['enclosure'])) $enclosure=unserialize($data['enclosure']);
    if (!empty($enclosure) && !preg_match("#\.(jpg|jpeg|png|gif)$#i",$enclosure["name"])) {
      return false;
    }
    #--
    if (empty($data['title']) && !empty($enclosure)) {
      $data['title']=preg_replace('#\.[^.]*$#','',$enclosure['name']);
    }
    #--
    $data['visible']=array();
    foreach (getTableArray('select id from ccms_page') as $line) if (isset($_POST['_p'][$line['id']])) $data['visible'][$line['id']]=$line['id'];
    $data['visible']=json_encode($data['visible']);
    #--
    $res=parent::save($id,$data,$err);
    if ($res) {
      //--
    }
    return $res;
  }

}

// end