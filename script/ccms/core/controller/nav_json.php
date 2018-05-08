<?php

class Nav_jsonController extends CCMSController {

	public function __construct($param) {
		$root=intval(preg_replace("#[^\d]#","",$param));
		$model=NavModel::getInstance();
		$view=new JsonView("",$model);
		$view->root=$root;
		parent::__construct($view,$model);
	}
}

class JsonView extends CCMSDefaultView {

	public $root=0;
	
	protected function recursiveItems($pid,$data,&$items) {
		if (isset($data[$pid])) foreach ($data[$pid] as $node) {
		
			if (!$node["active"]) continue;

			$name=$node["name"];
			if (substr($name,0,1)==":") $name=$this->domainTranslate(substr($name,1),"_title");
			if (substr($name,0,1)==".") $name=$this->domainTranslate("Tree".$name);
			//$name=utf8_ent($name);
			
			if (!isset($node["url"])) {
				if ($node["page"]) $node["url"]="page/{$node["id"]}/".getPermalinkName($node["name"]).".html";
        else if ($node["class"]) $node["url"]="body/{$node["id"]}/".getPermalinkName($node["name"]).".html";
				else $node["url"]="javascript:;";
			}
			
			$res = array(
				'data'=>array(
          'title'=>$name,
          'attributes'=>array('href'=>$node["url"],'target'=>'_blank'),
        )
			);

			$children=array();
			$this->recursiveItems($node["id"],$data,$children);
			if (count($children)>0) $res["children"]=$children;

			$items[] = $res;
		}
	}
	
	public function render() {
		$data=$this->getModel()->getNavTree();
		
		$items=array();
		$this->recursiveItems($this->root,$data,$items);
		
		header("Content-Type: text/javascript");
		echo json_encode($items)."\n";
		return;
		
	}
}

//end