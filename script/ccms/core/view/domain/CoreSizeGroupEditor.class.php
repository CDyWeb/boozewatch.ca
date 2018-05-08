<?

class CoreSizeGroupEditor extends GenericEditor {

  public function outputScripts() {
    parent::outputScripts();
  }

  protected function outputStart() {
?>

<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#SizeGroup"><?= $this->domainTranslate('SizeGroup') ?></a></li>
  <? if (($this->id>0) && !$this->isDuplicating()) { ?><li><a data-toggle="tab" href="#Size"><?= $this->domainTranslate('Size._title') ?></a></li><? } ?>
</ul>
  
<div id="tabs" class="tab-content">
  <div class="tab-pane active" id="SizeGroup">
<?
    parent::outputStart();
  }
  
  protected function outputEnd() {
    parent::outputEnd();
?>
  </div>
  <div class="tab-pane" id="Size">
<?
    if (($this->id>0) && !$this->isDuplicating()) {
      $_SESSION['Size.sizegroup']=$this->id;
?>
<iframe id="aa" name="aa" src="<?= getConfigItem('base_url') ?>/inline/SizeManager.html" border="0" frameborder="0" style="width:100%;height:500px"></iframe>
<script type='text/javascript'>
document.write("");
function tabheight() {
  var h=jq(window).height()-170;
  jq('#aa').css('height',h+'px');
}
jq(window).load(tabheight);
jq(window).resize(tabheight);
</script>
<?
    }
?>
  </div>
</div>
<?
  }

}

//end