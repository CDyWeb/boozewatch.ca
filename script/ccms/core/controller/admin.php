<?

require_once("body.php");

class AdminController extends BodyController {

	public function handleRequest() {
		if (!UserModel::isAdmin()) {
			die("No Access!");
		}
		parent::handleRequest();
	}

}

//end