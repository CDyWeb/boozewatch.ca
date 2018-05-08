<?

class BodyController extends CCMSController {

  protected $tree_manager=null;
  protected $tree_id;
  protected $tree_line;
  protected $crud;

  public function __construct() {
    parent::__construct(new PageView());
    $this->crud=new CCMSCrudAdapter($this);
    log_message("trace",get_class($this)." constructed");
  }

  public function getCrud() {
    return $this->crud;
  }

  protected function getTreeManager() {
    if ($this->tree_manager==null) $this->tree_manager=new TreeManager();
    return $this->tree_manager;
  }
  
  public function getPagepathName($cls) {
    $n1=$this->tree_line["name"];
    if (substr($n1,0,1)==":") $n1=$this->view->domainTranslate(substr($n1,1),"_title");
    if (substr($n1,0,1)==".") $n1=$this->view->domainTranslate("Tree".$n1);
    $n2=$this->view->domainTranslate($cls."._title");
    if ($n1==$n2) return $n1;
    return "{$n1} - {$n2}";
  }
  
  public function setRedirect($url) {
    if ($this->getModel()->getDomainManager()->isTreeInvolved()) $_SESSION["NavTree.reload"]=true;
    parent::setRedirect($url);
  }
  
  public function getNode() {
    return $this->tree_line;
  }
  
  protected function sessionHasPageAccess($page) {
    $user_type='user';
    if (isset($_SESSION['user']['user_type'])) $user_type=$_SESSION['user']['user_type'];
    switch ($user_type) {
      case 'super' : 
      case 'editor' :
        break;
      default :
        $user_access=explode(',',getConfigItem('user_page_access',SettingsManager::setting('user_page_access','welcome')));
        if (!in_array($page,$user_access)) return false;
    }
    return true;
  }
  
  protected function sessionHasTreeAccess($tree_line) {
    switch ($_SESSION['user']['user_type']) {
    case 'super' : break;
    case 'editor' :
      if ($this->tree_line['user_type']=='super') return false;
      break;
      default :
      if ($this->tree_line['user_type']=='editor') return false;
      if ($this->tree_line['user_type']=='super') return false;
    }
    return true;
  }

  public function handleRequest() {
    log_message("trace",get_class($this).":handleRequest {$this->url_param}");
    if (!preg_match("#/(\d+)/#",$this->url_param,$match)) {
      $page="welcome";
      if (preg_match("#/(.+)$#",$this->url_param,$match)) $page=$match[1];
      
      log_message("trace",get_class($this).":handleRequest, page = {$page}");
      if (!$this->sessionHasPageAccess($page)) throw new Exception('Access denied');
      
      $this->view->setPage("{$page}.php");
      $this->view->addToPagepath("<a href='".getRequestURI()."'>{$page}</a>");
      return;
    }
    
    log_message("trace",get_class($this).":handleRequest, tree_id = {$match[1]}");
    $this->tree_id=$_SESSION["tree_id"]=intval($match[1]);
    $this->tree_line=$this->getTreeManager()->get($this->tree_id);
    
    if (!$this->sessionHasTreeAccess($this->tree_line)) throw new Exception('Access denied');

    log_message("trace","tree_line: {0}",array($this->tree_line));
    
    if (!$this->tree_line) {
      throw new Exception("tree node {$this->tree_id} not found");
    }
    
    $cls=$this->tree_line["class"];
    if (!$cls && $this->tree_line["args"]) {
      $this->view->setPage("{$this->tree_line["args"]}.php");
      $this->view->addToPagepath("<a href='".getRequestURI()."'>{$this->tree_line["name"]}</a>");
      return;
    }
    
    if (!$cls) {
      throw new Exception("tree node {$this->tree_id} is not viewable");
    }

    $_SESSION["{$cls}.tree_id"]=$this->tree_id;
    $this->crud->crud($cls);
  }

}

//end