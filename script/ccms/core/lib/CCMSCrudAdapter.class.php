<?php

#--

if (!in_array("GenericEditor", get_declared_classes())) {
  if (getResourcePath("view/CustomGenericEditor.class.php")!==false) {
    class GenericEditor extends CustomGenericEditor {
    }
  } else {
    class GenericEditor extends CoreGenericEditor {
    }
  }
}
if (!in_array("GenericList", get_declared_classes())) {
  if (getResourcePath("view/CustomGenericList.class.php")!==false) {
    class GenericList extends CustomGenericList {
    }
  } else {
    class GenericList extends CoreGenericList {
    }
  }
}

#--

class CCMSCrudAdapter {

  protected $controller;

  public function __construct(CCMSController $controller) {
    $this->controller=$controller;
  }
  
  public function getCls() {
    if (empty($this->cls)) throw new Exception('cls not defined');
    return $this->cls;
  }
  public function setCls($cls) {
    $this->cls=$cls;
  }

  public function getModel() {
    if (empty($this->model)) {
      $cls=$this->getCls();
      $this->model=new CCMSManagedModel("{$cls}Manager");
    }
    return $this->model;
  }
  public function setModel($model) {
    $this->model=$model;
  }

  public function crud($cls) {
    log_message("trace",get_class($this).":crud {$cls}");
    $this->setCls($cls);
    $model=$this->getModel();
    $this->controller->setModel($model);

    if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["__save"])) {
      log_message("trace",get_class($this).":crud - post save");
      $err=array();
      if (preg_match('#^d:(\d+)$#',$_POST["__save"],$match)) {
        $_POST["__save"]=0;
        $this->controller->getModel()->duplicateFrom($match[1]);
      }
      if (preg_match('#^many:(.+)$#',$_POST["__save"],$match)) {
        if ($match[1]=='all') {
          $ids=array();
          if (isset($_SESSION['edit.many.where'])) $ids=array_keys($this->controller->getModel()->getAllExt($_SESSION['edit.many.where'],null,'id','id'));
        } else {
          $ids=explode(',',$match[1]);
        }
        $res=$this->save($ids,$err);
        if ($res) $_SESSION['crud.flash']=array('Crud.updated.many',$cls);
      } else {
        $res=$this->save(intval($_POST["__save"]),$err);
        if ($res) {
          $itemName=$this->controller->getModel()->getItemName($res);
          if (empty($itemName)) $itemName='item '.$res;
          if (intval($_POST["__save"])) $_SESSION['crud.flash']=array('Crud.updated',$cls,$itemName);
          else $_SESSION['crud.flash']=array('Crud.added',$cls,$itemName);
        }
      }
      if ($res) {
        log_message("trace",get_class($this).":crud - post save ".(isset($_GET["edit"])?$_GET["edit"]:$res)." ok");
        $r=$_SERVER["_URI"];
        if (!empty($_POST['__redirect'])) $r=str_replace(array('__editor','__id'),array($_SERVER['_URI'].'?edit='.$res,$res),$_REQUEST['__redirect']);
        $this->controller->setRedirect($r); //.'#top');
        return;
      } else {
        log_message("trace",get_class($this).":crud - post save not ok, edit {$_POST["__save"]} again. err=".print_r($err,true));
        $_GET["edit"]=$_POST["__save"];
      }
    }
    
    if (isset($_GET["delete"])) {
      log_message("trace",get_class($this).":crud - get delete {$_GET["delete"]}");
      unset($_SESSION['crud.delete.err']);
      $m=$this->controller->getModel();
      if ($_GET["delete"]=='many') {
        $ids=isset($_GET['_cb'])?$_GET['_cb']:null;
        if (is_array($ids) && !empty($ids) && $m->canDelete($_SESSION['user'],$ids)) {
          $_SESSION['crud.delete.err']=$m->delete($ids);
          if (empty($_SESSION['crud.delete.err'])) $_SESSION['crud.flash']=array('Crud.deleted.many',$cls);
        }
      } else if ($m->canDelete($_SESSION['user'],$_GET["delete"])) {
        $itemName=$m->getItemName($_GET["delete"]);
        $_SESSION['crud.delete.err']=$m->delete(intval($_GET["delete"]));
        if (empty($_SESSION['crud.delete.err'])) $_SESSION['crud.flash']=array('Crud.deleted',$cls,$itemName);
      }
      $this->controller->setRedirect(str_replace("delete={$_GET["delete"]}","",$_SERVER["_URI"]));
      return;
    }
    
    if (isset($_GET["export"])) {
      $ids=isset($_REQUEST['_cb'])?$_REQUEST['_cb']:null;
      if (!is_array($ids) && strlen($ids)) $ids=explode(',',$ids);
      if (is_array($ids) && !empty($ids)) {
        $options=array();
        if (isset($_REQUEST['options'])) $options=json_decode($_REQUEST['options'],true);
        $this->controller->getModel()->export($_REQUEST["export"],$ids,$options);
      }
    }

    if (isset($_GET["up"])) {
      log_message("trace",get_class($this).":crud - get up {$_GET["up"]}");
      $this->controller->getModel()->up(intval($_GET["up"]));
      $this->controller->setRedirect(str_replace("up={$_GET["up"]}","",$_SERVER["_URI"]));
      return;
    }
    
    if (isset($_GET["down"])) {
      log_message("trace",get_class($this).":crud - get down {$_GET["down"]}");
      $this->controller->getModel()->down(intval($_GET["down"]));
      $this->controller->setRedirect(str_replace("down={$_GET["down"]}","",$_SERVER["_URI"]));
      return;
    }
    
    if (isset($_GET["duplicate"])) {
      log_message("trace",get_class($this).":crud - get duplicate {$_GET["duplicate"]}");

      $this->editorView($cls);
      $id=intval($_GET["duplicate"]);

      $line=$this->controller->getModel()->get($id);
      if (!$line) throw new Exception("cannot duplicate {$_GET["duplicate"]} - model returns no record");
      $name=$this->controller->getModel()->getItemName($line);
      $this->controller->getView()->setLine($line);
      $this->controller->getView()->setDuplicating(true);

      $this->controller->getView()->addToPagepath("<a href='".getRequestURI()."'>{$this->controller->getPagepathName($cls)}</a>");
      $this->controller->getView()->addToPagepath("<a href='".getRequestURI()."?duplicate={$_GET["duplicate"]}'>".$this->controller->getView()->_("Duplicate")." : {$name}</a>");
      return;
    }

    if (isset($_GET["edit"])) {
      log_message("trace",get_class($this).":crud - get edit {$_GET["edit"]}");
      $edit_url=getRequestURI()."?edit={$_GET["edit"]}";
      $this->editorView($cls);
      
      if ($_GET["edit"]=='many') {
        if (isset($_GET['all']) && isset($_SESSION['edit.many.where'])) {
          $edit_url.='&all';
          $group=$this->controller->getModel()->getAllExt($_SESSION['edit.many.where'],null,null,'id');
        } else {
          $ids=isset($_GET['_cb'])?$_GET['_cb']:null;
          if (isset($_GET['ids'])) $ids=explode(',',$_GET['ids']);
          $group=array();
          if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
              $line=$this->controller->getModel()->get(intval($id));
              if (empty($line)) throw new Exception("cannot edit {$id} - model returns no record");
              $group[$line['id']]=$line;
            }
            $edit_url.="&ids=".implode(',',array_keys($group));
          }
          if (count($group)==1) {
            $this->controller->setRedirect($_SERVER["_URI"]."?edit=".$id);
            return;
          }
        }
        if (!empty($group)) {
          $this->controller->getView()->setGroup($group);
          $caption=$this->controller->getView()->_("EditMany").' ('.count($group).')';
        } else {
          throw new Exception("cannot edit many - no group");
        }
      } else {
        $id=intval($_GET["edit"]);
        if ($id>0) {
          $line=$this->controller->getModel()->get($id);
          if (!$line) throw new Exception("cannot edit {$_GET["edit"]} - model returns no record");
          $name=$this->controller->getModel()->getItemName($line);
          $this->controller->getView()->setLine($line);
          $caption=$this->controller->getView()->_("Edit")." : {$name}";
        } else {
          $this->controller->getView()->setId($id);
          $caption=$this->controller->getView()->_("Add");
          if ($id<0) $caption=$this->controller->getView()->domainTranslate("{$cls}.edit.{$id}");
        }
      }

      $this->controller->getView()->addToPagepath("<a href='".getRequestURI()."'>{$this->controller->getPagepathName($cls)}</a>");
      $this->controller->getView()->addToPagepath("<a href='{$edit_url}'>{$caption}</a>");
      return;
    }

    $this->listView($cls);

    if (method_exists($this->controller->getModel()->getDomainManager(),'getPagepathName')) $n=$this->controller->getModel()->getDomainManager()->getPagepathName();
    else $n=$this->controller->getPagepathName($cls);
    $this->controller->getView()->addToPagepath("<a href='".getRequestURI()."'>{$n}</a>");
  }
  
  protected function fetchSaveData($id, array &$err) {
    $manager = $this->controller->getModel()->getDomainManager();
    return $manager->fetchPostData($id, $err);
  }

  protected function save($id, array &$err) {
    $data = $this->fetchSaveData($id, $err);
    if (count($err)>0) {
      log_message("debug",get_class($this).":save {$id} - error - ".print_r($err,true));
      return false;
    }
    log_message("debug",get_class($this).":save {$id} passing the data to the model");
    $m=$this->controller->getModel();
    if (is_array($id)) {
      foreach ($id as $i) if (!$m->canSave($_SESSION['user'],$i,$data) || !$m->save($i,$data,$err)) return false;
      return true;
    }
    if ($m->canSave($_SESSION['user'],$id,$data)) return $m->save($id,$data,$err);
    return false;
  }
  
  public function getViewClass($fcls,$gcls) {
    log_message("trace",get_class($this).":initView {$fcls}, {$gcls}");
    $viewClass="Custom{$fcls}";
    if (getResourcePath("view/domain/{$viewClass}.class.php")===false) {
      $viewClass="Core{$fcls}";
      if (getResourcePath("view/domain/{$viewClass}.class.php")===false) {
        $viewClass="Custom{$gcls}";
        if (getResourcePath("view/{$viewClass}.class.php")===false) {
          $viewClass="Core{$gcls}";
        }
      }
    }
    return $viewClass;
  }
  
  protected function initView($fcls,$gcls) {
    $viewClass=$this->getViewClass($fcls,$gcls);
    log_message("trace",get_class($this).":initView, viewClass = {$viewClass}");
    $this->controller->setView(new $viewClass($this->controller->getModel()));
  }

  protected function editorView($cls) {
    log_message("trace",get_class($this).":editorView {$cls}");
    $this->initView("{$cls}Editor","GenericEditor");
  }
  protected function listView($cls) {
    log_message("trace",get_class($this).":listView {$cls}");
    $this->initView("{$cls}List","GenericList");
  }
  
}

//end