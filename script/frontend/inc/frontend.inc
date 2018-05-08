<?php
/*
------------------------------------------------------------

  CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
WITHOUT THE PERMISION OF THE AUTHOR.    

Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class Frontend {

  protected $templateProcessor=null;
  protected $router=null;
  protected $output=null;
  
  protected $language=null;
  protected $stats_log_enabled=false;

  public function __construct() {
    _log(get_class().":__construct");
    //db_connect(); <<< ----
    global $instance;
    $instance=$this;
  }
  public function __destruct() {
    _log(get_class().":__destruct");
    db_disconnect();
  }
  
  public function getRouter() {
    if ($this->router==null) {
      $routerClass=getConfigItem('frontend.routerClass','DefaultRouter');
      $this->router=new $routerClass($this);
    }
    return $this->router;
  }
  
  public function getTemplateProcessor() {
    if ($this->templateProcessor==null) {
      $processorClass=getConfigItem("frontend.templateProcessorClass","DefaultTemplateProcessor");
      $this->templateProcessor=new $processorClass($this);
    }
    return $this->templateProcessor;
  }
  
  public function getOutput() {
    return $this->output;
  }
  
  public function getHtmlOutput($reset=false) {
    if (($this->output==null) || $reset) {
      $outputClass=getConfigItem("frontend.outputClass","DefaultHtmlOutput");
      $this->output=new $outputClass($this);
    }
    return $this->output;
  }
  
  public function getAjaxProcessor() {
    if ($this->templateProcessor==null) $this->templateProcessor=new AjaxTemplateProcessor($this);
    return $this->templateProcessor;
  }
  
  public function getAjaxOutput($reset=false) {
    if (($this->output==null) || $reset) {
      $this->output=new PlainOutput($this);
    }
    return $this->output;
  }
  
  public function getRssProcessor() {
    if ($this->templateProcessor==null) $this->templateProcessor=new RssTemplateProcessor($this);
    return $this->templateProcessor;
  }
  
  public function getRssOutput($reset=false) {
    if (($this->output==null) || $reset) {
      $this->output=new XmlOutput($this);
      $this->output->setContentType('application/rss+xml');
      $this->output->append('<rss version="2.0"><!--{cursor}--></rss>');
    }
    return $this->output;
  }
  
  public function getSitemapProcessor() {
    if ($this->templateProcessor==null) $this->templateProcessor=new SitemapTemplateProcessor($this);
    return $this->templateProcessor;
  }
  
  public function getSitemapOutput() {
    if ($this->output==null) $this->output=new XmlOutput($this);
    return $this->output;
  }
  
  protected function getHtmlTemplateFileName() {
    $fn=getConfigItem('html_template_file','index.html');
    if ($fn===null) return null;
    return getConfigItem('html_template_dir').$fn;
  }

  public function getHtmlTemplate($fn=null) {
    if (empty($fn)) $fn=$this->getHtmlTemplateFileName();
    if ($fn===null) return null;
    $res=file_get_contents($fn);
    $res=str_replace("\r","",$res);
    return $res;
  }
  
  public function getUserAgentLanguages() {
    $result=array();
    if (empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return $result;
    $s=preg_replace('#;q=[\d.]+#',',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
    _log($s);
    foreach(explode(',',$s) as $t) if (preg_match('#^(\w\w-\w\w)#',$t,$match) || preg_match('#^(\w\w)#',$t,$match)) {
      $m='_l'.strtoupper(str_replace('-','_',$match[1]));
      if (method_exists('CcmsLanguage',$m)) {
        $l=CcmsLanguage::$m();
        $k=(string)$l;
        $result[$k]=$l;
      }
    }
    return array_values($result);
  }
  
  public function getLanguage() {
    if (empty($this->language)) {
      $config=getConfigItem('language',array('default'=>'en','available'=>array('en')));
      if (clientIsRobot()) $this->language=CcmsLanguage::parse($config['default']);
      else $this->language=$this->getClientLanguage($config);
    }
    return $this->language;
  }
  public function setLanguage($language) {
    unset($this->inBaseLanguage);
    $this->language=$language;
  }
  
  public function uriLanguage() {
    $lang=$this->getLanguage();
    if (empty($lang)) return 'en';
    if (is_string($lang)) return $lang;
    return $lang->language;
  }
  
  public function isInBaseLanguage() {
    if (!isset($this->inBaseLanguage)) {
      $config=getConfigItem('language',array('base'=>'en'));
      $l=CcmsLanguage::parse($config['base']);
      $this->inBaseLanguage = $l->__equals($this->getLanguage());
    }
    return $this->inBaseLanguage;
  }
  
  public function getRemoteHostLanguage() {
    $host=getClientHost();
    if (preg_match('#\.(\w{2,3})$#',$host,$match)) {
      $tld=$match[1];
      if (strlen($tld)==3) return CcmsLanguage::_lEN_US();
      if ($tld=='uk') return CcmsLanguage::_lEN_GB();
      $m='_l'.strtoupper($tld);
      if (method_exists('CcmsLanguage',$m)) return CcmsLanguage::$m();
    }
    return null;
  }

  public function getClientLanguage($config) {
    if (!clientIsRobot()) {
      $ua=$this->getUserAgentLanguages();
      $available=array();
      foreach ($config['available'] as $a) $available[$a]=CcmsLanguage::parse($a);
      foreach ($ua as $l) foreach ($available as $a) if ($l->__equals($a)) return $a;
      if ((($res=$this->getRemoteHostLanguage())!==null) && in_array($res->language,$config['available'])) return $res;
    }
    return CcmsLanguage::parse($config['default']);
  }
  
  protected function outputLanguageByUri($available) {
    foreach ($available as $s) {
      $l=preg_replace('#_.*$#','',$s);
      if (preg_match("#^/{$l}(|/.*)$#i",$_SERVER["_URI"],$match)) {
        $_SERVER["_URI"]=strlen($match[1])?$match[1]:'/';
        $this->language = CcmsLanguage::parse($s);
        return;
      }
    }
    $this->getHtmlOutput()->redirect('/'.($this->uriLanguage()).'/'.trim($_SERVER["_URI"],'/'));
  }
  
  public function isMultiLang() {
    $config=getConfigItem('language',array('default'=>'en','available'=>array('en')));
    return (count($config['available'])>1);
  }

  public function outputLanguage() {
    _log(get_class().":outputLanguage");
    $config=getConfigItem('language',array('default'=>'en','available'=>array('en')));
    $available=$config['available'];
    if (count($available)<2) {
      $this->language = CcmsLanguage::parse(current($available));
      _log(get_class().":outputLanguage - no multilang - ".json_encode($available));
      return;
    }
    _log(get_class().":outputLanguage - multilang available : ".json_encode($available));
    
    $selector=null;
    if (isset($config['selector'])) $selector=$config['selector'];
    
    switch ($selector) {

    case 'uri-prefix' : {
        $this->outputLanguageByUri($available);
        break;
      }
      
    case 'domain' : {
        if (isset($config['domain'])) foreach($config['domain'] as $d=>$l) {
          if (preg_match('#'.$d.'#',$_SERVER['HTTP_HOST'])) {
            $this->language = CcmsLanguage::parse($l);
            return;
          }
        }
        $this->language = CcmsLanguage::parse($config['default']);
        return;
      }

    case 'session' :
      default : {
        if (!isset($_SESSION['language']) || isset($_GET['resetLanguage'])) {
          _log('getClientLanguage for '.(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])?$_SERVER["HTTP_ACCEPT_LANGUAGE"]:'').' - '.getClientHost());
          $_SESSION['language']=$this->getClientLanguage($config);
          _log('lang='.$_SESSION['language']);
          if (empty($_SESSION['language'])) throw new Exception('client lang');
        }
        $this->language = $_SESSION['language'];
        break;
      }
    }
  }
  
  public function getObjectCache() {
    return CcmsObjectCache::getInstance();
  }

  public function getStaticTranslator($refresh=false, $language=null) {
    if (empty($this->staticTranslator) || $refresh) {
      $l=empty($language)?$this->getLanguage():$language;
      if ($l instanceof CcmsLanguage) $l=$l->code();
      $this->staticTranslator = ezcCcmsTranslation::getInstance('static', $l, $refresh, $this->getObjectCache());
    }
    return $this->staticTranslator;
  }

  public function getContentsTranslator($refresh=false, $language=null) {
    if (empty($this->contentsTranslator) || $refresh) {
      #--
      $l=empty($language)?$this->getLanguage():$language;
      if ($l instanceof CcmsLanguage) $l=$l->code();
      $this->contentsTranslator = ezcCcmsTranslation::getInstance('contents', $l, $refresh, $this->getObjectCache());
      #--
    }
    return $this->contentsTranslator;
  }
  
  function translateContents($tbl, $id, $fld, $default, $params=null) {
    return $this->getContentsTranslator()->frontendTranslate($tbl,$id,$fld,$default,$params);
  }
  function translateStatic($key, $params=null) {
    return $this->_($key, $params);
  }
  function _($key, $params=null) {
    return $this->getStaticTranslator()->getTranslation($key, $params);
  }
  
  public function localStr($base, $tbl, $id, $fld, $params=null) {
    if ($this->isInBaseLanguage()) return $base;
    return $this->translateContents($tbl, $id, $fld, $base, $params);
  }

  public function localArr($base,$tbl,$id,$fields=null) {
    if ($this->isInBaseLanguage()) return $base;
    foreach ($base as $fld=>$value) {
      if ($fields!==null && !in_array($fld,$fields)) continue;
      if (is_string($value)) $base[$fld] = $this->translateContents($tbl, $id, $fld, $base[$fld]);
    }
    return $base;
  }

  public function localObj($base,$tbl,$id) {
    if ($this->isInBaseLanguage()) return $base;
    if (method_exists($base,'translate')) $base->translate($this->getContentsTranslator());
    else {
      $arr=$base->get_object_vars($base);
      foreach ($arr as $fld=>$value) $base->$fld = $this->translateContents($tbl, $id, $fld, $base->$fld);
    }
    return $base;
  }

  public function locales() {
    $locales=$this->getHtmlOutput()->getLanguage()->getLocales();
    if (!empty($locales)) {
      setlocale(LC_TIME,$locales);
      _log(get_class()."::locales - Time locale set to " . serialize($locales));
    }

    //@todo no session abuse
    if (function_exists('getSessionCurrency')) {
      $arr=getConfigItem('currency',array('default'=>'EUR'));
      $cur_locale=getCurrencyConfig("locale",$arr['default']); //getSessionCurrency());
      if (!empty($cur_locale)) {
        setlocale(LC_MONETARY,$cur_locale);
        _log(get_class()."::locales - Currency locale set to {$cur_locale}");
      }
    }
  }
  
  public function getSiteURL() {
    return $this->getSiteLink(true);
  }

  public function getSiteURI() {
    return $this->getSiteLink(false);
  }
  
  public function getSiteLink($full=true) {
    $result = $full ? SITE_BASE_URL : SITE_BASE_URI;
    $config=getConfigItem('language');
    if (isset($config['selector']) && $config['selector']=='uri-prefix') $result.=$this->uriLanguage().'/';
    return $result;
  }
  function getPageURL($id,$append=null) {
    return trim($this->getSiteURL(),'/').$this->router->pageUri($id,$append);
  }
  
  public function setting($key,$default=null) {
    global $settings;
    if (!$settings) {
      $settings=$this->getObjectCache()->get(getConfigItem('domain').':settings');
      if (!$settings) $to_cache=true;
    }
    $result=setting($key,$default);
    if (isset($to_cache)) $this->getObjectCache()->set(getConfigItem('domain').':settings',$settings,60*60*24);
    return $result;
  }

  public function render() {
    _log(get_class().":render");
    
    $this->stats_log_enabled=setting('SiteStats.log.enabled',true);

    $router = $this->getRouter();

    if (preg_match('#/sitemap\.xml$#i',$_SERVER["_URI"])) {
      $output=$this->getSitemapOutput();
      $this->getSitemapProcessor()->process($router,"",$output);
      $output->output();
      return;
    }
    
    $output=$this->getHtmlOutput();
    $this->outputLanguage();
    if ($output->isRedirected()) {
      $output->output();
      return;
    }

    if ($this->setting('is_online')=='no') {
      $html=file_get_contents(getConfigItem('public_base').'shared/offline/index.html');
      $this->output->append($html);
      $output->output();
      return;
    }

    $this->locales();

    $page = $router->route($_SERVER["_URI"]);

    if ($output->isRedirected()) {
      $output->output();
      return;
    }

    if (empty($page)) {
      $output->redirect($router->pageUri());
      $output->output();
      return;
    }
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')) {
      $this->stats_log_enabled=false;
      $this->getAjaxProcessor()->process($router,'',$output=$this->getAjaxOutput(true));
    } else if (preg_match('#\.rss$#i',$_SERVER['_URI'])) {
      $this->getRssProcessor()->process($router,'',$output=$this->getRssOutput(true));
    } else {
      $tmpl=$this->getHtmlTemplate();
      $this->getTemplateProcessor()->process($router,$tmpl,$output);
    }
    $output->output();
    $this->afterRender();
    _log(__FILE__ . ' is done');
  }
  
  protected function afterRender() {
    if (($_SERVER['REQUEST_METHOD']=='POST') || (!$this->getHtmlOutput()->isRedirected() && !clientIsRobot())) {
      if ($this->stats_log_enabled) {
        global $clsSiteStats;
        $clsSiteStats='SiteStats';
        if (!class_exists($clsSiteStats)) {
          $require = getConfigItem('SiteStats.class',setting('SiteStats.class','../shared/cyane/SiteStats.class.php'));
          if (empty($require)) return;
          require $require;
        }
        if (!empty($clsSiteStats)) {
          $stats=new $clsSiteStats();
          $stats->logVisitorInfo();
        }
      }
    }
  }

}

function getInstance() {
  global $instance;
  return $instance;
}

function frontend_setcookie($name,$value,$expire=0,$path='/',$domain=null,$secure=false,$httponly=false) {
  //if (empty($domain)) $domain=$_SERVER['HTTP_HOST'];
  getInstance()->getHtmlOutput()->setCookie($name,$value,$expire,$path,$domain,$secure,$httponly);
}

//end