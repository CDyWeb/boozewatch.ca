<?php

require_once '../app/lib/data.inc';

if (isset($_GET['stores'])) {
  if (isset($_POST['stores']) && is_array($_POST['stores'])) {
    $this->output->setCookie('stores', implode(',', $_POST['stores']), time()+(60*60*24*365*5), '/');
  } else {
    $this->output->setCookie('stores', null, time()+(60*60*24*365*5), '/');
  }
  $this->output->redirect($this->uri());
  return;
}

$db=db_connect();

$store_ids=array();
if (isset($_COOKIE['stores'])) $store_ids = explode(',',$_COOKIE['stores']);

$store_placeholders = array();
foreach ($store_ids as $id) $store_placeholders[] = '?';

$arr = array();
if (!empty($store_ids)) {
  $arr = $db->prepare("
    select * from inventory 
      join product on inventory.product_id = product.id 
    where 
      store_id in (".implode(',', $store_placeholders).") 
      and added_on>?
    group by
      product_id
    order by
      primary_category, secondary_category
    ");
  $arr->execute(array_merge($store_ids, array(date('Y-m-d',strtotime('-3 DAY')))));
  $arr = $arr->fetchAll();
}

$ratebeer = array();
$ratewine = array();
foreach (getTableArray('select product_id, rating from ratebeer_beer') as $line) $ratebeer[$line['product_id']] = $line['rating'];
foreach (getTableArray('select * from ratewine_percentile') as $line) $ratewine[$line['product_id']] = $line['percentile'];

?>

<h1>New Booze</h1>
<div class="row">
<form action="?stores" method="post">
<p>
<select class="select2" style="width:100%" multiple="multiple" name="stores[]" onchange="$('#save-btn').show()">
<?php foreach(getTableArray('select * from store order by city, name') as $line) { ?>
<option <?= in_array($line['id'],$store_ids)?'selected="selected"':'' ?> value="<?= $line['id'] ?>"><?= $line['city'] ?>, <?= $line['name'] ?></option>
<?php } ?>
</select>
</p>
<p>
<button type="submit" class="btn btn-primary pull-right" id="save-btn" style="display:none">SAVE</button>
</p>
</form>
</div>

<hr>

<ul class="nav nav-tabs" role="tablist">
<?php
$tabs=array();
$tab='';
foreach ($arr as $i=>$line) {
  if ($tab!=$line['primary_category']) {
    $tab = $line['primary_category'];
    $tabs[] = $tab;
?>
    <li role="presentation" class="<?= $i==0?'active':'' ?>"><a href="#<?= getPermalinkName($tab) ?>" aria-controls="home" role="tab" data-toggle="tab"><?= $tab ?></a></li>
<?php
  }
}

echo "</ul>\n\n<div class=\"tab-content\">";

foreach ($tabs as $i=>$tab) {
  echo '<div role="tabpanel" class="tab-pane'.($i==0?' active':'').'" id="'.getPermalinkName($tab).'">'."\n";
  foreach ($arr as $line) if ($line['primary_category']==$tab) {
?>
<div class="col-sm-2" style="padding:10px;">
  <a style="height:270px;" href="/virtual-store/?id=<?= $line['id'] ?>" onclick="$('#myModal').modal(); $('#myModal .modal-body').html('loading...').load('/virtual-store/?id=<?= $line['id'] ?>'); return false" class="thumbnail">
    <div id="rating-<?= $line['id'] ?>" style="text-align:center;">
      <?= isset($ratebeer[$line['id']])?rate_to_stars($ratebeer[$line['id']]):'' ?>
      <?= isset($ratewine[$line['id']])?rate_to_stars($ratewine[$line['id']]):'' ?>
    </div>
    <img src="<?= $line['image_thumb_url']?$line['image_thumb_url']:'no-image-available.png' ?>" style="max-height:150px;">
    <?= $line['name'] ?><br>
    <?php if ($line['primary_category']=='Wine') { ?>
    <?= str_replace(', Region Not Specified','',$line['origin']) ?> / $<?= number_format($line['price_in_cents']/100,2) ?>
    <? } else { ?>
    $<?= number_format($line['price_in_cents']/100,2) ?> / <?= $line['alcohol_content']/100 ?>% abv
    <?php } ?>
  </a>
</div>
<?php
  }
  echo '</div>';
}
echo '</div>';

?>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$('.select2').select2();
</script>
