<?

class MockMySqlWrapper extends MySqlWrapper {
	public function __construct() {
	}
	public function db_connect() {
		return true;
	}
	public function db_disconnect() {
		return true;
	}
	public function db_query($sql) {
		if (empty($sql)) return false;
		$this->log($sql);
		return null;
	}
	public function db_escape($s) {
		if (strlen($s)<1) return "";
		if (is_numeric($s)) return $s;
		return str_replace("'","''",$s);
	}
	public function executeSql($sql) {
		$this->db_query($sql);
		return 1;
	}
	public function getOneValue($sql,$field=false) {
		$this->db_query($sql);
		return null;
	}
	public function getOneRow($sql) {
		return array();
	}
	public function getTableArray($sql,$idcol=false) {
		return array();
	}
	public function getListTableArray($sql,$idcol=null,$valcol=null) {
		return array();
	}
}


//end