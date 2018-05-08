<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 108 $
$LastChangedDate: 2009-05-24 17:33:49 +0200 (zo, 24 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class StaticTranslator {

	static $instance=null;
	public static function getInstance() {
		if (empty(self::$instance)) self::$instance=new StaticTranslator();
		return self::$instance;
	}
	
	private $txt=array();

	private function __construct() {}
	
	protected function load_defaults($language) {
		$this->language_to_parse=$language;
		#--
		$arr=readdir_ls(dirname(__FILE__).'/../plugins');
		foreach ($arr as $path) if (is_dir($path) && file_exists($fn=$path."/lang/{$language}.php")) {
			require $fn;
		}
		//var_dump($this->txt);
		//die();
		#--
	}
	
	protected function parse($s) {
		foreach (explode("\n",$s) as $s) if ((strlen($s=trim($s))>0) && ($s[0]!="#")) {
			if (preg_match("#^([^=]+)=(.+)$#",$s,$match) && (!isset($this->txt[$this->language_to_parse][$match[1]]) || $this->txt[$this->language_to_parse][$match[1]]=="_{$this->language_to_parse}_{$match[1]}_")) {
				$this->txt[$this->language_to_parse][$match[1]]=$match[2];
			}
		}
	}
	
	public function translate($lang,$key,$args=null,$default=null,$insert=true) {
		if (empty($key) || $key===false) return '';
    if (empty($lang)) {
			$arr=getConfigItem("language");
			$lang=$arr["default"];
		}
		
		$tbl_name=tbl_name("static_translate");
		if (!isset($this->txt[$lang])) {
			$res=null;
			try { $res=getTableArray("describe `{$tbl_name}`","Field"); } catch (MySqlException $ex) { if ($ex->error!=1146) throw ex;	}
			if (!$res) executeSql("create table `{$tbl_name}` (`lang` char(5) NOT NULL,`key` varchar(255) NOT NULL,`value` TEXT NOT NULL, PRIMARY KEY  (`lang`,`key`)) ENGINE=InnoDB");
			$this->txt[$lang]=getListTableArray("select `key`,`value` from `{$tbl_name}` where lang='{$lang}'");
		}
		$default_code="_{$lang}_{$key}_";
		if ($default===null) $default=$default_code;
		if (!isset($this->txt[$lang][$key]) || ($this->txt[$lang][$key]==$default_code)) {
			$this->load_defaults($lang);
		}
		if (!isset($this->txt[$lang][$key])) {
			if ($insert) executeSql("insert into `{$tbl_name}` set `lang`='{$lang}', `key`='{$key}', `value`='".db_escape($default)."'");
			$this->txt[$lang][$key]=$default;
		}
		$res=$this->txt[$lang][$key];
		if ($args!==null) {
			if (!is_array($args)) $args=array($args);
			foreach ($args as $i=>$token) $res=str_replace('{'.$i.'}',$token,$res);
		}
		if (preg_match_all("#\{site_config.([^\}]+)\}#",$res,$matches,PREG_SET_ORDER)) {
			global $site_config;
			foreach($matches as $match) $res=str_replace($match[0],@$site_config[$match[1]],$res);
		}
		return $res;
	}

}

function static_translate($lang,$key,$args=null,$default=null,$insert=true) {
	return StaticTranslator::getInstance()->translate($lang,$key,$args,$default,$insert);
}


//end












