<?

class CoreProductEditor extends GenericEditor {

  protected function recursiveTreeOptions($a, $all, $tree_id, $prefix, &$result, $lookup_id, $lookup_caption) {
    if (isset($all[$tree_id])) foreach ($all[$tree_id] as $node) { //if ($node['active']) {
      $node_id=$node['id'];
      if (isset($a[$node_id])) $result[$a[$node_id]]=$prefix.$node['name'];
      $this->recursiveTreeOptions($a,$all,$node_id,$node['name'].' - ',$result, $lookup_id, $lookup_caption);
    }
  }
  
  public function getTreeOptions($field) {
    if (isset($this->tree_options)) return $this->tree_options;
    #--
    $fieldName = 'tree_id';
    $attributes = $field->getAttributes();
    $result = array();
    if ($attributes) {
      foreach (explode(",",$attributes) as $expr) if (preg_match("#^([^:]+):(.*)$#",$expr,$match)) {
        $match[1]="lookup_".$match[1];
        $$match[1]=$match[2];
      }
      $lookup_where=isset($lookup_where)?"where {$lookup_where}":''; // and `active`=1":"where `active`=1";
      if (isset($lookup_table) && isset($lookup_caption)) {
        if (empty($lookup_orderby)) $lookup_orderby='orderby';
        if (empty($lookup_id)) $lookup_id="id";
        $sql="select {$lookup_id},{$lookup_caption},parent_id from {$lookup_table} {$lookup_where} order by {$lookup_orderby}";
        #echo $sql;
        $a = array();
        foreach (getTableArray($sql,'id') as $id=>$line) $a[$id]=$line[$lookup_id];
        $all = array();
        foreach (getTableArray("select {$lookup_id},{$lookup_caption},`parent_id`,`class`,orderby,active from {$lookup_table} order by orderby",'id') as $id=>$line) $all[$line['parent_id']][$id]=$line;

        $roots=getTableArray("select id from {$lookup_table} where name='.Cat' and active=1");
        foreach ($roots as $root) {
          $this->recursiveTreeOptions($a,$all,$root['id'],'',$result, $lookup_id, $lookup_caption);
        }
        #foreach ($a as $line) if (!isset($a[$line['parent_id']])) {
        #  $this->recursiveTreeOptions($a,$all,$result,$line, $lookup_id, $lookup_caption);
        #}
      }
    }
    #var_dump($result);
    return ($this->tree_options=$result);
  }
  
  protected function getFkOptions(CCMSDomainField $field) {
    switch ($field->name) {
      case 'tree_id': {
        return $this->getTreeOptions($field);
      }
    }
    return parent::getFkOptions($field);
  }
  
  protected function editMulticat($field) {
    parent::editField($field);
    $values=array();
    if (isset($this->line['id'])) $values=getTableArray('select * from '.tbl_name('product_cat').' where product_id='.intval($this->line['id']),'tree_id');
    if (isset($this->line['tree_id'])) unset($values[$this->line['tree_id']]);
?>

<div class="form-group">
  <label class="control-label col-sm-2"><?= $this->domainTranslate($this->getModel()->getName(),'multi_cat') ?></label>
  <div class="controls input-group col-sm-10">
    <select multiple="mutiple" style="width:100%" name="_multi_cat_[]" id="product-multi-cat">
<? 
    foreach ($this->getTreeOptions($field) as $id=>$option) {
      #if (isset($this->line['tree_id']) && ($id==$this->line['tree_id'])) continue;
?>
    <option <?= isset($values[$id])?'selected="selected"':'' ?> value="<?= $id ?>"><?= $option ?></option>
<?
    }
?>
    </select>
    <script> $(document).ready(function() { $('#product-multi-cat').select2() }) </script>
  </div>
</div>

<?
  }
  
  protected function editField($field) {
    if (($field->name=='tree_id') && getConfigItem('products_multi_cat')) {
      $this->editMulticat($field);
      return;
    }
    parent::editField($field);
  }

  protected function editFieldByName($fieldName) {
		if ($fieldName=="_product_sizes_") {
      $grpManager=new SizeGroupManager();
      $sizeManager=new SizeManager();
      $productSizeManager=$this->getManager()->getProductSizeManager();
      $groups=$grpManager->getAll();
      $checked=$productSizeManager->byProduct($this->id);
      if (!empty($groups)) {
?>
<script>
function grpClick(checked,id) {
  if (checked) { 
    jq('#sizes_'+id).slideDown();
    jq('.cb_sizegroup_'+id).attr('checked','checked');
    var upl=jq('#sizes_'+id+' SPAN.file_upload');
    if (upl.length>0) jq.each(upl,function(i,e) {
      var jqe=jq(e);
      jqe.removeClass('file_upload').html('<input style="width:auto;" type="file" value="" name="'+e.id+'" />');
    });
  } else {
    jq('#sizes_'+id).slideUp();
    jq('.cb_sizegroup_'+id).attr('checked','');
  }
}
</script>
<p>&nbsp;</p>
<?

        foreach ($groups as $grp) {
          $_SESSION['Size.sizegroup']=$grp['id'];
          $sizes=$sizeManager->getAll();
          if (!empty($sizes)) {
            $grpChecked=false;
            foreach ($sizes as $size) if (isset($checked[$size['id']]) && $checked[$size['id']]['active']) $grpChecked=true;
?>
<div class="p">
  <label>
    <? if (!empty($this->group_lines)) { ?><input type="checkbox" class="checkbox" name="_grp__product_sizes_" value="1" onclick="if (this.checked) jq(document.getElementById('editForm:_product_sizes_<?= $grp['id'] ?>')).fadeIn(); else jq(document.getElementById('editForm:_product_sizes_<?= $grp['id'] ?>')).fadeOut()" />&nbsp;<? } ?>
    <?= $this->domainTranslate('SizeGroup') ?> <?= $grp['name'] ?>
  </label>
  <span id="editForm:_product_sizes_<?= $grp['id'] ?>" style="float:left;<?= empty($this->group_lines)?'':'display:none' ?>">
    <table><tr><td valign="top">
      <input style="margin-right:20px;" id="sizegroup_<?= $grp['id'] ?>" name="sizegroup_<?= $grp['id'] ?>" value="1" class="checkbox" type="checkbox" <?= $grpChecked?'checked="checked"':'' ?> onclick="grpClick(this.checked,<?= $grp['id'] ?>)" />
    </td><td>
      <table cellspacing='0' cellpadding='0' id="sizes_<?= $grp['id'] ?>" style="<?= $grpChecked?'':'display:none' ?>">
        <tr>
<? if (!getConfigItem('ProductSizeManager.autoActive',false)) { ?>
          <th><?= $this->domainTranslate('ProductSize.active') ?>&nbsp;&nbsp;</th>
<? } ?>
          <th><?= $this->domainTranslate('ProductSize.size') ?>&nbsp;&nbsp;</th>
          <? foreach ($productSizeManager->getEditFields() as $k) { ?><th><?= $this->domainTranslate('ProductSize.'.$k) ?>&nbsp;&nbsp;</th><? } ?>
        </tr>
<?
            foreach ($sizes as $size) {
              $groupsize=isset($checked[$size['id']])?$checked[$size['id']]:array('id'=>'s'.$size['id'],'active'=>false);
              if (getConfigItem('ProductSizeManager.alwaysStock',false)) $groupsize['stock']=isset($groupsize['stock'])?intval($groupsize['stock']):'0';
?>
        <tr>
<? if (!getConfigItem('ProductSizeManager.autoActive',false)) { ?>
          <td valign="top"><input name='product_size_active[]' value='<?= $size['id'] ?>' class='checkbox cb_sizegroup_<?= $grp['id'] ?>' type="checkbox" <?= $groupsize['active']?'checked="checked"':'' ?> id="size_<?= $size['id'] ?>" /></td>
<? } ?>
          <td valign="top"><label style="float:none;padding-right:10px" for="size_<?= $size['id'] ?>"><?= $size['name'] ?></label></td>
<? foreach ($productSizeManager->getEditFields() as $k) if ($k=='img') { ?>
          <td width="70" valign="top">
            <? if ($grpChecked) { ?>
            <input style="width:auto;" type="file" value="" name="product_size_<?= $k ?>_<?= $size['id'] ?>" />
            <? } else { ?>
            <span class="file_upload" id="product_size_<?= $k ?>_<?= $size['id'] ?>">file upload</span>
            <? } ?>
            <? if (!empty($groupsize[$k])) { ?>
            <a target="_blank" href="<?= $this->shared_url() ?>/cyane/thumb.php?maxwidth=800&maxheight=600&path=<?= base64_encode($this->getManager()->getImgDir('').$groupsize[$k]) ?>">
              <img src="<?= $this->shared_url() ?>/cyane/thumb.php?maxwidth=35&maxheight=35&path=<?= base64_encode($this->getManager()->getImgDir('').$groupsize[$k]) ?>" alt="" border="0" />
            </a>
            <? } ?>
          </td>
<? } else { ?>
          <td valign="top" width="70"><input value='<?= isset($groupsize[$k])?utf8_ent($groupsize[$k]):'' ?>' type='text' size='5' class='autowidth product_size_<?= $k ?>' name='product_size_<?= $k ?>_<?= $size['id'] ?>' id='product_size_<?= $k ?>_<?= $size['id'] ?>' data-size-field="<?= $k ?>" data-size-id="<?= $size['id'] ?>" /></td>
<? } ?>
        </tr>
<?
            }
?>
      </table>
     </td></tr></table>
  </span>
</p>
<?
          }
        }
?>
<p>&nbsp;</p>
<?
      }
		}
  }
}

//end