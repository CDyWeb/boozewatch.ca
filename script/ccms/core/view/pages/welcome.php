<div id='txt'>
<?= CCMSController::getInstance()->getView()->_('page.welcome') ?>
<? 
/**
  if (file_exists($fn=getConfigItem('script_base').'shared/svn.info.txt')) {
	$info=unserialize(file_get_contents($fn));
	foreach ($info as $i=>$s) if (preg_match('#^(.*):(.*)$#U',$s,$match)) $info[trim($match[1])]=trim($match[2]);
	$_SESSION["ccms.svn.info"]=$info;
**/
  if (isset($_SESSION['ccms.svn.info'])) {
    $info=$_SESSION['ccms.svn.info'];
    echo "<ul style='list-style:none'>";
    if (isset($info['Revision'])) echo "<li>".($_SESSION["ccms.version"]="V 3.4 rev. ".$info['Revision'])."</li>";
    if (isset($info['Last Changed Date'])) echo "<li>Built on ".preg_replace('#(^\d{4}.*:\d{2}).*$#','$1',$info['Last Changed Date'])."</li>";
    echo "</ul>";
  }
?>
</div>