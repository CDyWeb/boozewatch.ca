<?

require_once dirname(__FILE__).'/load.inc';
_log('AllTests');

require 'PHPUnit/autoload.php';
require_once dirname(__FILE__).'/UnitTest.php';

require_once 'cyane/CyaneAllTests.php';
require_once 'plugins/PluginsAllTests.php';

class Framework_AllTests {
  public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('AllTests');
    
    foreach (array('cyane','plugins') as $sub) {
      $files = UnitTest::files('/test.*\.php/', dirname(__FILE__).'/'.$sub);
      foreach($files  as $file){
        require_once $sub.'/'.$file;
        $file = str_replace('.php', '', $file);
        if (defined('TEST_ONLY') && (strcasecmp($file,TEST_ONLY)!==0)) continue; 
        $suite->addTestSuite($file);
      }
    }
    
		log_message("debug","suite building done, ".$suite->count()." tests found");
		return $suite;
	}
}


//end