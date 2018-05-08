<?

abstract class BodyView extends CCMSDefaultView {

	protected $pagepath;
	protected $lang;
	protected $inline=false;

	public function __construct(CCMSModel $model=null) {
		parent::__construct('body.php', $model);
		global $config;
		$this->pagepath=array(
			"<a href='{$config["base_url"]}/body.html'>CCMS</a>"
		);
		$this->lang=CmsLang::getInstance();
	}
	
	public function getLang() {
		return $this->lang;
	}
	public function isInline() {
		return $this->inline;
	}
	public function setInline($inline) {
		$this->inline=$inline;
    $this->setScript('inline.php');
	}

	abstract function outputScripts();
	abstract function outputStyles();
	abstract function outputContent();
	
	public function getPagepath() {
		return $this->pagepath;
	}
	public function setPagePath(Array $pagepath) {
		log_message("trace",get_class($this)." setPagePath {0}",array($pagepath));
		$this->pagepath=$pagepath;
	}
	public function addToPagePath($s) {
		log_message("trace",get_class($this)." addToPagePath {0}",array($s));
		$this->pagepath[]=$s;
	}
	
	public function outputPagepathDiv() {
		$p=$this->getPagepath();
		if (count($p)<1) return;
		if (!$this->inline) echo "
<div id='pagepath'>
".implode("&nbsp; &raquo; &nbsp;",$p)."
</div>";
	}

	public function outputContentDiv() {
		ob_start();
		$this->outputContent();
		$res=ob_get_contents();
		ob_end_clean();
		echo "<div id='content'>{$res}</div>";
	}

	protected function getPlaceHolders($str) {
		$res=parent::getPlaceHolders($str);
		foreach ($res as $k=>$v) if ($v[1]=="body") {$body=$v;unset($res[$k]);}
		array_unshift($res,$body);
		return $res;
	}
	
	public function getTitle() {
		return "CDyWeb CMS";
	}

	protected function template_repl($keyword) {
		switch ($keyword) {
			case "title" : 
				return $this->getTitle();

			case "scripts" :
				ob_start();
				$this->outputScripts();
				$res=ob_get_contents();
				ob_end_clean();
				return $res;
			
			case "styles" :
				ob_start();
				$this->outputStyles();
				$res=ob_get_contents();
				ob_end_clean();
				return $res;

			case "body" :
				ob_start();
				$this->outputContentDiv();
				$body=ob_get_contents();
				ob_end_clean();

				ob_start();
				$this->outputPagepathDiv();
				$pp=ob_get_contents();
				ob_end_clean();
				return $pp.$body;

			default :
				return parent::template_repl($keyword);
		}
	}
  
  public function appFindInc($dir,$name) {
    $fn=getConfigItem('script_app').$dir.'/'.$name;
    if (file_exists($fn)) {
      return $fn;
    }
    _log('BodyView::appFindInc not found: '.$fn);
    return false;
  }

}

//end