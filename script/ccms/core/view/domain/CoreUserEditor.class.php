<?

class CoreUserEditor extends GenericEditor {

	protected function customEdit($field) {
		if ($field->getName()=="password") {
			$this->editPassword($field);
			return true;
		}
		return false;
	}
	
	protected function editPassword(CCMSDomainField $field) {
		$value=isset($this->line[$field->getName()])?$this->line[$field->getName()]:"";
    $cls=array();
    if ($field->required) $cls[]='required';
    $onblur=array('this.value=jq.trim(this.value)','inputChanged(this)');
?>
    <div class="form-group">
      <label class="control-label col-sm-2" for="<?= $this->getInputID($field) ?>"><?= $this->domainTranslate($this->getModel()->getName(),$field->name).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-10">
        <input onchange="inputChanged(this)" onblur="<?= implode('; ',$onblur) ?>" type="password" class="form-control required" value="<?= $value?UserManager::NOT_CHANGED:"" ?>" required="required" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" class="<?= implode(' ',$cls) ?>" />
      </div>
    </div>
<?
	}
	
}

//end