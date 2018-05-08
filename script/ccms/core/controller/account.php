<?

require "body.php";

class AccountController extends BodyController {

  public function __construct() {
    parent::__construct();
    $model=new CCMSManagedModel('UserManager');
    $this->setModel($model);
  }
	
	//@Override
	public function handleRequest() {
    if ($_SERVER['REQUEST_METHOD']=='POST') {
      $m=$this->model->getDomainManager();
      $m->setEditFields($m->getProfileFields());
      $this->crud->setModel($this->model);
      $this->crud->crud('User');
      
      $_SESSION['user']=$m->get($_SESSION['user']['id']);

      header('Location: '.$_SERVER['_URI'],true,303);
      exit();
    }
		require 'core/view/account/index.php';
    
    $this->setView($v=new AccountView($this->getModel()));
    $v->addToPagePath("<a href='account.html'>{$this->getView()->_('My account')}</a>");

	}

}

//end