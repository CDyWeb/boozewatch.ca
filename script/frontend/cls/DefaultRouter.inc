<?php
/*
------------------------------------------------------------

  CyaneCMS

$LastChangedRevision: 133 $
$LastChangedDate: 2009-07-01 14:23:38 +0200 (wo, 01 jul 2009) $
$LastChangedBy: $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class DefaultRouter extends FrontendBase implements Router {

  protected $pageArray=null;
  protected $pageLookup=null;
  protected $page=null;
  protected $homepage=null;
  protected $uri;
  protected $ext=""; //".html";
  protected $canonicalLink=null;

  public function __construct($frontend) {
    parent:: __construct($frontend);
  }
  
  public function getPage() {
    return $this->page;
  }
  public function setPage(Page $page) {
    $this->page=$page;
  }
  
  public function getHomePage() {
    return $this->homepage;
  }
  
  public function getExt() {
    return $this->ext;
  }
  
  public function getUri() {
    return $this->uri;
  }

  public function setExt($ext) {
    $this->ext=strtolower($ext);
  }
  
  public function getCanonicalLink() {
    return $this->canonicalLink;
  }

  public function setCanonicalLink($canonicalLink) {
    if (!isset($this->aliasDomain)) $this->canonicalLink=$canonicalLink;
  }
  
  public function getAliasDomain() {
    return isset($this->aliasDomain)?$this->aliasDomain:null;
  }
  
  protected function getPageQueryFields() {
    return getConfigItem('Router.PageQueryFields','`id`, `parent_id`, `tree_id`, `name`, `meta_title`, `uri`, `page_type`, `attributes`, `lastmod`, `indexable`, `sitemap_changefreq`, `sitemap_priority`');
  }

  public function fetchPages($where=null, $refresh=false) {
    $cache=$this->frontend->getObjectCache();
    $cache_key=getConfigItem('domain').':Router:fetchPages';
    $cache_array=$cache->get($cache_key);

    $lang=$this->frontend->uriLanguage();
    if (empty($lang)) $lang='base';

    if (!$refresh) {
      if (isset($cache_array[$lang][$where])) {
        _log('DefaultRouter:fetchPages returns '.count($cache_array[$lang][$where]).' records from cache: '.$cache_key);
        return $cache_array[$lang][$where];
      }
    }
    if (empty($cache_array)) $cache_array=array();
    if (empty($cache_array[$lang])) $cache_array[$lang]=array();

    $cache_array[$lang][$where]=$this->realFetchPages($where,$refresh);
    $cache->set($cache_key,$cache_array,60*60*24);
    return $cache_array[$lang][$where];
  }
  
  protected function loadPagesFromStorage($fields,$where) {
    $sql="select {$fields} from `{$this->tbl("page")}` where {$where} order by orderby";
    return getTableArray($sql,"id");
  }

  public function realFetchPages($where=null, $refresh=false) {
    $fields=$this->getPageQueryFields();
    if (empty($where)) {
      $tree_ids=$this->getPageTreeIds();
      if (empty($tree_ids)) $where='`active`=1';
      else $where="`active`=1 and `tree_id` in ({$tree_ids})";
    }
    $result=$this->loadPagesFromStorage($fields,$where);
    if ($this->frontend->isInBaseLanguage()) return $result;
    #--
    $tr=$this->frontend->getContentsTranslator($refresh);
    foreach ($result as $i=>$line) {
      $result[$i]['local_name']=$result[$i]['name'];
      $result[$i]['name']=$tr->frontendTranslateByFld('page', $line['id'], 'name', $line['name']);
      $result[$i]['meta_title']=$tr->frontendTranslateByFld('page', $line['id'], 'meta_title', $line['meta_title']);
      $result[$i]['uri']=$tr->frontendTranslateByFld('page', $line['id'], 'uri', $line['uri']);
      $result[$i]['attributes']=$tr->frontendTranslateByFld('page', $line['id'], 'attributes', $line['attributes']);
      if ($result[$i]['name']=='-') unset($result[$i]);
    }
    return $result;
  }

  public function getPageArray($idToInclude=null, $refresh=false) {
    if (($this->pageArray===null) || $refresh) {
      $this->pageArray=$this->fetchPages(null, $refresh);
      #--
      $idToInclude=intval($idToInclude);
      if ($idToInclude>0) {
        foreach ($this->pageArray as $line) if ($line['id']==$idToInclude) $idToInclude=null;
        if ($idToInclude) $this->pageArray=array_merge($this->pageArray,$this->fetchPages('`id`='.$idToInclude, $refresh));
      }
      #--
      #-- _log(get_class().":getPageArray - ".str_replace("\n","",print_r($this->pageArray,true)));
    }
    return $this->pageArray;
  }

  public function getParentPage($page) {
    if ($page==null) return null;
    $arr=$this->getPageArray();
    $pid=is_array($page)?$page['parent_id']:$page->parent_id;
    if (!$pid) return null;
    if (isset($arr[$pid])) return $arr[$pid];
    return null;
  }

  public function getRootPage($page) {
    if ($page==null) return null;
    while (true) {
      $parent=$this->getParentPage($page);
      if (!$parent) return $page;
      $page=$parent;
    }
  }

  public function getPageChildren($page) {
    if ($page==null) return null;
    $arr=$this->getPageArray();
    $pid=is_array($page)?$page['id']:(is_object($page)?$page->id:$page);
    if (!$pid) return null;
    $result=array();
    foreach ($arr as $line) if ($line['parent_id']==$pid) $result[]=$line;
    return $result;
  }
  
  public function getPageUrl($id=null,$append='',$ext=null) {
    return $this->frontend->getSiteURL().trim($this->getPageUri($id,$append,$ext),'/');
  }
  public function getPageUri($id=null,$append='',$ext=null) {
    return $this->pageUri($id,$append,$ext);
  }
  public function pageUri($id=null,$append='',$ext=null) {
    if ($ext===null) $ext=$this->ext;
    if (empty($id)) $id=$this->getHomePageId();
    $arr=$this->getPageArray();
    if (!isset($arr[$id])) {
      $arr=$this->fetchPages("id=".$id);
      if (!isset($arr[$id])) {
        throw new Exception("not a valid page: {$id}, ".implode(',',array_keys($arr)));
      }
    }
    $line=$arr[$id];
    if ($line["page_type"]=="link") {
      $url=$line["attributes"];
      /**
      if (!preg_match("#^(mailto:|https?://)#i",$url)) {
        if (substr($url,0,1)!="/") $url=$this->frontend->getSiteURI().$url;
      }
      **/
      return $url;
    }
    if ($line["uri"] && empty($append)) $res = '/'.trim($line["uri"],'/');
    else {
      $res=array();
      while (true) {
        array_unshift($res,getPermalinkName($line["name"]));
        if ($line["parent_id"]) {
			if (!isset($arr[$line["parent_id"]])) $arr=$this->fetchPages("id=".$line["parent_id"]);
			$line=$arr[$line["parent_id"]];
		}
        else break;
      }
      $res = '/'.implode("/",$res).$append.$ext;
    }
    _log(get_class().":pageUri {$id} {$ext} - {$res}");
    return $res;
  }

  public function getPageTreeIds($n="route_tree_ids") {
    if (defined(strtoupper($n))) $res=constant(strtoupper($n));
    else $res = getConfigItem($n,setting($n));
    _log(get_class().":getPageTreeIds : {$res}");
    return $res;
  }
  
  protected function recursivePages($arr,$pid,$prefix="") {
    foreach (array_keys($arr) as $id) {
      if (intval($arr[$id]["parent_id"])!==intval($pid)) continue;
      $name=$k=$prefix.getPermalinkName($arr[$id]["name"]);
      #if ($arr[$id]["page_type"]!='link') {
        if ($arr[$id]["uri"]) $k=preg_replace("#\.\w+$#","",$arr[$id]["uri"]);
        if (($arr[$id]['page_type']=='link') && (isset($this->pageLookup[$k]))) continue;
        $this->pageLookup[$k]=$id;
      #}
      $this->recursivePages($arr,$id,$name."/");
    }
  }

  public function getPageLookup() {
    if ($this->pageLookup==null) {
      $this->pageLookup=array();
      $this->recursivePages($this->getPageArray(),null);
      _log(get_class().":getPageLookup : ".print_r($this->pageLookup,true));
    }
    return $this->pageLookup;
  }
  
  public function getPluginPage($name) {
    $arr=$this->getPageArray();
    foreach (array_keys($arr) as $key) if (($arr[$key]["page_type"]=="plugin") && ($arr[$key]["attributes"]==$name)) {
      return new Page($arr[$key]);
    }
    return null;
  }
  
  public function getPluginUri($name,$append="",$ext=null) { //".html") {
    $page=$this->getPluginPage($name);
    if ($page!==null) {
      $res=$this->pageUri($page->id,$append,$ext);
      _log("getPluginUri {$name} returns {$res}");
      return $res;
    }
    _log("getPluginUri {$name} returns null, plugin `{$name}` not found");
    return null;
  }
  public function getPluginUrl($name,$append="",$ext=null) {
    return $this->frontend->getSiteURL().trim($this->getPluginUri($name,$append,$ext),'/');
  }
  
  protected function lookupPage($arr) {
    $l=$this->getPageLookup();
    while (count($arr)>0) {
      $k=implode("/",$arr);
      if (isset($l[$k])) {
        _log(get_class().":lookupPage : {$k} matches {$l[$k]}");
        $this->uri=$k;
        return $l[$k];
      } else {
        _log(get_class().":lookupPage : {$k} no match");
      }
      array_pop($arr);
    }
    $this->uri='/'.trim($this->pageUri(),'/');
    return null;
  }
  
  public function getHomePageId($n="home_page") {
    if (defined(strtoupper($n))) $res=intval(constant(strtoupper($n)));
    else $res = intval(getConfigItem($n,setting($n)));
    _log(get_class().":getHomePageId returns {$res}");
    return $res;
  }
  
  protected function makePageComplete(array $dest, $with_id) {
    if ($dest["id"]==$with_id) return $dest;
    $line=getOneRow("select * from `{$this->tbl("page")}` where `id`={$with_id}");
    return $this->makePageCompleteEx($dest, $line);
  }
  protected function makePageCompleteEx(array $dest, array $line) {
    if ($dest["id"]==$line["id"]) return $dest;
    if ($line) foreach (array("name","meta_title","meta_keywords","meta_description") as $k) if (!$dest[$k]) $dest[$k]=$line[$k];
    return $dest;
  }
  protected function makeLocalPageComplete($dest, $id) {
    if ($dest->id==$id) return $dest;
    $line=$this->getLocalPage($id);
    if ($line) foreach (array("name","meta_title","meta_keywords","meta_description") as $k) if (!$dest->$k) $dest->$k=$line->$k;
    return $dest;
  }
  
  public function getMenuDefaultChild($page) {
    $line=getOneRow("select * from `{$this->tbl("page")}` where `parent_id`={$page->id} and `active`=1 order by orderby limit 1");
    return $line;
  }
  
  protected function virtualPluginUri($name) {
    $this->pageArray[-1]=array('id'=>-1,'uri'=>substr($_SERVER["_URI"],1),'tree_id'=>-1,'parent_id'=>null,'name'=>$name,'page_type'=>'plugin','attributes'=>$name);
    $result=$this->getPluginUri($name);
    $this->page=new Page($this->pageArray[-1]);
    return $result;
  }
  
  public function getLocalPage($id) {
    $arr=getOneRow("select * from `{$this->tbl("page")}` where `id`={$id}");
    return $this->frontend->localObj(new Page($arr),'page',$id);
  }
  
  public function route($uri) {

    if (preg_match("#(ideal|paypal|mrcash|directebanking)/(notify|success|error|cancel|service)\.#",$uri,$match)) {
      $uri=$this->getPluginUri("webshop");
    }

    if (isset($_GET['newsletter-subscribe']) || isset($_GET['newsletter-unsubscribe']) || !empty($_GET['newsletter-hash']) || preg_match('#/[\d\w]+/newsletter-(view|preview|unsubscribe|history)[./]#',$_SERVER['_URI'])) {
      $uri=$this->getPluginUri("newsletter");
      if ($uri===null) {
        $uri=$this->virtualPluginUri('newsletter');
        return $this->page;
      }
    }

    if (preg_match("#/__plugin/([^/]*)/#iU",$_SERVER["_URI"],$match)) {
      $uri=$this->getPluginUri($match[1]);
      if ($uri===null) {
        $uri=$this->virtualPluginUri($match[1]);
        return $this->page;
      }
    }
    
    $this->uri = preg_replace("#^".preg_quote(getConfigItem("rel_base"))."#","",$uri);
    if (empty($this->uri)) $this->uri = "index.html";

    if (preg_match("#^sitemap\.xml$#",$this->uri)) return null;
    
    if (preg_match("#^(.*)(\.\w+|/)$#",$this->uri,$match)) {
      $this->uri=$match[1];
    }

    /**/
    if (strcasecmp($d=preg_replace('#^www\.#i','',$_SERVER['HTTP_HOST']),$c=getConfigItem('domain'))!==0) {
      _log(get_class().":route - {$d} is not {$c} - alias request?");
      try {
        $a=getOneRow('select * from `'.tbl_name('aliasdomain').'` where `active`=1 and `domain`='.dbStr($d));
      } catch (MySqlException $ex) {
        //no aliasdomain table exists
        $a=null;
      }
      if (!empty($a)) {
        _log(get_class().":route - yes this is an alias domain!");
        $this->aliasDomain=$a;
        /**/
        $page_id=$this->getHomePageId();
        $this->page=$this->homepage=$this->getLocalPage($page_id);

        $this->page->name=$a['domain'];
        foreach (array("text","meta_title","meta_keywords","meta_description") as $k) {
          $this->page->$k=$this->frontend->localStr($this->aliasDomain[$k], 'aliasdomain', $a['id'], $k);
        }
        
        //$this->canonicalLink='http://'.$d.'/index.html';
        $this->canonicalLink=preg_replace('#^http://[^/]*#','http://'.$d,$this->url());

        return $this->page;
        /**/
      } else {
        #_log(get_class().":route - no alias");
      }
    }
    /**/

    $arr=explode("/",strtolower($this->uri));
    $page_id=$this->lookupPage($arr);
    $homepage_id=$this->getHomePageId();
    if ($page_id===null) {
      if (setting('router_404_redirect_home')) {
        return null;
      }
      $page_id=$homepage_id;
    }
    _log(get_class().":route {$uri} > page id = {$page_id}");

    if ($page_id>0) {
      if (($page_id==$homepage_id) || !$homepage_id) {
        $this->page=$this->homepage=$this->getLocalPage($page_id);
        if (isset($_GET['newsletter-hash'])) {
          $this->page->page_type='plugin';
          $this->page->attributes='newsletter';
        }
      } else {
        $this->page=$this->getLocalPage($page_id);
        if ($this->page->page_type=='link') {
          $this->frontend->getOutput()->redirect($this->page->attributes);
          return;
        }
        if ($this->page->parent_id) $this->makeLocalPageComplete($this->page,$this->page->parent_id);

        if (!empty($homepage_id)) {
          $this->makeLocalPageComplete($this->page,$homepage_id);
        }
        $this->homepage=$this->getLocalPage($homepage_id);
        if ($this->page->page_type=='text') $this->canonicalLink=$this->url();
        if ($this->page->page_type=='plugin') switch ($this->page->attributes) {
          case 'links' : case 'gallery' : case 'mailform' : case 'search' : $this->canonicalLink=$this->url();
        }
      }
    }

    return $this->page;
  }
  
  function uri($append=null) {
    return make_url($this->frontend->getSiteURI(),$this->uri,$append,$this->ext);
  }
  function url($append=null) {
    return make_url($this->frontend->getSiteURL(),$this->uri,$append,$this->ext);
  }

  function user_img($name) {
    return getConfigItem('url_userfiles_userimg').$name;
  }
  
}

//end