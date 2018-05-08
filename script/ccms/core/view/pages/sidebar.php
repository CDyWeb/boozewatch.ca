<?

$model=new CCMSManagedModel("SettingsManager");

$controller=CCMSController::getInstance();
$view=$controller->getView();
$node=$controller->getNode();
$view->addToPagePath("<a href='{$_SERVER["_URI"]}'>".$node['name']."</a>");

if (isset($_POST['text'])) {
	$something_changed=false;
	if (!empty($_POST['text'])) $something_changed |= SettingsManager::set('sidebar_'.$node['id'],$_POST['text']);
	if ($something_changed) $ok['main']=$view->cmsTranslate('Changes_saved');
}

$ck_style=getConfigItem("cke_css");
$ck_width="800px";
$ck_height="400px";

$value=SettingsManager::setting('sidebar_'.$node['id']);

?>
<style type="text/css">
.editFieldSet {
  display:inline-block;
  width:60em;
  background-color: #FFF;
}
.editFieldSet P, .editFieldSet .p-ck {
  margin:0;
  padding:0.5em 0;
  clear:both;
}
.editFieldSet P LABEL {
	float:left;
	width:15em;
	padding-left:5px;
}
</style>

<? if (!empty($err['main'])) echo sprintf('<p style="color:red">%s</p>',implode("\n",$err['main'])) ?>
<? if (!empty($ok['main'])) echo sprintf('<p style="color:green">%s</p>',$ok['main']) ?>

<form method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">

  <p>
      <textarea style="width:<?= $ck_width ?>;height:<?= $ck_height ?>;display:none;" class="replaceme" id="text" name="text" ><?= htmlentities($value,ENT_QUOTES,"UTF-8") ?></textarea>
      <div id="cke_preview_text" class="ck-preview" style="width:<?= $ck_width ?>;" onclick="$(document.getElementById('cke_preview_text')).fadeOut('fast',function() { CKEDITOR.replace('text', {customConfig : '<?= $view->resources_url('/cke/ckconfig.php') ?>'});});">
        <p class='ck-click-here'><?= $view->_('ck_editor.click_here') ?></p>
        <iframe src="about:blank" name="iframe_text" id="iframe_text" border="0" frameborder="0" style="width:100%;height:<?= $ck_height ?>"></iframe>
        <script type="text/javascript">
$(document).ready(function() {
  var frame=window.iframe_text;
  frame.document.open();
  frame.document.write('<html><head><style type="text/css">@import url("<?= $ck_style ?>");</style></head><body>'+document.getElementById('text').value+'</body></html>');
  frame.document.close();
});
        </script>
      </div>
  </p>
  
  <p>&nbsp;</p>
  
  <p class='btnpanel'>
    <label>&nbsp;</label>
    <button type="submit" class="ok-button"><span class="ui-icon ui-icon-disk" style="float:left"></span><?= $view->cmsTranslate('Save') ?></button>
  </p>

</fieldset></form>

<script type="text/javascript">
$(function() {
  $(".btnpanel BUTTON").button();
});
</script>