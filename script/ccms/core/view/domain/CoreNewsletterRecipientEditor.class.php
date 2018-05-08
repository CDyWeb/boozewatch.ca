<?

class CoreNewsletterRecipientEditor extends GenericEditor {

	protected function getFkOptions(CCMSDomainField $field) {
		$res=parent::getFkOptions($field);
		if ($field->name=='newsletter_group') {
			foreach ($res as $k=>$v) if ($v=='.Customers') $res[$k]=$this->_('NewsletterGroup'.$v);
		}
		return $res;
	}
	
	public function outputContent() {
		if (!$this->id) {
			$this->invite();
		} else {
			parent::outputContent();
		}
	}
	
	protected function outputField($fieldName) {
		if ($fieldName=='_invite_txt') {
			$f=new CCMSDomainField('_invite_txt',CCMSDomainField::FIELDTYPE_TEXT,0,'',true);
			$this->line['_invite_txt']=SettingsManager::setting('newsletter.email.invite',str_replace('\n','<br />',$this->_('newsletter.email.invite',array('domain'=>getConfigItem('domain')))));
			$this->editText($f);
			return;
		}
		parent::outputField($fieldName);
	}
	
	protected function customEdit($field) {
		switch ($field->getName()) {
			case 'language' :
				$arr=array();
				$lc=getConfigItem('language',array('default'=>'en','available'=>array('en')));
				foreach($lc['available'] as $l) $arr[$l]=$this->_('l.'.$l);
				$field->attributes=$arr;
				$field->required=true;
				if ($this->id==0) $this->line['language']=$lc['default'];
				$this->editEnum($field);
				return true;
		}
		return false;
	}
	
	protected function getFields() {
		$res=parent::getFields();
		if ($this->id==0) $res[]='_invite_txt';
		return $res;
	}
	
	public function invite() {
?>

<ul>
  <li class="active"><a data-toggle="tab" href="#tab1"><?= $this->_('NewsletterRecipient.add_one') ?></a></li>
  <li><a data-toggle="tab" href="#tab2"><?= $this->_('NewsletterRecipient.add_many') ?></a></li>
  <li><a data-toggle="tab" href="#tab3"><?= $this->_('NewsletterRecipient.add_csv') ?></a></li>
</ul>
<div id="tabs-invite" class="tab-content">
	<div class="tab-pane active" id="tab1">
<? parent::outputContent() ?>
	</div>
	<div class="tab-pane" id="tab2">
<?
	$this->formName='editForm2';
?>
		<form id="editForm2" name="editForm2" action="/ccms/inline/NewsletterRecipientManager.html" method="post" enctype="multipart/form-data"><fieldset class="grayborder editFieldSet">
			<input type="hidden" value="0" name="__save">
<? $this->outputField('language') ?>
<? $this->outputField('newsletter_group') ?>
<p>
      <label for="editForm:many"><?= $this->_('NewsletterRecipient.add_many.label') ?> *</label>
	  <textarea id="editForm:many" name="many" style="width:35em;height:10em;"></textarea>
</p>
<? $this->outputField('_invite_txt') ?>
<? $this->buttonPanel(); ?>
		</fieldset></form>
	</div>
	<div class="tab-pane" id="tab3">
<?
  $this->formName='editForm3';
?>
		<form id="editForm3" name="editForm3" action="/ccms/inline/NewsletterRecipientManager.html" method="post" enctype="multipart/form-data"><fieldset class="grayborder editFieldSet">
			<input type="hidden" value="0" name="__save">
<? $this->outputField('language') ?>
<? $this->outputField('newsletter_group') ?>
<? /**/ ?>
<p>
    <label for="editForm:csv"><?= $this->_('NewsletterRecipient.add_csv.label') ?> *</label>
	  <input type="file" id="editForm:csv" name="csv" />
</p>
<? $this->outputField('_invite_txt') ?>
<? $this->buttonPanel(); ?>
<? /**/ ?>
		</fieldset></form>
	</div>
</div>
<?
		$this->formName='editForm';
	}
  
  public function getOkButtonCaption() {
    if ($this->id) return parent::getOkButtonCaption();
    return $this->_('Invite');
  }
  
  public function outputPagepathDiv() {
    $m=$this->getManager();
    $n=$m->getGroupName();
    if (empty($n)) $n=$this->_('NewsletterGroup.null');
    echo '
<div id="pagepath">
  <a target="_parent" href="'.$this->base_url().'/body.html">CCMS</a>
  &raquo;
  <a target="_parent" href="'.$this->base_url().'/page/newsletter.html">'.$this->domainTranslate('Tree.Newsletter').'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterGroupManager.html">'.$this->domainTranslate('NewsletterGroup._title').'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterRecipientManager.html">'.$this->domainTranslate('NewsletterRecipient._title').' : '.($n).'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterRecipientManager.html?edit='.$this->id.'">'.($this->id?$this->_('Edit'):$this->_('Add')).' '.$this->domainTranslate('NewsletterRecipient').'</a>
</div>
';
  }
  
}

//end