<?

if (!defined("CMS_LANG")) define("CMS_LANG","nl");
if (!defined("CMS_BACKUP_LANG")) define("CMS_BACKUP_LANG","en-US");

//Deprecated
class CmsLang {
	private static $instance;
	public static function getInstance() {
		if (!self::$instance) self::$instance=new CmsLang();
		return self::$instance;
	}

	protected $system_txt=null;
	protected $domain_txt=null;
	private $translate_value_cache=array();

	private function __construct() {
		#$this->cms_lang = @$_SESSION["cms_lang"];
		#$this->cms_backup_lang = @$_SESSION["cms_backup_lang"];
		if (isset($_REQUEST["cms_lang"])) $_SESSION["cms_lang"]=$_REQUEST["cms_lang"];

		$this->cms_lang = isset($_SESSION["cms_lang"])?$_SESSION["cms_lang"]:null;

		if (!$this->cms_lang) {
			$this->cms_lang = $_SESSION["cms_lang"] = CMS_LANG;
			if (!file_exists("./core/lang/{$this->cms_lang}/system_txt.php")) {
				$this->cms_lang = $_SESSION["cms_lang"] = CMS_BACKUP_LANG;
				if (!file_exists("./core/lang/{$this->cms_lang}/system_txt.php")) {
					throw new Exception("language files not found: ./core/lang/{$this->cms_lang}/system.php!");
				}
			}
		}
		
		$this->cms_backup_lang = isset($_SESSION["cms_backup_lang"])?$_SESSION["cms_backup_lang"]:null;

		if (!$this->cms_backup_lang) {
			$this->cms_backup_lang = $_SESSION["cms_backup_lang"] = CMS_BACKUP_LANG;
		}
	}
	
	public function setValue($tbl,$key,$value) {
		switch ($tbl) {
			case "domain_txt" : $this->domain_txt[$key]=$value; break;
			case "system_txt" : $this->system_txt[$key]=$value; break;
		}
	}
	public function getValue($tbl,$key,$defaultStr=null) {
		$arr=null;
		switch ($tbl) {
			case "domain_txt" : $arr=&$this->domain_txt; break;
			case "system_txt" : $arr=&$this->system_txt; break;
			default : return null;
		}
		if (isset($arr[$key])) return $arr[$key];
		return $arr[$key]=$defaultStr;
	}
	
	private function parse($s,$dest='domain_txt') {
    $arr=$this->$dest;
		foreach (explode("\n",$s) as $s) if ((strlen($s=trim($s))>0) && ($s[0]!="#")) {
			if (preg_match("#^([^=]+)=(.+)$#",$s,$match) && !isset($arr[$match[1]])) $this->setValue($arr,$match[1],$match[2]);
		}
    $this->$dest=$arr;
	}

	//Deprecated
	function getDomainText($cls,$prop=null,$args=false,$defaultStr=false) {
		$key=$cls;
		if ($prop!==null) $key.=".{$prop}";
		if (!$defaultStr) $defaultStr="?{$key}";
		return $this->getCmsText($key,$args,$defaultStr,"domain_txt");
	}

	//Deprecated
	function getCmsText($key,$args=false,$defaultStr=false,$tbl="system_txt") {
		if (!$this->cms_lang) throw new Exception("?");
		if (!$this->$tbl) {
			$this->$tbl=array();
			
			if (!file_exists("./core/lang/{$this->cms_lang}/{$tbl}.php")) throw new Exception("file not found: ./core/lang/{$this->cms_lang}/{$tbl}.php");
			require("./core/lang/{$this->cms_lang}/{$tbl}.php");
			if (file_exists("./custom/lang/{$this->cms_lang}/{$tbl}.php")) include("./custom/lang/{$this->cms_lang}/{$tbl}.php");
		}
		
    $arr=$this->$tbl;
		if (!isset($arr[$key])) {
			if (file_exists("./core/lang/{$this->cms_backup_lang}/system.php")) include("./core/lang/{$this->cms_backup_lang}/system.php");
			if (file_exists("./custom/lang/{$this->cms_backup_lang}/system.php")) include("./custom/lang/{$this->cms_backup_lang}/system.php");
		}

		$s=$this->getValue($tbl,$key,$defaultStr?$defaultStr:"?{$tbl}:{$key}");
		if ($args) {
			if (is_array($args)) {
				foreach($args as $i=>$v) $s=str_replace('{'.$i.'}',$v,$s);
			} else {
				$s = str_replace('{0}',$args,$s);
			}
		}
		return $s;
	}

	function isWithTargetLang() {
		global $site_config;
		$res = isset($site_config["language"]) && isset($site_config["language"]["base"]) && isset($site_config["language"]["available"]) && (count($site_config["language"]["available"])>0);
		if ($res) {
			if (!isset($_SESSION["target_lang"]) && isset($site_config["language"]["default"])) $_SESSION["target_lang"]=$site_config["language"]["default"];
			if (!isset($_SESSION["target_lang"])) $_SESSION["target_lang"]=$site_config["language"]["base"];
		}
		//debug($_SERVER["HTTP_HOST"]." isWithTargetLang? ".($res?"yes":"no"));
		return $res;
	}

		
	function translate_value($tableObject, $id, $field, $defaultValue) {
		$tname = $tableObject->getName();
		$fname = $field->getName();
		if (!isset($this->translate_value_cache[$tname])) {
			$ta=array();
			foreach (getTableArray("select * from iprocms_translation where t='{$tname}' and l='".$_SESSION["target_lang"]."'") as $line) {
				$ta[$line["id"]][$line["k"]]=$line["v"].$line["m"];
			}
			$this->translate_value_cache[$tname] = $ta;
		}
		if (!isset($this->translate_value_cache[$tname][$id][$fname])) {
			$v="v";
			if ($field->getType()==CCMSDomainField::FIELDTYPE_TEXT) $v="m";
			if ($field->getType()==CCMSDomainField::FIELDTYPE_SIMPLETEXT) $v="m";
			executeSql("insert into iprocms_translation set t='{$tname}', l='".$_SESSION["target_lang"]."', id='{$id}', k='{$fname}', {$v}='".db_escape($defaultValue)."'");
			$this->translate_value_cache[$tname][$id][$fname]=$defaultValue;
		}
		return $this->translate_value_cache[$tname][$id][$fname];
	}

}

//Deprecated
function getCmsText($key,$args=false,$defaultStr=false,$tbl="system_txt") {
	return CmsLang::getInstance()->getCmsText($key,$args,$defaultStr,$tbl);
}

//Deprecated
function getDomainText($cls,$prop=null,$args=false,$defaultStr=false) {
	return CmsLang::getInstance()->getDomainText($cls,$prop,$args,$defaultStr,"");
}

//end















