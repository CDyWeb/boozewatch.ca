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

$store_ids=array();
if (isset($_COOKIE['stores'])) $store_ids = explode(',',$_COOKIE['stores']);

$store_placeholders = array();
foreach ($store_ids as $id) $store_placeholders[] = '?';

$primary_category='';
if (isset($_GET['c1'])) $primary_category=trim(strip_tags($_GET['c1']));

$secondary_category='';
if (isset($_GET['c2'])) $secondary_category=trim(strip_tags($_GET['c2']));

$tertiary_category='';
if (isset($_GET['c3'])) $tertiary_category=trim(strip_tags($_GET['c3']));

$db=db_connect();

#===============================================================

if (isset($_GET['rate'])) {
    rate_product($_GET['rate']);
	return;
}

#===============================================================

if (isset($_GET['list'])) {
	if (empty($store_ids)) {
		foreach(getTableArray('select id from store') as $store_line) {
			$store_ids[] = $store_line['id'];
			$store_placeholders[] = '?';
		}
	}
	if ($tertiary_category) {
		$arr = $db->prepare("
		select * from inventory 
			join product on inventory.product_id = product.id 
		where 
			store_id in (".implode(',',$store_placeholders).") 
			and quantity>0
			and primary_category=?
			and secondary_category=?
			and tertiary_category=?
			".($_GET['sale']?'and has_limited_time_offer=1':'')."
		group by
			product_id
		order by
			primary_category, secondary_category, tertiary_category, price_in_cents
		");
		$arr->execute(array_merge($store_ids, array($primary_category, $secondary_category, $tertiary_category)));
	} else if ($secondary_category) {
		$arr = $db->prepare("
		select * from inventory 
			join product on inventory.product_id = product.id 
		where 
			store_id in (".implode(',',$store_placeholders).") 
			and quantity>0
			and primary_category=?
			and secondary_category=?
                        ".($_GET['sale']?'and has_limited_time_offer=1':'')."
		group by
			product_id
		order by
			primary_category, secondary_category, tertiary_category, price_in_cents
		");
		$arr->execute(array_merge($store_ids, array($primary_category, $secondary_category)));
	} else {
		$arr = $db->prepare("
		select * from inventory 
			join product on inventory.product_id = product.id 
		where 
			store_id in (".implode(',',$store_placeholders).") 
			and quantity>0
			and primary_category=?
                        ".($_GET['sale']?'and has_limited_time_offer=1':'')."
		group by
			product_id
		order by
			primary_category, secondary_category, tertiary_category, price_in_cents
		");
		$arr->execute(array_merge($store_ids, array($primary_category)));
  }
	$arr = $arr->fetchAll();

	$ratebeer = array();
  $ratewine = array();
	if ($primary_category == 'Beer') {
		foreach (getTableArray('select product_id, rating from ratebeer_beer') as $line) $ratebeer[$line['product_id']] = $line['rating'];
	}
	if ($primary_category == 'Wine') {
		foreach (getTableArray('select * from ratewine_percentile') as $line) $ratewine[$line['product_id']] = $line['percentile'];
	}

	$ter='-';
	foreach ($arr as $line) {
		if ($line['tertiary_category']!=$ter) {
			if ($ter!='-') echo '<hr>';
			$ter=$line['tertiary_category'];
			echo $line['tertiary_category']?'<h2 style="clear:left">'.$line['secondary_category'].' / '.$line['tertiary_category'].'</h2>':'<h2 style="clear:left">'.$line['secondary_category'].'</h2>';
		}
	?>
			<div class="col-sm-3" style="padding:10px;">
				<a style="height:250px;" href="?id=<?= $line['id'] ?>" onclick="$('#myModal').modal(); $('#myModal .modal-body').html('loading...').load('?id=<?= $line['id'] ?>'); return false;" class="thumbnail">
<?php if ($line['is_ocb']) { ?><img src="/ocb.png" width="20" height="20" style="float:right" title="Ontario Craft Beer" /><?php } ?>
					<div id="rating-<?= $line['id'] ?>" style="text-align:center">
            <?= isset($ratebeer[$line['id']])?rate_to_stars($ratebeer[$line['id']]):'' ?>
            <?= isset($ratewine[$line['id']])?rate_to_stars($ratewine[$line['id']]):'' ?>
          </div>
					<img src="<?= $line['image_thumb_url']?$line['image_thumb_url']:'no-image-available.png' ?>" style="max-height:150px;">
					<?= $line['name'] ?><br>
					$<?= $line['has_limited_time_offer']?'<strike style="color:red">'.number_format($line['regular_price_in_cents']/100,2).'</strike> ':'' ?><?= number_format($line['price_in_cents']/100,2) ?> / <?= $line['alcohol_content']/100 ?>% abv
				</a>
			</div>
			<?php if ( ($primary_category == 'Beer') && !isset($ratebeer[$line['id']]) ) { ?><script> rateMe(<?= $line['id'] ?>) </script><?php } ?>
      <?php if ( ($primary_category == 'Wine') && !isset($ratewine[$line['id']]) ) { ?><script> rateMe(<?= $line['id'] ?>) </script><?php } ?>
	<?php
	}
	exit();
}


#===============================================================

if (!empty($_GET['id'])) {
    require 'view.inc';
	return;
}

#===============================================================

if (isset($_GET['nav'])) {
	if (empty($store_ids)) {
		$arr = $db->prepare("select * from store_count");
	} else {
		$arr = $db->prepare("select * from store_count where store_id in (".implode(',',$store_placeholders).")");
	}
	$arr->execute($store_ids);
	$arr = $arr->fetchAll();
	$primary_categories=array();
	$secondary_categories=array();
	$tertiary_categories=array();
	foreach ($arr as $line) {
		@$primary_categories[$line['primary']] += $line['cnt'];
		if ($line['secondary']) {
			@$secondary_categories[$line['primary']][$line['secondary']] += $line['cnt'];
			if ($line['tertiary']) {
				@$tertiary_categories[$line['primary']][$line['secondary']][$line['tertiary']] += $line['cnt'];
			}
		}
	}
	foreach ($primary_categories as $n=>$c) {
		echo '<li>';
		echo '<a href="javascript:;" '.($primary_category==$n?'class="active"':'').' onclick="open_category(\''.$n.'\')">'.$n.' ('.$c.')</a>';
		if (($primary_category==$n) && isset($secondary_categories[$n])) {
			echo '<ul>';
			foreach ($secondary_categories[$n] as $nn=>$cc) {
				echo '<li>';
				echo '<a href="javascript:;" '.($secondary_category==$nn?'class="active"':'').' onclick="open_category(\''.$n.'\', \''.$nn.'\')">'.$nn.' ('.$cc.')</a>';
				if (($secondary_category==$nn) && isset($tertiary_categories[$n][$nn])) {
					echo '<ul>';
					foreach ($tertiary_categories[$n][$nn] as $nnn=>$ccc) {
						echo '<li>';
						echo '<a href="javascript:;" '.($tertiary_category==$nnn?'class="active"':'').' onclick="open_category(\''.$n.'\', \''.$nn.'\', \''.$nnn.'\')">'.$nnn.' ('.$ccc.')</a>';
						echo '</li>';
					}
					echo '</ul>';
				}
				echo '</li>';
			}
			
			echo '</ul>';
		}
		echo '</li>';
	}
	exit();
}

#===============================================================

$this->output->addToHead('
<style>
#ul-nav, #ul-nav UL { list-style:none; margin:0; padding:0 }
#ul-nav A { display:block; padding:5px; margin-bottom:1px; }
#ul-nav A.active { background-color: #337ab7; color:white; }
#ul-nav A:hover { text-decoration:underline; }
#ul-nav > LI > A { background-color:#333; color:white; font-weight:bold; }
#ul-nav > LI > UL > LI > UL > LI { padding-left:20px; }
</style>
');

?>

<h1>Virtual Store</h1>
<div class="row">
<form action="?stores" method="post">
<p>
<label>Select your local stores:</label>
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

<script>
$('.select2').select2();
</script>

<hr>

<? /* if (empty($store_ids)) return; */ ?>

<div class="row">
  <div class="col-sm-3">
    <ul id="ul-nav">
      <li>loading...</li>
    </ul>
  </div>
  <div class="col-sm-9">
	<div class="row">
			<div>
				<div class="col-sm-3 checkbox pull-right"><label><input onclick="load_contents()" type="checkbox" name="sale" id="checkbox-sale"> On Sale</label></div>
			</div>
	</div>
	<hr>
	<div id="main-div"></div>
  </div>
</div>


<script>
  $(document).ready(function() {
    $('#ul-nav').load('?nav');
  });

  var c1=false;
  var c2=false;
  var c3=false;

  function open_category(cat1,cat2,cat3) {
    window.c1 = cat1;
    window.c2 = cat2;
    window.c3 = cat3;
    load_contents();
  }
  function load_contents() {
    var sale = $('#checkbox-sale')[0].checked ? 1 : 0;
    $.get('?',{nav:true, c1:c1, c2:c2, c3:c3, sale: sale}, function(response) { $('#ul-nav').html(response); });
    if (c1) {
      $('#main-div').html('loading...');
      $.get('?',{list:true, c1:c1, c2:c2, c3:c3, sale: sale}, function(response) { $('#main-div').html(response); window.setTimeout(processRating, 1000) });
    }
  }
  var rate_ids = [];
  function rateMe(id) {
    rate_ids[rate_ids.length] = id;
  }
  
  function processRating() {
	if (rate_ids.length == 0) return;
	var id = rate_ids.shift();
	$.get('?', {rate:id}, function(response) { 
		$('#rating-'+id).html(response); 
		window.setTimeout(processRating, 3000 + (Math.random()*5000));
	}, 'html');
  }

</script>

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
