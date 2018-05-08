<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 108 $
$LastChangedDate: 2009-05-24 17:33:49 +0200 (zo, 24 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class MySqlException extends Exception {
	public $error;
	public $str;
	public function __construct($str,$sql,$error) {
		parent::__construct("[MYSQL ERROR][{$error}][{$str}][{$sql}]");
		$this->str=$str;
		$this->error=$error;
		sql_log($this);
	}
}

function tbl_name($name) {
	return tbl_prefix($name).$name;
}

function tbl_prefix($name) {
  global $site_config;
  if (isset($site_config['tbl_prefix_func'])) {
    $tbl_prefix_func=$site_config['tbl_prefix_func'];
    $result=$tbl_prefix_func($name);
    if (!empty($result)) return $result;
  }
  return $site_config['database_prefix'];
}


function sql_log($s) { return MySqlWrapper::getInstance()->log($s); }
function db_connect() { return MySqlWrapper::getInstance()->db_connect(); }
function db_disconnect() { return MySqlWrapper::getInstance()->db_disconnect(); }
function db_query($sql) { return MySqlWrapper::getInstance()->db_query($sql); }
function db_escape($s) { return MySqlWrapper::getInstance()->db_escape($s); }
function dbStr($s) { return "'".MySqlWrapper::getInstance()->db_escape($s)."'"; }
function executePSql($sql,Array $parameters) { return MySqlWrapper::getInstance()->executePSql($sql,$parameters); }
function executeSql($sql) { return MySqlWrapper::getInstance()->executeSql($sql); }
function executeTransSql(Array $trans) { return MySqlWrapper::getInstance()->executeTransSql($trans); }
function executeTableSql($sql) { return MySqlWrapper::getInstance()->executeTableSql($sql); }
function getOneValue($sql,$field=false) { return MySqlWrapper::getInstance()->getOneValue($sql,$field); }
function getOneRow($sql) { return MySqlWrapper::getInstance()->getOneRow($sql); }
function getTableArray($sql,$idcol=false) { return MySqlWrapper::getInstance()->getTableArray($sql,$idcol); }
function getListTableArray($sql,$idcol=null,$valcol=null) { return MySqlWrapper::getInstance()->getListTableArray($sql,$idcol,$valcol); }

#--

class MySqlWrapper {

	public static $profile=null;

	private static $instance=null;
	private $connected=false;

	private function __construct() {
		// singleton
	}
	public function __destruct() {
		$this->log("__destruct");
		$this->db_disconnect();
	}

	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance=new MySqlWrapper();
			#--
			global $site_config;
			if (!empty($site_config["database_profile"])) {
				if (empty(self::$profile)) {
					$dbp=0;
					if (!empty($site_config['database_use_profile'])) $dbp=$site_config['database_use_profile'];
					else if (isset($_SESSION['ccms_dbp'])) $dbp=intval($_SESSION['ccms_dbp']);
					else if (isset($_COOKIE['ccms_dbp'])) $dbp=intval($_COOKIE['ccms_dbp']);
					else if (isset($site_config["database_default_profile"])) $dbp=$site_config["database_default_profile"];
					self::$profile=array_merge($site_config,$site_config["database_profile"][$dbp]);
					sql_log("profile #{$dbp} '".self::$profile['database_profile_name']."' is active");				
				}
			} else {
				self::$profile=$site_config;
			}
			#--
		}
		return self::$instance;
	}
	
	public static function setInstance(MySqlWrapper $instance) {
		self::$instance=$instance;
	}

	public function db_connect() {
		if ($this->connected) return;
		if (empty(self::$profile["database_host"]) || empty(self::$profile["database_scheme"])) {
			sql_log(print_r(self::$profile,true));
			throw new Exception("no config");
		}
		$profile=self::$profile;
		$fail=false;

		$db = @mysql_connect($profile["database_host"],$profile["database_username"],$profile["database_password"]);
		if (!$db) throw new Exception("fail MySQL connect");
		
		@mysql_query("SET NAMES " . $profile["database_names"]) or $fail=true;
		if ($fail) throw new MySqlException("{$profile["database_names"]} fail","",mysql_error());
		
		@mysql_select_db($profile["database_scheme"]) or $fail=true;
		if ($fail) throw new MySqlException("Database [{$profile["database_scheme"]}] fail.","",mysql_error());
		
		sql_log("db connected, {$profile["database_username"]}@{$profile["database_host"]}:{$profile["database_scheme"]}");

		$this->connected=true;
	}

	public function db_disconnect() {
		if (!$this->connected) return;
		@mysql_close();
		$this->log("db disconnected");
		$this->connected=false;
	}

	public function log($s) {
		global $site_config,$config;
		if (isset($config["logging_sql_no_select"]) && $config["logging_sql_no_select"] && preg_match("#^select#i",$s)) return;
    if (isset($config["logging_sql_only_select"]) && $config["logging_sql_only_select"] && !preg_match("#^select#i",$s)) return;
		$s = $site_config['database_scheme']." - {$s}";
		_log($s,isset($config["logging_sql"])?$config["logging_sql"]:LOG_LEVEL_TRACE);
	}

	public function db_query($sql) {
		if (empty($sql)) return false;
		$this->db_connect();
		$this->log($sql);
		$result = mysql_query($sql);
		$error=mysql_errno();
		if ($error!==0) {
			throw new MySqlException(mysql_error(),$sql,$error);
		}
		return $result;
	}

	public function db_escape($s) {
		if (strlen($s)<1) return "";
		if (is_numeric($s)) return $s;
		$this->db_connect();
		return mysql_real_escape_string($s);
	}

	public function executePSql($sql,Array $parameters) {
		$this->db_connect();
		foreach ($parameters as $k=>$v) {
			if ($v!=="NULL") $v="'".db_escape($v)."'";
			$sql=preg_replace("#:{$k}([ ,]+|$)#",$v.'$1',$sql);
		}
		executeSql($sql);
	}

	public function executeSql($sql) {
		global $insertedId;
		$this->db_query($sql);
		$result=mysql_affected_rows();
		$insertedId=mysql_insert_id();
		return $result;
	}

	public function executeTransSql(Array $trans) {
		foreach($trans as $i=>$sql) if (strlen($sql=trim($sql))>0) {
			if ($this->db_query($sql)===false) return false;
		}
		return true;
	}
	public function executeTableSql($sql) {
		return $this->db_query($sql);
	}
	public function getOneValue($sql,$field=false) {
		$d = $this->executeTableSql($sql);
		if ($d) {
			$line=mysql_fetch_assoc($d);
			if (is_array($line)) {
				return $field?@$line[$field]:@current($line);
			} else {
				$this->log("getOneValue [$field] did not fetch a row on [$sql]");
				return null;
			}
		} else {
			$this->log("getOneValue [$field] query failed on [$sql]");
			return null;
		}
	}
	public function getOneRow($sql) {
		$d = $this->executeTableSql($sql);
		if ($d) return mysql_fetch_assoc($d);
		else return null;
	}
	public function getTableArray($sql,$idcol=false) {
		$d = $this->executeTableSql($sql);
		$result=array();
		if ($d) while ($line=mysql_fetch_assoc($d)) {
			if ($idcol) $result[$line[$idcol]] = $line;
			else $result[] = $line;
		}
		return $result;
	}
	public function getListTableArray($sql,$idcol=null,$valcol=null) {
		$d = $this->executeTableSql($sql);
		$result=array();
		if ($d) while ($line=mysql_fetch_assoc($d)) {
			if (!$idcol || !$valcol) {
				$keys=array_keys($line);
				if (!$idcol) $idcol=$keys[0];
				if (!$valcol) $valcol=$keys[1];
			}
			$result[$line[$idcol]] = $line[$valcol];
		}
		return $result;
	}
}




//end
