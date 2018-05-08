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

class PlainOutput extends HttpOutput {

  protected $contentType="text/html";
  
  protected $cookies=array();
  protected $replacements=array();
  protected $content="";
  
  public function __construct($frontend) {
    parent:: __construct($frontend);
  }

  public function getContentType() {
    return $this->contentType;
  }
  public function setContentType($contentType) {
    $this->contentType=$contentType;
  }
  
  public function getLanguage() {
    return $this->frontend->getLanguage();
  }
  
  public function htmlHeaders() {
    $lang=$this->getLanguage();
    $this->setHeader("Content-Type: {$this->contentType}; charset=".strtolower($this->charset),false);
    $this->setHeader("Content-Language: {$lang}",false);
    $this->setHeader("Cache-Control: no-cache",false);
  }

  public function setReplacement($k,$v) {
    $this->replacements[$k]=$v;
  }
  
  function setCookie($arr) {
    if (!is_array($arr)) $arr = func_get_args();
    $this->cookies[]=$arr;
  }

  public function append($str) {
    $this->content.=$str;
  }

  public function setContent($str) {
    $this->content=$str;
  }
  
  protected function output_do_replacements() {
    foreach (array_keys($this->replacements) as $k) {
      $this->content=str_replace($k,$this->replacements[$k],$this->content);
    }
  }
  
  public function addToHead($s) {
    //noop
  }

  // @Override
  public function output($truncate=true) {
    _log(__FILE__ . " output {$truncate} > l=".strlen($this->content));

    $this->htmlHeaders();
    parent::output($truncate);
    
    #close session
    session_write_close();

    #cookies
    foreach ($this->cookies as $cookie) {
      if (!headers_sent()) {
        call_user_func_array ( 'setcookie' , $cookie );
        _log("cookie '".implode(', ', $cookie)."' set.");
      } else {
        _log("** NO cookie '".implode(', ', $cookie)."' set.");
      }
    }

    if ($this->isRedirected()) {
      _log("I am redirected, no output!");
      return;
    }

    #replacements
    $this->output_do_replacements();
    
    #output
    $output=trim($this->content);
    echo $output;

    if ($truncate) $this->content="";

    _log(__FILE__ . " is done");
  }
}

//end