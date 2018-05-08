<?

class CoreSizeGroupList extends GenericList {

	protected function getRowButtons(CCMSDomainManagerInterface $manager) {
		$res=parent::getRowButtons($manager);
		if ($manager->isAddable()) array_unshift($res,"duplicate");
		return $res;
	}
	
	protected function getRowButton($btnName,$line,$lineCount,$dataCount) {
		if ($btnName=='duplicate') {
			return 
"<td width='20'>
	<a href='{$this->uri($line)}?duplicate={$line["id"]}'>
		<img height='16' src='{$this->resources_url('/img/icon/duplicate.png')}' alt='' title='{$this->cmsTranslate("Duplicate")}' border='0' />
	</a>
</td>";
		}
		return parent::getRowButton($btnName,$line,$lineCount,$dataCount);
	}

}


//end