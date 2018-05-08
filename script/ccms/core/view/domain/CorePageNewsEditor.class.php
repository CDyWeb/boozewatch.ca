<?

class CorePageNewsEditor extends GenericEditor {

	public function fkEditor($field) {
		$product_id=$this->line['product_id'];
?>
  <div class="p">
    <label>Product</label>
    <input id="product_id" name="input_product_id" type="hidden" value="<?= $product_id ?>" />
    <table><tr><td id="product_name">
        <? if ($product_id) echo getOneValue('select name from ccms_product where id='.$product_id) ?>
      </td><td>
        <input type="button" value="···" style="width:4em;padding:2px" onclick="select_prod()" />
      </td>
    </tr></table>
  </div>
	<script type="text/javascript">
	function select_prod() {
		window.selectprod=function(id,name) {
			jq('#product_name').html(name);
      jq('#product_id').val(id);
		}
		window.open('<?= getConfigItem('base_url') ?>/inline/selectprod.html?<?= isset($_SESSION['Product.tree_id'])?intval($_SESSION['Product.tree_id']):0 ?>','','width=500,height=300');
	}
</script><?
	}
	
	protected function customEdit($field) {
		switch ($field->name) {
			case 'product_id':
				$this->fkEditor($field);
				return true;
		}
		return false;
	}

}

//end