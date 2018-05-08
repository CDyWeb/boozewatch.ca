<?

class CoreOrderByStatusList extends GenericList {

	protected function getRowButtons(CCMSDomainManagerInterface $manager) {
		$res=parent::getRowButtons($manager);
		$res[]="invoice";
		return $res;
	}
	
	protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
		switch ($btnName) {
			case "invoice" :
				return 
"<td width='25'>
	<a href='javascript:;' onclick='window.open(\"{$this->base_url()}/inline/order_invoice.html?id={$line["id"]}".($line["printed"]?"":"&print_ccms")."\",\"_blank\",\"width=800,height=600,scrollbars=yes,resizable=yes\")'>
		<img height='16' src='{$this->resources_url('/img/icon/application.png')}' alt='' title='{$this->cmsTranslate("Invoice")}' border='0' />
	</a>
</td>";
		}
		return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
	}

	public function outputContent() {
		$this->startList();
		$this->outputDescription();
		$this->outputTable($this->data);
		$this->endList();
	}
}

class CoreOrderList extends GenericList {
  
  public $page_size = 100;

	protected function getWidth() {
		return 900;
	}
  
  protected function orderTab($value,$data,$active) {
?>
		<div id="tab_<?= $value ?>" class="<?= $active?'tab-pane active':'tab-pane' ?>">
    
<p style="margin-top:1em;text-align:right">
    <? 
    for ($i=0;;$i++) { 
    ?>
    <a href="javascript:;" onclick="$(this).parent().parent().find('tbody').html('<tr><td>...</td></tr>').load('<?= $_SERVER['_URI'] ?>?tab=<?= $value ?>&page=<?= $i ?>')"><?= $i+1 ?></a>
    <?
      if (($i+1)*$this->page_size > count($data)) break;
    }
    ?>
</p>
			<? 
				$l=new CoreOrderByStatusList($this->getModel());
				$l->data=array_splice($data,0,$this->page_size);
				$l->outputContent();
			?>
		</div>
<?
  }
	
	public function outputContent() {
		$manager=$this->getManager();
		$status=$manager->getField("status");

    if (isset($_GET['tab']) && isset($_GET['page'])) {
      $data = $manager->getAllExt(array("`status`='{$_GET['tab']}'"),array($manager->getOrderBy()));
      $offset = $_GET['page']*$this->page_size;
      $data = array_splice($data,$offset,$this->page_size);
      $trBody = array();
      $l=new CoreOrderByStatusList($this->getModel());
      $rowButtons=$l->getRowButtons($manager);
      $fields=$l->getListFields();
      $l->getTableBody($trBody,$manager,$data,$rowButtons,$fields,false);
      ob_end_clean();
      echo implode("\r\n", $trBody);
      exit();
    }

		$this->startList();
		$this->outputDescription();
		
?>
<ul class="nav nav-tabs">
<? 
	$alldata=array();
  $first_tab=null;
  foreach (explode(",",$status->attributes) as $value) {
		$data = $manager->getAllExt(array("`status`='{$value}'"),array($manager->getOrderBy()));
		if (count($data)<1) continue;
    if (empty($first_tab)) $first_tab=$value;
    $alldata[$value]=$data;
?>
  <li <?= $value==$first_tab?'class="active"':'' ?>><a data-toggle="tab" href="#tab_<?= $value ?>"><?= $this->domainTranslate("Order.status.{$value}") ?> (<?= count($data) ?>)</a></li>
<? } ?>
</ul>
<div id="tabs-order" class="tab-content">
<?
  foreach ($alldata as $value=>$data) {
    $this->orderTab($value,$data,$value==$first_tab);
  }
?>
</div>
<?
		$this->endList();
	}

}


//end