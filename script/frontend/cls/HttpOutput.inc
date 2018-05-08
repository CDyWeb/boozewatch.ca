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

abstract class HttpOutput extends FrontendBase implements Output {

	protected $charset="UTF-8"; //ISO-8859-1
	protected $headers=array();
	protected $redirected=false;
	
	abstract public function getContentType();
	
	public function ent($s) {
		return htmlentities($s,ENT_QUOTES,$this->charset);
	}
	
	public function isRedirected() {
		return $this->redirected;
	}

	public function getCharset() {
		return $this->charset;
	}
	public function setCharset($charset) {
		$this->charset=$charset;
	}

	public function setHeader($header,$overwrite=true,$responseCode=null) {
		$this->headers[]=array($header,$overwrite,$responseCode);
	}
  
  public function getHeaders() {
    return $this->headers;
  }
  public function setHeaders($array) {
    return $this->headers=$array;
  }

	function redirect($uri="", $method='location', $http_response_code=301) {
		if ((substr($uri,0,1)!="/") && !preg_match('#^https?://#i', $uri)) $uri = $this->site_url($uri);
		_log(get_class().":redirect {$uri}, {$method}, {$http_response_code}");
		switch($method) {
			case 'refresh'	: $this->setHeader("Refresh:0;url=".$uri); break;
			default			: $this->setHeader("Location: ".$uri, true, $http_response_code); break;
		}
		$this->redirected=true;
	}
  
  function seeOther($uri) {
    $this->redirect($uri,'location',303);
  }

	// @Override
	public function output($truncate=true) {

		#headers
		foreach ($this->headers as $arr) {
			if (!defined('UNIT_TESTING')) {
        header($arr[0],$arr[1],$arr[2]);
      }
			_log("header '".implode(', ', $arr)."' set.");
		}

	}

}