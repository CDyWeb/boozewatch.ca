<?

class NewsletterTrack {

  private $newsletter=null;
  private $nlid=null;

	public function __construct($newsletter) {
		_log(get_class().".__construct, newsletter=".$newsletter['id']);
		$this->newsletter=$newsletter;
    $this->nlid=$newsletter['id'];
	}

  public function invoke($view) {
		_log(get_class().".invoke");
    $stats=array();
    
    $stats['total']=getOneValue('select count(*) from '.tbl_name('newsletter_log').' where newsletter='.$this->nlid);
    $stats['bounce']=getOneValue('select count(*) from '.tbl_name('newsletter_log').' where status='.dbStr('bounce').' and newsletter='.$this->nlid);
    $stats['open']=getOneValue('select count(*) from '.tbl_name('newsletter_log').' where status='.dbStr('open').' and newsletter='.$this->nlid);
    $stats['unsubscribe']=getOneValue('select count(*) from '.tbl_name('newsletter_track').' where op='.dbStr('unsubscribe').' and id in (select id from '.tbl_name('newsletter_log').' where newsletter='.$this->nlid.')');
    $stats['click']=getOneValue('select count(*) from '.tbl_name('newsletter_track').' where op='.dbStr('click').' and id in (select id from '.tbl_name('newsletter_log').' where newsletter='.$this->nlid.')');
    
    $stats['neither']=max(0,$stats['total']-$stats['open']-$stats['bounce']);
    
    $stats['pbounce']=($stats['bounce']/$stats['total'])*100;
    $stats['popen']=($stats['open']/$stats['total'])*100;
    $stats['pneither']=($stats['neither']/$stats['total'])*100;
    
    $click=array();
    foreach (getTableArray('select t.id,t.arg,t.exarg from '.tbl_name('newsletter_track').' t join '.tbl_name('newsletter_log').' l on t.id=l.id where l.newsletter='.$this->nlid.' and t.op='.dbStr('click')) as $line) {
      $url=$line['exarg']?$line['exarg']:$line['arg'];
      if (empty($click[$url])) $click[$url]=array('url'=>$url,'u'=>array(),'t'=>0);
      $click[$url]['t']++;
      $click[$url]['u'][]=$line['id'];
    }

    $details=array(
      'click'=>array(),
      'track'=>array(),
      'unsubscribe'=>array(),
      'bounce'=>array(),
    );
    foreach (getTableArray('select t.op,s.name,l.email,t.dt,t.arg,t.exarg from '.tbl_name('newsletter_track').' t join '.tbl_name('newsletter_log').' l on t.id=l.id left join '.tbl_name('newsletter_subscribe').' s on l.recipient=s.id where l.newsletter='.$this->nlid.' order by t.dt desc') as $line) {
      if ($line['op']=='online') $line['op']='track';
      $details[$line['op']][]=$line;
    }
    foreach (getTableArray('select * from '.tbl_name('newsletter_log').' where status='.dbStr('bounce').' and newsletter='.$this->nlid.' order by dt desc') as $line) {
      $details['bounce'][]=$line;
    }

/**echo '
<div id="pagepath">
  <a target="_parent" href="'.$view->base_url().'/body.html">CCMS</a>
  &raquo;
  <a target="_parent" href="'.$view->base_url().'/page/newsletter.html">'.$view->domainTranslate('Tree.Newsletter').'</a>
  &raquo;
  <a href="'.$view->base_url().'/inline/NewsletterManager.html">'.$view->domainTranslate('Newsletter._title').'</a>
  &raquo;
  <a href="'.$view->base_url().'/inline/newsletter.html?track='.$this->newsletter['id'].'">'.$view->domainTranslate("Newsletter._track").'</a>
</div>
';
**/

?>
<style>
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


<h3><a href="/ccms/inline/newsletter.html?track=<?= $this->newsletter['id'] ?>"><?= $view->_("page.newsletter-track.title",'<br>'.$this->newsletter['name']) ?></a></h3>

<table cellspacing="0" id="tableMessageProperties" class="table" style="width:800px">
  <thead>
    <tr>
      <th colspan="2"><?= $view->_("page.newsletter-track.properties.th") ?></th>
    </tr>
  </thead>
  <tbody>
    <tr class="trDataGridRow">
    <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.properties.Subject") ?></td>
    <td valign="top" class="tableValues"><?= utf8_ent($this->newsletter['name']) ?></td>
    </tr>
<? /**
    <tr class="trDataGridAltRow">
    <td valign="top" class="tableLabels">Type</td>
    <td valign="top" class="tableValues">HTML</td>
    </tr>
**/ ?>
    <tr class="trDataGridRow">
    <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.properties.Sent") ?></td>
    <td valign="top" class="tableValues"><?= strftime('%x',strtotime($this->newsletter['dt_sent'])) ?></td>
    </tr>
    <tr class="trDataGridRow">
    <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.properties.Total Recipients") ?></td>
    <td valign="top" class="tableValues"><?= $stats['total'] ?></td>
    </tr>
  </tbody>
</table>

<table cellspacing="0" id="tableMessageStatistics" class="table" style="width:800px">
  <thead>
    <tr>
      <th colspan="2"><?= $view->_("page.newsletter-track.statistics.th") ?></th>
    </tr>
  </thead>
  <tbody>
    <tr class="trDataGridAltRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Bounces") ?></td>
      <td valign="top" class="tableValues"><span class="pieChartPercent"><?= number_format($stats['pbounce'],1) ?>%</span><div class="pieChartKey bounces">&nbsp;</div><?= $stats['bounce'] ?></td>
    </tr>
    <tr class="trDataGridRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Released") ?></td>
      <td valign="top" class="tableValues"><?= $stats['total'] ?></td>
    </tr>
    <tr class="trDataGridAltRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Unsubscribes") ?></td>
      <td valign="top" class="tableValues"><?= $stats['unsubscribe'] ?></td>
    </tr>
    <tr class="trDataGridRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Opens") ?></td>
      <td valign="top" class="tableValues"><span class="pieChartPercent"><?= number_format($stats['popen'],1) ?>%</span><div class="pieChartKey opens">&nbsp;</div><?= $stats['open'] ?></td>
    </tr>
    <tr class="trDataGridAltRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Clicks") ?></td>
      <td valign="top" class="tableValues"><?= $stats['click'] ?></td>
    </tr>
<? /**
    <tr class="trDataGridRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Forwards") ?></td>
      <td valign="top" class="tableValues">0</td>
    </tr>
    <tr class="trDataGridAltRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Comments") ?></td>
      <td valign="top" class="tableValues">0</td>
    </tr>
    <tr class="trDataGridRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Complaints") ?></td>
      <td valign="top" class="tableValues">0</td>
    </tr>
**/ ?>
    <tr class="trDataGridAltRow">
      <td valign="top" class="tableLabels"><?= $view->_("page.newsletter-track.statistics.Neither") ?></td>
      <td valign="top" class="tableValues"><span class="pieChartPercent"><?= number_format($stats['pneither'],1) ?>%</span><div class="pieChartKey neither">&nbsp;</div><?= $stats['neither'] ?></td>
    </tr>
  </tbody>
</table>

<table cellspacing="0" id="tableClickReport" class="table" style="width:800px">
  <thead>
    <tr><th colspan="3"><?= $view->_("page.newsletter-track.click.th") ?></th></tr>
    <tr><th>Url</th><th nowrap=""><?= $view->_("page.newsletter-track.click.th.Unique Clicks") ?></th><th nowrap=""><?= $view->_("page.newsletter-track.click.th.Total Clicks") ?></th></tr>
  </thead>
  <tbody>
<? if (empty($click)) { ?><tr class="trDataGridAltRow"><td valign="top" class="tableLabels">-</td><td class="tableValues">-</td><td class="tableValues">-</td></tr><? } else foreach ($click as $line) { ?>
    <tr class="trDataGridAltRow">
      <td valign="top" class="tableLabels"><a title="<?= $line['url'] ?>" href="<?= $line['url'] ?>"><?= text_limit($line['url'],40) ?></a></td>
      <td class="tableValues"><?= count($line['u']) ?></td>
      <td class="tableValues"><?= $line['t'] ?></td>
    </tr>
<? } ?>
  </tbody>
</table>

<table cellspacing="0" id="tableClickReport" class="table" style="width:800px">
  <thead>
    <tr><th>Details</th></tr>
  </thead>
  <tbody>
    <tr><td>

      <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#tab-clicks"><?= $view->_("page.newsletter-track.details.clicks") ?></a></li>
        <li><a data-toggle="tab" href="#tab-opens"><?= $view->_("page.newsletter-track.details.opens") ?></a></li>
        <li><a data-toggle="tab" href="#tab-unsubscribes"><?= $view->_("page.newsletter-track.details.unsubscribes") ?></a></li>
        <li><a data-toggle="tab" href="#tab-bounces"><?= $view->_("page.newsletter-track.details.bounces") ?></a></li>
      </ul>

      <div id="tabs-details" class="tab-content">
        <div id="tab-clicks" class="tab-pane active">
          <h3><?= $view->_("page.newsletter-track.details.clicks") ?></h3>
          <table width="100%" cellspacing="0" cellpadding="0" class="table"><tbody>
            <tr class="title"><th><?= $view->_("page.newsletter-track.details.Contact") ?></th><th width="20%"><?= $view->_("page.newsletter-track.details.Date") ?></th></tr>
<? foreach ($details['click'] as $line) { 
    $url=$line['exarg']?$line['exarg']:$line['arg'];
?>
            <tr class="trDataGridAltRow"><td class="smaller"><strong><?= empty($line['name'])?'':$line['name'].'<br />' ?></strong><?= preg_replace('#.*\<(.*)\>.*#','$1',$line['email']) ?><br /><a title="<?= $url ?>" href="<?= $url ?>"><?= text_limit($url,70) ?></a></td><td align="right" class="smaller"><?= $line['dt'] ?></td></tr>
<? } ?>
          </tbody></table>
        </div>
        <div id="tab-opens" class="tab-pane">
          <h3><?= $view->_("page.newsletter-track.details.opens") ?></h3>
          <table width="100%" cellspacing="0" cellpadding="0" class="table"><tbody>
            <tr class="title"><th><?= $view->_("page.newsletter-track.details.Contact") ?></th><th width="30%"><?= $view->_("page.newsletter-track.details.Date") ?></th></tr>
<? foreach ($details['track'] as $line) { ?>
            <tr class="trDataGridAltRow"><td class="smaller"><strong><?= empty($line['name'])?'':$line['name'].'<br />' ?></strong><?= preg_replace('#.*\<(.*)\>.*#','$1',$line['email']) ?></td><td align="right" class="smaller"><?= $line['dt'] ?></td></tr>
<? } ?>
          </tbody></table>
        </div>
        <div id="tab-unsubscribes" class="tab-pane">
          <h3><?= $view->_("page.newsletter-track.details.unsubscribes") ?></h3>
          <table width="100%" cellspacing="0" cellpadding="0" class="table"><tbody>
            <tr class="title"><th><?= $view->_("page.newsletter-track.details.Contact") ?></th><th width="30%"><?= $view->_("page.newsletter-track.details.Date") ?></th></tr>
<? foreach ($details['unsubscribe'] as $line) { ?>
            <tr class="trDataGridAltRow"><td class="smaller"><strong><?= empty($line['name'])?'':$line['name'].'<br />' ?></strong><?= preg_replace('#.*\<(.*)\>.*#','$1',$line['email']) ?></td><td align="right" class="smaller"><?= $line['dt'] ?></td></tr>
<? } ?>
          </tbody></table>
        </div>
        <div id="tab-bounces" class="tab-pane">
          <h3><?= $view->_("page.newsletter-track.details.bounces") ?></h3>
          <table width="100%" cellspacing="0" cellpadding="0" class="table"><tbody>
            <tr class="title"><th><?= $view->_("page.newsletter-track.details.Contact") ?></th><th width="30%"><?= $view->_("page.newsletter-track.details.Date") ?></th></tr>
<? foreach ($details['bounce'] as $line) { ?>
            <tr class="trDataGridAltRow"><td class="smaller"><strong><?= empty($line['name'])?'':$line['name'].'<br />' ?></strong><?= preg_replace('#.*\<(.*)\>.*#','$1',$line['email']) ?></td><td align="right" class="smaller"><?= $line['dt'] ?></td></tr>
<? } ?>
          </tbody></table>
        </div>
      </div>
    </td></tr>
  </tbody>
</table>

<hr />
<p><a href="/ccms/inline/NewsletterManager.html">&laquo; Back</a></p>

<?
   }
}
?>