<?

class CoreNewsletterRecipientList extends GenericList {

	protected function tableRowAdd(&$tr,$manager) {
		if ($manager->isAddable()) {
			$tr[]="<tr class='trAdd'><td colspan='{$this->colCount}' align='right'><a href='{$this->uri(null)}?edit=0'>[+] {$this->domainTranslate("NewsletterRecipient._Add")}</a></td></tr>";
		}
	}

  public function outputPagepathDiv() {
    $m=$this->getManager();
    $n=$m->getGroupName();
    if (empty($n)) $n=$this->_('NewsletterGroup.null');
    echo '
<div id="pagepath">
  <a target="_parent" href="'.$this->base_url().'/body.html">CCMS</a>
  &raquo;
  <a target="_parent" href="'.$this->base_url().'/page/newsletter.html">'.$this->domainTranslate('Tree.Newsletter').'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterGroupManager.html">'.$this->domainTranslate('NewsletterGroup._title').'</a>
  &raquo;
  <a href="'.$this->base_url().'/inline/NewsletterRecipientManager.html">'.$this->domainTranslate('NewsletterRecipient._title').' : '.($n).'</a>
</div>
';
  }

}


//end