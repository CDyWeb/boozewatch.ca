<?

#if (!class_exists('Memcache')) {
#--
class Memcache {
  public function __construct() {
    $this->tmp=dirname(__FILE__).'/tmp';
    if (!file_exists($this->tmp)) mkdir($this->tmp);
  }
  public function fn($key) {
    return $this->tmp.'/'.str_replace(':','_',$key).'.txt';
  }
  public function get($key) {
    _log(__FILE__.':get '.$key);
    $fn=$this->fn($key);
    if (file_exists($fn)) return unserialize(file_get_contents($fn));
  }
  public function set($key,$value,$flag,$expire) {
    _log(__FILE__.':set '.$key);
    file_put_contents($this->fn($key),serialize($value));
  }
  public function delete($key) {
    _log(__FILE__.':delete '.$key);
    $fn=$this->fn($key);
    if (file_exists($fn)) unlink($fn);
  }
  public function connect() {
    return true;
  }
}
#--
#}

//end