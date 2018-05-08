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

class Page extends FrontendBase {

  private $arr;

  public function __construct(array $arr) {
    parent:: __construct(null);
    $this->arr=$arr;
    //_log(get_class().":construct ".str_replace("\n","",print_r($arr,true)));
  }

  public function __get($name) {
    if (isset($this->arr[$name])) $res=$this->arr[$name];
    else $res=null;
    //_log(get_class().":__get {$name} returns {$res}");
    return $res;
  }

  public function __set($name,$value) {
    //_log(get_class().":__set {$name} to {$value}");
    $this->arr[$name]=$value;
  }
  
  public function __toString() {
    return $this->name;
  }

  public function toArray() {
    return $this->arr;
  }
  
  public function translate($translator) {
    foreach (array('name','meta_title','meta_keywords','meta_description','uri','text') as $p) {
      $this->$p=$translator->frontendTranslate('page',$this->id,$p,$this->$p);
    }
  }

}

//end