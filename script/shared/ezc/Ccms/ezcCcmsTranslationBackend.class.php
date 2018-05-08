<?php

//INSERT INTO `ccms_translation` (`context`, `lang`, `key`, `value`, `status`, `comment`) VALUES ('ccms', 'nl_NL', 'Login', 'Login', 'translated', NULL);

class ezcCcmsTranslationBackend implements ezcTranslationBackend {

  private $status_lookup=array(
    'translated'=>ezcTranslationData::TRANSLATED,
    'unfinished'=>ezcTranslationData::UNFINISHED,
    'obsolete'=>ezcTranslationData::OBSOLETE,
  );


  public function setOptions( $configurationData ) {
    //noop
  }
  
  protected function parseLangFile($locale, $context, $fn) {
    if (!file_exists($fn)) return;
    foreach (file($fn) as $s) {
      if (preg_match("#^([^=]*)=(.*)$#",trim($s),$match)) {
        $this->filebase[$locale][$context][$match[1]]=$match[2];
      }
    }
  }
  
  protected function getFileBase($locale, $context) {
    $lang=strtolower(preg_replace('#_.*$#','',$locale));
    if (!isset($this->filebase[$locale][$context])) {
      $this->filebase[$locale][$context]=array();
      $script_base=getConfigItem('script_base');
      switch ($context) {
      case 'ccms' :
        $this->parseLangFile($locale, $context, $script_base.'ccms/core/lang/'.$lang.'/system_txt.php');
        $this->parseLangFile($locale, $context, $script_base.'ccms/custom/lang/'.$lang.'/system_txt.php');
        #--
        $arr=readdir_ls($script_base.'shared/plugins');
        if (file_exists($pn=getConfigItem('script_app').'plugins')) $arr=array_merge(readdir_ls($pn),$arr);
        foreach ($arr as $fn=>$path) {
          if ($fn=="_base") continue;
          $this->parseLangFile($locale, $context, $path.'/lang/'.$lang.'/system_txt.php');
        }
        #--
        if (file_exists( $script_base.'app/lang/'.$lang.'/system_txt.php')) {
          $this->parseLangFile($locale, $context, $script_base.'app/lang/'.$lang.'/system_txt.php');
        }
        #--
        break;

      case 'domain' :
        $this->parseLangFile($locale, $context, $script_base.'ccms/core/lang/'.$lang.'/domain_txt.php');
        $this->parseLangFile($locale, $context, $script_base.'ccms/custom/lang/'.$lang.'/domain_txt.php');
        #--
        $arr=readdir_ls($script_base.'shared/plugins');
        if (file_exists($pn=getConfigItem('script_app').'plugins')) $arr=array_merge(readdir_ls($pn),$arr);
        foreach ($arr as $fn=>$path) {
          if ($fn=="_base") continue;
          $this->parseLangFile($locale, $context, $path.'/lang/'.$lang.'/domain_txt.php');
        }
        #--
        if (file_exists( $script_base.'app/lang/'.$lang.'/domain_txt.php')) {
          $this->parseLangFile($locale, $context, $script_base.'app/lang/'.$lang.'/domain_txt.php');
        }
        #--
        break;

      case 'static' :
        $this->parseLangFile($locale, $context, $script_base.'frontend/lang/'.$lang.'/static_txt.php');
        #--
        $arr=readdir_ls($script_base.'shared/plugins');
        if (file_exists($pn=getConfigItem('script_app').'plugins')) $arr=array_merge(readdir_ls($pn),$arr);
        foreach ($arr as $fn=>$path) {
          if ($fn=="_base") continue;
          $this->parseLangFile($locale, $context, $path.'/lang/'.$lang.'/static_txt.php');
        }
        #--
        if (file_exists( $script_base.'app/lang/'.$lang.'/static_txt.php')) {
          $this->parseLangFile($locale, $context, $script_base.'app/lang/'.$lang.'/static_txt.php');
        }
        #--
        break;

      case 'frontend' :
      case 'contents' :
      default:
        break;
      }
    }
    return $this->filebase[$locale][$context];
  }

  public function getContext($locale, $context) {
    #--
    if (!defined('TRANSLATE_DB_BASED')) switch ($context) {
      case 'ccms':
      case 'domain':
      case 'static':
        $result=array();
        $base=$this->getFileBase($locale, $context);
        foreach ($base as $key=>$value) {
          $result[]=new ezcTranslationData($key,$value,'',ezcTranslationData::TRANSLATED);
        }
        return $result;
    }

    /**/
    #--
    try { $meta=getTableArray('describe '.DATABASE_PREFIX.'translation','Field'); } catch (MySqlException $ex) {
      if ($ex->error==1146) { //Table 'translation' doesn't exist
        executeSql("create table ".DATABASE_PREFIX."translation (`context` ENUM('ccms','domain','frontend','contents') NOT NULL default 'contents', `lang` varchar(5) NOT NULL DEFAULT '', `key` varchar(255) NOT NULL DEFAULT '', `value` text NOT NULL, `status` enum('translated','unfinished','obsolete') NOT NULL default 'translated', `comment` text NULL,  PRIMARY KEY ( `context` , `lang` , `key` )) ENGINE=InnoDB");
        $meta=getTableArray('describe '.DATABASE_PREFIX.'translation','Field');
      } else throw $ex;
    }
    $meta_context=$meta['context'];
    if (empty($meta_context)) throw new Exception('meta_context not found');
    if (!preg_match("#'".preg_quote($context)."'#",$meta_context['Type'])) {
      $meta_context['Type']=str_replace("enum(","enum('".db_escape($context)."',",$meta_context['Type']);
      executeSql("alter table ".DATABASE_PREFIX."translation  CHANGE `context` `context` {$meta_context['Type']} NOT NULL");
    }
    if (($context=='contents') && !isset($meta['tblid'])) {
      executeSql('ALTER TABLE `'.DATABASE_PREFIX.'translation` ADD `tblid` VARCHAR(255) NULL default NULL AFTER `key`, ADD `tblfld` VARCHAR(255) NULL default NULL AFTER `tblid`');
      executeSql('ALTER TABLE `'.DATABASE_PREFIX.'translation` ADD INDEX ( `tblid` ( 100 ), `tblfld` ( 100 ))');
    }
    #--
    if ($context=='contents') return array();
    #--
    $arr = getTableArray('select * from '.DATABASE_PREFIX.'translation where `lang`='.dbStr($locale).' and `context`='.dbStr($context));
    $result=array();
    foreach ($arr as $i=>$line) {
      //$original, $translation, $comment, $status, $filename = null, $line = null, $column = null;
      if ($line['status']=='unfinished') {
        $base=$this->getFileBase($locale, $context);
        if (isset($base[$line['key']])) {
          $line['status']='translated';
          $line['value']=$base[$line['key']];
          $this->insert($line['key'], $line['value'], $locale, $context);
        }
      }
      //not the best place to do this?
      #$line['value'] = preg_replace("#\{site_config.([^\}]+)\}#e","getConfigItem('\\1')",$line['value']);
      $result[]=new ezcTranslationData($line['key'],$line['value'],$line['comment'],$this->status_lookup[$line['status']]);
    }
    return $result;
    /**/
  }
  
  //@Deprecated
  protected function paramConv($s) {
    /*$s=str_replace('{0}','%firstparam',$s);
    $s=str_replace('{1}','%secondparam',$s);
    $s=str_replace('{2}','%thirdparam',$s);
    $s=str_replace('{3}','%fourthparam',$s);
    $s=str_replace('{4}','%fifthparam',$s);
    $s=str_replace('{5}','%sixthparam',$s);
    $s=str_replace('{6}','%seventhparam',$s);
    $s=str_replace('{7}','%eighthparam',$s);
    $s=str_replace('{8}','%ninthparam',$s);
    $s=str_replace('{9}','%tenthparam',$s);*/
    return $s;
  }
  
  public function insert($key, $value, $locale, $context, $status='translated', $tblid=null, $tblfld=null) {
    $lang=strtolower(preg_replace('#_.*$#','',$locale));
    //if (defined('TRANSLATE_FILE_BASED')) return;
    switch ($context) {
      case 'ccms':
      case 'domain':
        if (!defined('TRANSLATE_DB_BASED')) {
          if (function_exists('_log')) _log("ezcCcmsTranslationBackend::insert ignored {$key} {$lang} {$context}");
          return;
        }
        break;
      case 'static':
        if (file_exists('c:\\')) {
          $script_base=getConfigItem('script_base');
          $static_txt_file=$script_base.'app/lang/'.$lang.'/static_txt.php';
          if (file_exists($static_txt_file)) {
            $static_txt=file_get_contents($static_txt_file);
            if (!empty($static_txt)) {
              if (empty($value)) $value=$key;
              $static_txt.="{$key}={$value}\n";
              file_put_contents($static_txt_file, $static_txt);
            }
          }
        }
        return;
    }
    #--
    if (empty($value)) $v="''";
    else $v=dbStr($value); //$v=dbStr($this->paramConv($value));
    $sql=sprintf('replace into '.DATABASE_PREFIX.'translation set `key`=%s, `value`=%s, `status`=%s, `lang`=%s, `context`=%s',dbStr($key),$v,dbStr($status),dbStr($locale),dbStr($context));
    if (!empty($tblid)) $sql.=", tblid='{$tblid}'";
    if (!empty($tblfld)) $sql.=", tblfld='{$tblfld}'";
    executeSql($sql);
  }
  
  public function ezcTranslationKeyNotAvailable($key, $locale, $context, $default=null) {
    if ($default===null) $default='['.$key.']';
    $base=$this->getFileBase($locale, $context);
    if (isset($base[$key])) {
      $this->insert($key, $base[$key], $locale, $context);
      return $base[$key];
    } else {
      $this->insert($key, '', $locale, $context,'unfinished');
      return $default;
    }
  }
  
  public function frontendCache_get($key) {
    if (!isset($this->cached[$key])) {
      #$cache=CcmsObjectCache::getInstance();
      #$this->cached[$key]=$cache->get(getConfigItem('domain').':ezcCcmsTranslationBackend:'.$key);
      $this->cached[$key]=null;
    }
    return $this->cached[$key];
  }
  public function frontendCache_set($key,$value) {
    #$cache=CcmsObjectCache::getInstance();
    #$cache->set(getConfigItem('domain').':ezcCcmsTranslationBackend:'.$key,$value);
    $this->cached[$key]=$value;
  }

  public function frontendTranslateById($locale, $tbl, $id, $fld, $default, $params=null) {
    return $this->frontendTranslate($locale, $tbl, $id, $fld, $default, $params, true);
  }
  public function frontendTranslateByFld($locale, $tbl, $id, $fld, $default, $params=null) {
    return $this->frontendTranslate($locale, $tbl, $id, $fld, $default, $params, false);
  }
  public function frontendTranslate($locale, $tbl, $id, $fld, $default, $params=null, $byId=true) {
    $tblid=$tbl.'.'.$id;
    $tblfld=$tbl.'.'.$fld;
    if ($byId) $groupByName='tblid'; else $groupByName='tblfld';
    $groupBy=$$groupByName;

    /*
    if (!isset($this->cache[$groupBy])) {
      $this->cache[$groupBy]=array();
      foreach (getTableArray("select * from ".DATABASE_PREFIX."translation where `{$groupByName}`='{$groupBy}'") as $line) {
        $this->cache[$groupBy][$line['lang']][$line['key']]=new ezcTranslationData($line['key'],$line['value'],$line['comment'],$this->status_lookup[$line['status']]);
      }
    }
    */

    $cached=$this->frontendCache_get($groupBy);
    if (empty($cached)) {
      $cached=array();
      foreach (getTableArray("select * from ".DATABASE_PREFIX."translation where `{$groupByName}`='{$groupBy}'") as $line) {
        $cached[$line['lang']][$line['key']]=new ezcTranslationData($line['key'],$line['value'],$line['comment'],$this->status_lookup[$line['status']]);
      }
      $this->frontendCache_set($groupBy,$cached);
    }

    $key=$tblid.'.'.$fld;

    if (!isset($cached[$locale][$key])) {
      $this->insert($key, $default, $locale, 'contents', 'unfinished', $tblid, $tblfld);
      return $default;
    }
    $data=$cached[$locale][$key];
    switch ($data->status) {
      case ezcTranslationData::TRANSLATED : return $data->translation;
      default : return $default;
    }
  }
}

//end



