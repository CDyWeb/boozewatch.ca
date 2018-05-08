<?

class SettingsManager extends CCMSDomainManager {

  protected static $settings=null;

  function __construct() {
    
    parent::__construct('Settings');

    $this->addFieldConfig('name=key;type='.CCMSDomainField::FIELDTYPE_STRING.';required=1');
    $this->addFieldConfig('name=str;type='.CCMSDomainField::FIELDTYPE_STRING.';required=0');
    $this->addFieldConfig('name=txt;type='.CCMSDomainField::FIELDTYPE_TEXT.';required=0');
    
    $this->setListFields(array('name','str'));
    $this->setEditFields(array('name','str','txt'));

    $this->init();
  }
  
  public static function setting($key,$default=null) {
    if (!self::$settings) {
      self::$settings=array();
      try {
        $arr=getTableArray("select * from `".tbl_name("settings")."`","key");
      } catch (MySqlException $ex) {
        if ($ex->error==1146) {
          new SettingsManager();
          $arr=getTableArray("select * from `".tbl_name("settings")."`","key");
        }
      }
      foreach ($arr as $k=>$line) self::$settings[$k]=$line["str"].$line["txt"];
    }
    if (!isset(self::$settings[$key])) return $default;
    return self::$settings[$key];
  }
  
  public static function set($key,$value) {
    $current=self::setting($key);
    if ($current===$value) return false;
    if ($value===null) executeSql("replace into `".tbl_name("settings")."` set `key`='".db_escape($key)."', `str`=NULL, `txt`=NULL");
    else if (strlen($value)<50) executeSql("replace into `".tbl_name("settings")."` set `key`='".db_escape($key)."', `str`='".db_escape($value)."', `txt`=NULL");
    else executeSql("replace into `".tbl_name("settings")."` set `key`='".db_escape($key)."', `str`=NULL, `txt`='".db_escape($value)."'");
    self::$settings[$key]=$value;
    #--
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $cache=CcmsObjectCache::getInstance();
    $cache->delete(getConfigItem('domain').':settings');
    #--
    return true;
  }

  
  function createTable() {
    parent::createTable();
    executeSql("ALTER TABLE `ccms_settings` ADD UNIQUE (`key`)");
    executeSql("insert into {$this->getTableName()} set `key`='route_tree_ids', `str`='1001,1002,1003,1004'");
    executeSql("insert into {$this->getTableName()} set `key`='home_page', `str`='1'");
  }
  
}

// end