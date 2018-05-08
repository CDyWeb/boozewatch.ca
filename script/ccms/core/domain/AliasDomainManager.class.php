<?

class AliasDomainManager extends CCMSDomainManager {

  function __construct() {
    
    parent::__construct('AliasDomain');

    $this->addFieldConfig('name=active;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=1');
    $this->addFieldConfig('name=domain;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    
    $this->addFieldConfig('name=meta_title;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=meta_keywords;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=meta_description;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    
    $this->addFieldConfig('name=text;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');
    
    $this->addFieldConfig('name=lastmod;type='.CCMSDomainField::FIELDTYPE_DATETIME.';required=0;attributes=on_insert,on_update');
    
    $this->addFieldConfig('name=analytics;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    
    $this->setListFields(array('domain','active'));
    $this->setEditFields(array('domain','active','meta_title','meta_keywords','meta_description','text','analytics'));
    $this->setTranslateFields(array('meta_title','meta_keywords','meta_description','text'));

    $this->init();
  }
  
  protected function getItemNameFieldName() {
    return 'domain';
  }
  protected function getOrderBy() {
    return 'domain';
  }
  
  //@Override
  protected function checkTableFields($describe) {
    parent::checkTableFields($describe);
    if (isset($describe['meta_keywords']['Type']) && strcasecmp('text',$describe['meta_keywords']['Type'])!==0) executeSql("alter table `{$this->tableName}` change `meta_keywords` `meta_keywords` TEXT NULL default NULL");
    if (isset($describe['meta_description']['Type']) && strcasecmp('text',$describe['meta_description']['Type'])!==0) executeSql("alter table `{$this->tableName}` change `meta_description` `meta_description` TEXT NULL default NULL");
  }
  
}

// end