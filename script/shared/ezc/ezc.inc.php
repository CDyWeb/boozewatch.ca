<?

class CcmsEzcAutoload {

	private static $ezc_autoload=null;
	private static $ezc_dir=null;

	public function __construct() {
		self::$ezc_dir=dirname(__FILE__);
	}
	
	public function autoload($classname) {
		if (self::$ezc_autoload===null) {
			$ez_result=array();
			foreach (readdir_ls(self::$ezc_dir) as $n=>$p) {
				if (!is_file($p) || !preg_match('#^(.*)_autoload\.php$#',$n,$match)) continue;
				$arr = require $p;
				$ez_result=array_merge($ez_result,$arr);
			}
			self::$ezc_autoload = $ez_result;
		}

		if (!array_key_exists($classname,self::$ezc_autoload)) return false;
		
		require self::$ezc_dir.'/'.self::$ezc_autoload[$classname];
		return true;
	}

}

//end