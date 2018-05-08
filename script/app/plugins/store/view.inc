<?php

$line = $db->query('select * from product where id='.intval($_GET['id']))->fetch();
$customer=getSessionCustomer();

if (!empty($customer['id']) && isset($_GET['found'])) {
    $st = $db->prepare('update ratebeer_search set beer_id=? where q=?');
    $st->execute(array($_GET['found'], $line['name']));
    rate_beer($line, true);
    echo '<p>Thank you!</p>';
    return;
}

if (!empty($customer['id']) && isset($_POST['search'])) {
    $result = shell_exec('/home/boozewatch/.rbenv/shims/ruby /ratebeer/bin/search "'.$_POST['search'].'" 2>&1');
    $json = json_decode($result, true);
    echo '<option value=""> &raquo; '.count($json['beers']).' beers found &laquo;</option>';
    if (isset($json['beers'])) foreach ($json['beers'] as $json_beer) {
        echo '<option value="'.$json_beer['id'].'">'.$json_beer['name'].'</option>';
    }
    return;
}

if (!empty($customer['id']) && isset($_GET['find'])) {
?>
    <h2>Find this beer</h2>
    <div class="form-group">
        <label>Search for:</label>
        <div class="row">
            <div class="col-sm-10">
                <input class="form-control" type="text" id="input-search" value="<?= $line['name'] ?>">
            </div>
            <div class="col-sm-2">
                <a href="javascript:;" onclick="searchBeer()" class="btn btn-primary">Search</a>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label>Select the beer</label>
        <select id="select-beer" class="form-control" onchange="if (window.confirm('You sure this is the beer?')) $('#after-table').load('<?= $this->router->getPluginUrl('store') ?>?id=<?= $_GET['id'] ?>&found='+this.value)">
            <option></option>
            <?php
                $st = $db->prepare('select `result` from ratebeer_search where q=?');
                $st->execute(array($line['name']));
                $json = json_decode($st->fetchColumn(), true);
                if (isset($json['beers'])) foreach ($json['beers'] as $json_beer) {
                    echo '<option value="'.$json_beer['id'].'">'.$json_beer['name'].'</option>';
                }
            ?>
        </select>
    </div>
    <script>
        function searchBeer() {
            $('#select-beer').html('<option>searching...</option>');
            $.post('<?= $this->router->getPluginUrl('store') ?>?id=<?= $_GET['id'] ?>', { search: $('#input-search').val() }, function(r) {
                $('#select-beer').html(r);
            }, 'html');
        }
    </script>

<?
    return;
}

if (empty($store_ids)) {
	$inv = $db->prepare('select * from inventory join store on inventory.store_id=store.id where product_id='.intval($_GET['id']).' order by city, name');
} else {
	$inv = $db->prepare('select * from inventory join store on inventory.store_id=store.id where store_id in ('.implode(',',$store_placeholders).') and product_id='.intval($_GET['id']).' order by city, name');
}
$inv->execute($store_ids);
$inv = $inv->fetchAll();


$rating = false;
if ($line['primary_category']=='Beer') {
	$rating_line = getOneRow('select * from ratebeer_beer where product_id='.$line['id']);
	if ($rating_line) {
		$rating = $rating_line['rating'];
		$json_result = json_decode($rating_line['result'], true);
		$line['description'] = $json_result['description'];
	}
}
if ($line['primary_category']=='Wine') $rating = getOneValue('select percentile from ratewine_percentile where product_id='.$line['id']);

?>

    <div class="row">
        <div class="col-sm-3">
            <img src="<?= $line['image_url']?$line['image_url']:'no-image-available.png' ?>" class="thumbnail" style="max-width:100%">
        </div>
        <div class="col-sm-9">
            <table class="table">
                <tr><th>Name: </th><td><?= $line['name'] ?></td></tr>
                <tr><th>Price: </th><td>$<?= number_format($line['price_in_cents']/100,2) ?></td></tr>
                <tr><th>Category: </th><td><?= $line['primary_category'] ?> / <?= $line['secondary_category'] ?></td></tr>
                <tr><th>Varietal: </th><td><?= $line['varietal'] ?></td></tr>
                <tr><th>Style: </th><td><?= $line['style'] ?></td></tr>
                <tr><th>Origin: </th><td><?= $line['origin'] ?></td></tr>
                <tr><th>Package: </th><td><?= $line['package'] ?></td></tr>
                <tr><th>Producer: </th><td><?= $line['producer_name'] ?></td></tr>
<?php if ($rating) { ?>
                <tr><th>Consumer Rating: </th><td><?= rate_to_stars($rating) ?></td></tr>
<?php } else if ($line['primary_category']=='Beer') {
    if (!empty($customer['id'])) { ?>
                <tr><th>Consumer Rating: </th><td><a href="javascript:;" onclick="$('#after-table').load('<?= $this->router->getPluginUrl('store') ?>?id=<?= $_GET['id'] ?>&find')">Find this beer</a></td></tr>
<?php } } ?>
            </table>
            <div id="after-table"></div>
            <?php if ($line['description']) echo '<p>'.$line['description'].'</p>'; ?>
            <?php if ($line['serving_suggestion']) echo '<p><b>Serving suggestion: </b>'.$line['serving_suggestion'].'</p>'; ?>
            <?php if ($line['tasting_note']) echo '<p><b>Tasting notes: </b>'.$line['tasting_note'].'</p>'; ?>
            <h2>Inventory</h2>
            <table class="table table-striped">
                <tr><th>Store</th><th>Stock</th><th>Stock date</th></tr>
                <?php foreach($inv as $i) { ?>
                    <tr><td><?= $i['city'] ?>, <?= $i['name'] ?></td><td><?= $i['quantity'] ?></td><td><?= $i['added_on'] ?></td></tr>
                <?php } ?>
            </table>
        </div>
    </div>

<?php

