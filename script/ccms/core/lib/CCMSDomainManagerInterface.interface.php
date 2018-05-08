<?

interface CCMSDomainManagerInterface extends CCMSManager {
	
	function isAddable();
	function isEditable();
	function isDeletable();
	function isMovable();
  function orderby($orderby);
	
	public function getItemName($arg);

}

//end