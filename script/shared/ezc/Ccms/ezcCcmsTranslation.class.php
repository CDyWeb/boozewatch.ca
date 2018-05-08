<?php

class ezcCcmsTranslation {

  public static function getInstance($context, $language, $refresh=false, $object_cache=null) {
    $object_cache_key=getConfigItem('domain').':ezcCcmsTranslation:'.$context.':'.$language;
    #--
    if (!defined('TRANSLATE_FILE_BASED') && !defined('TRANSLATE_DB_BASED') && !empty($object_cache)) {
      if ($refresh) {
         _log('ezcCcmsTranslation:refresh deletes cache '.$object_cache_key);
         $object_cache->delete($object_cache_key);
      } else {
        $fromCache=$object_cache->get($object_cache_key);
        if (!empty($fromCache)) {
          $fromCache->object_cache=$object_cache;
          $fromCache->object_cache_key=$object_cache_key;
          _log('ezcCcmsTranslation:returns from cache '.$object_cache_key);
          return $fromCache;
        }
        _log('ezcCcmsTranslation:not in cache '.$object_cache_key);
      }
    }
    #--
    $backend = new ezcCcmsTranslationBackend();
    $manager = new ezcCcmsTranslationManager($backend);
    $result = new ezcCcmsTranslation($manager, $language, $context);
    #--
    if (!defined('TRANSLATE_FILE_BASED') && !defined('TRANSLATE_DB_BASED') && !empty($object_cache)) {
      //$object_cache->set($object_cache_key,$result,0);
      $result->object_cache=$object_cache;
      $result->object_cache_key=$object_cache_key;
      $result->hasChanged=true;
    }
    #--
    return $result;
  }

  public $manager;
  private $wrapped;
  private $locale;
  
  public function __construct(ezcCcmsTranslationManager $manager, $locale, $context) {
    $this->manager=$manager;
    $this->locale=$locale;
    $this->context=$context;
    $this->wrapped=$manager->getContext($locale, $context);
  }
  
  public function __destruct() {
    if (!isset($this->hasChanged)) {
      _log('ezcCcmsTranslation:not changed');
      return;
    }
    if (!empty($this->object_cache) && !empty($this->object_cache_key)) {
      _log('ezcCcmsTranslation:writing to cache: '.$this->context.' '.$this->locale.' : '.$this->object_cache_key);
      $object_cache=$this->object_cache;
      $object_cache_key=$this->object_cache_key;
      unset($this->object_cache);
      unset($this->object_cache_key);
      unset($this->hasChanged);
      $object_cache->set($object_cache_key,$this,0);
    } else {
      _log('ezcCcmsTranslation:not writing to cache');
    }
  }

  public function getTranslation($key, $params=null, $default=null) {
    if (empty($params)) $params=array();
    else if (!is_array($params)) $params=array('firstparam'=>$params);
    try {
      try {
        return $this->wrapped->getTranslation($key, $params);
      } catch (ezcTranslationKeyNotAvailableException $ex) {
        $backend=$this->manager->getBackend();
        $value=$backend->ezcTranslationKeyNotAvailable($key, $this->locale, $this->context, $default);
        $this->wrapped->add($key,new ezcTranslationData($key,$value,null,'translated'));
        $this->hasChanged=true;
        return $this->wrapped->getTranslation($key, $params);
      }
    } catch (ezcTranslationParameterMissingException $ex) {
      return '%'.$key.'%';
    }
  }
  
  public function frontendTranslateById($tbl, $id, $fld, $default, $params=null) {
    return $this->frontendTranslate($tbl, $id, $fld, $default, $params, true);
  }
  public function frontendTranslateByFld($tbl, $id, $fld, $default, $params=null) {
    return $this->frontendTranslate($tbl, $id, $fld, $default, $params, false);
  }
  public function frontendTranslate($tbl, $id, $fld, $default, $params=null, $byId=true) {
    if (empty($params)) $params=array();
    $backend=$this->manager->getBackend();
    return $backend->frontendTranslate($this->locale, $tbl, $id, $fld, $default, $params, $byId);
  }
  
}

//end