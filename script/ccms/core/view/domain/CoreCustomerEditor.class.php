<?

class CoreCustomerEditor extends GenericEditor {

  public function outputScripts() {
    parent::outputScripts();
  }

  protected function outputStart() {
?>

<ul class="nav nav-tabs">
  <li class="active"><a href="#Customer" data-toggle="tab"><?= $this->domainTranslate('Customer') ?></a></li>
  <? if (($this->id>0) && !$this->isDuplicating()) { ?><li><a href="#Orders" data-toggle="tab"><?= $this->domainTranslate('Order._title') ?></a></li><? } ?>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="Customer">
<?
    parent::outputStart();
  }
  
  protected function outputEnd() {
    parent::outputEnd();
?>
    </div>
    <div class="tab-pane" id="Orders">
<?
    if (($this->id>0) && !$this->isDuplicating()) {
      $_SESSION['Order.customer']=$this->id;
?>
<iframe id="aa" name="aa" src="<?= getConfigItem('base_url') ?>/inline/CustomerOrderManager.html" border="0" frameborder="0" style="width:100%;height:500px"></iframe>
<script type='text/javascript'>
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