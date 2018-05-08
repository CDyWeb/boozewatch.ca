<?

class CCMSTranslator {
  protected $lang;
	protected $translator=null;
	protected $ccmsTranslation=null;
	protected $domainTranslation=null;
  protected $staticTranslation=null;
  
  protected static $instances=array();
  
  public function __construct($lang) {
    $this->lang=$lang;
    self::$instances[$lang]=$this;
  }
  
  public static function instance($lang=null) {
    if (empty($lang)) {
      if (!empty(self::$instances)) return current(self::$instances);
      $config=getConfigItem('language');
      $lang=$config['default'];
    }
    if (empty(self::$instances[$lang])) self::$instances[$lang]=new CCMSTranslator($lang);
    return self::$instances[$lang];
  }

  public function getTranslator() {
		if (empty($this->translator)) {
      $this->translator=ezcCcmsTranslationManager::getCcmsInstance();
      $this->translator->addFilter( ezcTranslationBorkFilter::getInstance() );
    }
    return $this->translator;
  }
  public function getCcmsTranslation() {
    if (empty($this->ccmsTranslation)) { //$this->ccmsTranslation = new ezcCcmsTranslation($this->getTranslator(), $this->lang, 'ccms');
      require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
      $this->ccmsTranslation = ezcCcmsTranslation::getInstance('ccms', $this->lang, false, CcmsObjectCache::getInstance());
      $this->ccmsTranslation->manager->addFilter( ezcTranslationBorkFilter::getInstance() );
    }
    return $this->ccmsTranslation;
  }
  public function getDomainTranslation() {
    if (empty($this->domainTranslation)) { //$this->domainTranslation = new ezcCcmsTranslation($this->getTranslator(), $this->lang, 'domain');
      require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
      $this->domainTranslation = ezcCcmsTranslation::getInstance('domain', $this->lang, false, CcmsObjectCache::getInstance());
      $this->domainTranslation->manager->addFilter( ezcTranslationBorkFilter::getInstance() );
    }
    return $this->domainTranslation;
  }
  public function getStaticTranslation() {
    if (empty($this->staticTranslation)) { //$this->staticTranslation = new ezcCcmsTranslation($this->getTranslator(), $this->lang, 'static');
      require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
      $this->staticTranslation = ezcCcmsTranslation::getInstance('static', $this->lang, false, CcmsObjectCache::getInstance());
      $this->staticTranslation->manager->addFilter( ezcTranslationBorkFilter::getInstance() );
    }
    return $this->staticTranslation;
  }
}

function ccmsTranslate_ccms($k,$p=null) {
  return CCMSTranslator::instance()->getCcmsTranslation()->getTranslation($k,$p);
}
function ccmsTranslate_domain($k,$k2=null,$p=null) {
  if (strlen($k2)>0) $k.='.'.$k2;
  return CCMSTranslator::instance()->getDomainTranslation()->getTranslation($k,$p);
}
function ccmsTranslate_static($k,$p=null) {
  return CCMSTranslator::instance()->getStaticTranslation()->getTranslation($k,$p);
}

//end