<?

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'CyaneAllTests::main');
}

require_once dirname(__FILE__).'/../load.inc';
_log('CyaneAllTests');

if (!class_exists('Framework_AllTests')) {
  class Framework_AllTests {
    public static function suite() {
      $files = UnitTest::files('/test.*\.php/', dirname(__FILE__));
      $suite = new PHPUnit_Framework_TestSuite('CyaneAllTests');
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