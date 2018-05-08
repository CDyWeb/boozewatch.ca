<?

#--
$r=CCMSManagedModel::getManager("NewsletterRecipientManager");
$r->bounceCheck();
#--

class CoreNewsletterGroupList extends GenericList {

	protected function tableRowAdd(&$tr,$manager) {
		if ($manager->isAddable()) {
			$tr[]="<tr class='trAdd'><td colspan='{$this->colCount}' align='right'><a href='{$this->uri(null)}?edit=0'>[+] {$this->domainTranslate("NewsletterGroup._Add")}</a></td></tr>";
		}
	}

	//@Override
	public function getListValue($manager,$fieldName,$line,$maxlength) {
		$result=parent::getListValue($manager,$fieldName,$line,$maxlength);
		if ($fieldName=='name') {
			if ($line['id']==-1) $result=$this->_('NewsletterGroup.null');
			if ($line['name']=='.Customers') $result=$this->_('NewsletterGroup'.$line['name']);
			$result="<a href='{$this->base_url()}/inline/NewsletterRecipientManager.html?NewsletterRecipient-newsletter_group={$line['id']}' style='color:blue;text-decoration:underline;line-height:2em;' title='".$this->domainTranslate('NewsletterRecipient')."'>{$result}</a>";
		}
		return "<span style='line-height:2em;'>{$result}</span>";
	}
  
  
}


//end