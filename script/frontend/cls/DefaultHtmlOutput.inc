<?php
/*
------------------------------------------------------------

  CyaneCMS

$LastChangedRevision: 130 $
$LastChangedDate: 2009-07-01 10:20:21 +0200 (wo, 01 jul 2009) $
$LastChangedBy: $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

class DefaultHtmlOutput extends HttpOutput {

  const HTML_TRANSITIONAL = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
  const XHTML_TRANSITIONAL = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
  const XHTML_STRICT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  
  const HTML5 = '<!DOCTYPE HTML>';

  protected $docType=self::XHTML_TRANSITIONAL;
  protected $contentType="text/html";
  
  protected $cookies=array();
  protected $extraHead=array();
  protected $extraBody=array();
  protected $replacements=array();
  protected $content="";
  
  public function __construct($frontend) {
    parent:: __construct($frontend);
    if (getConfigItem('html_output_type')=='XHTML_STRICT') $this->docType=self::XHTML_STRICT;
    if (getConfigItem('html_output_type')=='HTML5') $this->docType=self::HTML5;
  }

  public function getDocType() {
    return $this->docType;
  }
  public function setDocType($docType) {
    $this->docType=$docType;
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
    $this->setHeader("X-Meta-imagetoolbar: no",false);
    $this->setHeader("MSThemeCompatible: no",false);
  }

  public function addToHead($s) {
    $this->extraHead[]=$s;
  }
  public function addToBody($s) {
    $this->extraBody[]=$s;
  }

  public function addHeadMetaTag($name,$value) {
    $this->addToHead(sprintf('    <meta name="%s" content="%s" />',$name,$value));
  }

  public function addHeadLink($rel,$href) {
    $this->addToHead(sprintf('    <link rel="%s" href="%s" />',$rel,$href));
  }

  public function setReplacement($k,$v) {
    $this->replacements[$k]=$v;
  }
  
  /**
  * store cookie headers
  */
  //setcookie  ( string $name  [, string $value  [, int $expire= 0  [, string $path  [, string $domain  [, bool $secure= false  [, bool $httponly= false  ]]]]]] )
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
    if (isset($this->replace_settings)) {
      if (preg_match_all('#(\{|\%7B)setting:([\w.-]+)(\}|\%7D)#',$this->content,$matches,PREG_SET_ORDER)) {
        foreach ($matches as $match) {
          $this->content=str_replace($match[0],str_replace('||','<br />',setting($match[2])),$this->content);
        }
      }
    }
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

    #html tag
    $lang=$this->getLanguage();
    if ($this->docType==self::XHTML_TRANSITIONAL || $this->docType==self::XHTML_STRICT) {
      $this->content=preg_replace("#<html[^>]*>#","<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$lang}\">",$this->content);
    } else if ($this->docType==self::HTML5) {
      $this->content=preg_replace("#<html[^>]*>#","<html lang=\"{$lang}\">",$this->content);
    }

    #replacements
    $this->output_do_replacements();
    
    #mail riddler
    if (strpos($this->content,"base64.js")>0) $this->content = preg_replace_callback("#(<a[^>]*href=['\"]mailto:[^<]*</a>)#i",create_function(
      '$matches', 
      'return "<script> document.write(decodeBase64(\"".base64_encode($matches[1])."\")) </script>";' 
    ),$this->content);
    
    #output
    if (preg_match('#^.*<!DOCTYPE[^>]*>(.+)$#Usi',$output=trim($this->content),$match)) $output=$match[1];
    $output=$this->docType."\n".trim($output);
    
    if (preg_match_all('#(<meta[^>]*)></meta>#',$output,$matches,PREG_SET_ORDER)) {
      foreach ($matches as $match) $output=str_replace($match[0],$match[1].' />',$output);
    }
    if (preg_match_all('#(<link[^>]*)></link>#',$output,$matches,PREG_SET_ORDER)) {
      foreach ($matches as $match) $output=str_replace($match[0],$match[1].' />',$output);
    }

    if (count($this->extraHead)>0) {
      $output=preg_replace("#</head>#i",implode("\n",$this->extraHead)."\n</head>",$output);
    }
    if (count($this->extraBody)>0) {
      $output=preg_replace("#</body>#i",implode("\n",$this->extraBody)."\n</body>",$output);
    }

    echo $output;

    if ($truncate) $this->content="";

    _log(__FILE__ . " is done");
  }
}

//end