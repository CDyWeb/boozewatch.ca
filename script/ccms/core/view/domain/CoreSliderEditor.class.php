<?

class CoreSliderEditor extends GenericEditor {

  protected function customEdit($field) {
		if ($field->name=="visible") {
    
      $visible=empty($this->line['visible'])?array():json_decode($this->line['visible'],true);
    
?>
<div class="p">
  <label><?= $this->domainTranslate('Slider.visible') ?></label>
  <table cellspacing="0" cellpadding="0">
<? foreach (getTableArray('select id,name from ccms_page order by tree_id,parent_id,orderby') as $line) { ?>
    <tr><td><input <? if (isset($visible[$line['id']])) echo 'checked="checked"'; ?> style="width:auto;" type="checkbox" name="_p[<?= $line['id'] ?>]" value="<?= $line['id'] ?>" id="_p_<?= $line['id'] ?>" /></td><td><label style="float:none;" for="_p_<?= $line['id'] ?>"><?= utf8_ent($line['name']) ?></label></td></tr>
<? } ?>
  </table>
  <br /><br />
</div>
<?
      return true;
    }
    return parent::customEdit($field);
  }
}

//end