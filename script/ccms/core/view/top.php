<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://www.w3.org/2005/10/profile">
	<title>CDyWeb CMS</title>
<? if (defined('MSIE_LT_7')) { ?><meta http-equiv="X-UA-Compatible" content="chrome=1" />
<?} ?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/x-icon" href="{resources_url}/img/favicon.ico" />
	<link rel="stylesheet" href="{resources_url}/css/top.css" type="text/css" />
	<script type="text/javascript">
function checkFrame() {
	if (window.top.location.href==window.location.href) window.top.location.href="{base_url}";
}
	</script>
</head>
<body onload='checkFrame()'>
	<div id="top">
		<div id="home">
			<h1><a target='_top' href="{base_url}">CDyWeb CMS</a></h1>
		</div>
		<div id="menu">
			{translate:Language} :
			<select onchange="window.location.href='<?= $_SERVER['_URI'] ?>?system_lang='+this.value">
<? foreach (array('en_US','nl_NL') as $l) { ?><option value="<?= $l ?>" <?= getConfigItem('system_lang')==$l?'selected="selected"':'' ?>>{translate:<?= $l ?>}</option><? } ?>
			</select>
			|
			<a target='frame_body' href='support.html'>{translate:Support}</a>
			<!--
			|
			<a target='frame_body' href='profile.html'>My profile</a>
			-->
			|
			<a target='_top' href='{_URI}?logout'>{translate:Logout}</a> ({username})
		</div>
		<div id="version"><? echo isset($_SESSION["ccms.version"])?$_SESSION["ccms.version"]:"dev" ?></div>
	</div>
<? if (isset($_GET['system_lang'])) { ?>
<script>
window.parent.frame_nav.location.href=window.parent.frame_nav.location.href;
window.parent.frame_body.location.href=window.parent.frame_body.location.href;
</script>
<? } ?>
</body>
</html>