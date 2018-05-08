<?

if (function_exists('setting')) return;

function setting($key,$default=null) {
  global $settings;
  if (!$settings) {
    $settings=array();
    #foreach (getTableArray("select * from `".tbl_name("settings")."`","key") as $k=>$line) {
    #  $settings[$k]=$line["str"].$line["txt"];
    #}
    //_log(__FILE__ .":settings: ".print_r($settings,true));
  }
  if (!isset($settings[$key])) {
    _log(__FILE__ .":settings: {$key} is not set, returning default - {$default}");
    return $default;
  }
  _log(__FILE__ .":settings: {$key} - {$settings[$key]}");
  return $settings[$key];
}

function set_setting($key,$value) {
  #$field="`str`";
  #if (strlen($value)>250) $field="`str`=NULL, `txt`";
  #executeSql("replace into `".tbl_name("settings")."` set `key`='".db_escape($key)."', {$field}='".db_escape($value)."'");
  global $settings;
  $settings[$key]=$value;
}
