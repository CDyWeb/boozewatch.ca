<?

_require ('GCF.inc.php');
if (defined('MSIE_LT_7') && !headers_sent()) header('X-UA-Compatible: chrome=1');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head profile="http://www.w3.org/2005/10/profile">
	<title><?= getConfigItem('cms.title','CDyWeb CMS') ?> &rsaquo; {translate:Login}</title>
<? if (defined('MSIE_LT_7')) { ?><meta http-equiv="X-UA-Compatible" content="chrome=1" />
<?} ?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/x-icon" href="{resources_url}/img/favicon.ico" />
	<link rel="stylesheet" href="{resources_url}/css/login.css" type="text/css" />
	<script type="text/javascript">
function checkFrame() {
	if (window.top!=window) window.top.location.href=window.location.href;
}
	</script>
  
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/<?= getConfigItem('jquery.version','1.7.1') ?>/jquery.min.js"></script>
  
</head>
<body onload='checkFrame()'>
	<div id="login">
		<h1><a href="<?= getConfigItem('cms.title_ahref','http://www.cdyweb.com/') ?>"><?= getConfigItem('cms.title','CDyWeb CMS') ?></a></h1>
		{error}
		<form name="loginform" id="loginform" action="<?= $this->base_url('/') ?>" method="post">
			{profile}
			<p><label>{translate:E-mail address}:<br /><input type="text" name="log" id="log" value="" size="20" tabindex="1" /></label></p>
			<p><label>{translate:Password}:<br /> <input type="password" name="pwd" id="pwd" value="" size="20" tabindex="2" /></label></p>
			<p class="submit">
				<input type="submit" name="submit" id="submit" value="{translate:Login} &raquo;" tabindex="3" />
			</p>
      <p style="text-align:right"><a href="#" onclick="$('#loginform').hide();$('#passwordform').fadeIn(); return false;">{translate:Lost your password}</a></p>
		</form>
		<form name="passwordform" id="passwordform" action="<?= $this->base_url('/') ?>" method="post" style="display:none">
			{translate:Lost your password}
			<p><label>{translate:E-mail address}:<br /><input type="text" name="lost" id="lost" value="" size="20" tabindex="1" /></label></p>
			<p class="submit">
				<input type="submit" name="submit" id="submit2" value="{translate:Next} &raquo;" tabindex="3" />
			</p>
      <p style="text-align:right"><a href="#" onclick="$('#passwordform').hide();$('#loginform').fadeIn(); return false;">{translate:Back}</a></p>
		</form>
    {passwordreset}
	</div>
	
<?
if (defined('MSIE_LT_7') && !CHROMEFRAME) {
?>
<style>
#login { display:none; }
</style>
<script src="../shared/js/CFInstall.js?<?= time(); ?>" type="text/javascript"></script>
<script type="text/javascript">
CFInstall.check([mode='popup']);
</script>
<? } else { ?>
<script type="text/javascript">
document.getElementById('log').focus();
</script>	
<? } ?>
</body>
</html>