<?

require "body.php";

class PageController extends BodyController {

  protected $crud=null;

	//@Override
	public function handleRequest() {
		log_message("trace",get_class($this).":handleRequest {$this->url_param}");
		if (!preg_match("#/(\d+)/#",$this->url_param,$match)) {
			$page="welcome";
			if (preg_match("#/(.+)$#",$this->url_param,$match)) $page=$match[1];
			
			log_message("trace",get_class($this).":handleRequest, page = {$page}");
			$this->view->setPage("{$page}.php");
			$this->view->addToPagepath("<a href='".getRequestURI()."'>{$page}</a>");
			return;
		}
		
		log_message("trace",get_class($this).":handleRequest, tree_id = {$match[1]}");
		$this->tree_id=$_SESSION["tree_id"]=intval($match[1]);
		$this->tree_line=$this->getTreeManager()->get($this->tree_id);
		
		log_message("trace","tree_line: {0}",array($this->tree_line));
		
		if (!$this->tree_line) {
			throw new Exception("tree node {$this->tree_id} not found");
		}
		
		$page=$this->tree_line["page"];
		$cls=$this->tree_line["class"];
    
    $inCrud=isset($_POST["__save"]) || isset($_GET['edit']) || isset($_GET['delete']) || isset($_GET['up']) || isset($_GET['down']) || isset($_GET['duplicate']);

		if (empty($page) || $inCrud) {
      if (empty($cls)) throw new Exception("tree node {$this->tree_id} is not viewable");
       $this->crud=new CCMSCrudAdapter($this);
       $_SESSION["{$cls}.tree_id"]=$this->tree_id;
       $this->crud->crud($cls);
		} else {
      $_SESSION["{$page}.tree_id"]=$this->tree_id;
      $this->view->setPage($page.".php");
    }
	}


}

//end