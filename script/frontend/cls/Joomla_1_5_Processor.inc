<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class JURI {
	public static function getInstance() { return new JURI(); }
}

class JRequest {
	public static function getInstance() { return new JRequest(); }
	public static function getCmd() { return ""; }
	public static function getInt() { return 0; }
}

class JText {
	public static function getInstance() { return new JURI(); }
	public static function _($name) { return $name; }
}
class HashTable {
	public function toArray() { return array(); }
	public function get($key) { return ""; }
}

class Joomla_1_5_Processor extends DefaultTemplateProcessor {

	#-- joomla functions --
	public function countModules($position) {
		return 0;
	}
	public function getBuffer($name) {
		return false;
	}
	#--

	protected function mainContentWrap($txt) {
		return "<div style='min-height:200px'>{$txt}</div>";
	}

	public function getMyContent() {
		return $this->getMainContent();
	}
	
	protected function makeUpHtml() {
		#--
		define("_JEXEC",true);
		define("DS","");
		$this->language=$this->getFrontend()->getLanguage();
		$this->baseurl=preg_replace("#/$#","",$this->frontend->getSiteURL());
		$this->template=getConfigItem("joomla_template");
		$this->direction="ltr";
		$this->params=new HashTable();
		#--

		$this->theme_dir=getConfigItem("html_template_dir");
		ob_start();
		require "{$this->theme_dir}/index.php";
		$this->html=ob_get_contents();
		ob_end_clean();
		
		#--
		$this->html=str_replace($this->baseurl."/templates/",getConfigItem("joomla_template_href"),$this->html);
		#--
		
		$this->html=preg_replace('#<meta name="generator" content=".*" />#Usi','<meta name="generator" content="CCMS - WPThemeProcessor v.'.date("Y-m-d",filemtime(__FILE__)).'" />',$this->html);
	}

	
	public function process(Router $router, $template, Output $output) {
		_log(get_class().":process ".get_class($router)." ".mb_strlen($template)." ".get_class($output));

		global $joomla;
		$joomla=$this;

		$this->router=$router;
		$this->page=$router->getPage();
		if (!$this->page) {
			_log("no page to process");
			return;
		}
		$this->output=$output;
		$this->html="";

		#--
		$this->makeUpHtml();
		if ($output->isRedirected()) return;
		#--

		$output->append($this->html);
	}

}

function getJoomla() {
	global $joomla;
	return $joomla;
}
