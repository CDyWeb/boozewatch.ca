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

class DefaultTemplateProcessor extends FrontendBase implements TemplateProcessor {

	protected $dom=null;
	protected $router=null;
	protected $output=null;
	protected $page;
  
	public function __construct($frontend=null) {
    if (empty($frontend)) {
      log_message('error',__FILE__.'::__construct missing frontend parameter, using the global singleton');
      global $instance;
      $frontend=$instance;
    }
    parent:: __construct($frontend);
  }

	public function getShopBase() {
		return "shop";
	}

	public function getRouter() {
		return $this->router;
	}
	
	public function getOutput() {
		return $this->output;
	}
	
	public function getPage() {
		return $this->page;
	}

	public function getHomePage() {
		return $this->getRouter()->getHomePage();
	}
	
	protected function getDom() {
		if (!$this->dom) {
			$this->dom = new DOMDocument("1.0",$this->output->getCharset());
			$this->dom->preserveWhiteSpace=true;
			$this->dom->formatOutput=true;
		}
		return $this->dom;
	}
	
	function getElementsByAttr($attr,$value) {
		if (empty($this->xpath)) $this->xpath = new DOMXPath($this->dom);
		$res=$this->xpath->evaluate("//*[@{$attr}='{$value}']", $this->dom);
		return $res;
	}

	function getElementByAttr($attr,$value) {
		$res=$this->getElementsByAttr($attr,$value);
		if (@$res->length>0) return $res->item(0);
		return null;
	}
	function getElementById($id) {
		return $this->getElementByAttr('id',$id);
	}
	function getElementByClass($class) {
		return $this->getElementByAttr('class',$class);
	}

	protected function createElement($name,$value="",array $attributes=array()) {
		$node = $this->getDom()->createElement($name,$value);
		foreach ($attributes as $k=>$v) @$node->setAttribute($k,$v);
		return $node;
	}
	
	protected function appendElement($target,$name,$value="",array $attributes=array()) {
		$node=$this->createElement($name,$value,$attributes);
		$target->appendChild($node);
		return $node;
	}
	
	public function replaceElement($node,$xml) {
		$f = $this->dom->createDocumentFragment();
		$f->appendXML($xml);
		$node->parentNode->replaceChild($f,$node);
	}
  
  public function replaceElementById($id,$xml) {
    $e=$this->getElementById($id);
    if (!empty($e)) $this->replaceElement($e,$xml);
  }

	protected function emptyElement($node) {
		if (empty($node)) throw new Exception('empty node');
		while (true) {
			$children=$node->childNodes;
			if ($children->length>0) $node->removeChild($children->item(0));
			else break;
		}
	}
	
	//@deprecated
	protected function empty_element($node) {
		while (true) {
			$children=$node->childNodes;
			if ($children->length>0) $node->removeChild($children->item(0));
			else break;
		}
	}
	
	public function getHtmlHead() {
		return $this->getDom()->getElementsByTagName("head")->item(0);
	}
	
	public function getHtmlBody() {
		return $this->getDom()->getElementsByTagName("body")->item(0);
	}

  protected function makeUpHead($head) {
    if ($this->output->getDocType()=='<!DOCTYPE HTML>') {
      // noop
      // Content-Type and Content-Language are obsolete in HTML5, they are only expected in the HTTP headers.
    } else {
      #content-type
      $found=false;
      if (empty($head)) throw new Exception('no head');
      foreach ($head->childNodes as $node) if (($node->nodeName=="meta") && (strcasecmp($node->getAttribute("http-equiv"),"Content-Type")==0)) { $found=true; break; }
      if (!$found) {
        $firstHead = $head->childNodes->item(0);
        $node = $this->getDom()->createElement("meta");
        $node->setAttribute("http-equiv","Content-Type");
        $firstHead = $head->insertBefore($node,$firstHead);
      }
      $node->setAttribute("content","text/html; charset=".strtolower($this->output->getCharset()));
      
      #content-language
      $found=false;
      foreach ($head->childNodes as $node) if (($node->nodeName=="meta") && (strcasecmp($node->getAttribute("http-equiv"),"Content-Language")==0)) { $found=true; break; }
      if (!$found) {
        $firstHead = $head->childNodes->item(0);
        $node = $this->getDom()->createElement("meta");
        $node->setAttribute("http-equiv","Content-Language");
        $firstHead = $head->insertBefore($node,$firstHead);
      }
      $node->setAttribute("content",$this->output->getLanguage());

      #base
      if (!getConfigItem("head_no_base",true)) {
        $firstHead = $head->childNodes->item(0);
        $node = $this->getDom()->createElement("base");
        $node->setAttribute("href",SITE_BASE_URL); //getConfigItem("html_base_href"));
        $firstHead = $head->insertBefore($node,$firstHead);
      }
    }

    #favicon
    if (!getConfigItem("head_no_favicon",false)) {
      $found=false;
      foreach ($head->childNodes as $node) if (($node->nodeName=="link") && (strcasecmp($node->getAttribute("rel"),"icon")==0)) { $found=true; break; }
      if (!$found) {
        $node = $this->appendElement($head,"link","",array("rel"=>"icon","href"=>getConfigItem('default_favicon','favicon.ico'),"type"=>"image/x-icon"));
        $node = $this->appendElement($head,"link","",array("rel"=>"apple-touch-icon","href"=>getConfigItem('default_apple_icon','apple-touch-icon.png'),"type"=>"image/png"));
      } else {
        $node->setAttribute("type","image/x-icon");
      }
    }
  }

	protected function getMainContentNode($body) {
		$res = $this->getDom()->getElementById("content");
		return $res;
	}
	
	protected function getMainContentReplace() {
		return "--[[replace:content]]--";
	}
	
	protected function mainContentWrap($txt) {
		return $txt;
	}
	
	protected function getMainContent() {
		_log("DefaultTemplateProcessor::getMainContent");
		if (!isset($this->mainContent)) switch ($this->page->page_type) {
			case "plugin" :
				_log("DefaultTemplateProcessor::getMainContent - getting plugin content");
        #--
        #--
        if (!isset($this->isAjax) && in_array($this->page->attributes,getConfigItem('https.plugin',array())) && !is_https()) {
          $this->output->redirect('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); return;
        }
        #--
        #--
				if ($this->router->uri()==$_SERVER['_URI']) $this->router->setCanonicalLink($this->router->url());
				ob_start();
				$fn="{$this->page->attributes}/plugin.inc";
				if (file_exists($app_pluginpath=getConfigItem('script_app').'plugins/'.$fn)) require $app_pluginpath;
				else if (file_exists($base_pluginpath=getConfigItem('script_base').'shared/plugins/'.$fn)) require $base_pluginpath;
        else die("file not found: {$app_pluginpath} {$base_pluginpath} ");
				$this->mainContent=ob_get_contents();
				ob_end_clean();
				break;
			case "menu" :
				_log("DefaultTemplateProcessor::getMainContent - getting menu content");
				$firstChild=$this->router->getMenuDefaultChild($this->page);
				if (!empty($firstChild)) {
					$this->output->redirect($this->pageURL($firstChild['id']));
				} else {
					$this->mainContent=$this->page->text;
				}
				break;
			case "link" :
				return "link";
			default :
				_log("DefaultTemplateProcessor::getMainContent - getting page->text content");
				$this->router->setCanonicalLink($this->router->url());
				$this->mainContent=$this->page->text;
		}
		if ($this->output->isRedirected()) return null;
		_log("DefaultTemplateProcessor::getMainContent - not isRedirected > return wrap");
		return $this->mainContentWrap($this->mainContent);
	}
	
	protected function makeUpMainContent($body) {
		_log("DefaultTemplateProcessor::makeUpMainContent - getMainContent");
		$html=$this->getMainContent();
		if ($this->output->isRedirected()) return;

		$contentNode=$this->getMainContentNode($body);
		$repl=$this->getMainContentReplace();
		if ($contentNode) $contentNode->nodeValue=$repl;
		$this->output->setReplacement($repl,$this->fixRelativeLinks($html));
	}

	protected function getMenuNode($line) {
		$nodeId=strtolower(preg_replace("#[^\w]#","",$line["name"]));
		return $this->getDom()->getElementById($nodeId);
	}
	
	//@Todo
	protected function makeUpMenu($line,$node) {
		_log(get_class($this)." makeUpMenu:todo");
	}

	protected function getPageTree($tree_id) {
		return $this->getRecursivePageTree($this->router->getPageArray(),$tree_id,null);
	}
	protected function getRecursivePageTree($flat,$tree_id,$parent_id) {
		$res=array();
		foreach ($flat as $line) if (($line["tree_id"]==$tree_id) && ($line["parent_id"]==$parent_id)) {
			$line["_children"]=$this->getRecursivePageTree($flat,$tree_id,$line["id"]);
			$res[$line["id"]]=$line;
		}
		return $res;
	}
	
	protected function makeUpMenus($body) {
		$ids=$this->router->getPageTreeIds();
		$arr=getTableArray("select * from {$this->tbl("tree")} where active=1 and id in ({$ids})");
		foreach ($arr as $line) {
			$node=$this->getMenuNode($line);
			if (!$node) {
				_log("node not found for menu ".$line["name"],LOG_LEVEL_ERROR);
				continue;
			}
			$this->makeUpMenu($line,$node);
		}
	}
	
	//@Todo
	protected function makeUpShop($body,$prodUri,$shopUri) {
		_log(get_class($this)." makeUpShop:todo");
	}
	
	protected function recursiveBuildCatTree($sel,$keys,$flat,&$dest,$prefix="",$path=array()) {
		$active=false;
		foreach ($keys as $k) if ($flat[$k]['parent_id']==$dest['id']) {
			$child=$flat[$k];
			$n=getPermalinkName($child['name']);
			$child['_uri']="/{$this->getShopBase()}/{$child['id']}/".$prefix.$n.".html";
			$child['_path']=$path;
			$child['_path'][]=$child;
			$active |= $child['_active']=$this->recursiveBuildCatTree($sel,$keys,$flat,$child,$prefix.$n."-",$child['_path']);
			$dest['_children'][]=$child;
		}
		$act=$active || ($dest['id']==$sel);
		return $act;
	}

	protected function getCatTree($sel=null,$shopRoot=1100) {
		if (isset($this->catTree[$shopRoot])) return $this->catTree[$shopRoot];
		if ($sel===null) {
			if (preg_match('#/'.$this->getShopBase().'/(\d+)/.+/.+#i',$_SERVER['_URI'],$match)) {
        $sel=getOneValue('select tree_id from `'.tbl_name('product').'` where id='.intval($match[1]));
      } else if (preg_match('#/'.$this->getShopBase().'/(\d+)/#i',$_SERVER['_URI'],$match)) {
        $sel=$match[1];
      }
		}
		$flat=getTableArray("select * from `".tbl_name('tree')."` where `parent_id` is not null and `active`=1 order by `orderby`","id");
		if (!isset($flat[$shopRoot])) return null;
		$root=$flat[$shopRoot];
		$k=array_keys($flat);
		$this->recursiveBuildCatTree($sel,$k,$flat,$root);
		$this->catTree[$shopRoot] = $root;
		return $root;
	}
	
	protected function makeUpBody($body) {
		#content
		$this->makeUpMainContent($body);
		if ($this->output->isRedirected() || isset($this->simplePage)) return;
		
		#menu's
		$this->makeUpMenus($body);
		
		#shop
		$prodUri=$this->router->getPluginUri("products");
		$shopUri=$this->router->getPluginUri("webshop");
		$this->makeUpShop($body,$prodUri,$shopUri);
	}
  
  protected function getProductsPluginUri() {
    if (!isset($this->productsPluginUri)) $this->productsPluginUri=$this->router->getPluginUri('products');
    return $this->productsPluginUri;
  }
	
	protected function getRss() {
		$res=array();
		$arr=$this->router->getPageArray();
		$lookup=$this->router->getPageLookup();
		foreach ($lookup as $name=>$id) {
			if (!isset($arr[$id])) continue;
			if ($arr[$id]["page_type"]!="plugin") continue;
			if (
        file_exists($fn=getConfigItem('script_base')."app/plugins/{$arr[$id]["attributes"]}/_get_rss.inc")
        ||
        file_exists($fn=getConfigItem('script_base')."shared/plugins/{$arr[$id]["attributes"]}/_get_rss.inc")
      ) {
        require_once($fn);
			}
		}
		return $res;
	}
	
	protected function makeUpMeta($head) {
		
		#title
		$title=$head->getElementsByTagName("title");
		if ($title->length==1) $title=$title->item(0); else $title=$this->appendElement($head,"title");
		$title->nodeValue=$this->output->ent($this->page->meta_title);

		#meta
		$this->appendElement($head,"meta","",array("name"=>"keywords","content"=>$this->page->meta_keywords));
		$this->appendElement($head,"meta","",array("name"=>"description","content"=>$this->page->meta_description));
		
		$arr=$this->getRss();
		foreach ($arr as $line) {
			//<link rel="alternate" title="NU.nl Algemeen"  type="application/rss+xml" href="http://www.nu.nl/feeds/rss/algemeen.rss" />
			$this->appendElement($head,"link","",array("rel"=>"alternate","type"=>"application/rss+xml","title"=>$line["title"],"href"=>$line["href"]));
		}
	}
	
	protected function makeUpHtml() {
		$body=$this->getHtmlBody();
		$head=$this->getHtmlHead();

		#--
		$this->makeUpHead($head);
		$this->makeUpBody($body);
		if ($this->output->isRedirected() || isset($this->simplePage)) return;

		$this->makeUpMeta($head);
		#--
	}

	/**
  //Moved to upper class
	function uri($append=null) {
		return $this->router->uri($append);
	}
	//Moved to upper class
  function url($append=null) {
		return $this->router->url($append);
	}
  //Moved to upper class
  function pageURL($id) {
    return $this->frontend->getPageURL($id);
  }
  **/

	protected function pre_process(Router $router, $template, Output $output) { 
		//noop
	}

  protected function post_process(Router $router, $template, Output $output, $res) {
    $this->output->setReplacement("<br></br>","<br />");
    $this->output->setReplacement("></img>"," />");
    $this->output->setReplacement("></input>"," />");
    $this->output->setReplacement("{site_uri}",$this->frontend->getSiteURI());
    $this->output->setReplacement("{site_url}",$this->frontend->getSiteURL());
    $this->output->setReplacement("{page_uri}",$this->uri());
    $this->output->setReplacement("{page_url}",$this->url());
    return $res;
  }
	
	protected function seo($res) {
		#--
		if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')!==false)) {
      $this->output->addHeadMetaTag('MSSmartTagsPreventParsing','true');
    }
		#--
		$googleAnalytics=setting('googleAnalytics',null);
    $aliasDomain=$this->router->getAliasDomain();
    if (!empty($aliasDomain['analytics'])) $googleAnalytics=$aliasDomain['analytics'];
    #--
    $googleAnalytics_replace=getConfigItem('googleAnalytics_replace',setting('googleAnalytics_replace','</body>'));
		if (!empty($googleAnalytics) && !preg_match('#'.preg_quote('pageTracker._trackPageview()').'#',$res)) $res=str_ireplace($googleAnalytics_replace,<<<HTML
<script type="text/javascript">
// <![CDATA[
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '{$googleAnalytics}']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
// ]]>
</script>
{$googleAnalytics_replace}
HTML
,$res);

		#--
		$googleVerify=setting('googleVerify',null);
		if (!empty($googleVerify)) {
			//if (preg_match('#<meta name="(google-site-verification|verify-v1)" content="[^"]*"#',$res,$match)) $res=str_replace($match[0],'<meta name="google-site-verification" content="'.$googleVerify.'"',$res);
			//else $res=str_ireplace("</head>","    <meta name=\"google-site-verification\" content=\"{$googleVerify}\"></meta>\n</head>",$res);
			$this->output->addHeadMetaTag('google-site-verification',$googleVerify);
		}
		#--
		#http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html <link rel="canonical" href="http://www.example.com/product.php?item=swedish-fish" />
		$canonicalLink=$this->router->getCanonicalLink();
		if (!empty($canonicalLink)) $this->output->addHeadLink('canonical',$canonicalLink);
		#--
    $piwikId=setting('ccms.piwik',null);
    if (!empty($piwikId)) {
      $url_base=getConfigItem('url_base');
      $res=str_ireplace("</body>",<<<HTML
<!-- Piwik -->
<script type="text/javascript" src="{$url_base}shared/piwik/piwik.js"></script>
<script type="text/javascript">
// <![CDATA[
try { var piwik_tracker = Piwik.getTracker("{$url_base}shared/piwik/", {$piwikId}); piwik_tracker.trackPageView(); piwik_tracker.enableLinkTracking(); } catch( err ) {
  (function() { var img = document.createElement('img'); img.src = '{$url_base}shared/piwik/?action_name=ImgFallback&idsite={$piwikId}'; document.body.appendChild(img); })(); 
}
// ]]>
</script>
<noscript><p><img src="{$url_base}shared/piwik/?action_name=Noscript&amp;idsite={$piwikId}" style="border:0" alt="" /></p></noscript>
<!-- End Piwik -->
</body>
HTML
,$res);
    }
    #--
		return $res;
	}
	
	protected function loadHtmlTemplate() {
		$dom=$this->getDom();
		if (!isset($this->templateLoaded)) {
			@$dom->loadHtml('<?xml encoding="UTF-8">'.$this->template);
			$this->templateLoaded=true;
		}
		return $dom;
	}
  
  /* 
  //moved to parent class
  function htmlbase() {
    return getConfigItem('html_base_href');
  }
  protected function fixRelativeLinks($html) {
    return $this->fixRelativeLinksInDir($html,$this->htmlbase());
  }
  protected function fixRelativeLinksInDir($html,$dirname) {
    if (preg_match_all('#<(img|script|link|input)[^>]*((href|src)=([\'"])([^\'"]+)[\'"])#i',$html,$matches,PREG_SET_ORDER)) {
      foreach($matches as $match) {
        if ((substr($match[5],0,1)=='/') || (preg_match('#://#',$match[5]))) continue;
        $html=str_replace($match[2],$match[3].'='.$match[4].$dirname.$match[5].$match[4],$html);
      }
    }
    return $html;
  }
  */
  
  public function _($key, $params=null) {
    return $this->frontend->_($key, $params);
  }

	public function process(Router $router, $template, Output $output) {
		
		$this->router=$router;
		$this->template=$template;
		$this->output=$output;
		
		$this->pre_process($router, $template, $output);
		
		_log(get_class().":process ".get_class($router)." ".mb_strlen($template)." ".get_class($output));
		
		$this->page=$router->getPage();
		if (!$this->page) {
			_log("no page to process");
			return;
		}
		
		_log("DefaultTemplateProcessor::process - getMainContent");
		$this->getMainContent();
		if ($output->isRedirected()) return;
		if (isset($this->noHtml)) return;
		if (isset($this->plainHtml)) {
			$this->output->setContent($this->mainContent);
			return;
		}
    
    $bot=clientIsRobot();

		_log("DefaultTemplateProcessor::process - not isRedirected & not isset.noHtml & not isset.plainHtml");
		$dom=$this->loadHtmlTemplate();

		#--
		$this->makeUpHtml();
		if ($output->isRedirected()) return;
		#--

		$dom->normalize();

		if (getConfigItem('html_output_type')=='HTML5') {
      $res = $dom->saveHTML();
      $res = str_ireplace('<!DOCTYPE html>'."\n".'<?xml encoding="UTF-8">','<!DOCTYPE HTML>',$res);
    }
    else $res = $dom->saveXML($dom->documentElement,LIBXML_NOEMPTYTAG);
    
		//$res = str_replace("<script type=\"text/javascript\"><![CDATA[\n","<script type=\"text/javascript\">\n//<![CDATA[",$res);
		//$res = preg_replace("#".preg_quote("type=\"text/javascript\">")."\s*".preg_quote("<![CDATA[\n")."#is","type=\"text/javascript\">\n//<![CDATA[\n",$res);
		//$res = str_replace("\n]]></script>","\n//]]>\n</script>",$res);
		//$res = preg_replace("#".preg_quote("\n]]>")."\s*".preg_quote("</script>")."#is","\n//]]>\n</script>",$res);

		$res = str_replace('<![CDATA[','',$res);
		$res = str_replace(']]>','',$res);
		
		$res = str_replace('<br></br>','<br />',$res);
		$res = str_replace('<hr></hr>','<hr />',$res);
		
		$res = $this->post_process($router, $template, $output, $res);
		$res = $this->seo($res);
    
    $res = $this->fixRelativeLinks($res);

		$output->append($res);
	}
	
}

//end
