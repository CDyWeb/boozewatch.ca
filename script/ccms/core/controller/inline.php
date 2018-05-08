<?php

require "body.php";

class InlineController extends BodyController {

  public function getPagepathName($cls) {
    $cls=preg_replace("#^Plugin_(\w+)_#i","",$cls);
    return "{$this->view->domainTranslate($cls."._title")}";
  }
  
  protected function sessionHasClassAccess($page) {
    switch ($_SESSION['user']['user_type']) {
    case 'super' : 
    case 'editor' :
      break;
      default :
      $user_access=explode(',',getConfigItem('user_class_access',SettingsManager::setting('user_class_access','')));
      if (!in_array($page,$user_access)) return false;
    }
    return true;
  }

  //@Override
  public function handleRequest() {
    log_message("trace",get_class($this).":handleRequest {$this->url_param}");
    
    if (!preg_match("#/(\w+)Manager#",$this->url_param,$match)) {
      $page="welcome";
      if (preg_match("#/(.+)$#",$this->url_param,$match)) $page=$match[1];
      
      log_message("trace",get_class($this).":handleRequest, page = {$page}");
      if (!$this->sessionHasPageAccess($page)) throw new Exception('Access denied');

      $this->view->setPage("{$page}.php");
      $this->view->addToPagepath("<a href='".getRequestURI()."'>{$page}</a>");
      $this->view->setInline(true);
      return;
    }

    $cls=$match[1];
    log_message("trace",get_class($this).":handleRequest, cls = {$cls}");
    if (!$this->sessionHasClassAccess($cls)) throw new Exception('Access denied');

    if (!$cls) {
      throw new Exception("cls is not viewable");
    }

    $this->crud->crud($cls);
    $this->view->setInline(true);
  }

}

//end