<?

require_once 'CcmsObjectCache.class.php';

class CcmsSessionHandler {

  public static $adapter=null;
  public static $save_path=null;
  public static $session_name=null;
  public static $is_initialized=false;
  public static $is_new=true;
  
  //singleton
  private function __construct() {
    //noop
  }

  private function __destruct() {
    //noop
  }

  protected static function getAdapter() {
    if (empty(self::$adapter)) {
      if (getConfigItem('CcmsSessionHandler.use.ObjectCache',isset($_SERVER['WINDIR']) || extension_loaded('memcache'))) self::$adapter=new CcmsSession_ObjectCache();
      else {
        if (!function_exists('tbl_name')) throw new Exception('db?');
        self::$adapter=new CcmsSession_Db();
      }
      _log(__FILE__.':'.get_class(self::$adapter));
    }
    return self::$adapter;
  }
  
  //---
  protected static function initialize() {
    if (self::$is_initialized) return;
    _log(__FILE__.':initialize');
    self::getAdapter()->initialize();
    self::$is_initialized=true;
  }
  
  //session_set_save_handler('CcmsSessionHandler::open', 'CcmsSessionHandler::close', 'CcmsSessionHandler::read', 'CcmsSessionHandler::write', 'CcmsSessionHandler::destroy', 'CcmsSessionHandler::gc');

  public static function open($save_path, $session_name) {
    _log(__FILE__.':open '.$session_name);
    self::$save_path=$save_path;
    self::$session_name=$session_name;
    self::$is_new=empty($_COOKIE[$session_name]) || ($_COOKIE[$session_name]=='_new');
    _log(__FILE__.':open '.$session_name.' savepath='.self::$save_path.'; session_name='.self::$session_name.'; is_new='.(self::$is_new?'true':'false; cookie: '.$_COOKIE[$session_name]));
    #--
    return self::getAdapter()->open($save_path, $session_name);
  }
  
  public static function close() {
    _log(__FILE__.':close');
    return self::getAdapter()->close();
    self::$is_initialized=false;
    self::$adapter=null;
  }
  
  public static function read($id) {
    _log(__FILE__.':read '.$id);
    if (self::$is_new || ($id=='_new')) {
      if ($id=='_new') session_id($id=sha1('ccms:'.uniqid(rand(), true)));
      _log(__FILE__.':read '.$id.': session is NEW!');
      session_encode();
      return null;
    }
    _log(__FILE__.':read '.$id.': session is not new');
    #--
    if (!self::$is_initialized) self::initialize();
    #--
    _log(__FILE__.':read '.$id);
    return self::getAdapter()->read($id);
  }
  
  public static function write($id, $data) {
    if (defined('CCMS_SESSION_READONLY')) return;
    if (self::$is_new && empty($data)) {
      return;
    }
    if (!self::$is_initialized) self::initialize();
    #--
    _log(__FILE__.':write '.$id);
    $res=self::getAdapter()->write($id, $data);
    #--
    if (!headers_sent()) {
      _log(__FILE__.':write.setcookie '.self::$session_name.' to '.$id);
      $cookie_params=session_get_cookie_params();
      $exp=0; //time()+max($cookie_params['lifetime'],getConfigItem('session.gc_maxlifetime'));
      $path=$cookie_params['path'];
      $domain=null;
      $secure=false;
      $httponly=true;
      if (function_exists('frontend_setcookie')) frontend_setcookie(self::$session_name,$id,$exp,$path,$domain,$secure,$httponly);
      else setcookie(self::$session_name,$id,$exp,$path,$domain,$secure,$httponly);
    } else {
      _log(__FILE__.':write cannot set cookie, headers already sent! '.$id);
    }
    return $res;
  }
  
  public static function destroy($id) {
    if (defined('CCMS_SESSION_READONLY')) return;
    if (!self::$is_initialized) self::initialize();
    #--
    _log(__FILE__.':destroy '.$id);
    return self::getAdapter()->destroy($id);
  }
  
  public static function gc($maxlifetime) {
    _log(__FILE__.':gc '.$maxlifetime);
    return self::getAdapter()->gc($maxlifetime);
  }
}

#------------------------------------------------------------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------------------------------------------------------------

abstract class CcmsSession_Adapter {

  protected $session_name=null;

  public function __construct() {
    //noop
  }

  public function open($save_path, $session_name) {
    $this->session_name=$session_name;
  }
  
  public function close() {
    //noop
  }

  public function gc($maxlifetime) {
    //noop
  }

  protected function getRemoteIp() {
    if (function_exists('getClientIp')) return getClientIp();
    if (isset($_SERVER['REMOTE_ADDR'])) return $_SERVER['REMOTE_ADDR'];
    return null;
  }

  protected function validateIp($check) {
    if (getConfigItem('session.bypass.validateIp')) return true;
    $ip=$this->getRemoteIp();
    if (empty($ip)) {
      _log(__FILE__.': validateIp : remote IP is empty',LOG_LEVEL_ERROR);
      return false;
    }
    _log(__FILE__.': validateIp : validating '.$check.' with '.$ip,LOG_LEVEL_DEBUG);
    if (substr($check,0,6)=='127.0.') return (substr($ip,0,6)=='127.0.');
    if (substr($check,0,4)=='192.') return (substr($ip,0,4)=='192.');
    if (substr($check,0,3)=='10.') return (substr($ip,0,3)=='10.');
    return $check==$ip;
  }

  protected function destroyAndRenew($id) {
    $this->destroy($id);
    session_id($id=sha1('ccms:'.uniqid(rand(), true)));
    return $id;
  }

  abstract function initialize();
  abstract function read($id);
  abstract function write($id, $data);
  abstract function destroy($id);
}

#------------------------------------------------------------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------------------------------------------------------------

class CcmsSession_ObjectCache extends CcmsSession_Adapter {

  private $cache=null;

  public function initialize() {
    $this->getCache();
  }

  protected function getCache() {
    if (empty($this->cache)) $this->cache=CcmsObjectCache::getInstance();
    return $this->cache;
  }

  public function read($id) {
    $key=getConfigItem('domain').':CcmsSession:'.$this->session_name.':'.$id;
    $result=$this->getCache()->get($key);
    if (empty($result)) return null;
    if (!empty($result['ip']) && (!$this->validateIp($result['ip']))) {
      _log(__FILE__.': CcmsSession_ObjectCache:read:'.$id.' invalid IP: '.$result['ip'],LOG_LEVEL_ERROR);
      $this->destroyAndRenew($id);
      return null; 
    }
    if (!empty($result['exp']) && (time()>$result['exp'])) {
      _log(__FILE__.': CcmsSession_ObjectCache:read:'.$id.' expired: '.$result['exp'].' '.date('Y-m-d H:i:s',$result['exp']),LOG_LEVEL_ERROR);
      $this->destroyAndRenew($id);
      return null;
    }
    _log(__FILE__.': CcmsSession_ObjectCache:read:'.$id,LOG_LEVEL_TRACE);
    return $result['data'];
  }
  
  public function write($id, $data) {
    $ip=$this->getRemoteIp();
    if ($id=='_new') session_id($id=sha1('ccms:'.uniqid(rand(), true)));
    $key=getConfigItem('domain').':CcmsSession:'.$this->session_name.':'.$id;
    $cookie_params=session_get_cookie_params();
    $exp=time()+max($cookie_params['lifetime'],getConfigItem('session.gc_maxlifetime', (60*60*8) )); //8h
    $record=array(
      'data'=>$data,
      'ip'=>$ip,
      'exp'=>$exp,
    );
    _log(__FILE__.': CcmsSession_ObjectCache:write:'.$id.' exp '.$exp.' '.date('Y-m-d H:i:s',$exp),LOG_LEVEL_TRACE);
    return $this->getCache()->set($key, $record, $exp);
  }

  public function destroy($id) {
    _log(__FILE__.': CcmsSession_ObjectCache:destroy:'.$id,LOG_LEVEL_TRACE);
    $key=getConfigItem('domain').':CcmsSession:'.$this->session_name.':'.$id;
    return $this->getCache()->delete($key);
  }

}

#------------------------------------------------------------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------------------------------------------------------------

class CcmsSession_Db extends CcmsSession_Adapter {

  private $tbl_name=null;
  
  public function initialize() {
    $this->tblname=tbl_name('_'.$this->session_name);
    if (defined('CCMS_SESSION_READONLY')) return;
    #--
		try {
			$res=getTableArray('describe '.$this->tblname,'Field');
		} catch (MySqlException $ex) {
			if ($ex->error!=1146) throw $ex;
      executeSql(
<<<SQL
create table {$this->tblname} (
  `id` CHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `data` LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL,
  `dt` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` CHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  INDEX (`dt`),
  PRIMARY KEY (`id`)
) ENGINE = MYISAM;
SQL
      );
		}
  }

  public function read($id) {
    $result=getOneRow(
      sprintf(
        'select * from `%s` where `id`=%s',
        $this->tblname,
        "'".db_escape($id)."'"
      )
    );
    if (empty($result)) return null;
    if (!empty($result['ip']) && (!$this->validateIp($result['ip']))) {
      $this->destroyAndRenew($id);
      return null; 
    }
    return $this->last_read=$result['data'];
  }

  public function write($id, $data) {
    if (!empty($this->last_read) && (strcmp($this->last_read,$data)==0)) {
      _log(__FILE__.':write:'.$id.' data has not changed, just update timestamp');
      executeSql(sprintf('update %s set dt=now() where id=%s',$this->tblname,"'".db_escape($id)."'"));
      return;
    }
    $ip=null;
    if (function_exists('getClientIp')) $ip=getClientIp();
    if ($id=='_new') session_id($id=sha1('ccms:'.uniqid(rand(), true)));
    executeSql(
      sprintf(
        'replace into `%s` set `id`=%s, `data`=%s, `ip`=%s',
        $this->tblname,
        "'".db_escape($id)."'",
        "'".db_escape($data)."'",
        empty($ip)?'NULL':"'{$ip}'"
      )
    );
  }

  public function destroy($id) {
    executeSql(
      sprintf(
        'delete from `%s` where `id`=%s',
        $this->tblname,
        "'".db_escape($id)."'"
      )
    );
  }

  public function close() {
    //noop
  }

  public function gc($maxlifetime) {
    $this->initialize();
    if (empty($this->session_name)) throw new Exception('session_name?');
    $this->tblname=tbl_name('_'.$this->session_name);
    executeSql(
      sprintf(
        'delete from `%s` where dt<DATE_SUB(now(), INTERVAL %d SECOND)',
        $this->tblname,
        $maxlifetime
      )
    );
  }
}

//end