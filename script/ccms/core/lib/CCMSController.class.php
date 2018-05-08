<?

abstract class CCMSController {

	protected $model;
	protected $view;
	protected $redirect=null;
	protected $url_param;
	protected $lang;
	private static $instance=null;

	public function __construct(CCMSView $view=null, CCMSModel $model=null) {
		$this->view=$view;
		$this->model=$model;
		$this->lang=CmsLang::getInstance();
		self::$instance = $this;
	}
	
	public static function getInstance() {
		return self::$instance;
	}
	
	public function getModel() {
		return $this->model;
	}
	public function setModel($model) {
		$this->model=$model;
	}

	public function getView() {
		return $this->view;
	}
	public function setView($view) {
		$this->view=$view;
	}
	
	public function getRedirect($redirect) {
		return $redirect;
	}
	public function setRedirect($url) {
		log_message("trace",get_class($this).":setredirect {$url}");
		$this->redirect=$url;
	}
	
	protected function redirect() {
		log_message("trace",get_class($this).":redirecting to {$this->redirect}");
		redirect_url($this->redirect);
	}
	
	public function handleGet() {}
	public function handlePost() {}

	public function handleRequest() {
		if ($_SERVER["REQUEST_METHOD"]=="POST") $this->handlePost();
		else $this->handleGet();
	}

	function invoke($url_param="") {
		$this->url_param=$url_param;
		
		$this->handleRequest();
		
		if ($this->redirect!=null) {
			$this->redirect();
			return;
		}
		
		$view=$this->getView();
		if (!is_object($view)) throw new Exception("No view available for ".get_class($this));

		$res=$view->render();
		echo $res;
	}
}