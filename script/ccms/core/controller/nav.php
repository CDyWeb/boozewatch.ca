<?

class NavController extends CCMSController {

	public function __construct() {
		$model=new NavModel();
		parent::__construct(new CCMSDefaultView("nav.php",$model),$model);
	}

}

//end