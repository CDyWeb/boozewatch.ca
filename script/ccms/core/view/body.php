<!DOCTYPE html>
<html lang="en">
<head>
	<title>{title}</title>
<? if (defined('MSIE_LT_7')) { ?><meta http-equiv="X-UA-Compatible" content="chrome=1" />
<?} ?>
	<meta charset="utf-8">

	<link rel="stylesheet" href="{resources_url}/css/body.css" type="text/css">
	<script type="text/javascript" src="{shared_url}/js/base64.js"></script>
  <script type="text/javascript" src="{shared_url}/js/swfobject.js"></script>
  <script type="text/javascript" src="{resources_url}/cke/ckeditor/ckeditor.js"></script>

  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/<?= getConfigItem('jquery.version','1.11.0') ?>/jquery.min.js"></script>
  
  <script type="text/javascript" src="{shared_url}/jquery/jquery.cookie.js"></script>
  <script type="text/javascript" src="{shared_url}/jquery/jquery.number_format.js"></script>
  
  <script type="text/javascript" src="{shared_url}/jquery/jquery.event.drag-1.4.js"></script>
  <script type="text/javascript" src="{shared_url}/jquery/jquery.kiketable.colsizable-1.1.js"></script>
  <link rel="stylesheet" href="{shared_url}/jquery/jquery.kiketable.colsizable.css">
  
  <script type="text/javascript" src="{shared_url}/jquery/jquery.tablednd.js"></script>
  <script type="text/javascript" src="{shared_url}/jquery/sort/jquery.tablesorter.js"></script>
  <link rel="stylesheet" href="{shared_url}/jquery/sort/themes/blue/style.css">
  
  <script type="text/javascript" src="{shared_url}/jquery/select2/select2.js"></script>
  <link rel="stylesheet" href="{shared_url}/jquery/select2/select2.css">

  <script src="{shared_url}/jquery/bootstrap-datepicker/js/bootstrap-datepicker.js" type="text/javascript"></script>
  <link rel="stylesheet" href="{shared_url}/jquery/bootstrap-datepicker/css/datepicker3.css">

  <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">
  <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
  
  <script src="{shared_url}/ccms/js/bootstrap-tabs.js"></script>

<?
if (getConfigItem('jquery.jqupload',true)) require 'jqupload.inc';
?>

	<script type="text/javascript">
window.jq = jQuery;

{scripts}

<?
if (isset($_GET['system_lang']) || isset($_SESSION["NavTree.reload"])) {
?>
	$(document).ready(function() {
		window.parent.frame_nav.refreshMe();
	});
<?
  unset($_SESSION["NavTree.reload"]);
}
?>

	</script>
	<style type='text/css'>
<? if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('#Macintosh#',$_SERVER['HTTP_USER_AGENT'])) { ?>
.btnpanel BUTTON { float:right; }
<? } else { ?>
.btnpanel { text-align:right; }
<? } ?>

{styles}

	</style>
</head>
<body>

<? if (isset($_SESSION['crud.flash'])) { ?>
  <div id="ccms-crud-flash" class="<?= isset($_SESSION['crud.flash']['class'])?$_SESSION['crud.flash']['class']:'ok' ?>">
<?
  switch ($_SESSION['crud.flash'][0]) {
    case 'literal' :
      echo $_SESSION['crud.flash'][1];
      break;
    case 'Crud.deleted.many' :
    case 'Crud.updated.many' :
      echo $this->_($_SESSION['crud.flash'][0]);
      break;
    default :
      echo $this->cmsTranslate($_SESSION['crud.flash'][0],array('firstparam'=>$this->domainTranslate($_SESSION['crud.flash'][1].'._title'),'secondparam'=>$_SESSION['crud.flash'][2]));
      break;
  }
?>
  </div>
  <script type="text/javascript">
$(document).ready(function() {
  window.setTimeout(function() {
    $('#ccms-crud-flash').fadeOut();
  }, 5000);
});
  </script>
<? 
    unset($_SESSION['crud.flash']);
  }
  
  $cms_lang=getConfigitem('cms.lang',array('en_US','nl_NL'));

?>

	<div id="ccms-top">
		<div id="ccms-top-home">
			<h1><a name="top"></a><a target='_top' href="{base_url}"><?= getConfigItem('cms.title','CDyWeb CMS') ?></a></h1>
		</div>
		<div id="ccms-top-menu">
<? if (is_array($cms_lang) && !empty($cms_lang)) { ?>
			<form class="form-inline pull-left" style="margin:0 10px;">
        {translate:Language} :
        <select onchange="window.location.href='<?= $_SERVER['_URI'] ?>?system_lang='+this.value" class="input-medium">
          <? foreach ($cms_lang as $l) { ?><option value="<?= $l ?>" <?= getConfigItem('system_lang')==$l?'selected="selected"':'' ?>>{translate:<?= $l ?>}</option><? } ?>
        </select>
      </form>
			|
<? } ?>
<? if (getConfigitem('cms.menu_with_support',true)) { ?>
			<a href='{base_url}/support/index.html'>{translate:Support}</a>
			|
<? } ?>
<? if (getConfigitem('cms.menu_with_profile',true)) { ?>
			<a href='{base_url}/account/index.html'>{translate:My account}</a>
			|
<? } ?>
<? if (getConfigitem('cms.menu_with_piwik',true) && (SettingsManager::setting('ccms.piwik',0)>0)) { ?>
			<a href='{base_url}/piwik/index.html'>{translate:Analytics}</a>
			|
<? } ?>
			<a target='_top' href='{_URI}?logout'>{translate:Logout}</a> ({username})
		</div>
		<div id="ccms-top-version"><? echo isset($_SESSION["ccms.version"])?$_SESSION["ccms.version"]:'-' ?></div>
	</div>

  {body}

</body>
</html>