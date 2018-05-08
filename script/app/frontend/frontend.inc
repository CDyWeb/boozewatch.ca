<?

class MyProcessor extends DefaultTemplateProcessor {

	public $mainContentId='content';
	public $mainContentWrapper=null;

	//@Override
	protected function getMainContentNode($body) {
		if (isset($this->simplePage)) return parent::getMainContentNode($body);
		return $this->getDom()->getElementById($this->mainContentId);
	}
	
	//@Override
	protected function mainContentWrap($txt) {
		if (isset($this->simplePage)) return $txt;
    $txt=$this->fixRelativeLinksInDir($txt,'http://'.$_SERVER['HTTP_HOST'].'/');
    return $txt;
	}

	//@Override
	protected function getMenuNode($line) {
		switch($line['id']) {
			case 1001 :
				return $this->getDom()->getElementById('ul-navigation');
//			case 1002 :
//				return $this->getDom()->getElementById('primary');
		}
		return null;
	}

	//@Override
	protected function makeUpMenu($line,$node) {
		$root=$this->router->getRootPage($this->page->toArray());
    switch($line['id']) {
      case 1001 : 
/**/
				$this->emptyElement($node);
        $arr=$this->getPageTree($line['id']);
        $selected=null;
        $breadcrumbs='';
				foreach ($arr as $line) {
					$cls='';
          if ($line['page_type']=='link') $link=$line['attributes'];
          else $link=$this->pageURL($line['id']);
          if ($line['id']==$root['id']) {
            $cls='selected';
            $selected=$line;
            //$breadcrumbs='<a href="'.$link.'">'.$this->output->ent($line['name']).'</a>';
          }
					$li=$this->appendElement($node,'li',null,array());
					$ahref=$this->appendElement($li,'a',$this->output->ent($line['name']),array('class'=>$cls,'href'=>$link));
          if (($line['id']==$root['id']) && !empty($line['_children'])) {
            $ul=$this->appendElement($li,'ul',null,array());
            foreach ($line['_children'] as $child) {
              $cls='';
              $link=$this->pageURL($child['id']);
              $li=$this->appendElement($ul,'li',null,array());
              if ($child['id']==$this->page->id) {
                $cls='selected';
                //$breadcrumbs='<a href="'.$link.'">'.$this->output->ent($line['name']).'</a>';
              }
              $ahref=$this->appendElement($li,'a',$this->output->ent($child['name']),array('class'=>$cls,'href'=>$link));
            }
          }
        }
				break;
    }
		return null;
	}

	//@Override
	protected function makeUpBody($body) {
		#content
		$this->makeUpMainContent($body);
		if ($this->output->isRedirected() || isset($this->simplePage)) return;

		#menu's
		$this->makeUpMenus($body);
    
    #%Y
    $this->output->setReplacement('{%Y}',date('Y'));
	}
	
	//@Override
	protected function makeUpHead($head) {
    parent::makeUpHead($head);
		if (isset($this->simplePage)) return;
	}
}

class CustomFrontend extends Frontend {

	public function getTemplateProcessor() {
		if ($this->templateProcessor==null) $this->templateProcessor=new MyProcessor($this);
		return $this->templateProcessor;
	}

}

$frontend_cls = "CustomFrontend";

//end