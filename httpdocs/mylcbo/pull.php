<?php

require 'vendor/autoload.php';

global $db;
$db = new PDO('mysql:host=localhost;dbname=boozewatch;charset=utf8', 'boozewatch', 'uSss4ghY9zPJue7V', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

global $result_from_cache;
function curl_http_get($url) {
	global $result_from_cache;

	#$md5 = md5($url);
	#if (file_exists($md5) && filesize($md5)) {
	#	$result_from_cache=true;
	#	return file_get_contents($md5);
	#}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	$headers = array(
		'Expect:',
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$server_output = curl_exec ($ch);
	curl_close ($ch);

	#file_put_contents($md5, $server_output);

	$result_from_cache=false;
	return $server_output;
}

function check_table($table, $line) {
	global $db;
	$e=array();
	foreach ($db->query('describe `'.$table.'`')->fetchAll() as $row) $e[$row['Field']] = $row['Field'];
	foreach ($line as $k=>$v) {
		if (!isset($e[$k])) {
			$type='varchar(255)';
			if (substr($k,0,4)=='has_') $type='tinyint';
			if (substr($k,0,3)=='is_') $type='tinyint';
			$db->query("alter table `{$table}` add `{$k}` {$type} null default null");
		}
	}
}

function api_pull($uri, $table, $base_query='?per_page=100', $one_page=false) {
	global $db;
	$url = 'http://lcboapi.com/'.$uri;
	$table_checked = false;
	$replace_statement = false;
	$query = $base_query;
	while(true) {
		$str = curl_http_get($url.$query);
		if (empty($str)) throw new Exception('no response from '.$url);
		$json = json_decode($str, true);
		if (empty($str)) throw new Exception('json_decode failed from '.$url);
		if (empty($json['status']) || ($json['status']!='200') || empty($json['pager']) || empty($json['pager']['total_record_count'])) throw new Exception('json data error from '.$url);
		
		foreach ($json['result'] as $line) {
			if (!$table_checked) {
				check_table($table, $line);
				$table_checked = true;
			}
			if (!$replace_statement) {
				$sql = array();
				foreach ($line as $k=>$v) {
					$sql[] = "`{$k}`=:{$k}";
				}
				$sql = 'replace into `'.$table.'` set '.implode(', ',$sql);
				$replace_statement = $db->prepare($sql);
			}
			
			foreach ($line as $k=>$v) {
				if (is_array($v)) {
					$line[$k] = json_encode($v);
				}
			}
			$replace_statement->execute($line);
		}
		
		$current_page = $json['pager']['current_page'];
		$total_pages = $json['pager']['total_pages'];
		echo "done with page {$current_page}\n";
		if ($one_page || ($current_page >= $total_pages)) {	
			return;
		}
		$query = $base_query.'&page='.($current_page+1);
	}
}

function csv_pull($uri, $table) {
	global $db;
	$db->query('delete from '.$table.' where 1');

	$fp = fopen($uri.'.csv', 'rb');
	$columns = fgetcsv($fp,0,',');
	$table_checked = false;

	$replace_sql = false;
	$replace_values = array();
	$replace_counter = 0;
	while($line = fgetcsv($fp,0,',')) {
		$line = array_combine($columns, $line);
		if (!$table_checked) {
			check_table($table, $line);
			$table_checked=true;
		}
    
    if ($table=='inventory_temp') {
      if ($line['is_dead']=='t') {
        continue;
      }
    }

		$replace_counter++;
		foreach ($line as $k=>$v) {
			if ($v=='t') $v=1;
			if ($v=='f') $v=0;
			$replace_values[] = $v;
		}

		if ($replace_counter >= 400) {
			mass_sql($table, $columns, $replace_counter, $replace_values);
			$replace_counter = 0;
			$replace_values = array();
		}
	}
	if ($replace_counter>0) {
		mass_sql($table, $columns, $replace_counter, $replace_values);
	}
}

function mass_sql($table, $columns, $replace_counter, $replace_values) {
	global $db;
	$sql = "insert into `{$table}` (".implode(',',$columns).") values ";
	$arr = array();
	foreach ($columns as $c) $arr[]='?';
	for ($i=0;$i<$replace_counter;$i++) {
		$sql .= '('.implode(',',$arr).')';
		if ($i+1<$replace_counter) $sql.=',';
	}
	$db->prepare($sql)->execute($replace_values);
}


api_pull('datasets','dataset', '?per_page=10&order=created_at.desc', true);
$row = $db->query('select * from dataset order by updated_at desc limit 1')->fetch();
if (empty($row)) throw new Exception('dataset?');

$current_id = $db->query('select max(id) from current_dataset')->fetchColumn(0);
if ($row['id']==$current_id) {
	echo "dataset {$current_id} is already pulled\n";
	exit();
}
$db->query('replace into current_dataset set id='.intval($row['id']));

if (!file_exists('stores.csv')) {
	if (!file_exists('csv.zip')) {
		echo "downloading {$row['csv_dump']}...\n";
		$zip = file_get_contents($row['csv_dump']);
		file_put_contents('csv.zip', $zip);
	}
	echo "extracting zip...\n";
	$zip = new PclZip('csv.zip');
	if ($zip->extract() == 0) {
		die("Error : ".$zip->errorInfo(true));
	}
}
echo "stores...\n";
csv_pull('stores','store');
echo "products...\n";
csv_pull('products','product');
echo "inventories...\n";
$db->query(
"CREATE TABLE IF NOT EXISTS `inventory_temp` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `store_id` int(11) NOT NULL DEFAULT '0',
  `is_dead` tinyint(4) DEFAULT NULL,
  `quantity` int(11) DEFAULT '0',
  `reported_on` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `previous_quantity` int(11) DEFAULT '0',
  `added_on` date null default null
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);
csv_pull('inventories','inventory_temp');

echo "inventories build index...\n";
$db->query('ALTER TABLE `inventory_temp` ADD index (`product_id`,`store_id`), add index(`added_on`), ADD index (`product_id`), add index(`store_id`), add index (`quantity`), add index(`previous_quantity`)');

echo "inventories update previous_quantity...\n";
$db->query('update inventory_temp set previous_quantity=(select quantity from inventory where inventory_temp.product_id=inventory.product_id and inventory_temp.store_id=inventory.store_id)');
$db->query('update inventory_temp set added_on=(select added_on from inventory where inventory_temp.product_id=inventory.product_id and inventory_temp.store_id=inventory.store_id)');
$db->query('update inventory_temp set added_on=now() where quantity>0 and (previous_quantity=0 or previous_quantity is null)');

$db->query('
CREATE TABLE IF NOT EXISTS `store_count_temp` (
  `store_id` int(11) NOT NULL,
  `primary` varchar(255) NOT NULL,
  `secondary` varchar(255) NOT NULL,
  `tertiary` varchar(255) NOT NULL,
  `cnt` int(11) NOT NULL,
  PRIMARY KEY (`store_id`,`primary`,`secondary`,`tertiary`)
) ENGINE=InnoDB
');
$db->query("
insert into store_count_temp (`store_id`, `primary`, `secondary`, `tertiary`, `cnt`)
select store_id,primary_category, secondary_category, tertiary_category, cnt from (
	select
		store_id,primary_category, secondary_category, tertiary_category, concat(primary_category,'-',secondary_category,'-',tertiary_category) as h1, count(*) as cnt
	from product
		join inventory_temp on product.id=inventory_temp.product_id and quantity>0
	group by store_id, h1
) as s1");

$db->query('DROP TABLE inventory');
$db->query('RENAME TABLE `inventory_temp` TO `inventory`');

$db->query('DROP TABLE store_count');
$db->query('RENAME TABLE `store_count_temp` TO `store_count`');

$db->query('update product set limited_time_offer_start=null where has_limited_time_offer=0');
$db->query('update product set limited_time_offer_start=now() where has_limited_time_offer=1 and limited_time_offer_start is null');

unlink('stores.csv');
unlink('products.csv');
unlink('inventories.csv');
unlink('csv.zip');

require 'alert.php';
alerts();

#-------------------
$day_num = date('N');
$year_week = sprintf('%04d%02d', date('Y'), date('W'));
$db->query("
	insert into sales (year_week, store_id, product_id, day{$day_num}, price{$day_num}) 
	SELECT {$year_week} , i.store_id, i.product_id, i.previous_quantity - i.quantity, p.price_in_cents
	FROM inventory i
	JOIN product p ON i.product_id = p.id
	left JOIN sales s on i.product_id = s.product_id and i.store_id=s.store_id and s.year_week={$year_week}
	WHERE s.year_week is null
	and previous_quantity > quantity
");
$db->query("
	update sales s
	JOIN inventory i on i.product_id=s.product_id and i.store_id=s.store_id
	JOIN product p on s.product_id=p.id
	set day{$day_num} = i.previous_quantity - i.quantity,
	price{$day_num} = p.price_in_cents
	where s.year_week={$year_week}
	and previous_quantity > quantity
");
#-------------------

echo "done!\n";

