<?

class CcmsInvoice {

  protected $translator=null;
  
  public function __construct($translator,array $opt=null) {
    $this->translator=$translator;
    $e=setting('invoice.elements',null);
    if (empty($e)) {
      $e=json_encode(array(
        'start',
        'address',
        'header',
        'body',
        'info',
        'log',
        'footer',
        'end'
      ));
      if (function_exists('set_setting')) set_setting('invoice.elements',$e);
    }
    $this->elements=json_decode($e,true);
    foreach ($opt as $k=>$v) $this->$k=$v;
  }
  
  protected function wrap($s) {
return 
<<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <head>
    <style>
BODY,TABLE,HTML,P,A,DIV,LI,UL,TR,TD,INPUT {
  font-family:verdana;
  font-size:11px;
}
BODY {
  margin:0px;
  padding:0px;
}
    </style>
  </head>
  <body>
    
      {$s}
    
  </body>
</html>
HTML;
  }
  
  public function output_start() {
    echo '<div id="tblInvoiceWrap"><table id="tblInvoice" style="width:740px;margin:20px" cellspacing="0" cellpadding="5">';
  }
  public function output_address() {
?>
<tr>
	<td valign="top">
		<? if ($this->customer["last_name"]) { ?>
		<b><?= $this->translator->getTranslation("invoice.customer_details_header") ?></b><br /><br />
		<?= $this->translator->getTranslation("address_construct",array(
			'firstparam'=>(empty($this->customer["company"])?'':'<b>'.$this->customer["company"].'</b><br />').trim(@$this->customer["first_name"]." ".@$this->customer["last_name"]),
			'secondparam'=>@$this->customer["adr1_address1"].(@$this->customer["adr1_address2"]?'<br />'.@$this->customer["adr1_address2"]:''),
			'thirdparam'=>@$this->customer["adr1_city"],
			'fourthparam'=>@$this->customer["adr1_state"],
			'fifthparam'=>@$this->customer["adr1_zip"],
			'sixthparam'=>@$this->customer["adr1_country"],
		)) ?>
		<br />
		<? if (@$this->customer["tel1"]) { ?><?= $this->translator->getTranslation("invoice.Tel1") ?>: <?= $this->customer["tel1"] ?><br /><? } ?>
		<? if (@$this->customer["email"]) { ?><?= $this->translator->getTranslation("invoice.Email") ?>: <a href="mailto:<?= $this->customer["email"] ?>"><?= $this->customer["email"] ?></a><br /><? } ?>
		<br />
		<? } ?>
	</td>
	<td valign="top" align='right'>
		<?= $this->translator->getTranslation("address_construct",array(
			'firstparam'=>setting("company_name"),
			'secondparam'=>setting("company_address1").(setting("company_address2")?'<br />'.setting("company_address2"):''),
			'thirdparam'=>setting("company_city"),
			'fourthparam'=>setting("company_state"),
			'fifthparam'=>setting("company_zip"),
			'sixthparam'=>setting("company_country"),
		)) ?>
		<br />
		<? if ($tel1=setting("company_tel1")) { ?><?= $this->translator->getTranslation("invoice.Tel1") ?>: <?= $tel1 ?><br /><? } ?>
		<? if ($fax=setting("company_fax")) { ?><?= $this->translator->getTranslation("invoice.Fax") ?>: <?= $fax ?><br /><? } ?>
		<? if ($email=setting("company_email")) { ?><?= $this->translator->getTranslation("invoice.Email") ?>: <a href='mailto:<?= $email ?>'><?= $email ?></a><br /><? } ?>
		<? if ($website=setting("company_website")) { ?><?= $this->translator->getTranslation("invoice.Website") ?>: <a href="<?= preg_match('#^'.preg_quote('http://').'#',$website)?$website:'http://'.$website ?>"><?= $website ?></a><br /><? } ?>
		<br />
		<? if ($bank=setting("company_bank")) { ?><?= $this->translator->getTranslation("invoice.Bank") ?>: <?= $bank ?><br /><? } ?>
		<? if ($iban=setting("company_iban")) { ?><?= $this->translator->getTranslation("invoice.Iban") ?>: <?= $iban ?><br /><? } ?>
		<? if ($bic=setting("company_bic")) { ?><?= $this->translator->getTranslation("invoice.Bic") ?>: <?= $bic ?><br /><? } ?>
		<br />
		<? if ($coc=setting("company_coc")) { ?><?= $this->translator->getTranslation("invoice.Coc") ?>: <?= $coc ?><br /><? } ?>
		<? if ($tax=setting("company_tax")) { ?><?= $this->translator->getTranslation("invoice.TaxReg") ?>: <?= $tax ?><br /><? } ?>
	</td>
</tr>
<?
  }
  public function output_header() {
?>
<tr id="h2-Invoice">
	<td colspan='2'><h2><?= $this->translator->getTranslation("invoice.Invoice") ?></h2></td>
</tr>
<tr>
	<td colspan='2'>
		<b><?= $this->translator->getTranslation("invoice.Order_id") ?>: <?= $this->order["order_id"] ?> (<?= $this->translator->getTranslation("invoice.Order_status.".$this->order["status"]) ?>)</b>
	</td>
</tr>
<tr>
	<td>
		<?= $this->translator->getTranslation("invoice.Order_date") ?>: <?= date($this->translator->getTranslation("_date_tpl"),strtotime($this->order["date_insert"])) ?>
	</td>
	<td align='right' valign='bottom'>
		<?= $this->translator->getTranslation("invoice.Total_due") ?>: <?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_total"]) ?>
	</td>
</tr>
<?
  }
  public function output_body() {
?>
<tr>
	<td style='border:1px solid black' width='300' valign='top'>
		<b><?= $this->translator->getTranslation("invoice.customer_details_adr2") ?></b><br />
		<br />
		<?= $this->translator->getTranslation("address_construct",array(
			'firstparam'=>(empty($this->customer["company"])?'':'<b>'.$this->customer["company"].'</b><br />').trim(@$this->customer["adr2_name"]),
			'secondparam'=>@$this->customer["adr2_address1"].(@$this->customer["adr2_address2"]?'<br />'.@$this->customer["adr2_address2"]:''),
			'thirdparam'=>@$this->customer["adr2_city"],
			'fourthparam'=>@$this->customer["adr2_state"],
			'fifthparam'=>@$this->customer["adr2_zip"],
			'sixthparam'=>@$this->customer["adr2_country"],
		)) ?>
		<br />
		<? 
			if (file_exists($fn=getConfigItem('script_app').'frontend/shared_cyane_invoice.shipping.inc')) {
        require $fn;
      } else {
?>
		<b><?= $this->translator->getTranslation("invoice.customer_details_tracking") ?></b><br />
		<br />
<?
        if ($this->order["tracking"]) echo $this->order["tracking"]; else echo "-";
      }
		?>
</td>
<td style='border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black' valign='top'>
	<b><?= $this->translator->getTranslation("invoice.Products") ?></b><br />
	<br />
	<table style='width:450px'>
<?
  
  if (getConfigItem('webshop.with_sizes',true)) {
    $ps=getTableArray("select s.id, s.name, sg.name as groupname from ".tbl_name('size')." s left join ".tbl_name('sizegroup')." sg on s.sizegroup=sg.id",'id');
  }

  $cart=$this->cart;
  $order=$this->order;
  $currency_html=$this->currency_html;
  
	if (file_exists($fn=getConfigItem('script_app').'frontend/shared_cyane_invoice.cart.inc')) {
		require $fn;
	} else {
		foreach ($this->cart as $line) { 
			$price = $line["unit_price"];
			$amount = $line["amount"];
			$qty = isset($line["quantity"])?$line["quantity"]:1;
			if ($qty<=0) $qty=1;
			if ($qty!=1) {
				$pack = ceil($amount/$qty);
				$amount = $pack*$qty;
			}
?>
	<tr>
		<td align='right' valign='top'><?= $amount>0?$amount:'&nbsp;' ?></td>
		<td valign='top'><?= $amount>0?'X':'&nbsp;' ?></td>
		<td valign='top' width='330'>
<?= trim($line["sku"]." ".@$line["brandname"]." ".$line["name"]) ?><?
	for ($i=1;$i<=9;$i++) if (@$line["o{$i}"]) {
    $opt_str=$line["option{$i}"];
    if (is_array($line["option{$i}"]) && isset($line["option{$i}"]['caption'])) $opt_str=$line["option{$i}"]['caption'];
    echo "<br />".$opt_str." : ".$line["o{$i}"];
  }
  if (!empty($line['product_size']) && isset($ps[$line['product_size']])) echo '<br /> - '.$ps[$line['product_size']]['groupname'].' : '.$ps[$line['product_size']]['name'];
  if ($line['discount_percent']>0) echo '<br /> - '.$this->translator->getTranslation("invoice.Discount").': '.str_replace('.00','',$line['discount_percent']).'%';
  //var_dump($line);
?>
		</td>
		<td valign='top' align='right'><nobr /><?= $this->currency_html ?> <?= sprintf("%0.2f",($line["unit_price"])) ?></td>
	</tr>
<? 		}
	}
?>
	</table>
	<br />
	<? if (strlen($this->order["notes"])>0) {?>
	<b><?= $this->translator->getTranslation("invoice.Notes") ?></b><br />
	<?= nl2br($this->order["notes"]) ?>
	<?} ?>
</td>
</tr>
<?
  }
  public function output_info() {
?>
<tr><td colspan='2'>
	<b><?= $this->translator->getTranslation("invoice.Invoice_info") ?></b>
</td></tr>
<tr>
<td style='border:1px solid black' width='300' valign='top'>
	<b><?= $this->translator->getTranslation("invoice.Payment_method") ?></b><br />
	<?= $this->translator->getTranslation("invoice.Order_payment.".$this->order["payment"]) ?>
</td>
<td style='border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black' valign='top'>
	<table style='width:450px'>
	<tr><td width='350' align='right'><?= $this->translator->getTranslation("invoice.Sub_total") ?></td><td align='right'><nobr /><?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_subtotal"]) ?></td></tr>
	<? if ($this->order["am_processing"]>0) {	?><tr><td width='350' align='right'><?= $this->translator->getTranslation("invoice.Processing")	?></td><td align='right'><nobr /><?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_processing"]) ?></td></tr><? } ?>
	<? if ($this->order["am_transport"]>0) { 	?><tr><td width='350' align='right'><?= $this->translator->getTranslation("invoice.Shipping")	?></td><td align='right'><nobr /><?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_transport"]) ?></td></tr><? } ?>
	<? if ($this->order["am_discount"]>0) { 	?><tr><td width='350' align='right'><?= $this->translator->getTranslation("invoice.Discount")	?></td><td align='right'><nobr /><?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_discount"]) ?></td></tr><? } ?>
	<? if ($this->order["am_tax"]>0) { 		?><tr><td width='350' align='right'><?= $this->translator->getTranslation("invoice.Tax") 		?></td><td align='right'><nobr /><?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_tax"]) ?></td></tr><? } ?>
	<tr><td width='350' align='right'><?= $this->translator->getTranslation("invoice.Total_due") ?></td><td align='right'><nobr /><b><?= $this->currency_html ?> <?= sprintf("%0.2f",$this->order["am_total"]) ?></b></td></tr>
	</table>
</td>
</tr>
<?
  }
  public function output_log() {
?>
<tr>
  <td colspan='2'>
    <b><?= $this->translator->getTranslation("invoice.Order_log") ?></b>
  </td>
</tr>
<tr>
  <td colspan='2' style='border:1px solid black'>
    <table>
    <? foreach (array_values($this->log) as $i=>$line) { ?>
    <tr>
    <td width='100' valign='top'><?= date($this->translator->getTranslation("_date_tpl"),strtotime($line["dt"])) ?></td>
    <td>
      <b><?= $this->translator->getTranslation("invoice.Order_log.".$line["status"],$line["data"]) ?></b><br />

  <? if (strlen($s=$this->translator->getTranslation("invoice.status_info.".$line["status"],$this->order))>0) echo $s.'&nbsp;'; ?>
  <? if (($i==0) && (($this->order["status"]=='new') || ($this->order["status"]=='in_progress')) && ($s=$this->translator->getTranslation("invoice.payment_info.".$this->order["payment"],$this->order))) echo $s.'&nbsp;'; ?>
  
    </td>
    </tr>
  <? 
    }
  ?>
    </table>
  </td>
</tr>
<?
  }

  public function output_footer() {
?>
<tr>
  <td colspan='2'>
    <?= setting("invoice.footer",$this->translator->getTranslation("invoice.footer")) ?>
  </td>
</tr>
<?
  }
  
  public function output_end() {
    echo '</table></div>';
    if (getConfigItem('invoice.footer.include_link',true)) {
      $link=getConfigItem('url_base').'__plugin/webshop/invoice/'.$this->order['id'].'/'.$this->order['uid'].'.html';
?>
  <style type="text/css" media="print"> .noprint { display:none; } </style>
  <div class="noprint"><center><a href="<?= $link ?>">Online <?= $this->translator->getTranslation("invoice.Invoice") ?></a></center></p>
  <? }
  }
  
  public function output($return=false) {

    ob_start();

    if (file_exists($fn=getConfigItem('script_app').'frontend/shared_cyane_invoice.header.inc')) {
      require $fn;
    } else {
      //noop
    }
    foreach ($this->elements as $e) { 
      echo "<!-- {$e} -->\n";
      $f='output_'.$e;
      $this->$f();
    }

    $html=ob_get_contents();
    ob_end_clean();
    
    if (isset($this->print_and_close)) {
      $html.='<script type="text/javascript"> window.onload=function() { window.print();window.close(); } </script>';
    }
    if (isset($this->print_ccms)) {
      executeSql("update ".tbl_name('order')." set printed=1 where id={$this->order["id"]}");
      $html.='<script type="text/javascript"> window.onload=function() { window.opener.location.href=window.opener.location.href;window.print(); } </script>';
    }
    
    if ($return) return $html;
    echo $html;
  }
}

//end