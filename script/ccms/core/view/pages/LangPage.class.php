<?

class LangPage {

  function __construct() {
    $this->model=new CCMSManagedModel("SettingsManager");
    $this->controller=CCMSController::getInstance();
    $this->view=$this->controller->getView();
    
    $l=getConfigItem('language');
    if (isset($_REQUEST['l'])) $this->lang=$_SESSION['LangPage.lang']=$_REQUEST['l'];
    else if (isset($_SESSION['LangPage.lang'])) $this->lang=$_SESSION['LangPage.lang'];
    else $this->lang=$l['default'];
    $this->tr=new CCMSTranslator($this->lang);
  }

  function invoke() {
    if ($_SERVER['REQUEST_METHOD']=='POST') $this->post();
    $this->output();
  }
  
  function set($context,$key,$value, $status='translated') {
    return executeSql('replace into ccms_translation set `lang`='.dbStr($this->lang).', `context`='.dbStr($context).', `key`='.dbStr($key).', `value`='.dbStr($value).', `status`='.dbStr($status));
  }

  function post() {
    #--
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $cache=CcmsObjectCache::getInstance();
    $key=getConfigItem('domain').':ezcCcmsTranslation:static:'.$this->lang;
    $cache->delete($key);
    #--
    foreach ($this->getTabs() as $tabname=>$caption) {
      if (isset($_POST[$tabname])) {
        $this->something_changed=false;
        foreach ($_POST['static'] as $k=>$v) {
          $this->something_changed |= $this->set('static',$k,$v);
        }
        if ($this->something_changed) $this->ok[$tabname]=$this->view->cmsTranslate('Changes_saved');
      }
    }
    /**
    if (isset($_POST['invoice'])) {
      $this->something_changed=false;
      foreach ($_POST['static'] as $k=>$v) {
        $this->something_changed |= $this->set('static',$k,$v);
      }
      if ($this->something_changed) $this->ok['invoice']=$this->view->cmsTranslate('Changes_saved');
    }
    **/
  }
  
  function getTabs() {
    if (empty($this->tabs)) {
      $this->tabs=array(
        'mailform'=>'Email Forms'
      );
      if (getConfigItem('plugin.enable.shop',false)) $this->tabs['invoice']='Shop';
      if (getOneValue('select id from ccms_tree where active=1 and page=\'newsletter\'')) $this->tabs['newsletter']='Newsletter';
    }
    return $this->tabs;
  }
  
  function tab_mailform() {
?>
	<form method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
<? foreach (array('mailform_done_msg'=>'Confirm message') as $s=>$caption) { ?>
		<p>
			<label for="<?= $s ?>"><?= $caption ?></label>
			<textarea style="width:800px;" id="<?= $s ?>" name="static[<?= $s ?>]"><?= trim($this->tr->getStaticTranslation()->getTranslation($s,null,'')) ?></textarea>
		</p>
<? } ?>
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input type="submit" name="mailform" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>
<?
  }
  
  function tab_newsletter() {
?>
	<form method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
<?
    $arr=array(
      'newsletter.ok.subscribe'=>'Newsletter subscribed',
      'newsletter.ok.unsubscribe'=>'Newsletter unsubscribed',
      'newsletter.err.invalid_email'=>'Subscribe failed, invalid email',
      'newsletter.err.empty_name'=>'Subscribe failed, your name is required',
      'newsletter.err.not-subscribed'=>'Unsubscribed failed, not subscribed',
    );
    if (!getConfigItem('newsletter.name_required',false)) unset($arr['newsletter.err.empty_name']);
    foreach ($arr as $s=>$caption) { ?>
		<p>
			<label for="<?= $s ?>"><?= $caption ?></label>
			<textarea style="width:800px;" id="<?= $s ?>" name="static[<?= $s ?>]"><?= trim($this->tr->getStaticTranslation()->getTranslation($s,null,'')) ?></textarea>
		</p>
<? } ?>
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input type="submit" name="newsletter" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>
<?
  }
  
  function tab_invoice() {
?>
	<form method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
<? foreach (array('new','in_process','backorder','payed','sent','closed','cancelled') as $s) { ?>
		<p>
			<label for="status_info_<?= $s ?>">Status: <?= $this->view->domainTranslate('Order.status.'.$s) ?></label>
			<input style="width:800px;" type="text" id="status_info_<?= $s ?>" name="static[invoice.status_info.<?= $s ?>]" value="<?= trim($this->tr->getStaticTranslation()->getTranslation('invoice.status_info.'.$s,null,'')) ?>" />
		</p>
<? } ?>
<? foreach (array('visit','in_advance','rembours','ideal','paypal','account','cc','visit_in_advance') as $s) { ?>
		<p>
			<label for="status_info_<?= $s ?>">Betaalwijze: <?= $this->view->domainTranslate('Order.payment.'.$s) ?></label>
			<input style="width:800px;" type="text" id="status_info_<?= $s ?>" name="static[invoice.payment_info.<?= $s ?>]" value="<?= trim($this->tr->getStaticTranslation()->getTranslation('invoice.payment_info.'.$s,null,'')) ?>" />
		</p>
<? } ?>
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input type="submit" name="invoice" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>

<?
  }

  function output() {
  
    $this->view->addToPagePath("<a href='{$_SERVER["_URI"]}'>".$this->view->domainTranslate("Tree.Language")."</a>");
    $tabs=$this->getTabs();
    
    $l=getConfigItem('language');

?>
<style type="text/css">
.editFieldSet P LABEL {
	float:left;
	width:15em;
	padding-left:5px;
}
</style>
<div id="tabs-page">
<? if (count($l['available'])>1) { ?>
  <form method="POST" action="<?= $_SERVER['_URI'] ?>"><p>
    <label></label>
    <select name='l' onchange="this.form.submit()"><?
      
      foreach ($l['available'] as $c) echo '<option '.($this->lang==$c?'selected="selected"':'').' value="'.$c.'">'.$this->view->_('l.'.$c).'</option>';
    
    ?></select>
  </p></form>
<? } ?>
  <ul>
    <? foreach ($tabs as $n=>$c) { ?><li><a href="#tab-<?= $n ?>"><?= $c ?></a></li><? } ?>
  </ul>
  <? foreach ($tabs as $n=>$c) { 
      $m='tab_'.$n;
?>
  <div id="tab-<?= $n ?>">
    <? if (!empty($this->err[$n])) echo sprintf('<p style="color:red">%s</p>',implode("\n",$this->err[$n])) ?>
    <? if (!empty($this->ok[$n])) echo sprintf('<p style="color:green">%s</p>',$this->ok[$n]) ?>
    <? $this->$m() ?>
  </div>
  <? } ?>
</div>
<script type="text/javascript">
jq(function() {
  jq("#tabs-page").tabs();
});
</script>
<?
  }
}

//end