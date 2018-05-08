<?

if (defined('PHPUnit_MAIN_METHOD')) {
	global $_SESSION;
	$_SESSION=array();
} else {
  require getConfigItem('script_base').'shared/cyane/CcmsSessionHandler.class.php';
  
  function _ccms_session_close() {
    if (function_exists("_log")) _log('_ccms_session_close');
    if (class_exists('CcmsSessionHandler')) CcmsSessionHandler::close();
  }
  
  session_set_save_handler("CcmsSessionHandler::open", "_ccms_session_close", "CcmsSessionHandler::read", "CcmsSessionHandler::write", "CcmsSessionHandler::destroy", "CcmsSessionHandler::gc");
  if (!empty($_GET['cms_session'])) _log(__FILE__.' setting cookie from _GET cms_session '. ($_COOKIE['cms_session']=$_GET['cms_session']));
  if (!empty($_POST['cms_session'])) _log(__FILE__.' setting cookie from _POST cms_session '. ($_COOKIE['cms_session']=$_POST['cms_session']));
	session_name("cms_session");
	session_start();
}
