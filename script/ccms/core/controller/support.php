<?

require "body.php";

class SupportController extends BodyController {
	
	//@Override
	public function handleRequest() {
		_require('view/support/index.php');

    $this->setView($v=new SupportView());
    $v->addToPagePath("<a href='index.html'>{$this->view->_('Support')}</a>");

    
	}

}

//end