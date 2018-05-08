<?

class SettingsPage {

  function __construct() {
    $this->model=new CCMSManagedModel("SettingsManager");
    $this->controller=CCMSController::getInstance();
    $this->view=$this->controller->getView();
  }

  function invoke() {
    if ($_SERVER['REQUEST_METHOD']=='POST') $this->post();
    $this->output();
  }

  function post() {
    if (isset($_POST['main'])) {
      $this->something_changed=false;
      if (!empty($_POST['is_online'])) $this->something_changed |= SettingsManager::set('is_online',$_POST['is_online']);
      if (!empty($_POST['home_page'])) $this->something_changed |= SettingsManager::set('home_page',$_POST['home_page']);
      if ($this->something_changed) $this->ok['main']=$this->view->cmsTranslate('Changes_saved');
    }
    if (isset($_POST['google'])) {
      $this->something_changed=false;
      if (preg_match('#.*content="(.*)"#Usi',$_POST['googleVerify'],$match)) $_POST['googleVerify']=$match[1];
      if (!empty($_POST['googleVerify'])) $this->something_changed |= SettingsManager::set('googleVerify',$_POST['googleVerify']);
      if (!empty($_POST['googleAnalytics'])) $this->something_changed |= SettingsManager::set('googleAnalytics',$_POST['googleAnalytics']);
      if ($this->something_changed) $this->ok['google']=$this->view->cmsTranslate('Changes_saved');
    }
    if (isset($_POST['webshop'])) {
      $this->something_changed=false;
      if (!empty($_POST['shipping_free'])) $this->something_changed |= SettingsManager::set('webshop.shipping_free',floatval($_POST['shipping_free']));
      if ($this->something_changed) $this->ok['webshop']=$this->view->cmsTranslate('Changes_saved');
    }
    if (isset($_POST['company'])) {
      $this->something_changed=false;
      foreach ($_POST as $k=>$v) if (preg_match('#^company_#',$k)) {
        $this->something_changed |= SettingsManager::set($k,$v);
      }
      if ($this->something_changed) $this->ok['company']=$this->view->cmsTranslate('Changes_saved');
    }
    if (isset($_POST['social'])) {
      $this->something_changed=false;
      foreach ($_POST as $k=>$v) if (preg_match('#^social_#',$k)) {
        $this->something_changed |= SettingsManager::set(str_replace('_','.',$k),$v);
      }
      if ($this->something_changed) $this->ok['social']=$this->view->cmsTranslate('Changes_saved');
    }
  }
  
  function getTabs() {
    $res=array(
      'main'=>$this->view->cmsTranslate("page.settings.main"),
      'company'=>$this->view->cmsTranslate("page.settings.company"),
      'google'=>'Google',
      'social'=>'Social Media',
    );
    if (getConfigItem('plugin.enable.shop',false)) $res['webshop']='E-Commerce';
    return $res;
  }
  
  function tab_main() {
  
    $is_online=SettingsManager::setting('is_online','yes');
    $home_page=SettingsManager::setting('home_page');

?>
	<form class="form-horizontal" method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
	
		<p>
      <label for="home_page"><?= $this->view->cmsTranslate("page.settings.main.is_online") ?></label>
      <select id="is_online" name="is_online">
        <option <?= $is_online=='no'?'':'selected="selected"' ?> value="yes">Yes</option>
        <option <?= $is_online=='no'?'selected="selected"':'' ?> value="no">No</option>
      </select>
    </p>
    
    <p><label for="home_page"><?= $this->view->cmsTranslate("page.settings.main.home_page") ?></label><select id="home_page" name="home_page"><option value=""></option><?
		
			$route_tree_ids=SettingsManager::setting('route_tree_ids');
		
			$opt=array();
			foreach (getTableArray('select p.id, p.name, p.parent_id from ccms_tree t, ccms_page p where t.id=p.tree_id and t.id in ('.$route_tree_ids.') order by t.orderby, p.orderby') as $line) {
				$pid=empty($line['parent_id'])?0:$line['parent_id'];
				$opt[$pid][]=$line;
			}
			foreach ($opt[0] as $line) { ?>
<option <?= $home_page==$line['id']?'selected="selected"':'' ?> value="<?= $line['id'] ?>"><?= $line['name'] ?></option>
			<? if (isset($opt[$line['id']])) foreach ($opt[$line['id']] as $line) { ?>
<option <?= $home_page==$line['id']?'selected="selected"':'' ?> value="<?= $line['id'] ?>"> - <?= $line['name'] ?></option>
			<? } ?><? } ?>
	
		</select></p>
		
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input class="btn btn-primary" type="submit" name="main" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>

	</fieldset></form>
<?
  }
  
  function tab_google() {
?>
	<form class="form-horizontal" method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
		<p>
			<label for="googleVerify"><?= $this->view->cmsTranslate("page.settings.google.googleVerify") ?></label>
			<input type="text" id="googleVerify" name="googleVerify" value="<?= htmlentities(SettingsManager::setting('googleVerify')) ?>" />
		</p>
		<p>
			<label for="googleAnalytics"><?= $this->view->cmsTranslate("page.settings.google.googleAnalytics") ?></label>
			<input type="text" id="googleAnalytics" name="googleAnalytics" value="<?= htmlentities(SettingsManager::setting('googleAnalytics')) ?>" />
		</p>
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input class="btn btn-primary" type="submit" name="google" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>
<?
  }
  
  function tab_webshop() {
?>
	<form class="form-horizontal" method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
		<p>
			<label for="shipping_free"><?= $this->view->cmsTranslate("page.settings.webshop.shipping_free") ?></label>
			<input type="text" id="shipping_free" name="shipping_free" value="<?= htmlentities(SettingsManager::setting('webshop.shipping_free')) ?>" />
		</p>
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input class="btn btn-primary" type="submit" name="webshop" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>
<?
  }
  
  function tab_social() {
?>
	<form class="form-horizontal" method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
		<p>
			<label for="social1">Twitter Name</label>
			<input type="text" id="social1" name="social_twitter_name" value="<?= htmlentities(SettingsManager::setting('social.twitter.name')) ?>" />
		</p>
		<p>
			<label for="social2">Facebook link</label>
			<input type="text" id="social2" name="social_facebook_link" value="<?= htmlentities(SettingsManager::setting('social.facebook.link')) ?>" />
		</p>
		<p>
			<label for="social3">Linkedin link</label>
			<input type="text" id="social3" name="social_linkedin_link" value="<?= htmlentities(SettingsManager::setting('social.linkedin.link')) ?>" />
		</p>
		<p>
			<label for="social4">Youtube link</label>
			<input type="text" id="social4" name="social_youtube_link" value="<?= htmlentities(SettingsManager::setting('social.youtube.link')) ?>" />
		</p>
		<p>&nbsp;</p>
		
		<p>
			<label>&nbsp;</label><input class="btn btn-primary" type="submit" name="social" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>
<?
  }
  
  
  function tab_company() {
?>
	<form class="form-horizontal" method="POST" action="<?= $_SERVER['_URI'] ?>"><fieldset class="editFieldSet">
<? foreach (array('name','address1','address2','city','state','zip','country','tel1','tel2','tel3','email','website','bank','iban','bic','tax','reg') as $k) { ?>
		<p>
			<label for="company_<?= $k ?>"><?= $this->view->cmsTranslate('page.settings.company.'.$k) ?></label>
			<input type="text" id="company_<?= $k ?>" name="company_<?= $k ?>" value="<?= htmlentities(SettingsManager::setting('company_'.$k)) ?>" />
		</p>
<? } ?>
		<p>&nbsp;</p>

		<p>
			<label>&nbsp;</label><input class="btn btn-primary" type="submit" name="company" value="<?= $this->view->cmsTranslate("Save") ?>" />
		</p>
	</fieldset></form>

<?
  }

  function output() {
  
    $this->view->addToPagePath("<a href='{$_SERVER["_URI"]}'>".$this->view->domainTranslate("Settings")."</a>");
    
    $tabs=$this->getTabs();
    
?>
<style type="text/css">
.editFieldSet P LABEL {
	float:left;
	width:15em;
	padding-left:5px;
}
</style>
<ul class="nav nav-tabs">
<?
    $first_tab=null;
    
    foreach ($tabs as $n=>$c) { 
      if (empty($first_tab)) $first_tab=$n;
?><li class="<?= $n==$first_tab?'active':'' ?>"><a data-toggle="tab" href="#tab-<?= $n ?>"><?= $c ?></a></li><? 
    } ?>
</ul>
<div id="tabs-page" class="tab-content">
  <?
    
    foreach ($tabs as $n=>$c) { 
      $m='tab_'.$n;
?>
  <div id="tab-<?= $n ?>" class="<?= $n==$first_tab?'tab-pane active':'tab-pane' ?>" >
    <? if (!empty($this->err[$n])) echo sprintf('<p style="color:red">%s</p>',implode("\n",$this->err[$n])) ?>
    <? if (!empty($this->ok[$n])) echo sprintf('<p style="color:green">%s</p>',$this->ok[$n]) ?>
    <? $this->$m() ?>
  </div>
  <? } ?>
</div>
<?
  }
}

//end