<?

class CoreSizeEditor extends GenericEditor {

	protected function customEdit($field) {
		if ($field->name=="name") {
      if ($this->id>0) return false;
      
      echo '<p><label style="width:auto">'.$this->domainTranslate($this->getModel()->getName(),'_add_many').'</label><br style="clear:both" /></p>';
      for ($i=0;$i<10;$i++) {
?>
		<p>
			<label for="editForm:name"><?= $this->domainTranslate($this->getModel()->getName(),'name') ?></label>
			<input type="text" value="" size="40" name="input_name[]" />
		</p>
<?
      }
      return true;
    }
  }
}

//end