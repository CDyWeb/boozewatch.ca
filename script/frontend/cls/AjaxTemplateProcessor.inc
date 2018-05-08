<?php
/*
------------------------------------------------------------

	CyaneCMS

 Copyright (c) 2006-2012 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class AjaxTemplateProcessor extends FrontendBase implements TemplateProcessor {

  public $isAjax=true;
  
  public function _($key, $params=null) {
    return $this->frontend->_($key, $params);
  }
  
	public function getRouter() {
		return $this->frontend->getRouter();
	}
	
	public function getOutput() {
		return $this->frontend->getOutput();
	}

	protected function getMainContent() {
		if (!isset($this->mainContent)) switch ($this->page->page_type) {
			case "plugin" :
				ob_start();
				$fn="{$this->page->attributes}/plugin.inc";
				if (file_exists($app_pluginpath=getConfigItem('script_app').'plugins/'.$fn)) require $app_pluginpath;
				else require getConfigItem('script_base').'shared/plugins/'.$fn;
				$this->mainContent=ob_get_contents();
				ob_end_clean();
				break;

			case "menu" :
				$firstChild=$this->router->getMenuDefaultChild($this->page);
				if (!empty($firstChild)) {
					$this->output->redirect($this->pageURL($firstChild['id']));
				} else {
					$this->mainContent=$this->page->text;
				}
				break;

			case "link" :
				$this->output->redirect($this->page->attributes);
        break;

			default :
				$this->mainContent=$this->page->text;
		}
		if ($this->output->isRedirected()) return null;
		return $this->mainContent;
	}

	public function process(Router $router, $template, Output $output) {
		$this->output=$output;
		$this->router=$router;
		$this->page=$router->getPage();

    $this->getMainContent();
    if ($output->isRedirected()) return;
		$this->output->setContent($this->mainContent);
  }

}

