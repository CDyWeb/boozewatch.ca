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

class XmlOutput extends HttpOutput {

	private $contentType="text/xml";
	private $content="<xml/>";
	
	public static $cdata_wrap=false;

	public function append($str) {
		$this->appendCursor($str,false);
	}
	public function appendNode($name, array $children=null, array $attr=null, $cdata=null, $includeNewCursor=true) {
		$this->appendCursor(self::toXml($name,$children,$attr,$cdata),$includeNewCursor);
	}
	public static function toXml($name, array $children=null, array $attr=null, $cdata=null) {
		$str="<{$name}";
		if ($attr!==null) foreach ($attr as $k=>$v) $str.=' '.$k.'="'.$this->ent($v).'"';
		if ($children!==null) {
			$str.=">";
			foreach ($children as $tag=>$data) {
				if (is_string($data)) $data=array("children"=>null,"attr"=>null,"cdata"=>$data);
				$str.=self::toXml($tag,$data["children"],$data["attr"],$data["cdata"]);
			}
			$str.="</{$name}>";
		} else if (($cdata!==null) && (strlen($cdata)>0)) {
			if (!is_numeric($cdata) && self::$cdata_wrap) $cdata="<![CDATA[{$cdata}]]>";
			$str.=">{$cdata}</{$name}>";
		} else {
			$str.=" />";
		}
		return $str;
	}
	public function appendCursor($str,$includeNewCursor=true) {
		if ($includeNewCursor) $str.='<!--{cursor}-->';
		if (strpos($this->content,'<!--{cursor}-->')!==false) $this->content=str_replace("<!--{cursor}-->",$str,$this->content);
		else $this->content=$str;
	}
	public function getContentType() {
		return $this->contentType;
	}
	public function setContentType($contentType) {
		$this->contentType=$contentType;
	}
	
	public function xmlHeaders() {
		$this->setHeader("Content-Type: {$this->contentType}; charset=".strtolower($this->charset),false);
		$this->setHeader("Cache-Control: no-cache",false);
	}

	public function output($truncate=true) {
		$this->xmlHeaders();
		parent::output($truncate);
		echo "<?xml version=\"1.0\" encoding=\"{$this->charset}\"?>\n";
		echo str_replace("<!--{cursor}-->","",$this->content);
	}

}

//end