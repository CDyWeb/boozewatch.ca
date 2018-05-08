<?

require "body.php";

class AjaxView extends CCMSDefaultView {
	public function __construct(CCMSModel $model=null) {
		parent::__construct("ajax.php", $model);
		$this->lang=CmsLang::getInstance();
	}
}

class AjaxController extends BodyController {

  public function __construct() {
    parent::__construct();//new AjaxView()
  }
  
  public function getManager() {
    return $this->model->getDomainManager();
  }

	//@Override
	public function handleRequest() {
		log_message("trace",get_class($this).":handleRequest {$this->url_param}");
		
    #--
    if (count($_GET)==0) throw new Exception('nothing to do');
    $method=current(array_keys($_GET));
		if (!method_exists($this,$method)) throw new Exception('cannot to that: '.$method);
    #--

		if (!preg_match("#/(\w+)Manager#",$this->url_param,$match)) throw new Exception('no Manager');

		$cls=$match[1];
		log_message("trace",get_class($this).":handleRequest, cls = {$cls}");
    $this->model=new CCMSManagedModel("{$cls}Manager");
    
    $this->$method();
	}
  
  public function orderby() {
    $arr=array();
    if (empty($_POST)) die(json_encode(array('error'=>'no data')));
    $arg1=current($_POST);
    if (!is_array($arg1)) die(json_encode(array('error'=>'no array data')));
    foreach ($arg1 as $v) if (preg_match('#^tr-(\d+)$#',$v,$match)) $arr[]=$match[1];
    try {
      $this->model->orderby($arr);
    } catch (Exception $ex) {
      die(json_encode(array('error'=>$ex->getMessage())));
    }
    echo json_encode(array('ok'));
    exit();
  }
  
  public function imageUpload() {
    $err='';
    if (($fn=$this->getManager()->tempImageUpload($err))===false) echo json_encode(array('err'=>$err));
    else echo json_encode(array('ok'=>$fn));
    exit();
  }
  public function fileUpload() {
    $err='';
    if (($fn=$this->getManager()->tempFileUpload($err))===false) echo json_encode(array('err'=>$err));
    else echo json_encode(array('ok'=>$fn));
    exit();
  }
  
  public function readdir() {
    if (isset($_GET['path'])) {
      $ls=readdir_ls($_GET['path']);
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $res=array();
      foreach ($ls as $k=>$v) {
        $utf8_k=utf8_encode($k);
        $utf8_v=utf8_encode($v);
        $e=explode('.',$utf8_k);
        $res[]=array(
          'path'=>$utf8_v,
          'name'=>$utf8_k,
          'size'=>return_size(filesize($v)),
          'type'=>end($e),
          'mtime'=>date('m/d/Y g:i: A',filemtime($v)),
          'mime'=>finfo_file($finfo, $v),
        );
      }
      finfo_close($finfo);
      echo json_encode($res);
    }
    exit();
  }

  public function deleteFile() {
    $result='fail';
    if (isset($_GET['b64'])) $_GET['name']=urldecode(base64_decode($_GET['b64']));
    if (isset($_GET['path']) && isset($_GET['name'])) {
      $fn=$_GET['path'].'/'.$_GET['name'];
      if (file_exists($fn)) {
        unlink($fn);
        $result='ok';
      }
    }
    echo json_encode(array('result'=>$result));
    exit();
  }

  public function download() {
    if (isset($_GET['b64'])) $_GET['name']=urldecode(base64_decode($_GET['b64']));
    if (isset($_GET['path']) && isset($_GET['name'])) {
      $fn=$_GET['path'].'/'.$_GET['name'];
      if (!file_exists($fn)) die('file not found: '.$fn);
      ob_start();
      header("Cache-Control: no-cache");
      header("Pragma: no-cache");
      header("Expires: Fri, 01 Jan 2010 05:00:00 GMT");
      header('Content-disposition: attachment; filename="'.$_GET['name'].'"');
      header('Content-Transfer-Encoding: binary');
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      header('Content-Type: '.finfo_file($finfo, $fn));
      finfo_close($finfo);
      ob_end_clean();
      readfile($fn);
      exit();
    }
  }
  public function downloadPreview() {
    if (isset($_GET['b64'])) $_GET['name']=urldecode(base64_decode($_GET['b64']));
    if (isset($_GET['path']) && isset($_GET['name'])) {
      $fn=$_GET['path'].'/'.$_GET['name'];
      if (!file_exists($fn)) die('file not found: '.$_GET['name']);
      ob_start();
      $expires = 60*60*24;
      header("Pragma: public");
      header("Cache-Control: maxage=".$expires);
      header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
      header('Content-disposition: attachment; filename='.$_GET['name']);
      header('Content-Transfer-Encoding: binary');
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      header('Content-Type: '.finfo_file($finfo, $fn));
      finfo_close($finfo);
      ob_end_clean();
      readfile($fn);
      exit();
    }
  }
}


//end