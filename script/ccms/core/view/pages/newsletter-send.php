<?

class NewsletterSend {

	protected $newsletter=null;

	public function __construct($newsletter) {
		_log(get_class().".__construct, newsletter=".$newsletter['id']);
		$this->newsletter=$newsletter;
	}
	
	protected function getGroups() {
		_log(get_class().".getGroups");
		$sql="
select count(*) as c, g.id, g.name, g.orderby, s.language
from ".tbl_name('newsletter_subscribe')." s left join ".tbl_name('newsletter_group')." g on s.newsletter_group=g.id
where s.`status`='confirmed'
group by s.newsletter_group, s.language
order by orderby
";

		return getTableArray($sql);
	}
  
  protected function getGroupName($line) {
    if ($line['name']=='.Customers') return $this->view->_('NewsletterGroup'.$line['name']).' - '.$line['language'];
    return $line['name'].' - '.$line['language'];
  }

	protected function recipients($view) {
		_log(get_class().".recipients");
		foreach ($this->getGroups() as $line) {
?>
		<p>
			<input value='<?= $_SESSION['Newsletter.sendKey'] ?>' type='checkbox' name='newsletter_group_<?= $line['id'] ?>_<?= $line['language'] ?>' id='newsletter_group_<?= $line['id'] ?>_<?= $line['language'] ?>' />
			<label for='newsletter_group_<?= $line['id'] ?>_<?= $line['language'] ?>'>
<?
  if (empty($line['name'])) echo $view->_('NewsletterGroup.null').' '.$line['language'];
  else echo $this->getGroupName($line);
?>
&nbsp;(<?= intval($line['c']) ?>)
			</label>
		</p>
<?
		}
	}
	
	protected function recipientsFromPost() {
		_log(get_class().".recipientsFromPost");
		$res=array();
		#--
		if (isset($_POST['to_manual'])) {
			foreach (explode("\n",str_replace(" ","\n",$_POST['to_manual'])) as $t) if (isValidEmail($t=trim($t))) {
				$res[$t]=array('id'=>null,'email'=>$t);
			}
		}
		#--
		foreach ($this->getGroups() as $grp) if (isset($_POST['newsletter_group_'.$grp['id'].'_'.$grp['language']]) && ($_POST['newsletter_group_'.$grp['id'].'_'.$grp['language']]==$_SESSION['Newsletter.sendKey'])) {
			foreach (getTableArray('select id,name,email,hash from '.tbl_name('newsletter_subscribe').' where `language`=\''.$grp['language'].'\' and `status`=\'confirmed\' and newsletter_group'.($grp['id']?'='.$grp['id']:' is null')) as $line) if ($email=formatNiceEmail($line['email'],$line['name'])) {
				$line['name']=trim($line['name']); if (empty($line['name'])) $email=$line['email'];
        $res[$line['email']]=array('id'=>$line['id'],'email'=>$email,'hash'=>$line['hash']);
			}
		}
		#--
		return $res;
	}
	
	protected function getBusy($view,$size) {
    #--
    executeSql('update '.tbl_name('newsletter').' set dt_sent=now() where id='.intval($this->newsletter['id']));
    #--
		_log(get_class().".getBusy");
?>

<fieldset style="width:700px;border:1px solid #CCC;padding:5px 5px 50px;">
	<h1><?= $view->_("page.newsletter-send.sending",$this->newsletter['name']) ?></h1>
	<p style='text-align:center' id='animateBusy'>
		<img src="<?= getConfigItem('resources_url') ?>/img/busy.gif" alt="" /><br /><br />
		<span id="myProgress">0 / <?= $size ?></span><br /><br />
    <a href="<?= $view->base_url() ?>/inline/NewsletterManager.html"><?= $view->_('Cancel') ?></a>
	</p>
	<p style='text-align:center;display:none;' id='jobIsDone'>
		<?= $view->_("Sending newsletter done",array('size'=>$size)); ?>
    <br /><br />
    <a href="<?= $view->base_url() ?>/inline/NewsletterManager.html"><?= $view->_('back') ?></a>
	</p>
</fieldset>

<script type="text/javascript">
	var dummy=<?= time() ?>;
	function dispatchMe() {
		jq.post("<?= $_SERVER['_URI'] ?>?send=<?= $this->newsletter['id'] ?>&dispatch&"+(dummy++),{},function(data) {
			if (data.done) jobDone();
			else if (data.todo) { updateProgress(data.todo); window.setTimeout('dispatchMe()',500); }
			else if (data.error) alert(data.error);
			else alert('Unexpected error');
		},'json');
	}
	function jobDone() {
		document.getElementById('animateBusy').style.display='none';
		document.getElementById('jobIsDone').style.display='';
	}
	function updateProgress(todo) {
		document.getElementById('myProgress').innerHTML=""+(<?= $size ?>-todo)+" / <?= $size ?>";
	}
	jq(document).ready(window.setTimeout('dispatchMe()',1500));
</script>

<?
	}
	
	protected function createDispatcher($view) {		
		_log(get_class().".createDispatcher");
		require_once(SHARED_PATH.'/cyane/valid_email.inc.php');
		
		$recipients=$this->recipientsFromPost();
		if (count($recipients)<1) throw new Exception($view->_('No recipients selected'));
		
		global $newsletterDispatcherClass;
		$newsletterDispatcherClass="NewsletterDispatcher";
		_require ("inc/NewsletterDispatcher.class.php");
    $from=null;
    if (!empty($_POST['from'])) $from=$_POST['from'];
		$newsletterDispatcherClass::init($this->newsletter,$recipients,$from);
		return count($recipients);
	}

	public function invoke($view) {
		_log(get_class().".invoke");
    $this->view=$view;

    require_once(SHARED_PATH.'/cyane/valid_email.inc.php');

		$view->addToPagePath("<a href='".getConfigItem('base_url')."/page/{$_SESSION['tree_id']}/newsletter.html' target='_parent'>".$view->domainTranslate("Newsletter._title")."</a>");
		$view->addToPagePath("<a href='{$_SERVER["REQUEST_URI"]}'>".$view->domainTranslate("Newsletter._send")."</a>");

		if (count($_POST)>0) {
			try {
				if (empty($_SESSION['Newsletter.sendKey']) || empty($_POST['sendKey']) || ($_SESSION['Newsletter.sendKey']!==$_POST['sendKey'])) {
					throw new Exception($view->_('Resend is not possible'));
				}
				$size=$this->createDispatcher($view);
				unset($_SESSION['Newsletter.sendKey']);
				$this->getBusy($view,$size);
			} catch (Exception $ex) {
				echo "<p style='color:red'>{$ex->getMessage()}</p><p><a href='javascript:window.history.back()'>&lt;&lt;&lt; {$view->_('back')}</a></p>";
			}
			return;
		}

/*    echo '
<div id="pagepath">
  <a target="_parent" href="'.$view->base_url().'/body.html">CCMS</a>
  &raquo;
  <a target="_parent" href="'.$view->base_url().'/page/newsletter.html">'.$view->domainTranslate('Tree.Newsletter').'</a>
  &raquo;
  <a href="'.$view->base_url().'/inline/NewsletterManager.html">'.$view->domainTranslate('Newsletter._title').'</a>
  &raquo;
  <a href="'.$view->base_url().'/inline/newsletter.html?send='.$this->newsletter['id'].'">'.$view->domainTranslate("Newsletter._send").'</a>
</div>
';*/

?>

<script type="text/javascript" src="<?= getConfigItem('url_base') ?>/shared/jquery/fancybox/jquery.fancybox-1.3.1.js"></script>
<style type="text/css">
@import url('<?= getConfigItem('url_base') ?>/shared/jquery/fancybox/jquery.fancybox-1.3.1.css');
</style>
<script type="text/javascript" charset="utf-8">
jq(document).ready(function(){
  jq("a.lightbox").fancybox({width:900,height:600});
});
</script>

<form action="<?= $_SERVER["REQUEST_URI"] ?>" method="post" name="newsletterForm">
<input type="hidden" name="sendKey" value="<? echo $_SESSION['Newsletter.sendKey']=sha1(rand().uniqid().time()) ?>" />
<fieldset style="width:100%;border:1px solid #CCC;padding:5px 5px 50px">

	<span style='float:right;padding:10px'>
		<a class="btn btn-primary" href="<?= getConfigItem('base_url') ?>/inline/NewsletterManager.html?edit=<?= $this->newsletter['id'] ?>"><?= $view->_("page.newsletter-send.edit") ?></a>
    &nbsp;
		<a class="btn btn-warning iframe lightbox" href="<?= getConfigItem('url_base').$this->newsletter['id'].'/newsletter-preview.html' ?>"><?= $view->_("page.newsletter-send.preview") ?></a>
	</span>

	<h3><a href="<?= $view->base_url().'/inline/newsletter.html?send='.$this->newsletter['id'] ?>"><?= $view->_("page.newsletter-send.title",'<br><span style="font-size:16px">'.$this->newsletter['name']) ?></span></a></h3>

	<!--<p><?= $view->_("page.newsletter-send.to_head") ?></p>-->
	
	<div style="margin:10px;">
<?
		$this->recipients($view);
?>
		<p>&nbsp;</p>
    <div class="form-group">
      <label for="from"><?= $view->_("page.newsletter-send.from") ?>:</label><br />
      <input class="form-control" style="width:300px;" type="text" name="from" id="from" value="<?= utf8_ent(formatNiceEmail($_SESSION["user"]["email"],trim($_SESSION['user']['first_name'].' '.$_SESSION['user']['last_name']),false)) ?>" />
    </div>
    <p>&nbsp;</p>
		<div class="form-group">
			<?= $view->_("page.newsletter-send.to_manual") ?><br />
			<textarea class="form-control" name="to_manual" cols="40" rows="6"></textarea>
		<div>
	</div>
	
	<p>&nbsp;</p>
	<p style="text-align:center" id='btnpanel'>
		<button class="btn btn-primary" type="submit"><?= $view->domainTranslate("Newsletter","_send"); ?></button>
    &nbsp;
    <a href="<?= $view->base_url().'/inline/NewsletterManager.html' ?>">Cancel</a>
	</p>
	
</fieldset>
</form>
<?
	}
}