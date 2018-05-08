<?

class TopController extends CCMSController {

	public function __construct() {
		parent::__construct(new CCMSDefaultView("top.php"));
	}

}

//end