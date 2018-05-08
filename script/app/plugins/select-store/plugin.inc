<?php

$db=db_connect();

if (isset($_POST['stores']) && is_array($_POST['stores'])) {
  $this->output->setCookie('stores', implode(',', $_POST['stores']), time()+(60*60*24*365*5), '/');

  $customer=getSessionCustomer();
  if (isset($customer["id"]) && ($customer["id"]>0)) {
    $st = $db->prepare('update ccms_customer set `stores`=? where id=?');
    $st->execute(implode(',', $_POST['stores']), $customer['id']);
  }

  echo 'ok!';
  return;
}

$stores=array();
if (isset($_COOKIE['stores'])) $stores = explode(',',$_COOKIE['stores']);

echo $this->page->text;

?>
<h3>Select the LCBO stores you like to monitor</h3>
<form action="?" method="post" onsubmit="submitStores(this); return false;">
<p>
<select class="select2" style="width:100%" multiple="multiple" name="stores[]">
<?php foreach(getTableArray('select * from store order by city, name') as $line) { ?>
<option <?= in_array($line['id'],$stores)?'selected="selected"':'' ?> value="<?= $line['id'] ?>"><?= $line['city'] ?>, <?= $line['name'] ?></option>
<?php } ?>
</select>
</p>
<p>
<button type="submit" class="btn btn-primary pull-right" id="save-btn">SAVE</button>
</p>
</form>

<script>
$('.select2').select2();
function submitStores(form) {
  $('#save-btn').addClass('btn-default').removeClass('btn-primary');
  $.post('?', $(form).serialize(), function(response) {
    $('#save-btn').addClass('btn-primary').removeClass('btn-default');
  });
}
</script>
