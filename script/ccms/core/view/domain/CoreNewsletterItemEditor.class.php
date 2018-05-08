<?

class CoreNewsletterItemEditor extends GenericEditor {

	public $item_index=0;

	protected function getInputName($field,$prefix='input') {
		$result=array();
		$result[]=$prefix;
		$result[]=$field->name;
		$result[]=$this->item_index;
		return implode('_',$result);
	}
	
	protected function getInputID($field) {
		$result=array();
		$result[]='editForm';
		$result[]=$field->name;
		$result[]=$this->item_index;
		return implode('_',$result);
	}

	public function editField($field) {
		return parent::editField($field);
	}
	
	public function typeEditor($field) {
?><div><?
		$this->editEnum($field);
?></div><script type="text/javascript">
	function input_type_<?= $this->item_index ?>_changed(inp) {
		jq('#fkEditor_<?= $this->item_index ?> DIV').fadeOut();
		jq('#fkEditor_<?= $this->item_index ?> DIV.select_'+inp.value).fadeIn();
	}
</script><?
	}

	public function fkEditor($field) {
		$value_news=$this->line['type']=='news'?$this->line['fk']:null;
		$value_product=$this->line['type']=='product'?$this->line['fk']:null;
?>
	<div id="fkEditor_<?= $this->item_index ?>">
		<div class="select_news" style="<?= $this->line['type']=='news'?'':'display:none' ?>">
			<p>
				<label for="<?= $this->getInputID($field) ?>"><?= $this->domainTranslate($this->getModel()->getName(),'type.news').($field->required?" *":"") ?></label>
				<select class="" id="<?= $this->getInputID($field) ?>_news" name="<?= $this->getInputName($field) ?>_news">
					<option value=""></option>
					<? foreach (getTableArray('select * from '.tbl_name('pagenews').' order by pubdate desc','id') as $id=>$line) { ?><option <?= /**$id==$value_news?'selected="selected"':**/ '' ?> value="<?= $id ?>"><?= utf8_ent($line['title']) ?></option><? } ?>
				</select>
			</p>
		</div>
		<div class="select_product" style="<?= $this->line['type']=='product'?'':'display:none' ?>">
			<p>
				<label><?= $this->domainTranslate($this->getModel()->getName(),'type.product').($field->required?" *":"") ?></label>
				<input id="product_<?= $this->item_index?>_id" name="<?= $this->getInputName($field) ?>_product" type="hidden" />
				<input id="product_<?= $this->item_index?>_name" type="text" disabled="disabled" style="width:30em" />
				<input type="button" value="···" style="width:4em;padding:2px" onclick="select_prod__<?= $this->item_index ?>()" />
			</p>
		</div>
	</div><script type="text/javascript">
	function select_prod__<?= $this->item_index ?>() {
		window.selectprod=function(id,name) {
			jq('#product_<?= $this->item_index?>_id')[0].value=id;
			jq('#product_<?= $this->item_index?>_name')[0].value=name;
		}
		window.open('<?= getConfigItem('base_url') ?>/inline/selectprod.html?<?= isset($_SESSION['Product.tree_id'])?intval($_SESSION['Product.tree_id']):0 ?>','','width=500,height=300');
	}
	function input_fk_<?= $this->item_index ?>_changed(inp) {
		
	}
</script><?
	}
	
	protected function customEdit($field) {
		switch ($field->name) {
			case 'type':
				$this->typeEditor($field);
				return true;
			case 'fk':
				$this->fkEditor($field);
				return true;
		}
		return false;
	}

}

//end