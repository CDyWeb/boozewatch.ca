<?

#--
if (defined('MOCKMEMCACHE')) require 'MockMemcache.inc';
#--

abstract class CcmsObjectCache {
  static $instance=null;
  public static function getInstance() {
    if (!empty(self::$instance)) return self::$instance;
    if (getConfigItem('CcmsObjectCache.use.Nocache',false)) {
      return self::$instance=new CcmsObjectCache_Nocache();
    }
    if (getConfigItem('CcmsObjectCache.use.Memcache',extension_loaded('memcache'))) try {
      return self::$instance=new CcmsObjectCache_Memcache();
    } catch (Exception $ex) {
      _log('Cannot use Memcache for caching, falling back to file based');
    }
    return self::$instance=new CcmsObjectCache_File();
  }
}

class CcmsObjectCache_Nocache extends CcmsObjectCache {
  public function __construct() {
  }
  public function get($key) {
    return null;
  }  
  public function set($key,$value,$expire=0) {
  }
  public function delete($key) {
  }
}

class CcmsObjectCache_Memcache extends CcmsObjectCache {
  public function __construct() {
    $this->mc=new Memcache();
    if (!$this->mc->connect(getConfigItem('Memcache.server','localhost'), getConfigItem('Memcache.port',11211))) throw new Exception("Could not connect to memcached");
  }
  public function get($key) {
    return $this->mc->get($key);
  }  
  public function set($key,$value,$expire=0) {
    if (empty($value)) return $this->delete($key);
    return $this->mc->set($key,$value,false,$expire);
  }
  public function delete($key) {
    return $this->mc->delete($key);
  }
}

class CcmsObjectCache_File extends CcmsObjectCache {
  public function __construct() {
    $this->cache_dir=getConfigItem('object_cache_dir',getConfigItem('abs_base').'cache');
    if (!file_exists($this->cache_dir)) {
      mkdir($this->cache_dir);
      if (!isset($_SERVER['WINDIR'])) chmod($this->cache_dir, 0777);
      if (!is_writable($this->cache_dir)) throw new Exception('Cache dir ['.$this->cache_dir.'] is nto writable');
    }
  }
  public function fn($key) {
    return $this->cache_dir.'/'.preg_replace('#[^\w\d-.]#','_',$key).'.txt'; //sha1($key).'.'.
  }
  public function get($key) {
    _log('CcmsObjectCache_File:get '.$key);
    $fn=$this->fn($key);
    if (file_exists($fn)) return unserialize(file_get_contents($fn));
  }
  public function set($key,$value,$expire=0) {
    _log('CcmsObjectCache_File:set '.$key);
    file_put_contents($this->fn($key),serialize($value));
  }
  public function delete($key) {
    _log('CcmsObjectCache_File:delete '.$key);
    $fn=$this->fn($key);
    if (file_exists($fn)) unlink($fn);
  }
}

//end