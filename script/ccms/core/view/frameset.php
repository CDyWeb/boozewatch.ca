<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?= getConfigItem('cms.title','CDyWeb CMS') ?></title>
<? if (defined('MSIE_LT_7')) { ?><meta http-equiv="X-UA-Compatible" content="chrome=1" />
<?} ?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" type="image/x-icon" href="{resources_url}/img/favicon.ico" />
	<link rel="shortcut icon" type="image/x-icon" href="{resources_url}/img/favicon.ico" />
</head>

<frameset cols="250,*" border="1" frameborder="1" framespacing="1">
  <frame name="frame_nav" src="<?= $this->base_url('/nav.html') ?>" scrolling="auto" frameborder="0" marginheight="0" marginwidth="0"/>
  <frame name="frame_body" src="<?= $this->base_url('/body.html') ?>" scrolling="auto" frameborder="0" marginheight="0" marginwidth="0"/>
</frameset>

<? /**
<frameset rows="60,*" frameborder="0" border="0" framespacing="0">
	<frame src="<?= $this->base_url('/top.html') ?>" name="frame_top" scrolling="no" noresize="noresize" frameborder="0" border="0" marginheight="0" marginwidth="0" />
	<frameset cols="250,*" border="1" frameborder="1" framespacing="1">
		<frame name="frame_nav" src="<?= $this->base_url('/nav.html') ?>" scrolling="auto" frameborder="0" marginheight="0" marginwidth="0"/>
		<frame name="frame_body" src="<?= $this->base_url('/body.html') ?>" scrolling="auto" frameborder="0" marginheight="0" marginwidth="0"/>
	</frameset>
	<noframes><body><p>noframes</p></body></noframes>
</frameset>
**/ ?>

</html>