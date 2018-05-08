<?

class CCMSManagedModel extends CCMSDefaultModel {

  protected static $classes=array();

  public static function loadManagerClass($managerClass) {
    #--
    if (isset(self::$classes[$managerClass])) return self::$classes[$managerClass];
    #--
    if (preg_match("#^Plugin_(\w+)_#",$managerClass,$match)) {
      if (
          file_exists($fn=APP_PATH."/domain/{$managerClass}.class.php")
          ||
          file_exists($fn=APP_PATH."/plugins/".strtolower($match[1])."/domain/{$managerClass}.class.php")
          ||
          file_exists($fn=PLUGIN_PATH."/".strtolower($match[1])."/domain/{$managerClass}.class.php")
          ) { 
        require $fn;
        return self::$classes[$managerClass] = $managerClass;
      } else {
        throw new Exception('file not found '.$fn);
      }
    } else {
      $path1=CMS_PATH."/core/domain/{$managerClass}.class.php";
      $path2=CMS_PATH."/custom/domain/{$managerClass}.class.php";
      if (file_exists(APP_PATH."/domain/{$managerClass}.class.php")) $path2=APP_PATH."/domain/{$managerClass}.class.php";
      if (file_exists($path1) && file_exists($path2)) {
        require_once($path1);
        require_once($path2);
        return self::$classes[$managerClass] = "Custom{$managerClass}";
      } else {
        _require("domain/{$managerClass}.class.php");
        return self::$classes[$managerClass] = $managerClass;
      }
    }
  }
  
  public static function getManager($managerClass) {
    if (empty($managerClass)) throw new Exception('managerClass must not be empty');
    $managerClass=self::loadManagerClass($managerClass);
    return new $managerClass();
  }

  /**
    ---
  **/

  protected $domainManager;

  public function __construct($managerClass) {
    if ($managerClass!==null) $this->setManagerClass($managerClass);
  }
  
  public function setManagerClass($managerClass) {
    $obj=self::getManager($managerClass);
    $this->setDomainManager($obj);
  }
  
  public function getDomainManager() {
    return $this->domainManager;
  }
  public function setDomainManager(CCMSDomainManager $domainManager) {
    $this->domainManager=$domainManager;
  }
  
  public function getTableName() {
    if ($this->domainManager!==null) return $this->domainManager->getTableName();
    _log(new Exception("tableName not available, domainManager is null"),LOG_LEVEL_ERROR);
    return null;
  }
  
  public function getName() {
    return $this->getDomainManager()->getName();
  }
  
  public function getItemName($line) {
    return $this->getDomainManager()->getItemName($line);
  }
  
  public function create() {
    if ($this->domainManager!==null) return $this->domainManager->create();
    throw new Exception("create not possible, domainManager is null");
  }
  
  public function duplicateFrom($id) {
    log_message("debug",get_class()." duplicateFrom {$id}");
    if ($this->domainManager!==null) return $this->domainManager->setDuplicateFrom($id);
    throw new Exception("duplicateFrom not possible, domainManager is null");
  }
  
  public function canDelete($user,$id) {
    log_message("debug",get_class()." canDelete {$id}");
    if ($this->domainManager!==null) return $this->domainManager->canDelete($user,$id);
    throw new Exception("canDelete not possible, domainManager is null");
  }
  public function canMove($user,$id) {
    log_message("debug",get_class()." canMove {$id}");
    if ($this->domainManager!==null) return $this->domainManager->canMove($user,$id);
    throw new Exception("canMove not possible, domainManager is null");
  }
  public function canSave($user,$id,$data) {
    if ($id) return $this->canUpdate($user,$id,$data);
    else return $this->canInsert($user,$data);
  }
  public function canInsert($user,$data) {
    log_message("debug",get_class()." canInsert");
    if ($this->domainManager!==null) return $this->domainManager->canInsert($user,$data);
    throw new Exception("canInsert not possible, domainManager is null");
  }
  public function canUpdate($user,$id,$data) {
    log_message("debug",get_class()." canUpdate {$id}");
    if ($this->domainManager!==null) return $this->domainManager->canUpdate($user,$id,$data);
    throw new Exception("canUpdate not possible, domainManager is null");
  }

  public function save($id, $data, &$err) {
    log_message("debug",get_class()." saving {$id}");
    if ($this->domainManager!==null) return $this->domainManager->save($id,$data,$err);
    throw new Exception("save not possible, domainManager is null");
  }
  
  public function delete($id) {
    log_message("debug",get_class()." deleting {$id}");
    if ($this->domainManager!==null) return $this->domainManager->delete($id);
    throw new Exception("delete not possible, domainManager is null");
  }
  
  public function export($format,array $ids=null,array $options=null) {
    log_message("debug",get_class()." export {$format} ".implode($ids));
    if ($this->domainManager!==null) return $this->domainManager->export($format,$ids,$options);
    throw new Exception("export not possible, domainManager is null");
  }
  
  public function up($id) {
    log_message("debug",get_class()." moving up {$id}");
    if ($this->domainManager!==null) return $this->domainManager->move($id,"up");
    throw new Exception("up not possible, domainManager is null");
  }
  
  public function orderby($orderby) {
    if (!is_array($orderby)) throw new Exception('illegal argument');
    log_message("debug",get_class()." orderby ".implode(',',$orderby));
    if ($this->domainManager!==null) return $this->domainManager->orderby($orderby);
    throw new Exception("orderby not possible, domainManager is null");
  }
  
  public function down($id) {
    log_message("debug",get_class()." moving down {$id}");
    if ($this->domainManager!==null) return $this->domainManager->move($id,"down");
    throw new Exception("down not possible, domainManager is null");
  }
  
  public function get($id) {
    if ($this->domainManager!==null) return $this->domainManager->get($id);
    throw new Exception("get not possible, domainManager is null");
  }
  
  public function getAll() {
    if ($this->domainManager!==null) return $this->domainManager->getAll();
    throw new Exception("getAll not possible, domainManager is null");
  }

  public function getAllExt(array $where=null, array $orderby=null, $select="*", $idcol=null) {
    if ($this->domainManager!==null) return $this->domainManager->getAllExt($where,$orderby,$select,$idcol);
    throw new Exception("getAllExt not possible, domainManager is null");
  }

}

//end