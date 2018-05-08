<?

class CoreNewsletterList extends GenericList {

  public $withCheckboxes=true;

	protected function getRowButtons(CCMSDomainManagerInterface $manager) {
		$res=parent::getRowButtons($manager);
		if ($this->getManager()->isAddable()) array_unshift($res,"duplicate");
    $res[]="send";
    $res[]="track";
		return $res;
	}
	
	protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
		if ($btnName=='send') {
			return 
"<td width='25'>
	<a href='{$this->base_url()}/inline/newsletter.html?send={$line['id']}'>
		<img height='16' src='{$this->resources_url('/img/icon/mail.png')}' alt='' title='{$this->domainTranslate("Newsletter._send")}' border='0' />
	</a>
</td>";
		}
    if ($btnName=='track') {
      if (empty($line['dt_sent'])) {
        return '<td></td>';
      } else {
        return
"<td width='25'>
  <a href='{$this->base_url()}/inline/newsletter.html?track={$line['id']}'>
    <img height='16' src='{$this->resources_url('/img/icon/chart.png')}' alt='' title='{$this->domainTranslate("Newsletter._track")}' border='0' />
  </a>
</td>";
      }
    }
    if ($btnName=='duplicate') {
			return 
"<td width='25'>
	<a href='{$this->uri($line)}?duplicate={$line["id"]}'>
		<img height='16' src='{$this->resources_url('/img/icon/duplicate.png')}' alt='' title='{$this->cmsTranslate("Duplicate")}' border='0' />
	</a>
</td>";
		}
		return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
	}
  
  protected function trFootCheckboxItems($url) {
    $result=parent::trFootCheckboxItems($url);
    unset($result['e']);
    unset($result['d']);
    #--
    $result['xls']=
<<<HTML
<td width='35' align='center'>
<a href='{$this->uri(null)}' onclick="var ids=jq('#body-{$this->tableId} INPUT').serialize(); if (ids=='') return false; this.href='{$this->base_url()}/inline/newsletter.html?compare&'+ids; return true;">{$this->cmsTranslate("newsletter.compare.link.caption")}</a>
</td>
HTML;
    #--
    return $result;
  }
  


}


//end