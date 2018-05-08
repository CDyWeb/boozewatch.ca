<?

class CoreCustomerList extends GenericList {

	//@Override
	function getListValue($manager,$fieldName,$line,$maxlength) {
		switch($fieldName) {
			case "__NAME" : return CustomerManager::getCustomerName($line);
			case "__REWARD" : return $this->getManager()->getRewards($line);
			default : return parent::getListValue($manager,$fieldName,$line,$maxlength);
		}
	}

	public function startList() {
		
		$searched=null;
		if (isset($_GET["_search"])) $searched=$_SESSION["Customer.last_search"]=$_GET["_search"];
		else if (isset($_GET["clear"])) unset($_SESSION["Customer.last_search"]);
		else if (isset($_SESSION["Customer.last_search"])) $searched=$_SESSION["Customer.last_search"];
		
?>
<fieldset style="width:700px;text-align:right;margin-bottom:25px;">
<form action='<?= $_SERVER["_URI"] ?>'>
	<label for='_search'><?= $this->cmsTranslate("Search") ?> : </label>
	<input type='text' name='_search' value='<?= utf8_ent($searched) ?>' />
	<input type='submit' name='_find' value='<?= $this->cmsTranslate("search") ?>' onclick='return this.form._search.value!=""' />
</form>
<?

		if ($searched!==null) {
			$this->data=$this->getManager()->doSearch($searched);
?>
<p><?= $this->_("CustomerList.searched_for",array('',$searched,count($this->data),$_SERVER["_URI"]."?clear")) ?></p>
<?
		}
?>
</fieldset>
<?
		parent::startList();
	}
	
	public function getTableHtml($data=null,$plain=false) {
		if (isset($this->data)) $data=$this->data;
		return parent::getTableHtml($data,$plain);
	}

}


//end