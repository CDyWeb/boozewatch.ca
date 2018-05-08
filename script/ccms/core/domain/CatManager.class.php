<?

class CatManager extends TreeManager {

  function __construct() {
    $this->addFieldConfig('name=img;type='.CCMSDomainField::FIELDTYPE_IMG.';required=0');
    
    parent::__construct();
    
    $this->addFieldConfig('name=class;type='.CCMSDomainField::FIELDTYPE_ENUM.';required=1;defaultValue=Product;attributes='.implode(',',getConfigItem('plugin.products.treeClass',array('Product','Cat'))));
    
    $field=$this->getField('parent_id');
    $field->required=true;

    $this->setListFields(array('active','name','class'));
    $this->setEditFields(array('parent_id','active','class','img','name','text'));
    $this->setTranslateFields(array('name','text'));
    
    if (isset($_SESSION['Cat.tree_id'])) $_SESSION['Tree.parent_id']=$_SESSION['Cat.tree_id'];
    $this->setFilterFieldName('parent_id');
  }
  
  //@Override
  protected function checkMeta($create=false,$update=true) {
    try {
      $describe=getTableArray("describe {$this->tableName}","Field");
    } catch (MySqlException $ex) {
      //if ($ex->error!=1146 || !$create)
      throw $ex;
    }
    #--
    if (!isset($describe['img'])) {
      $field=$this->getField('img');
      $alter[]="add `{$field->name}` {$field->getMysqlDDL()}";
      executeSql("alter table `{$this->tableName}` ".implode(', ',$alter));
    }
    #--
    return;
  }
  
  protected function sessionHasTreeAccess($tree_line) {
    switch ($_SESSION['user']['user_type']) {
    case 'super' : break;
    case 'editor' :
      if ($tree_line['user_type']=='super') return false;
      break;
    default :
      if ($tree_line['user_type']=='editor') return false;
      if ($tree_line['user_type']=='super') return false;
    }
    return true;
  }

  //@Override 
  public function getListData() {
    $res=parent::getListData();
    foreach (array_keys($res) as $i) {
      if (!$this->sessionHasTreeAccess($res[$i])) {
        unset($res[$i]);
      }
    }
    return $res;
  }

  private function recursiveParentOptions($pid,$flat,&$res,$prefix="") {
    if (isset($flat[$pid])) foreach ($flat[$pid] as $line) {
      if ($line["class"]=="Cat") {
        if ($line['name']=='.Cat') $line['name']='';
        $res[$line["id"]] = $prefix.$line["name"];
      }
      $this->recursiveParentOptions($line["id"],$flat,$res,$prefix.$line["name"]." - ");
    }
  }
  
  public function getParentOptions($line) {
    if (!isset($line["id"])) {
      $line_id=0;
      $id=$this->getFilter();
      
    } else {
      $line_id=$id=$line["id"];
    }
    $res=array();
    $treeClass = getConfigItem('plugin.products.treeClass',array('Cat','Product'));
    if ($id>0) {
      $arr=getTableArray("select id,parent_id,name,class from {$this->tableName}","id");
      do {
        if (!isset($arr[$id])) throw new Exception("{$id} not found");
        $node=$arr[$id];
        if (!in_array($node["class"],$treeClass)) break;
        if ($node["parent_id"]>0) $id=$node["parent_id"];
        else break;
      } while ($node["parent_id"]>0);
      
      $flat=array();
      foreach ($arr as $line) if ($line["id"]!=$line_id) $flat[$line["parent_id"]][]=$this->toLocale($line);
      
      $this->recursiveParentOptions($id,$flat,$res);
    }
    return $res;
  }
  
  private static function recursiveCatTree($flat,&$res) {
    if (!isset($flat[$res["id"]])) return;
    $res["_children"]=array();
    foreach ($flat[$res["id"]] as $line) {
      self::recursiveCatTree($flat,$line);
      $res["_children"][]=$line;
    }
  }
  
  public function getFlatCatTree($root_id=null, &$res=null) {
    $arr=getTableArray("select id,parent_id,name,class from {$this->tableName} order by orderby","id");
    $flat=array();
    foreach ($arr as $line) {
      $line=$this->toLocale($line);
      $flat[$line["parent_id"]][]=$line;
      if ($line["id"]==$root_id) $res=$line;
    }
    return $flat;
  }
  
  public function getRootId() {
    return getOneValue("select id from ccms_tree where class='Cat' order by id limit 1");
  }
  
  public function getCatTree($root_id=null) {
    if (!$root_id) $root_id=$this->getRootId();
    $res=array();
    $flat=$this->getFlatCatTree($root_id, $res);
    if ($res) self::recursiveCatTree($flat,$res);
    return $res;
  }
  
  public function getExportAdapter($format) {
    if ($format=='xls') {
      return new CatExcelExport($this);
    }
    return parent::getExportAdapter($format);
  }

}

//end