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
<body><a name="top"></a>

<? if (isset($_SESSION['crud.flash'])) { ?>
  <div id="ccms-crud-flash" class="inline ok">
<?
  switch ($_SESSION['crud.flash'][0]) {
    case 'literal' :
      echo $_SESSION['crud.flash'][1];
      break;
    case 'Crud.deleted.many' :
    case 'Crud.updated.many' :
      echo _($_SESSION['crud.flash'][0]);
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
  }, 2000);
});
  </script>
<? 
    unset($_SESSION['crud.flash']);
  }
?>

  {body}

</body>
</html>