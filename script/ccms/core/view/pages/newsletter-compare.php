<?

class NewsletterCompare {

  private $newsletters=null;

	public function __construct($newsletters) {
		_log(get_class().".__construct");
		$this->newsletters=$newsletters;
	}

  public function invoke($view) {
    echo '
<div id="pagepath">
  <a target="_parent" href="'.$view->base_url().'/body.html">CCMS</a>
  &raquo;
  <a target="_parent" href="'.$view->base_url().'/page/newsletter.html">'.$view->domainTranslate('Tree.Newsletter').'</a>
  &raquo;
  <a href="'.$view->base_url().'/inline/NewsletterManager.html">'.$view->domainTranslate('Newsletter._title').'</a>
</div>
';


?>
<style>
table.tableDataGrid, table.tableStatGrid {
    border-left: 1px solid #BEEBFF;
    border-right: 1px solid #BEEBFF;
    margin-bottom: 20px;
    margin-top: 10px;
    width: 100%;
}
TABLE.tableDataGrid TH {
  text-align:left;
}
table.tableDataGrid th, table.tableStatGrid th {
    background-color: #BEEBFF;
    border-bottom: 1px solid #BEEBFF;
    border-top: 1px solid #BEEBFF;
    color: #004D6E;
    font-size: 12px;
    font-weight: bold;
    text-align: left;
}
table.tableDataGrid td, table.tableStatGrid td, th {
    padding: 4px;
}
TR.trDataGridRow {
}
TD.tableLabels {
  width:50%;
}
TD.tableValues {
}
tr.trDataGridRow td {
    background-color: #FFFFFF;
    border-bottom: 1px solid #BEEBFF;
    border-right: 0 none;
}
tr.trDataGridAltRow td {
    background-color: #FFFBF7;
    border-bottom: 1px solid #BEEBFF;
    border-right: 0 none;
}
table.tableDataGrid a, table.tableStatGrid a {
    text-decoration: none;
    color: #4774A0;
}
.pieChartPercent {
    float: left;
    margin-left: -70px;
    position: relative;
    text-align: right;
    width: 50px;
}
.pieChartKey {
    float: left;
    height: 10px;
    margin: 3px 5px 0 -15px;
    position: relative;
    text-indent: -9999px;
    width: 10px;
}
.pieChartKey.opens {
    background: none repeat scroll 0 0 #539D36;
}
.pieChartKey.bounces {
    background: -moz-linear-gradient(center top , #F47061, #702124) repeat scroll 0 0 transparent;
}
.pieChartKey.neither {
    background: none repeat scroll 0 0 #36569D;
}
</style>
<fieldset style="width: 700px; border: 1px solid rgb(204, 204, 204); padding: 5px 5px 50px;">

	<h1><?= $view->_("page.newsletter-compare.title") ?></h1>

<table cellspacing="0" id="tableClickReport" class="tableDataGrid">
  <thead>
    <tr>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Subject") ?></th>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Date") ?></th>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Recipients") ?></th>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Opens") ?></th>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Clicks") ?></th>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Bounces") ?></th>
      <th nowrap=""><?= $view->_("page.newsletter-compare.th.Unsubscribes") ?></th>
    </tr>
  </thead>
  <tbody>
<?
  foreach ($this->newsletters as $line) { 
    $t=getOneValue('select count(*) from '.tbl_name('newsletter_log').' where newsletter='.$line['id']);
    $s1=$t>0?getOneValue('select count(*) from '.tbl_name('newsletter_log').' where status='.dbStr('open').' and newsletter='.$line['id']):0;
    $s2=$t>0?getOneValue('select count(*) from '.tbl_name('newsletter_track').' where op='.dbStr('click').' and id in (select id from '.tbl_name('newsletter_log').' where newsletter='.$line['id'].')'):0;
    $s3=$t>0?getOneValue('select count(*) from '.tbl_name('newsletter_log').' where status='.dbStr('bounce').' and newsletter='.$line['id']):0;
    $s4=$t>0?getOneValue('select count(*) from '.tbl_name('newsletter_track').' where op='.dbStr('unsubscribe').' and id in (select id from '.tbl_name('newsletter_log').' where newsletter='.$line['id'].')'):0;
?>
    <tr class='trDataGridRow'>
      <td><?= $line['name'] ?></td>
      <td><?= $line['dt_sent']?date('Y-m-d',strtotime($line['dt_sent'])):'' ?></td>
      <td><?= $t ?></td>
      <td><?= $t>0?number_format(100*($s1/$t),2).'%':'-' ?></td>
      <td><?= $t>0?number_format(100*($s2/$t),2).'%':'-' ?></td>
      <td><?= $t>0?number_format(100*($s3/$t),2).'%':'-' ?></td>
      <td><?= $t>0?number_format(100*($s4/$t),2).'%':'-' ?></td>
    </tr>
<? } ?>
  </tbody>
</table>

</fieldset>
<?
   }
}
?>