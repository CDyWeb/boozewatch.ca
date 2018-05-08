<?

require_once dirname(__FILE__)."/IncAllTests.php";
echo "PHPUnit_MAIN_METHOD: ".PHPUnit_MAIN_METHOD;

class testFrontend extends UnitTestCase {

	public function __construct() {
		require_once '../../shared/cyane/mysql.inc.php';

    require_once '../inc/locale.inc';
    
    require_once '../cls/FrontendBase.inc';
    require_once '../cls/Router.inc';
    require_once '../cls/DefaultRouter.inc';
    require_once '../cls/TemplateProcessor.inc';
    require_once '../cls/DefaultTemplateProcessor.inc';
    require_once '../cls/RssTemplateProcessor.inc';
    require_once '../cls/SitemapTemplateProcessor.inc';
    require_once '../cls/Output.inc';
    require_once '../cls/HttpOutput.inc';
    require_once '../cls/XmlOutput.inc';
    require_once '../cls/Page.inc';
    require_once '../cls/DefaultHtmlOutput.inc';

    require_once '../inc/frontend.inc';
    $this->frontend=new Frontend();
		log_message('debug', get_class($this).' UnitTestCase constructed');
	}
	
	public function testConstruct() {
		//noop
	}
  public function testDestruct() {
    //noop
  }
  public function testGetRouter() {
    $r=$this->frontend->getRouter();
    $this->assertTrue($r instanceof DefaultRouter);
  }
  public function testGetTemplateProcessor() {
    $r=$this->frontend->getTemplateProcessor();
    $this->assertTrue($r instanceof DefaultTemplateProcessor);
  }
  public function testGetHtmlOutput() {
    $r=$this->frontend->getHtmlOutput();
    $this->assertTrue($r instanceof DefaultHtmlOutput);
  }
  public function testGetRssProcessor() {
    $r=$this->frontend->getRssProcessor();
    $this->assertTrue($r instanceof RssTemplateProcessor);
  }
  public function testGetRssOutput() {
    $r=$this->frontend->getRssOutput();
    $this->assertTrue($r instanceof XmlOutput);
  }
  public function testGetSitemapProcessor() {
    $r=$this->frontend->getSitemapProcessor();
    $this->assertTrue($r instanceof SitemapTemplateProcessor);
  }
  public function testGetSitemapOutput() {
    $r=$this->frontend->getSitemapOutput();
    $this->assertTrue($r instanceof XmlOutput);
  }
  public function testGetHtmlTemplate() {
    global $config;
    $config['html_template_file']=basename(__FILE__);
    $config['html_template_dir']=dirname(__FILE__).'/';
    $s=$this->frontend->getHtmlTemplate();
    $this->assertTrue(strlen($s)>0);
  }
  public function testGetUserAgentLanguage() {
    unset($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    $l=$this->frontend->getUserAgentLanguages();
    $this->assertEquals(array(),$l);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='';
    $l=$this->frontend->getUserAgentLanguages();
    $this->assertEquals(array(),$l);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='en;q=0.5,fr;q=0.5,de;q=0.5';
    $l=$this->frontend->getUserAgentLanguages();
    $this->assertEquals(array(CcmsLanguage::_lEN(),CcmsLanguage::_lFR(),CcmsLanguage::_lDE()),$l);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='nl-NL,nl;q=0.8,en-US;q=0.6,en;q=0.4';
    $l=$this->frontend->getUserAgentLanguages();
    $this->assertEquals(array(CcmsLanguage::_lNL(),CcmsLanguage::_lEN_US(),CcmsLanguage::_lEN()),$l);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='test,en-test,en-us,en;q=0.5';
    $l=$this->frontend->getUserAgentLanguages();
    $this->assertEquals(array(CcmsLanguage::_lEN_US(),CcmsLanguage::_lEN()),$l);
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='xx,nl,en-CA;q=0.7';
    $l=$this->frontend->getUserAgentLanguages();
    $this->assertEquals(array(CcmsLanguage::_lNL(),CcmsLanguage::_lEN_CA()),$l);
  }
  public function testGetRemoteHostLanguage() {
    $_SERVER["_HOST"]='1.2.3.4';
    $l=$this->frontend->getRemoteHostLanguage();
    $this->assertEquals(null,$l);
    $_SERVER["_HOST"]='aaaa.com';
    $l=$this->frontend->getRemoteHostLanguage();
    $this->assertEquals(CcmsLanguage::_lEN_US(),$l);
    $_SERVER["_HOST"]='aaaa.co.uk';
    $l=$this->frontend->getRemoteHostLanguage();
    $this->assertEquals(CcmsLanguage::_lEN_GB(),$l);
    $_SERVER["_HOST"]='aaaa.nl';
    $l=$this->frontend->getRemoteHostLanguage();
    $this->assertEquals(CcmsLanguage::_lNL(),$l);
    $_SERVER["_HOST"]='aaaa.xx';
    $l=$this->frontend->getRemoteHostLanguage();
    $this->assertEquals(null,$l);
    $_SERVER["_HOST"]='aaaa';
    $l=$this->frontend->getRemoteHostLanguage();
    $this->assertEquals(null,$l);
    
  }
  public function testGetClientLanguage() {
    $_SERVER["HTTP_USER_AGENT"]="Bogus Firefox";
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='';
    $_SERVER["_HOST"]='aaaa';
    $l=$this->frontend->getClientLanguage(array('default'=>'en','available'=>array('en')));
    $this->assertEquals(CcmsLanguage::_lEN(),$l);
    $l=$this->frontend->getClientLanguage(array('default'=>'en-CA','available'=>array('en-CA')));
    $this->assertEquals(CcmsLanguage::_lEN_CA(),$l);

    $_SERVER["HTTP_USER_AGENT"]="Bogus googlebot";
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='nl';
    $l=$this->frontend->getClientLanguage(array('default'=>'en','available'=>array('en')));
    $this->assertEquals(CcmsLanguage::_lEN(),$l);

    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='nl';
    $l=$this->frontend->getClientLanguage(array('default'=>'en','available'=>array('en','nl')));
    $this->assertEquals(CcmsLanguage::_lEN(),$l);

    $_SERVER["HTTP_USER_AGENT"]="Bogus Firefox";
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='nl';
    $l=$this->frontend->getClientLanguage(array('default'=>'en','available'=>array('en','nl')));
    $this->assertEquals(CcmsLanguage::_lNL(),$l);

    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='fr';
    $_SERVER["_HOST"]='aaaa.nl';
    $l=$this->frontend->getClientLanguage(array('default'=>'en','available'=>array('en','nl')));
    $this->assertEquals(CcmsLanguage::_lNL(),$l);
    
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='en-CA';
    $_SERVER["_HOST"]='aaaa.nl';
    $l=$this->frontend->getClientLanguage(array('default'=>'nl','available'=>array('en','nl')));
    $this->assertEquals(CcmsLanguage::_lEN(),$l);
    
    $_SERVER["HTTP_ACCEPT_LANGUAGE"]='en';
    $_SERVER["_HOST"]='aaaa.nl';
    $l=$this->frontend->getClientLanguage(array('default'=>'nl','available'=>array('en-CA','nl')));
    $this->assertEquals(CcmsLanguage::_lEN_CA(),$l);
  }
  
	public function testSetLanguage() {
    $this->frontend->setLanguage('test');
    $this->assertEquals('test',$this->frontend->getLanguage());
	}

	public function testGetLanguage() {
    $this->frontend->setLanguage('test');
    $this->assertEquals('test',$this->frontend->getLanguage());
	}

  public function testOutputLanguage() {
    global $site_config;
    $save=$site_config['language'];
    #--

    $_SERVER["_HOST"]='aaaa.nl';
    $_SERVER['_URI']='';
    $_SESSION=array();
    
    $nl=CcmsLanguage::_lNL();

    $site_config['language']=array('default'=>'en','available'=>array('en'));
    $this->frontend->outputLanguage();
    $this->assertFalse($this->frontend->getHtmlOutput()->isRedirected());
    
    $site_config['language']=array('default'=>'en','available'=>array('en','nl'));
    $this->frontend->outputLanguage();
    $this->assertFalse($this->frontend->getHtmlOutput()->isRedirected());
    $this->assertEquals(CcmsLanguage::_lNL(),$this->frontend->getHtmlOutput()->getLanguage());
    $this->assertEquals(array('language'=>$nl),$_SESSION);

    $site_config['language']=array('default'=>'xx','available'=>array('xx','yy'));
    $this->frontend->outputLanguage();
    $this->assertFalse($this->frontend->getHtmlOutput()->isRedirected());
    $this->assertEquals(CcmsLanguage::_lNL(),$this->frontend->getHtmlOutput()->getLanguage());
    $this->assertEquals(array('language'=>$nl),$_SESSION);
    
    $_SESSION=array();
    
    $site_config['language']=array('default'=>'en','selector'=>'uri-prefix','available'=>array('en'));
    $this->frontend->outputLanguage();
    $this->assertFalse($this->frontend->getHtmlOutput()->isRedirected());
    $this->assertEquals(array(),$_SESSION);
    
    $site_config['language']=array('default'=>'en','selector'=>'uri-prefix','available'=>array('en','nl'));
    $this->frontend->outputLanguage();
    $this->assertTrue($this->frontend->getHtmlOutput()->isRedirected());
    $this->assertEquals(array(),$_SESSION);
    
    $_SERVER['_URI']='/fr/test/123';
    $site_config['language']=array('default'=>'en','selector'=>'uri-prefix','available'=>array('en','nl','fr'));
    $this->frontend->outputLanguage();
    $this->assertEquals(array(),$_SESSION);
    $this->assertEquals('/test/123',$_SERVER['_URI']);
    $this->assertEquals(CcmsLanguage::_lFR(),$this->frontend->getHtmlOutput()->getLanguage());
    
    #--
    $site_config['language']=$save;
  }

  //@todo
  public function testlocales() {
    $this->assertTrue(true);
  }

  //@todo
  public function testAfterRender() {
    $this->assertTrue(true);
  }

  public function testRenderSiteMap() {
    $_SERVER['_URI']='/sitemap.xml';
    $this->frontend->render();
    $this->assertTrue(true);
  }

  public function testRenderRss() {
    global $site_config;
    $save=$site_config['language'];
    #--
    $site_config['route_tree_ids']='1001';
    $site_config['home_page']='1';
    $site_config['language']=array('default'=>'en','selector'=>'uri-prefix','available'=>array('en','nl'));
    $_SERVER['_URI']='/nl/test/123.rss';
    $this->frontend->render();
    $this->assertTrue(true);
    #--
    $site_config['language']=$save;
  }

  public function testRenderHtml() {
    global $site_config;
    $save=$site_config['language'];
    #--
    $site_config['route_tree_ids']='1001';
    $site_config['home_page']='1';
    $site_config['language']=array('default'=>'en','selector'=>'uri-prefix','available'=>array('en','nl'));
    $site_config['html_template_dir']=dirname(__FILE__).'/../data/';
    $site_config['html_template_file']='template.html';
    $_SERVER['_URI']='/nl/test/123';

    $this->frontend->render();
    $this->assertTrue(true);
    #--
    $site_config['language']=$save;
  }

}

//end