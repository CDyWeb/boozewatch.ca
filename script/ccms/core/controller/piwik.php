<?

require "body.php";

class PiwikController extends BodyController {

	//@Override
	public function handleRequest() {
		require 'core/view/piwik/index.php';

    $this->setView($v=new PiwikView());
    $v->addToPagePath("<a href='index.html'>{$this->view->_('Analytics')}</a>");

	}

}

//end