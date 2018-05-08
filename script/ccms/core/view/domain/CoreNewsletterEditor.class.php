<?

class CoreNewsletterEditor extends GenericEditor {

	protected function getItemEditor($itemModel) {
		return new CoreNewsletterItemEditor($itemModel);
	}

	protected function editFieldByName($fieldName) {
		switch ($fieldName) {
			case '__ITEMS' :
			
				$this->inItems=true;

				$itemModel=new CCMSManagedModel('NewsletterItemManager');
				$itemManager=$itemModel->getDomainManager();
				
				$typeField=$itemManager->getField('type');
				$typeField->type=CCMSDomainField::FIELDTYPE_ENUM;
				$typeField->attributes=getConfigItem('NewsletterItem.type.attributes','text');
				$typeField->defaultValue=preg_replace('#,.*#','',$typeField->attributes);

				$_SESSION['NewsletterItem.newsletter']=$this->id;
				$items=$itemManager->getAll();
				while(count($items)<SettingsManager::setting('Newsletter.itemcount',getConfigItem('Newsletter.itemcount','5'))) $items[]=$itemManager->create(); 

?>


<ul class="nav nav-tabs">
  <li class="active"><a href="#text_top" data-toggle="tab"><?= $this->domainTranslate("Newsletter.text_top") ?></a></li>
<? foreach($items as $i=>$item) { ?>
  <li><a href="#item<?= $i ?>" data-toggle="tab"><?= $this->domainTranslate("Newsletter._item_",null,$i+1) ?></a></li>
<? } ?>
  <li><a href="#text_bottom" data-toggle="tab"><?= $this->domainTranslate("Newsletter.text_bottom") ?></a></li>
</ul>

<div id="tabs-newsletter" class="tab-content">
	<div class="tab-pane active" id="text_top">
<?
	$this->editField($this->getManager()->getField('text_top'));
?>
	</div>

<?

				$e=$this->getItemEditor($itemModel);
				foreach($items as $i=>$item) {
					if (empty($item['type'])) $item['type']=$typeField->defaultValue;

					$e->item_index=$i;
					$e->setLine($item);
          
          $item_id=isset($item['id'])?$item['id']:'0';
          if ($this->duplicating) $item_id='0';
?>
	<div class="tab-pane" id="item<?= $i ?>"><input type="hidden" name="_item_<?= $i ?>" value="<?= $item_id ?>" />
<?
					$e->editField($typeField);
					$e->editField($itemManager->getField('fk'));
					$e->editField($itemManager->getField('title'));
					$e->editField($itemManager->getField('image'));
					$e->editField($itemManager->getField('caption'));
					$e->editField($itemManager->getField('text'));
?>
	</div>
<?
				}
?>
	<div class="tab-pane" id="text_bottom">
<?
	$this->editField($this->getManager()->getField('text_bottom'));
?>
	</div>
</div>
<?
				unset($this->inItems);
				break;
			default :
				throw new Exception('not implemented: edit '.$name);
		}
	}
	
	protected function customEdit($field) {
		switch ($field->getName()) {
			case 'text_top' :
			case 'text_bottom' :
				return !isset($this->inItems);
			case 'language' :
				$arr=array();
				$lc=getConfigItem('language',array('default'=>'en','available'=>array('en')));
				foreach($lc['available'] as $l) $arr[$l]=$this->_('l.'.$l);
				$field->attributes=$arr;
				if ($this->id==0) $this->line['language']=$lc['default'];
				$this->editEnum($field);
				return true;
			case 'template' :
				//@Todo
				return true;
		}
		return false;
	}
	
	public function outputStart() {
		parent::outputStart();
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
<a id="preview-link" href="<?= getConfigItem('url_base').$this->id.'/newsletter-preview.html' ?>" class="iframe lightbox" style="display:none"></a>

	<span style='float:right;padding:10px'>
		<a class="btn btn-primary" href="javascript:;" onclick="document.editForm.__redirect.value='<?= $this->base_url()."/inline/newsletter.html?send=" ?>__id'; document.editForm.submit()"><?= $this->_("page.newsletter-send.send") ?></a>
		&nbsp;
		<a class="btn btn-warning" href="javascript:;" onclick="document.editForm.__redirect.value='__editor&preview'; document.editForm.submit()"><?= $this->_("page.newsletter-send.preview") ?></a>
	</span>
<?
		if (isset($_GET['preview'])) {
?><script type="text/javascript">jq(document).ready(function() { jq('#preview-link').click(); });</script><?
		}
	}

/*  public function outputPagepathDiv() {
    global $config;
    echo '
<div id="pagepath">
  <a target="_parent" href="'.$this->base_url().'/body.html">CCMS</a>
  &raquo;
  <a target="_parent" href="'.$this->base_url().'/page/newsletter.html">'.$this->domainTranslate('Tree.Newsletter').'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterManager.html">'.$this->domainTranslate('Newsletter._title').'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterManager.html?edit='.$this->id.'">'.($this->id?$this->_('Edit'):$this->_('Add')).'</a>
</div>
';
  } */
}

//end