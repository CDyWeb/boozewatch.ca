<?

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'PluginsAllTests::main');
}

require_once dirname(__FILE__).'/../load.inc';
_log('PluginsAllTests');

require 'PHPUnit/autoload.php';
require_once dirname(__FILE__).'/../UnitTest.php';

if (!class_exists('Framework_AllTests')) {
  class Framework_AllTests {
    public static function suite() {
      $files = UnitTest::files('/test.*\.php/', dirname(__FILE__));
      $suite = new PHPUnit_Framework_TestSuite('PluginsAllTests');
      foreach($files  as $file){
        require_once $file;
        $file = str_replace('.php', '', $file);
        if (defined('TEST_ONLY') && (strcasecmp($file,TEST_ONLY)!==0)) continue; 
        $suite->addTestSuite($file);
      }
      log_message("debug","suite building done, ".$suite->count()." tests found");
      return $suite;
    }
  }
}

//end