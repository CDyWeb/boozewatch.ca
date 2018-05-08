<?

class PiwikView extends BodyView {
	function outputScripts() {}
	function outputStyles() {}
	function outputContent() {
  
    $idSite=SettingsManager::setting('ccms.piwik',0);
    $url=SettingsManager::setting('ccms.piwik_url','http://analytics.bhosted.ca/index.php');
    $token=SettingsManager::setting('ccms.piwik_token_auth','6be82f5680b94e828210ae96898e84e0');
    $src=$url.'?token_auth='.$token.'&module=Widgetize&action=iframe&moduleToWidgetize=Dashboard&actionToWidgetize=index&idSite='.$idSite.'&period=week&date=yesterday';
  
    #echo $src;
?>

<iframe id="aa" style="width:100%;height:100px;border:0;" width="100%" height="100" frameborder="0" border="0" src="<?= $src ?>"></iframe>
<script type='text/javascript'>
function tabheight() {
  var h=jq(window).height()-120;
  jq('#aa').css('height',h+'px');
}
jq(window).load(tabheight);
jq(window).resize(tabheight);
</script>

<?
	}
}


//end