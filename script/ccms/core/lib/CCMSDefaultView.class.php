<?

class CCMSDefaultView extends CCMSView {

	protected $script=null;
  protected static $system_lang;
  protected static $ccmsTranslator;

	public function __construct($script, CCMSModel $model=null) {
		parent::__construct($model);
		$this->script=$script;
	}

  public static function getSystemLang() {
    if (empty(self::$system_lang)) self::$system_lang=isset($_COOKIE['ccms_system_lang'])?$_COOKIE['ccms_system_lang']:'en_US';
    return self::$system_lang;
  }

  public static function getCCMSTranslator() {
    if (empty(self::$ccmsTranslator)) self::$ccmsTranslator=new CCMSTranslator(self::getSystemLang());
    return self::$ccmsTranslator;
  }

  public static function getCcmsTranslation() {
    return self::getCCMSTranslator()->getCcmsTranslation();
  }
  public static function getDomainTranslation() {
    return self::getCCMSTranslator()->getDomainTranslation();
  }
  public static function getStaticTranslation() {
    return self::getCCMSTranslator()->getStaticTranslation();
  }
	
	public function getScript($script) {
		return $script;
	}
	public function setScript($script) {
		$this->script=$script;
	}
	
	protected function getScriptPath() {
		if (substr($this->script,0,1)=="/") return ".".$this->script;
		if (substr($this->script,0,1)==".") return $this->script;
		return "view/".$this->script;
	}
	
	protected function template_repl($key) {
		return "";
	}
	
	protected function getPlaceHolders($str) {
		$res=preg_match_all("#\{(\w+)\}#",$str,$matches,PREG_SET_ORDER);
		return $res?$matches:null;
	}

	protected function getTranslateUnits($str) {
		$res=preg_match_all("#\{translate:([^{]+)\}#",$str,$matches,PREG_SET_ORDER);
		return $res?$matches:null;
	}
	
	protected function translate($str) {
		if (!($matches=$this->getTranslateUnits($str))) return $str;
		foreach ($matches as $match) {
			$str=str_replace($match[0],self::getCcmsTranslation()->getTranslation($match[1]),$str);
		}
		return $str;
	}
	
	public function _($k,$p=null,$default=null) {
		return self::getCcmsTranslation()->getTranslation($k,$p,$default);
	}
	public function cmsTranslate($k,$p=null,$default=null) {
		return $this->_($k,$p);
	}
	public function domainTranslate($k,$k2=null,$p=null,$default=null) {
		if (strlen($k2)>0) $k.='.'.$k2;
		if ((($m=$this->getModel())!==null) && (($m=$m->getDomainManager())!==null) && isset($m->translate[$k])) return $m->translate[$k];
		return self::getDomainTranslation()->getTranslation($k,$p,$default);
	}
	public function staticTranslate($k,$p=null,$default=null) {
    return self::getStaticTranslation()->getTranslation($k,$p,$default);
	}


	protected function template($str) {
		if (!($matches=$this->getPlaceHolders($str))) return $str;
		foreach ($matches as $match) {
			switch($match[1]) {
				case "REQUEST_URI" : {
					$repl=$_SERVER[$match[1]]; 
					break;
				}
				case "username" : {
					$repl=trim("{$_SESSION["user"]["first_name"]} {$_SESSION["user"]["last_name"]}");
					break;
				}
        case "base_url" :
        case "shared_url" :
				case "resources_url" : {
					$repl=getConfigItem($match[1]);
					break;
				}
				default :
					$repl=$this->template_repl($match[1]);
			}
			$str=str_replace($match[0],$repl,$str);
		}
		return $str;
	}
  
  public function base_url($append='') {
    return getConfigItem('base_url').$append;
  }
  public function shared_url($append='') {
    return getConfigItem('shared_url').$append;
  }
  public function resources_url($append='') {
    return getConfigItem('resources_url').$append;
  }

	public function render() {
		ob_start();
		$sp=$this->getScriptPath();
		$p=getResourcePath($sp);
		if (!$p) throw new Exception("resource not found: {$sp}");
		_log("getting view : {$sp} : {$p}");
		require_once($p);
		$res=ob_get_contents();
		ob_end_clean();
		return $this->template($this->translate($res));
	}
}

//end