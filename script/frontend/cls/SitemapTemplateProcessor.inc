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

class SitemapTemplateProcessor extends FrontendBase implements TemplateProcessor {

  protected function fetchPages() {
    return $this->router->getPageArray(null,true);
  }

	protected function getLines() {
		$res=array();
		$arr=$this->fetchPages();
		foreach ($arr as $page) {
			if ($page["page_type"]=='link') continue;
      if ($page["page_type"]=='menu') continue;
      if (array_key_exists('indexable',$page) && ($page['indexable']==0)) continue;
			$url=$this->router->getPageUrl($page["id"]); //$url=$this->frontend->getPageURL($page["id"]);
			$line=array("loc"=>$url);
			if (isset($page["lastmod"]) && $page["lastmod"]) $line["lastmod"]=w3cDate(strtotime($page["lastmod"]));
			if (isset($page["sitemap_changefreq"]) && $page["sitemap_changefreq"]) $line["changefreq"]=$page["sitemap_changefreq"];
			if (isset($page["sitemap_priority"]) && $page["sitemap_priority"]) $line["priority"]=$page["sitemap_priority"];
			$res[]=$line;
		}
		$res = $this->visitPlugins($res);
		return $res;
	}
	
	protected function visitPlugins(array $res) {
		$arr=$this->router->getPageArray();
		$lookup=$this->router->getPageLookup();
		foreach ($lookup as $name=>$id) {
			if (!isset($arr[$id])) continue;
			$page=$arr[$id];
			if ($page["page_type"]!="plugin") continue;
      //$url="http://".$_SERVER["HTTP_HOST"].preg_replace('#\.\w+$#','',$this->router->pageUri($id,"",""));
      $url=$this->router->getPageUrl($id);
      $path="plugins/{$page["attributes"]}/_get_sitemap.inc";
			if (file_exists(getConfigItem('script_base').'shared/'.$path)) {
        require_once(getConfigItem('script_base').'shared/'.$path);
      } else if (file_exists(getConfigItem('script_app').$path)) {
        require_once(getConfigItem('script_app').$path);
      }
		}
		return $res;
	}
	
	protected function sitemap() {
		$this->output->setCharset("UTF-8");
		$this->output->append('<?xml-stylesheet type="text/xsl" href="/shared/cyane/sitemap.xsl"?><urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><!--{cursor}--></urlset>');

    $lconf=getConfigItem('language',array('default'=>'en','available'=>array('en')));
    $arr=array($lconf['default']);

    $this->multilang=(count($lconf['available'])>1) && isset($lconf['selector']) && ($lconf['selector']=='uri-prefix');
    if ($this->multilang) $arr=$lconf['available'];

    foreach ($arr as $lcode) {
      $this->frontend->setLanguage(CcmsLanguage::parse($lcode));
      foreach ($this->getLines() as $line) $this->output->appendNode("url",$line);
    }
	}

	public function process(Router $router, $template, Output $output) {
		$this->output=$output;
		$this->router=$router;
		$this->sitemap();
	}

}

