<?

class SiteStats {

	public $gc_probability=10;
	public $gc_days=33;

	public function __construct($cleanUp=true) {
		if (function_exists('setting')) {
			$this->gc_probability=max(0,intval(setting('SiteStats.gc_probability',10))); //0-100;
			$this->gc_days=max(1,intval(setting('SiteStats.gc_days',33)));
		}
		if ($cleanUp && (rand(1,99)<$this->gc_probability)) $this->cleanUp();
	}

	public function visitorInfo() {
		$keys=array_keys($_SERVER);
		foreach (array('_URI','REQUEST_METHOD','REMOTE_ADDR','HTTP_REFERER','HTTP_USER_AGENT','HTTP_ACCEPT_LANGUAGE') as $k) {
			if (!in_array($k,$keys)) $_SERVER[$k]=null;
		}
		$request=array_merge($_GET,$_POST);
		$result=array(
			'uri'=>$_SERVER['_URI'],
			'method'=>empty($_SERVER['REQUEST_METHOD'])?'GET':$_SERVER['REQUEST_METHOD'],
			'request'=>empty($request)?null:json_encode($request),
			'session'=>session_id(),
			'ip'=>getClientIp(),
			'host'=>getClientHost(),
			'referer'=>$_SERVER['HTTP_REFERER'],
			'ua'=>$_SERVER['HTTP_USER_AGENT'],
			'ua_lang'=>$_SERVER["HTTP_ACCEPT_LANGUAGE"],
		);
		return $result;
	}
	
	public function logVisitorInfo(array $info=null) {
		if (!$this->isEnabled()) return;

		if (empty($info)) $info=$this->visitorInfo();

		$set_sql="";
		$sep="";
		foreach ($info as $k=>$v) {
			$set_sql.=$sep."`{$k}`=".(empty($v)?'NULL':"'".db_escape($v)."'");
			$sep=", ";
		}
		$sql="INSERT INTO `{$this->tblName()}` set {$set_sql}";
		executeSql($sql);
	}

	public function cleanUp() {
		$dt=time() - daysToSeconds($this->gc_days);
		$sql="delete from `{$this->tblName()}` where dt<'".date('Y-m-d',$dt)."'";
		executeSql($sql);
	}

	#--
	protected function isEnabled() {
		$result=function_exists('setting')?setting('SiteStats.log.enabled',null):null;
		if ($result===null) {
			$this->createTable();
			$result='SiteStats';
			if (function_exists('set_setting')) {
				set_setting('SiteStats.log.enabled',$result);
			}
		}
		return $result;
	}

	protected function tblName() {
		return tbl_name('log');
	}

	protected function createTable() {
		$sql=<<<SQL
CREATE TABLE IF NOT EXISTS `{$this->tblName()}` (
  `uri` varchar(255) NOT NULL,
  `method` enum('GET','POST') NOT NULL,
  `request` text,
  `dt` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `session` char(40) default NULL,
  `ip` char(16) NOT NULL,
  `host` varchar(100) default NULL,
  `referer` varchar(255) default NULL,
  `ua` varchar(255) default NULL,
  `ua_lang` varchar(100) default NULL  
) ENGINE=MyISAM;
SQL;
		executeSql($sql);
	}
}

//end