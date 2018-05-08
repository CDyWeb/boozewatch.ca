<?

class UserManager extends CCMSDomainManager {

  const NOT_CHANGED = "______";

  function __construct() {

    parent::__construct('User');

    $this->addFieldConfig('name=created_by;type='.CCMSDomainField::FIELDTYPE_FK.';required=0;defaultValue=1;attributes=table:'.$this->getTablePrefix().'user,caption:email,delete:restrict');
    $this->addFieldConfig('name=email;type='.CCMSDomainField::FIELDTYPE_EMAIL.';required=1');
    $this->addFieldConfig('name=first_name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=last_name;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    $this->addFieldConfig('name=password;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    $this->addFieldConfig('name=user_type;type='.CCMSDomainField::FIELDTYPE_ENUM.';required=1;defaultValue=editor;attributes=user,editor,super');
    $this->addFieldConfig('name=tech_admin;type='.CCMSDomainField::FIELDTYPE_BOOL.';required=1;defaultValue=0');
    $this->addFieldConfig('name=login_count;type='.CCMSDomainField::FIELDTYPE_INT.';required=1;defaultValue=0');
    $this->addFieldData('pref',CCMSDomainField::FIELDTYPE_TEXT);
	$this->addFieldData('acl',CCMSDomainField::FIELDTYPE_JSON);

    $this->setListFields(array('_name','email','user_type'));
    $this->setEditFields(getConfigItem('UserManager.editFields',array('first_name','last_name','email','password','user_type',)));

    $this->init();
  }
  
  public function getAuthUser($login) {
    return getOneRow("select * from {$this->getTableName()} where email='".db_escape($login)."'");
  }
  
  public function getProfileFields() {
    return array('first_name','last_name','email','password');
  }
  
  //do not store history data for users.
  protected function toHistory($line) {
    return;
  }
  
  protected function populate() {
    executeSql("insert into {$this->getTableName()} set email='ek@cdyweb.com', password='662e708c1d3c9264fe72909b551950759dfd8435', first_name='Erwin', last_name='Kooi', user_type='super', tech_admin=1, login_count=0");
    executeSql("update {$this->getTableName()} set created_by=1");
  }
  
  public function createTable() {
    parent::createTable();
    $this->populate();
  }

  public function getItemName($line) {
    return "{$line["first_name"]} {$line["last_name"]}";
  }

  public function getByEmail($s) {
    return getOneRow("select * from {$this->getTableName()} where email=".dbStr($s));
  }

  protected function extraSetSqlInsert() {
    return ", created_by=".$_SESSION["user"]["id"];
  }

  protected function customSetSql($id, array $data, array &$res, array &$err, $fieldName) {
    if ($fieldName=="password") {
      if ($data[$fieldName]==self::NOT_CHANGED) return true;
      $res[]="password='".sha1(getConfigItem("domain").":".$data["password"])."'";
      return true;
    }
    return false;
  }
  
  public function canUpdate($user,$id,$data) {
    if ($user['id']==$id) return true;
    return parent::canUpdate($user,$id,$data);
  }

  
}

// end
