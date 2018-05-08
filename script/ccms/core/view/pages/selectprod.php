<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://www.w3.org/2005/10/profile">
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="/shared/ccms/css/body.css" type="text/css" />
</head>
<body>
  <p>
    Category<br />
    <select style="width:450px;" onchange="window.location.href='<?= $_SERVER['_URI'] ?>?'+this.value"><option></option>
<?
	$arr=array();
	foreach(getTableArray('select * from ccms_tree where parent_id>0 order by orderby') as $line) $arr[$line['parent_id']][]=$line;
	foreach($arr[1100] as $line) {
		if ($line['class']=='Product') echo '<option value='.$line['id'].' '.($_SERVER['QUERY_STRING']==$line['id']?'selected="selected"':'').'>'.utf8_ent($line['name']).'</option>';
		if (isset($arr[$line['id']])) foreach($arr[$line['id']] as $sline) {
			if ($sline['class']=='Product') echo '<option value='.$sline['id'].' '.($_SERVER['QUERY_STRING']==$sline['id']?'selected="selected"':'').'>'.utf8_ent($line['name']).' - '.utf8_ent($sline['name']).'</option>';
      if (isset($arr[$sline['id']])) foreach($arr[$sline['id']] as $ssline) {
        if ($ssline['class']=='Product') echo '<option value='.$ssline['id'].' '.($_SERVER['QUERY_STRING']==$ssline['id']?'selected="selected"':'').'>'.utf8_ent($line['name']).' - '.utf8_ent($sline['name']).' - '.utf8_ent($ssline['name']).'</option>';
      }
		}
	}
?>
    </select>
  </p>
<?
  $arr=array();
  if (is_numeric($_SERVER['QUERY_STRING'])) $arr=getTableArray('select * from ccms_product where tree_id='.($_SERVER['QUERY_STRING']).' order by orderby');
  if (!empty($arr)) {
?>
  <p>
      Product<br />
      <select style="width:450px;" onchange="for(var i=0;i<this.options.length;i++) if (this.options[i].selected) window.opener.selectprod(this.options[i].value,this.options[i].text); window.close();" ><option></option>
        <? foreach ($arr as $line) { ?>
          <option value="<?= $line['id'] ?>"><?= utf8_ent(trim($line['sku'].' '.$line['name'])) ?></option>
        <? } ?>
      </select>
  </p>
<?
  }
?>
</body>
</html>