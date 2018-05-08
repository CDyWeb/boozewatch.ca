<?

/**
#--
CCMSManagedModel::getManager("NewsletterManager");
CCMSManagedModel::getManager("NewsletterGroupManager");
$r=CCMSManagedModel::getManager("NewsletterRecipientManager");
$r->bounceCheck();
#--
**/

$controller=CCMSController::getInstance();
$view=$controller->getView();

if (isset($_GET['compare'])) {
  if (!isset($_REQUEST['_cb']) || !is_array($_REQUEST['_cb'])) $_REQUEST['_cb']=array();
  $newsletters=count($_REQUEST['_cb'])>0?getTableArray("select * from ".(tbl_name('newsletter')).' where id in('.implode(',',$_REQUEST['_cb']).')'):array();
  if (count($newsletters)>0) {
    global $NewsletterCompare;
    $NewsletterCompare='NewsletterCompare';
    _require('view/pages/newsletter-compare.php');
    $obj=new $NewsletterCompare($newsletters);
    $obj->invoke($view);
    return;
  }
  header("Location: ".$view->base_url()."/inline/NewsletterManager.html");
  session_write_close();
  exit;
}

if (isset($_GET['track'])) {
  
  $newsletter=getOneRow("select * from ".(tbl_name('newsletter'))." where id=".intval($_GET['track']));
  if ($newsletter) {
    global $NewsletterTrack;
    $NewsletterTrack='NewsletterTrack';
    _require('view/pages/newsletter-track.php');
    $obj=new $NewsletterTrack($newsletter);
    $obj->invoke($view);
    return;
  }
  header("Location: ".$view->base_url()."/inline/NewsletterManager.html");
  session_write_close();
  exit;
}

if (isset($_GET['send'])) {
  $newsletter=getOneRow("select * from ".(tbl_name('newsletter'))." where id=".intval($_GET['send']));
  if ($newsletter) {
    
    $newsletter['items']=getTableArray('select * from '.tbl_name('newsletteritem').' where newsletter='.$newsletter['id'].' order by orderby');
    
    #--
    if (isset($_GET['dispatch'])) {
      ob_start();
      global $newsletterDispatcherClass;
      $newsletterDispatcherClass="NewsletterDispatcher";
      _require ("inc/NewsletterDispatcher.class.php");
      $jobDone = $newsletterDispatcherClass::dispatch();
      ob_end_clean();
      if ($jobDone) echo json_encode(array('done'=>true));
      else echo json_encode(array('todo'=>$newsletterDispatcherClass::$todo));
      session_write_close();
      exit();
    }
    #--
    
    global $clsNewsletterSend;
    $clsNewsletterSend='NewsletterSend';
    _require('view/pages/newsletter-send.php');
    $obj=new $clsNewsletterSend($newsletter);
    $obj->invoke($view);
    return;
  }
  header("Location: ".$view->base_url()."/inline/NewsletterManager.html");
  session_write_close();
  exit;
}

$view->setPagePath(array());
//$view->addToPagePath("<a href='{$_SERVER["_URI"]}'>".$view->domainTranslate("Newsletter._title")."</a>");

?>

<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#tab-main"><?= $view->domainTranslate("Newsletter._title") ?></a></li>
  <li><a data-toggle="tab" href="#tab-recipients"><?= $view->domainTranslate("NewsletterRecipient._title") ?></a></li>
  <li><a data-toggle="tab" href="#tab-settings"><?= $view->domainTranslate("Settings") ?></a></li>
</ul>

<div id="tabs-Newsletter" class="tab-content">
  <div id="tab-main" class="tab-pane active">
		<iframe id='aa' name='aa' src='<?= $view->base_url() ?>/inline/NewsletterManager.html' border='0' frameborder='0' style='width:100%;height:500px'></iframe>
  </div>
  <div id="tab-recipients" class="tab-pane">
		<iframe id='bb' name='bb' src='<?= $view->base_url() ?>/inline/NewsletterGroupManager.html' border='0' frameborder='0' style='width:100%;height:500px'></iframe>
  </div>
  <div id="tab-settings" class="tab-pane">
		<iframe id='cc' name='cc' src='<?= $view->base_url() ?>/inline/NewsletterSettings.html' border='0' frameborder='0' style='width:100%;height:500px'></iframe>
  </div>
</div>

<script type='text/javascript'>
function tabheight() {
  var h=jq(window).height()-170;
  jq('#aa').css('height',h+'px');
  jq('#bb').css('height',h+'px');
  jq('#cc').css('height',h+'px');
}
jq(window).load(tabheight);
jq(window).resize(tabheight);
</script>

<?

//end