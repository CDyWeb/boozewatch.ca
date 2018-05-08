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

abstract class FrontendBase {

  protected $frontend;

  public function __construct($frontend) {
    $this->frontend=$frontend;
  }
  
  public function getFrontend() {
    return $this->frontend;
  }

	public function tbl($name) {
		return tbl_name($name);
	}
	
	public function site_url($uri="") {
		return $this->frontend->getSiteURL().$uri;
	}
	
	public function thumb_img($name,$maxWidth,$maxHeight=0,$s="img",$dir=null) {
		return $this->_img("thumb",$name,$maxWidth,$maxHeight,$s,$dir);
	}

	public function crop_img($name,$maxWidth,$maxHeight=0,$s="img",$dir=null) {
		return $this->_img("crop",$name,$maxWidth,$maxHeight,$s,$dir);
	}

	public function _img($func,$name,$maxWidth,$maxHeight=0,$s="img",$dir=null) {
		require_once(getConfigItem('script_base').'/shared/cyane/site_media.inc.php');
		return img_url($func,$name,$maxWidth,$maxHeight,$s,$dir);
	}
	public function uri($append=null) {
		return $this->frontend->getRouter()->uri($append);
	}
	public function url($append=null) {
		return $this->frontend->getRouter()->url($append);
	}
  public function pageURL($id) {
    return $this->frontend->getPageURL($id);
  }
  
  public function htmlbase() {
    return getConfigItem('html_base_href');
  }
  public function fixRelativeLinks($html) {
    return $this->fixRelativeLinksInDir($html,$this->htmlbase());
  }
  public function fixRelativeLinksInDir($html,$dirname) {
    if (preg_match_all('#<(img|script|link|input)[^>]*((href|src)=([\'"])([^\'"]+)[\'"])#i',$html,$matches,PREG_SET_ORDER)) {
      foreach($matches as $match) {
        if ((substr($match[5],0,1)=='/') || (preg_match('#://#',$match[5]))) continue;
        $html=str_replace($match[2],$match[3].'='.$match[4].$dirname.$match[5].$match[4],$html);
      }
    }
    return $html;
  }

  
  public function appFindInc($dir,$name) {
    $fn=getConfigItem('script_app').$dir.'/'.$name;
    if (file_exists($fn)) {
      return $fn;
    }
    _log('FrontendBase::appFindInc not found: '.$fn);
    return false;
  }

}

//end