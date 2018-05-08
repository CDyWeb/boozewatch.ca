<?

class PageManager extends CCMSDomainManager {

  private $pagina_bits=null;

  function __construct() {
    
    parent::__construct('Page');

    $this->addFieldConfig('name=active;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=1');
    $this->addFieldConfig('name=indexable;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=1');
    $this->addFieldConfig('name=tree_id;type='.CCMSDomainField::FIELDTYPE_FK.';required=1;attributes=table:'.$this->getTablePrefix().'tree,caption:name,delete:restrict');
    $this->addFieldConfig('name=parent_id;type='.CCMSDomainField::FIELDTYPE_FK.";required=0;attributes=table:{$this->getTableName()},caption:name");
    $this->addFieldConfig('name=name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    $this->addFieldConfig('name=page_type;type='.CCMSDomainField::FIELDTYPE_ENUM.';required=1;defaultValue=text;attributes=text,menu,link,plugin');
    $this->addFieldConfig('name=attributes;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0;');

    $this->addFieldConfig('name=meta_title;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=meta_keywords;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=meta_description;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');

    $this->addFieldConfig('name=uri;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=text;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');

    $this->addFieldData('can_edit',CCMSDomainField::FIELDTYPE_BOOL,1,true);
    $this->addFieldData('can_delete',CCMSDomainField::FIELDTYPE_BOOL,1,true);
    $this->addFieldData('can_move',CCMSDomainField::FIELDTYPE_BOOL,1,true);
    $this->addFieldData('can_have_children',CCMSDomainField::FIELDTYPE_BOOL,1,false);

    $this->addFieldConfig('name=lastmod;type='.CCMSDomainField::FIELDTYPE_DATETIME.';required=0;attributes=on_insert,on_update');

    $this->addFieldConfig('name=sitemap_changefreq;type='.CCMSDomainField::FIELDTYPE_ENUM.';required=0;defaultValue=;attributes=always,hourly,daily,weekly,monthly,yearly,never');
    $this->addFieldConfig('name=sitemap_priority;type='.CCMSDomainField::FIELDTYPE_DECIMAL.';length=3,1;required=0;defaultValue=0.5');

    $this->addFieldData('orderby',CCMSDomainField::FIELDTYPE_ORDERINDEX);

    $this->setListFields(array('name'));
    $this->setEditFields(array('active','parent_id','page_type','attributes','name','meta_title','meta_description','uri','text')); //'meta_keywords',
    $this->setTranslateFields(array('name','meta_title','meta_keywords','meta_description','uri','text','attributes'));

    $this->setFilterFieldName('tree_id');
    $this->init();
  }
  
  //@Override
  protected function checkTableFields($describe) {
    parent::checkTableFields($describe);
    if (isset($describe['meta_keywords']['Type']) && strcasecmp('text',$describe['meta_keywords']['Type'])!==0) executeSql("alter table `{$this->tableName}` change `meta_keywords` `meta_keywords` TEXT NULL default NULL");
    if (isset($describe['meta_description']['Type']) && strcasecmp('text',$describe['meta_description']['Type'])!==0) executeSql("alter table `{$this->tableName}` change `meta_description` `meta_description` TEXT NULL default NULL");
  }
  
  function getParentOptions($line) {
    if (!isset($line['id'])) $line['id']=0;
    $res=array();
    $arr=$this->getAll();
    foreach (array_keys($arr) as $key) {
      if (($arr[$key]['id']==$line['id']) || ($arr[$key]['parent_id']>0) || (!$arr[$key]['can_have_children'])) continue;
      $res[$arr[$key]['id']]=$arr[$key]['name'];
      $pid=$arr[$key]['id'];
      foreach (array_keys($arr) as $key) {
        if (($arr[$key]['id']==$line['id']) || ($arr[$key]['parent_id']!==$pid) || (!$arr[$key]['can_have_children'])) continue;
        $res[$arr[$key]['id']]="   - ".$arr[$key]['name'];
      }
    }
    return $res;
  }
  
  function getMoveGroup($orderby,$move_id,$move_dir) {
    $tree_id = $this->getFilter();
    $res=array();
    
    $target=getOneRow("select parent_id from {$this->getTableName()} where id={$move_id}");
    if (!$target['parent_id']) {
      $sql="select id from {$this->getTableName()} where parent_id is null and tree_id={$tree_id} order by {$orderby}";
    } else {
      $sql="select id from {$this->getTableName()} where parent_id={$target['parent_id']} order by {$orderby}";
    }
    foreach (getTableArray($sql) as $line) $res[]=$line['id'];
    return $res;
  }
  
  function getPaginaBits() {
    if (!is_array($this->pagina_bits) || count($this->pagina_bits)!=4) {
      $tree_id = $this->getFilter();
      $this->pagina_bits = explode(",",TreeManager::getArg($tree_id,'bits'));
      if (!is_array($this->pagina_bits) || count($this->pagina_bits)!=4) {
        $this->pagina_bits=array(1,1,1,0);
      }
    }
    return $this->pagina_bits;
  }
  
  function extraSetSqlInsert() {
    $result="";

    #bits
    $p = $this->getPaginaBits();
    $result.=',can_edit='.$p[0].',can_delete='.$p[1].',can_move='.$p[2].',can_have_children='.$p[3];
    
    return $result;
  }

  public function delete($id) {
    #--
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $cache=CcmsObjectCache::getInstance();
    $cache->delete(getConfigItem('domain').':Router:fetchPages');
    #--
    return parent::delete($id);
  }

  public function orderby($orderby) {
    $res=parent::orderby($orderby);
    #--
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $cache=CcmsObjectCache::getInstance();
    $cache->delete(getConfigItem('domain').':Router:fetchPages');
    #--
    return $res;
  }
  
  //@Override
  public function save($id, $data, &$err) {
  
    #--
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $cache=CcmsObjectCache::getInstance();
    $cache->delete(getConfigItem('domain').':Router:fetchPages');
    #--

    switch ($data['page_type']) {
      case 'plugin' : $data['attributes'] = @$_POST['input_attributes_plugin']; break;
      case 'link' : $data['attributes'] = @$_POST['input_attributes_link']; break;
      default : $data['attributes'] = null;
    }

    $lconf=getConfigItem('language',array('default'=>'en','base'=>'en','available'=>array('en')));
    if ((count($lconf['available'])>1)) {
      foreach ($lconf['available'] as $lcode) if ($lcode!==$lconf['base']) {
        switch ($data['page_type']) {
          #case 'plugin' : $data[$lcode]['attributes'] = @$_POST[$lcode.'_input_attributes_plugin']; break;
          case 'link' : $data[$lcode]['attributes'] = @$_POST[$lcode.'_input_attributes_link']; break;
          default : $data[$lcode]['attributes'] = null;
        }
      }
    }

    $pluginSettingsFile=$this->getPluginSettingsFile($data);
    if (!empty($pluginSettingsFile)) require_once $pluginSettingsFile;
    if (function_exists('pageManager_preSave')) {
      $preSave = pageManager_preSave($this, $id, $data, $err);
      if ($preSave===false) return $preSave;
    }

    $res = parent::save($id, $data, $err);

    if ($res) {
      if (function_exists('pageManager_postSave')) {
        $postSave = pageManager_postSave($this, $id, $data, $err);
        if ($postSave===false) return $postSave;
      }
    }

    return $res;
  }
  
  protected function getNextOrderBy(array $data, CCMSDomainField $field) {
    $tree_id = $this->getFilter();
    if (isset($data['parent_id']) && ($data['parent_id']>0)) {
      $sql="select max({$field->name}) from {$this->getTableName()} where parent_id={$data['parent_id']} and tree_id={$tree_id}";
    } else {
      $sql="select max({$field->name}) from {$this->getTableName()} where parent_id is null and tree_id={$tree_id}";
    }
    $res = 1+intval(getOneValue($sql));
    return $res;
  }

  function getPluginSettingsFile($line) {
    if (!isset($line["page_type"])) return null;
    if ($line["page_type"]!="plugin") return null;
    $name=$line["attributes"];

    $fn=APP_PATH."plugins/{$name}/page_settings.inc.php";
    if (file_exists($fn)) return $fn;

    $fn="../shared/plugins/{$name}/page_settings.inc.php";
    if (file_exists($fn)) return $fn;
    
    return null;
  }
  
  public function populate() {
    executeSql("insert into {$this->getTableName()} set active=1, tree_id=1001, name='Home', uri='index.html', lastmod=now(), sitemap_changefreq='daily', sitemap_priority='0.9', meta_title='Welcome', meta_keywords='', meta_description='', text='<h1>Welcome</h1><p>Hello world</p>'");
  }
  
  function createTable() {
    parent::createTable();
    $this->populate();
  }
  
  #--
  //@Override
  //check dependencies
  protected function checkMeta($create=false,$update=true) {
    if (!isset($_SESSION["meta.checked.{$this->tableName}"])) {
      CCMSManagedModel::getManager("AliasDomainManager");
    }
    parent::checkMeta($create,$update);
  }

}

// end