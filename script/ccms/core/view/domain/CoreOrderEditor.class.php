<?

class CoreOrderEditor extends GenericEditor {

	protected function editEnum(CCMSDomainField $field) {
    parent::editEnum($field);
    if ($field->getName()=='status') {
?>
		<p style="display:none" id="p_send_status_notification">
			<label>&nbsp;</label>
			<input id="input_send_status_notification" class="checkbox" type="checkbox" name="input_send_status_notification" value="1" />
      <label for="input_send_status_notification" style="float:none"><?= $this->_('Order.Send status notification') ?></label>
		</p>
    <script type="text/javascript">
      $(document).ready(function() {
        $('#editForm_status').bind('change',function() {
          if (this.value=='<?= $this->line['status'] ?>') {
            $('#p_send_status_notification').hide();
            $('#input_send_status_notification').attr('checked','');
          } else $('#p_send_status_notification').fadeIn();
        });
      });
    </script>
<?
    }
  }
  protected function customEdit($field) {
    switch ($field->getName()) {
    case 'language' :
      $arr=array();
      $lc=getConfigItem('language',array('default'=>'en','available'=>array('en')));
      foreach($lc['available'] as $l) $arr[$l]=$this->_('l.'.$l);
      $field->attributes=$arr;
      if ($this->id==0) $this->line['language']=$lc['default'];
      $this->editEnum($field);
      return true;
    }
    return false;
  }
}

//end