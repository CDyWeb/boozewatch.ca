<?

class CustomerOrderManager extends OrderManager {
	
	function __construct() {
		parent::__construct();
		$this->setFilterFieldName("customer");
		$this->setListFields(array("printed","order_id","date_insert","status","am_total"));
	}

}

//end