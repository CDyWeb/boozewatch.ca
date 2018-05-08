<?

$controller=CCMSController::getInstance();
$view=$controller->getView();
$view->addToPagePath("<a href='{$_SERVER["_URI"]}'>".$view->cmsTranslate("page.shop.payment")."</a>");

$opt=array(
	'visit',
	'visit_in_advance',
	'in_advance',
	'account',
	'rembours',
	'cc',
	'ideal',
	'paypal',
);

if ($_SERVER['REQUEST_METHOD']=='POST') {
	if (isset($_GET['main'])) {
		if (empty($_POST['payment_methods'])) $_POST['payment_methods']=array();
		$ok_payment_methods=SettingsManager::set('webshop.payment_methods',implode(',',$_POST['payment_methods']));
	}
}
$checked=explode(",",SettingsManager::setting("webshop.payment_methods","in_advance"));

?>

<div id="tabs-page">
  <ul>
    <li><a href="#tab-main"><?= $view->cmsTranslate("page.shop.payment.main") ?></a></li>
  </ul>
  <div id="tab-main">
<?
	if (isset($ok_payment_methods)) echo $ok_payment_methods?'<p style="color:green">'.$view->cmsTranslate("Changes_saved").'</p>':'<p style="color:blue">'.$view->cmsTranslate("Nothing_changed").'</p>';
?>
	<form name='mainForm' action='<?= $_SERVER['_URI'] ?>?main' method='POST'>
		<p>
			<label><?= $view->cmsTranslate("page.shop.payment.main.method-select") ?></label>
		</p>
		<? foreach ($opt as $c) { ?>
		<p>
			&nbsp;<input id="opt_<?= $c ?>" name="payment_methods[]" value="<?= $c ?>" type="checkbox" class="checkbox" <?= in_array($c,$checked)?'checked="checked"':'' ?> />
			<label for="opt_<?= $c ?>"><?= $view->cmsTranslate('page.shop.payment.option.'.$c) ?></label>
		</p>
		<? } ?>
		<p>
			<input type="submit" name="main" value="<?= $view->cmsTranslate("Apply") ?>" />
		</p>
	</form>
  
  </div>
</div>
<script type="text/javascript">
jq(function() {
  jq("#tabs-page").tabs();
});
</script>

<?

//end