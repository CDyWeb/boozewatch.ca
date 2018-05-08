<?

$language=getConfigItem('language');
$controller=CCMSController::getInstance();
$view=$controller->getView();

if (!empty($_POST['text'])) {
  require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
  $cache=CcmsObjectCache::getInstance();
  foreach ($_POST['text'] as $l=>$value) {
    executeSql('replace into ccms_translation set `lang`='.dbStr($l).', `context`='.dbStr('static').', `key`='.dbStr('newsletter.email.activate').', `value`='.dbStr($value).', `status`='.dbStr('translated'));
    $key=getConfigItem('domain').':ezcCcmsTranslation:static:'.$l;
    $cache->delete($key);
  }
  echo '<p style="color:green">'.$view->cmsTranslate('Changes_saved').'</p>';
}

$manager=new NewsletterManager();

_require('inc/CcmsNewsletter.class.php');
class MyCcmsNewsletter extends ZZCcmsNewsletter {
  public function getActivateMsg($language) {
    return parent::getActivateMsg($language);
  }
}
$newsletterObj=new MyCcmsNewsletter($manager);

$ck_width='700px';
$ck_height='200px';

?>

<form method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet" style="width:700px">

<?
foreach ($language['available'] as $l) {
  $value=$newsletterObj->getActivateMsg($l);
?>
  <p>
    <label><?= $view->cmsTranslate("newsletter.confirm.txt") ?> : <?= strtoupper($l) ?></label>
    <textarea style="width:<?= $ck_width ?>;height:<?= $ck_height ?>;display:none;" class="replaceme" id="text_<?= $l ?>" name="text[<?= $l ?>]" ><?= htmlentities($value,ENT_QUOTES,"UTF-8") ?></textarea>
    <div id="cke_preview_text_<?= $l ?>" class="ck-preview" style="width:<?= $ck_width ?>;" onclick="$(document.getElementById('cke_preview_text_<?= $l ?>')).fadeOut('fast',function() { CKEDITOR.replace('text_<?= $l ?>', {customConfig : '<?= $view->resources_url('/cke/ckconfig.php') ?>'});});">
      <p class='ck-click-here'><?= $view->_('ck_editor.click_here') ?></p>
      <iframe src="about:blank" name="iframe_text_<?= $l ?>" id="iframe_text_<?= $l ?>" border="0" frameborder="0" style="width:100%;height:<?= $ck_height ?>"></iframe>
      <script type="text/javascript">
$(document).ready(function() {
  var frame=window.iframe_text_<?= $l ?>;
  frame.document.open();
  frame.document.write('<html><head></head><body>'+document.getElementById('text_<?= $l ?>').value+'</body></html>');
  frame.document.close();
});
      </script>
    </div>
  </p>
<?
}
?>
  <p>
    <label>&nbsp;</label><input type="submit" name="newsletter" value="<?= $view->cmsTranslate("Save") ?>" />
  </p>
</fieldset></form>