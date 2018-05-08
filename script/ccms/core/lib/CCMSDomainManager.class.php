<?

abstract class CCMSDomainManager implements CCMSDomainManagerInterface {

  protected $className;
  protected $tableName;
  
  protected $fields=array();
  protected $listFields=array();
  protected $editFields=array();
  protected $translateFields=array();

  protected $addable=true;
  protected $editable=true;
  protected $deletable=true;
  protected $movable=null;
  protected $nextOrderbyPositive=true;
  
  protected $filterField=null;
  protected $treeInvolved;
  
  protected $allLimit=null;
  
  #-- constructor & init
  public function __construct($className,$tableName=null) {
    $this->className=$className;
    $this->tableName=($tableName!==null) ? $tableName : tbl_name(strtolower($className));
  }
  public function init() {
    _log(get_class($this).":init className:{$this->className} tableName:{$this->tableName}");
    $auto_database=getConfigItem("database_auto_enabled",true);
    if ($auto_database) {
      $c=getConfigItem("database_auto_create");
      $u=getConfigItem("database_auto_update");
      if ($c || $u) $this->checkMeta($c,$u);
    }
  }
  protected function getTablePrefix() {
    return tbl_prefix(strtolower($this->className));
  }

  #-- getters/setters
  public function getName() {
    return $this->className;
  }
  public function getClassName() {
    return $this->className;
  }
  public function setClassName($className) {
    $this->className=$className;
  }
  public function getTableName() {
    return $this->tableName;
  }
  public function setTableName($tableName) {
    $this->tableName=$tableName;
  }
  public function getFields() {
    return $this->fields;
  }
  public function setFields($fields) {
    $this->fields=$fields;
  }
  public function getField($name) {
    if (!isset($this->fields[$name])) return null; //throw new Exception('field not set: '.$name);
    return $this->fields[$name];
  }
  public function getListFields() {
    return $this->listFields;
  }
  public function setListFields(Array $listFields) {
    $this->listFields=$listFields;
  }
  public function getEditFields() {
    return $this->editFields;
  }
  public function setEditFields(Array $editFields) {
    $this->editFields=$editFields;
  }
  public function getTranslateFields() {
    return $this->translateFields;
  }
  public function setTranslateFields(Array $translateFields) {
    $this->translateFields=$translateFields;
  }
  public function getFilterField() {
    return $this->filterField;
  }
  public function setFilterField($filterField) {
    $this->filterField=$filterField;
  }
  public function setFilterFieldName($filterFieldName) {
    $this->filterField=$this->getField($filterFieldName);
  }

  public function isTreeInvolved() { return $this->treeInvolved; }

  public function isAddable() { return $this->addable; }
  public function isEditable() { return $this->editable; }
  public function isDeletable() { return $this->deletable; }
  
  public function setAddable($b) { $this->addable=$b; }
  public function setEditable($b) { $this->editable=$b; }
  public function setDeletable($b) { $this->deletable=$b; }
  public function setMovable($b) { $this->movable=$b; }

  public function isMovable() {
    if ($this->movable===null) {
      $this->movable=false;
      foreach ($this->fields as $field) if ($field->type===CCMSDomainField::FIELDTYPE_ORDERINDEX) { $this->movable=true; break; }
      _log(get_class().":I am ".($this->movable?"very":"NOT")." movable");
    }
    return $this->movable;
  }
  
  #-- fields
  public function addFieldData($name,$type=CCMSDomainField::FIELDTYPE_STRING,$length=0,$defaultValue="",$required=false,$attributes=false,$editable=true) {
    $this->addField(new CCMSDomainField($name,$type,$length,$defaultValue,$required,$attributes,$editable));
  }
  public function addFieldConfig($s) {
    $this->addField($this->fieldByConfig($s));
  }
  public static function fieldByConfig($s) {
    $name="";
    $type=CCMSDomainField::FIELDTYPE_STRING;
    $length=0;
    $defaultValue="";
    $required=false;
    $attributes=false;
    $editable=true;
    foreach (explode(";",$s) as $expr) if (preg_match("#^([^=]+)=(.*)$#",$expr,$match)) {
      $$match[1]=$match[2];
    }
    if ($name!="") {
      $field = new CCMSDomainField($name,$type,$length,$defaultValue,$required,$attributes,$editable);
      if (isset($translatable)) $field->setTranslatable($translatable);
      return $field;
    }
    else throw new Exception("invalid field config: ".$s);
  }
  public function addField($field) {
    if (!$this->fields) $this->fields=array();
    $this->setField($field);
  }
  public function setField($field) {
    $this->fields[$field->getName()] = $field;
    switch ($field->getType()) {
    case CCMSDomainField::FIELDTYPE_LANGUAGE : 
      $this->translatable=false;
      $this->languageSpecific=true;
      break;
    }
  }
  
  #-- display
  protected function getItemNameFieldName() {
    return 'name';
  }
  public function getItemName($arg) {
    if (is_array($arg)) $line=$arg;
    else $line=$this->get($arg);
    $n=$this->getItemNameFieldName();
    if (isset($line[$n])) return $line[$n];
    return $line["id"];
  }
  public function staticTranslate($language,$key,$parameters=null) {
    if (empty($this->staticTranslation)) {
      #$this->staticTranslation = new ezcCcmsTranslation(ezcCcmsTranslationManager::getCcmsInstance(), $language, 'static');
      require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
      $this->staticTranslation = ezcCcmsTranslation::getInstance('static', $language, false, CcmsObjectCache::getInstance());
    }
    return $this->staticTranslation->getTranslation($key,$parameters);
  }

  //@Deprecated
  public function getListValue($fieldName,$line,$maxlength) {
    $field=$this->getField($fieldName);
    $res=$line[$fieldName];
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_IMG) {
      if (!$res) return "";
      return "<img src='{$this->base_url()}/../shared/cyane/thumb.php?maxwidth=64&maxheight=50&path=".base64_encode($this->getImgDir($fieldName).$res)."' alt='' border='0' />";
    }
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_FK) {
      if (!$res) return "";
      $attributes = $field->getAttributes();
      foreach (explode(",",$attributes) as $expr) if (preg_match("#^([^:]+):(.*)$#",$expr,$match)) {
        $match[1]="lookup_".$match[1];
        $$match[1]=$match[2];
      }
      if (!isset($this->fk_lookup[$lookup_table.":".$lookup_caption])) {
        $this->fk_lookup[$lookup_table.":".$lookup_caption]=getListTableArray("select id,{$lookup_caption} from {$lookup_table}");
      }
      $res=$this->fk_lookup[$lookup_table.":".$lookup_caption][$res];
    }
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_CUR) {
      $cur=getConfigItem("currency");
      if ($res) return $cur["html"][$cur["base"]]." ".number_format($res,2);
    }
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_PERCENT) {
      $cur=getConfigItem("currency");
      if ($res) return number_format($res,1)." %";
    }
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_ENUM) {
      //$transl=get Domain Text($this->className.".".$fieldName.".".$res,null,null,$res);
      //return $transl?$transl:$res;
      return $res;
    }
    if ($field && $field->type==CCMSDomainField::FIELDTYPE_BOOL) {
      //return $res?get Cms Text("Yes"):get Cms Text("No");
      return $res?"yes":"no";
    }
    if ($field && $field->isFloating()) {
      if ($res) return number_format($res,2);
    }
    if ($field && $field->isNumeric()) {
      if ($res) return number_format($res,0);
    }
    return text_limit($res,$maxlength);
  }

  public function getListData() {
    return $this->getAll();
  }
  
  public function getExportAdapter($format) {
    switch ($format) {
      case 'xls' : return new CCMSDomainExcelExport($this);
    }
    throw new Exception('format unknown: '.$format);
  }
  
  public function export($format,array $ids=null,$options=null) {
    $adapter=$this->getExportAdapter($format);
    return $adapter->export($ids,$options);
  }

  #-- db
  public function get($id) {
    $res = getOneRow("select * from {$this->getTableName()} where id={$id}");
    return $res;
  }
  
  protected function getOrderByField() {
    foreach ($this->fields as $field) if ($field->type===CCMSDomainField::FIELDTYPE_ORDERINDEX) return $field;
    return null;
  }
  
  protected function getOrderBy() {
    if (($field=$this->getOrderByField())!==null) return $field->name;
    return null;
  }
  
  public function getAllLimit() {
    return $this->allLimit;
  }
  public function setAllLimit(array $limit=null) {
    return $this->allLimit=$limit;
  }

  protected function getFilter() {
    $n="{$this->className}.{$this->filterField->name}";
    if (isset($_GET[str_replace('.','-',$n)])) {
      $_SESSION[$n] = $_GET[str_replace('.','-',$n)];
    }
    if (!isset($_SESSION[$n])) throw new Exception("filter value not set: {$n}; ".print_r($_GET,true));
    return $_SESSION[$n];
  }

  protected function getAllExtQuery($select='*', array $where=null, array $orderby=null) {
    if (empty($select)) $select='*';
    $sql=array("select {$select} from `{$this->getTableName()}`");
    if ($where!==null && (count($where)>0) && ($where[0]!==null)) $sql[]='where '.implode(' and ',$where);
    if ($orderby!==null && (count($orderby)>0) && ($orderby[0]!==null)) $sql[]='order by '.implode(',',$orderby);
    return $sql;
  }

  protected function getAllExtLimitQuery($select='*', array $where=null, array $orderby=null, array $limit=null) {
    $sql=$this->getAllExtQuery($select, $where, $orderby);
    if (empty($limit)) $limit=$this->getAllLimit();
    if (!empty($limit)) $sql[]='limit '.implode(',',$limit);
    return $sql;
  }

  public function getAllExt(array $where=null, array $orderby=null, $select="*", $idcol=null) {
    return $this->getAllExtLimit($where,$orderby,null,$select,$idcol);
  }

  public function getAllExtLimit(array $where=null, array $orderby=null, array $limit=null, $select="*", $idcol=null) {
    $sql = $this->getAllExtLimitQuery($select, $where, $orderby, $limit);
    #return getTableArray(die(implode(" ",$sql)),$idcol);
    return getTableArray(implode(" ",$sql),$idcol);
  }
  public function getAllExtCount(array $where=null) {
    $sql=$this->getAllExtQuery('count(*)',$where);
    return getOneValue(implode(" ",$sql));
  }

  public function getAll() {
    $where=null;
    if ($this->filterField!==null) {
      $filter=$this->getFilter();
      if ($filter===null) $where=array("`{$this->filterField->name}` is null");
      else $where=array("`{$this->filterField->name}`='{$filter}'");
    }
    return $this->getAllExtLimit($where,array($this->getOrderBy()));
  }
  
  public function create() {
    $res=array();
    foreach ($this->fields as $field) $res[$field->name]=$field->defaultValue;
    if ($this->filterField!==null) {
      $filter=$this->getFilter();
      $res[$this->filterField->name]=$filter;
    }
    return $res;
  }
  
  public function canDelete($user,$id) {
    return $this->sessionHasClassAccess();
  }
  
  public function delete($id) {
    $err=array();
    $this->safeDelete($id,$err);
    return $err;
  }
  public function safeDelete($id,&$err) {
    #--
    if (is_array($id)) {
      foreach ($id as $i) $this->safeDelete($i,$err);
      return;
    }
    #--
    try {
      $old_row=null;
      foreach ($this->fields as $field) {
        if ($field->type!==CCMSDomainField::FIELDTYPE_IMG) continue;
        if ($old_row==null) $old_row=$this->get($id);
        if ($old_row[$field->name]) {
          $fn="../".$this->getImgDir($field->name).$old_row[$field->name];
          if (file_exists($fn)) unlink($fn);
        }
      }
      foreach ($this->fields as $field) {
        if ($field->type!==CCMSDomainField::FIELDTYPE_FILE) continue;
        if ($old_row==null) $old_row=$this->get($id);
        if ($old_row[$field->name]) {
          $fn="../".$this->getFileDir($field->name).$old_row[$field->name];
          if (file_exists($fn)) unlink($fn);
        }
      }
      //@Todo cascade
      executeSql("delete from {$this->getTableName()} where id={$id}");
    } catch (Exception $ex) {
      $err[$id]=$ex;
    }
  }

  public function deleteWhere($where) {
    $arr=$this->getAllExt($where,null,'id');
    foreach ($arr as $line) $this->delete($line['id']);
  }

  protected function getMoveGroup($orderby,$move_id,$move_dir) {
    $sql="select id from {$this->getTableName()}";
    if ($this->filterField!==null) {
      $filter=$this->getFilter();
      $sql.=" where `{$this->filterField->name}`='{$filter}'";
    }
    $sql.=" order by {$orderby}";
    $res=array();
    foreach (getTableArray($sql) as $line) $res[]=$line["id"];
    return $res;
  }
  
  public function canMove($user,$id) {
    return $this->sessionHasClassAccess();
  }
 
  public function move($move_id,$move_dir) {
    if (!$this->isMovable()) return;
    $orderby = $this->getOrderBy();
    $temp_arr = $this->getMoveGroup($orderby,$move_id,$move_dir);
    foreach ($temp_arr as $temp_i=>$temp_id) {
      if ($temp_id==$move_id) {
        $temp_switch=$move_dir=="up"?$temp_i-1:$temp_i+1;
        $temp_id=$temp_arr[$temp_switch];
        $temp_arr[$temp_switch]=$move_id;
        $temp_arr[$temp_i]=$temp_id;
        break;
      }
    }
    $this->orderby($temp_arr);
  }
  
  public function orderby($orderby) {
    if (!is_array($orderby)) throw new Exception('illegal argument');
    if (!$this->isMovable()) return;
    $fld = $this->getOrderBy();
    foreach (array_values($orderby) as $i=>$id) {
      $sql="update {$this->getTableName()} set {$fld}={$i} where id={$id}";
      executeSql($sql);
    }
  }
  
  #--
  protected function fieldFromPost($fieldName, &$res, &$err, $lcode=null) {
    return $this->fieldFromInput($fieldName, $res, $err, $_POST, $lcode);
  }
  protected function fieldFromInput($fieldName, &$res, &$err, $src, $lcode=null) {
    if ($fieldName=='') return;
    if ($fieldName=='-') return;
    $field=$this->getField($fieldName);
    #--
    $prefix=empty($lcode)?'':$lcode.'_';
    if (isset($src[$prefix.'input_'.$fieldName])) {
      if (is_array($src[$prefix.'input_'.$fieldName])) {
        if ($field && ($field->type==CCMSDomainField::FIELDTYPE_JSON)) $value=json_encode($src[$prefix.'input_'.$fieldName]);
        else if ($field && ($field->type==CCMSDomainField::FIELDTYPE_DATETIME)) $value=trim(implode(' ',$src[$prefix.'input_'.$fieldName]));
        else $value=implode(',',$src[$prefix.'input_'.$fieldName]);
      }
      else $value=trim($src[$prefix.'input_'.$fieldName]);
    }
    else if (isset($_FILES[$prefix.'upload_'.$fieldName]['tmp_name']) && is_uploaded_file($_FILES[$prefix.'upload_'.$fieldName]['tmp_name'])) $value=serialize($_FILES[$prefix.'upload_'.$fieldName]);
    else if (!empty($src[$prefix."ajax_".$fieldName]) && preg_match('#^tmp_'.$fieldName.'_(.+)$#',$src[$prefix."ajax_".$fieldName],$match)) $value=serialize(array('tmp_name'=>$src[$prefix."ajax_".$fieldName],'name'=>$match[1]));
		else $value=null;
		#--
		if ($field && ($field->type==CCMSDomainField::FIELDTYPE_IMG)) {
      $res[$fieldName]=$value;
      if (isset($src[$prefix."_delete_img_".$fieldName]) && !$res[$fieldName]) $res[$fieldName]="_delete";
      return;
    }
		if ($field && ($field->type==CCMSDomainField::FIELDTYPE_FILE)) {
      $res[$fieldName]=$value;
      if (isset($src[$prefix."_delete_file_".$fieldName]) && !$res[$fieldName]) $res[$fieldName]="_delete";
      return;
    }
    #--
    $res[$fieldName]=$this->valueFromPost($fieldName,$value,$err);
	}

  public function fetchPostData($id, &$err) {
    return $this->fetchInputData($id, $err, $_POST);
  }
  public function fetchInputData($id, &$err, $src) {
    $lconf=getConfigItem('language',array('default'=>'en','base'=>'en','available'=>array('en')));
    $fields = $this->getEditFields();
    $transl = $this->getTranslateFields();
    $res=array();
    foreach ($fields as $fieldName) {
      if (is_array($id) && empty($src['_grp_'.$fieldName])) continue;
      $this->fieldFromInput($fieldName, $res, $err, $src);
      if (in_array($fieldName,$transl) && (count($lconf['available'])>1)) {
        foreach($lconf['available'] as $lcode) {
          if ($lcode!=$lconf['base']) $this->fieldFromInput($fieldName, $res[$lcode], $err, $src, $lcode);
        }
      }
    }
    return $res;
  }

  public function valueFromPost($fieldName, $value, array &$err) {
    $field=$this->getField($fieldName);
    if (!$field) throw new Exception("field not fetchable: {$fieldName}");
    return $field->valueFromPost($value,$err);
  }
  
  protected function customSetSql($id, array $data, array &$res, array &$err, $fieldName) {
    return false;
  }
  
  protected function getNextOrderBy(array $data, CCMSDomainField $field) {
    //$arr=$this->getMoveGroup($field->name,null,null);
    //return count($arr);
    $orderby = $this->getOrderBy();
    $func = $this->nextOrderbyPositive ? 'max':'min';
    $sql="select {$func}({$orderby}) from {$this->getTableName()}";
    if ($this->filterField!==null) {
      $filter=$this->getFilter();
      $sql.=" where `{$this->filterField->name}`='{$filter}'";
    }
    return $this->nextOrderbyPositive ? 1 + intval(getOneValue($sql)) : intval(getOneValue($sql)) - 1;
  }

  protected function setSqlField($id, array $data, array &$res, array &$err, $fieldName) {
    $field=$this->getField($fieldName);
    if ($this->customSetSql($id,$data,$res,$err,$fieldName)) return;
    if ($field && ($field->type==CCMSDomainField::FIELDTYPE_IMG)) return;
    if ($field && ($field->type==CCMSDomainField::FIELDTYPE_FILE)) return;
    $v=$data[$fieldName];
    if ($v===null) $v="NULL";
    else if (!is_numeric($v) || !$field->isNumeric()) $v="'".db_escape($v)."'";
    $res[]="`{$fieldName}`={$v}";
  }
  
  protected function setSql($id, array $data, array &$res, array &$err) {
    foreach (array_keys($data) as $k) {
      if (is_array($data[$k])) continue;
      $this->setSqlField($id, $data, $res, $err, $k);
    }
    if (!$id){
      if (($this->filterField!==null) && !in_array($this->filterField->name,array_keys($data))) {
        $filter=$this->getFilter();
        if ($filter===null) $res[]="`{$this->filterField->name}`=NULL";
        else $res[]="`{$this->filterField->name}`='{$filter}'";
      }
      if ((($field=$this->getOrderByField())!==null) && !isset($data[$field->name])) {
        $res[]="`{$field->name}`=".$this->getNextOrderBy($data,$field);
      }
    }
    foreach ($this->fields as $field) if ($field->isDate() && is_object($this->filterField) && !isset($data[$this->filterField->name])) {
      if (!$id && preg_match("#on_insert#",$field->attributes)) $res[]="`{$field->name}`=now()";
      if (($id>0) && preg_match("#on_update#",$field->attributes)) $res[]="`{$field->name}`=now()";
    }
  }
  
  protected function extraSetSqlInsert() { return ""; }
  protected function extraSetSqlUpdate() { return ""; }
  
  public function setDuplicateFrom($id) {
    $this->duplicateFrom=$id;
  }
  
  protected function toHistory($line) {
    if ($no=getConfigItem('no_history_tracking')) {
      if (!is_array($no)) return;
      if (in_array($this->tableName,$no)) return;
    }
    try {
      executeSql("insert into `".$this->getTablePrefix()."history_data` set `table`='{$this->tableName}',`table_id`={$line['id']},`user`={$_SESSION['user']['id']},`data`=".dbStr(json_encode($line)));
    } catch (MySqlException $ex) {
      return; //throw $ex;
    }
  }
  
  protected function sessionHasClassAccess() {
    switch ($_SESSION['user']['user_type']) {
    case 'super' : 
    case 'editor' :
      break;
    default :
      $user_access=explode(',',getConfigItem('user_class_access',SettingsManager::setting('user_class_access','')));
      if (!in_array($this->className, $user_access)) return false;
    }
    return true;
  }

  public function canInsert($user,$data) {
    return $this->sessionHasClassAccess();
  }
  public function canUpdate($user,$id,$data) {
    return $this->sessionHasClassAccess();
  }

  public function save($id, $data, &$err) {
    if (!is_array($data)) throw new Exception("data must be an array");
    $set_sql=array();
    $this->setSql($id,$data,$set_sql,$err);
    if (count($err)>0) {
      log_message("debug","save {$id} err: ".implode(",",$err));
      return false;
    }

    log_message("debug","save {$id} set_sql: ".implode(",",$set_sql));
    
    $set_sql=implode(", ",$set_sql);
    if ($id>0) {
      $set_sql.=$this->extraSetSqlUpdate();
      if (!empty($set_sql)) {
        $history=$this->get($id);
        $sql="update `{$this->tableName}` set {$set_sql} where `id`={$id}";
        $update_result=executeSql($sql);
        if ($update_result) $this->toHistory($history);
      }
    } else {
      $set_sql.=$this->extraSetSqlInsert();
      $sql="insert into `{$this->tableName}` set {$set_sql}";
      global $insertedId;
      executeSql($sql);
      $id=$insertedId;
    }
    
    $this->save_img($id,$data,$err);
    $this->save_file($id,$data,$err);
    
    $this->save_translation($id,$data,$err);
    
    log_message("debug","save result: {$id}");
    return $id;
  }
  
  protected function save_translation($id,$data,&$err) {
    $lconf=getConfigItem('language',array('default'=>'en','base'=>'en','available'=>array('en')));
    if ((count($lconf['available'])>1)) {
      $deleteCache=array();
      $transl=$this->getTranslateFields();
      foreach ($lconf['available'] as $lcode) if ($lcode!==$lconf['base']) {
        $deleteCache=array(getConfigItem('domain').':ezcCcmsTranslation:contents:'.$lcode);
        
        $tblid=strtolower($this->className).'.'.$id;
        $deleteCache[]=getConfigItem('domain').':ezcCcmsTranslationBackend:'.$tblid;

        foreach ($transl as $fieldName) {
          //@todo - eztranslation          
          $tblfld=strtolower($this->className).'.'.$fieldName;
          $deleteCache[]=getConfigItem('domain').':ezcCcmsTranslationBackend:'.$tblfld;

          if (empty($data[$lcode][$fieldName])) continue;
          $key=$tblid.'.'.$fieldName;
          $sql=sprintf(
            'replace into '.$this->getTablePrefix().'translation set `key`=%s, tblid=%s, tblfld=%s, `value`=%s, `status`=%s, `lang`=%s, `context`=%s',
            dbStr($key),
            dbStr($tblid),
            dbStr($tblfld),
            dbStr($data[$lcode][$fieldName]),
            dbStr('translated'),
            dbStr($lcode),
            dbStr('contents')
          );
          executeSql($sql);
        }
      }
      #--
      require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
      $cache=CcmsObjectCache::getInstance();
      foreach ($deleteCache as $key) $cache->delete($key);
      #--
    }
  }
  
  public function getImgDir($field) {
    return '../'.getConfigItem("rel_httpdocs").getConfigItem("rel_userfiles_userimg");
  }
  
  public function getImgMaxSize($field) {
    return getConfigItem("userimg_max_size",null);
  }
  
  public function getJpegQuality($field) {
    return getConfigItem("userimg_jpeg_quality",90);
  }
  
  public function getFileDir($field) {
    return '../'.getConfigItem("rel_httpdocs").getConfigItem("rel_userfiles_userfile");
  }
  
  public function getFileUrl($field) {
    return getConfigItem("url_userfiles_userfile");
  }
  
  protected function save_img($id, $data, &$err) {
    //_delete_img_enclosure
    $dummy=array();
    $old_row=null;
    foreach (array_keys($data) as $k) {
      $dataField=$this->getField($k);
      if (empty($dataField) || ($dataField->type!==CCMSDomainField::FIELDTYPE_IMG)) continue;
      if ($this->customSetSql($id,$data,$dummy,$err,$k)) continue;
      if (!isset($data[$k]) && !isset($_FILES["upload_".$k])) continue;
      if ($old_row==null) $old_row=$this->get($id);
      
      //if ($data[$k]=="_delete") {
      if (!empty($old_row[$k]) && ($data[$k]=="_delete")) {
        $fn="../".$this->getImgDir($k).$old_row[$k];
        if (file_exists($fn)) unlink($fn);
        executeSql("update `{$this->tableName}` set `{$k}`=null where `id`={$id}");
      } else {
        _log(get_class().":save_img {$id}:{$k}");
        $unserialized=@unserialize($data[$k]);
        if (!empty($unserialized['tmp_name']) && preg_match('#^tmp_'.$k.'#',$unserialized['tmp_name'])) {
          $value='../'.$this->getImgDir($k).$unserialized['tmp_name'];
          $ext=$this->checkUploadedImg($value);
        } else {
          $value = (isset($unserialized["name"]) && file_exists($unserialized["tmp_name"]) && (filesize($unserialized["tmp_name"])>0))?$unserialized["tmp_name"]:false;
          if (!$value) {
            if (is_array($unserialized)) {
              $value = (isset($_FILES["upload_".$k]["name"]) && is_uploaded_file($_FILES["upload_".$k]["tmp_name"]))?$_FILES["upload_".$k]["tmp_name"]:false;
            }
          }
          if (isset($this->duplicateFrom) && !$value) {
            if ($data[$k]!="_delete") {
              $copyFrom = getOneValue("select {$k} from `{$this->tableName}` where id={$this->duplicateFrom}");
              //var_dump($this->getImgDir($k).$copyFrom);
              if (!$copyFrom || !file_exists("../".$this->getImgDir($k).$copyFrom)) continue;
              $copyTo=str_replace(".{$k}.{$this->duplicateFrom}.",".{$k}.{$id}.",$copyFrom);
              _log(get_class().":copy from {$copyFrom} to {$copyTo}");
              copy("../".$this->getImgDir($k).$copyFrom,"../".$this->getImgDir($k).$copyTo);
              executeSql("update `{$this->tableName}` set `{$k}`='{$copyTo}' where `id`={$id}");
            }
            continue;
          }
          
          if (!$value) { _log(get_class().":save_img {$id}:{$k} - not uploaded "); continue; }

          $ext=$this->checkUploadedImg($value);
          if (!$ext) { _log(get_class().":save_img {$id}:{$k} - checkUploadedImg failed "); continue; }

          $this->uploaded_image_resize($value,$k);
        }

        $filename=sprintf("%s.%s.%s.%s.%s", $this->getName(),$k,$id,md5(rand().time()),$ext);
        _log(get_class().":save_img {$id}:{$k} - filename={$filename}");

        if ($old_row[$k] && ($old_row[$k]!==$filename)) {
          $fn="../".$this->getImgDir($k).$old_row[$k];
          if (file_exists($fn)) unlink($fn);
        }

        if ($value && $filename && file_exists($value) && (rename($value, '../'.$this->getImgDir($k).$filename))) {
          executeSql("update `{$this->tableName}` set `{$k}`='{$filename}' where `id`={$id}");
        }
      }
    }
  }
  
  function uploaded_image_resize($filename,$fieldName=null,$auto_rotate=true) {
  
    #--
    if ($auto_rotate && function_exists('exif_imagetype') && function_exists('exif_read_data')) {
      if (exif_imagetype($filename) == IMAGETYPE_JPEG) {
        $exif = exif_read_data($filename);
        if ($exif && isset($exif['Orientation'])) {
          $deg=0;
          switch ($exif['Orientation']) {
            case 3: $deg = 180; break;
            case 6: $deg = 270; break;
            case 8: $deg = 90; break;
          }
          if ($deg) {
            $img_src = imagecreatefromstring(file_get_contents($filename));
            $img_src = imagerotate($img_src, $deg, 0); 
            imagejpeg($img_src,$filename,100);
          }
        }
      }
    }
    #--
  
    if (($max_size=$this->getImgMaxSize($fieldName))!==null) {
      $info=getimagesize($filename);

      $max_width=intval($max_size[0]);
      $max_height=intval($max_size[1]);

      if ($info[0]>$max_width || $info[1]>$max_height) {
        _log(get_class().":uploaded_image_resize {$fieldName} - {$filename} - resizing {$info[0]}x{$info[1]} to {$max_width}x{$max_height}");
        $ratio=min($max_width/$info[0],$max_height/$info[1]);
        
        $img_src = imagecreatefromstring(file_get_contents($filename));
        $img_dst = imagecreatetruecolor($ratio*$info[0], $ratio*$info[1]);
        #--
        $img_dst_bg = imagecolorallocate($img_dst, 255, 255, 255);
        imagefill($img_dst, 0, 0, $img_dst_bg);
        #--
        imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $ratio*$info[0], $ratio*$info[1], $info[0], $info[1]);
        if($info["mime"]=="image/jpeg") {
          imagejpeg($img_dst,$filename,$this->getJpegQuality($fieldName));
        } else {
          imagepng($img_dst,$filename,9);
        }
        imagedestroy($img_src);
        imagedestroy($img_dst);
      } else {
        _log(get_class().":uploaded_image_resize {$fieldName} - {$filename} - no resize - {$info[0]}x{$info[1]}");
      }
    }
  }
  
  function tempImageUpload(&$err) {
    if (empty($_FILES)) {
      $err='no upload';
      return false;
    }
    $upload=current($_FILES);
    if (empty($upload['name']) || empty($upload['tmp_name']) || !file_exists($upload['tmp_name']) || (filesize($upload['tmp_name'])==0) || (($ext=$this->checkUploadedImg($upload['tmp_name']))===false)) {
      $err='upload failed';
      return false;
    }
    $this->uploaded_image_resize($upload['tmp_name']);
    $fn='tmp_'.$_REQUEST['name'].'_'.$upload['name'];
    if (file_exists('../'.$this->getImgDir(null).$fn)) @unlink('../'.$this->getImgDir(null).$fn);
    @rename($upload['tmp_name'],'../'.$this->getImgDir(null).$fn);
    chmod('../'.$this->getImgDir(null).$fn, 0755);
    return $fn;
  }
  
  function checkUploadedImg($value) {
    $imagesize = getimagesize($value);
    if (!$imagesize["mime"]) {
      _log("{$value} not readable - mime type empty");
      return false;
    }
    if (($imagesize[0]<1) || ($imagesize[1]<1)) {
      _log("{$value} not readable - width and/or height is 0");
      return false;
    }

    $ext = false;
    if (preg_match("#image/(.+)$#i",$imagesize["mime"],$match)) $ext=$match[1];
    if (!$ext || $ext=="bmp" || $ext=="vnd.wap.wbmp") {
      $value=false;
      _log("{$imagesize["mime"]} not supported");
      return false;
    }
    return str_replace("jpeg","jpg",$ext);
  }
  
  function tempFileUpload(&$err) {
    if (empty($_FILES)) {
      $err='no upload';
      return false;
    }
    $upload=current($_FILES);
    if (empty($upload['name']) || empty($upload['tmp_name']) || !file_exists($upload['tmp_name']) || (filesize($upload['tmp_name'])==0) || (($ext=$this->checkUploadedImg($upload['tmp_name']))===false)) {
      $err='upload failed';
      return false;
    }
    //xxx//$this->uploaded_image_resize($upload['tmp_name']);
    $fn='tmp_'.$_REQUEST['name'].'_'.$upload['name'];
    $dir=$this->getFileDir($_REQUEST['name']);
    if (substr($dir,0,1)=='.') $dir='../'.$dir;
    $path=$dir.$fn;
    if (file_exists($path) && is_file($path)) @unlink($path);
    @rename($upload['tmp_name'],$path);
    return $fn;
  }
  
  protected function save_file($id, $data, &$err) {
    //_delete_file_enclosure
    $dummy=array();
    $old_row=null;
    foreach (array_keys($data) as $k) {
      $dataField=$this->getField($k);
      if (empty($dataField) || ($dataField->type!==CCMSDomainField::FIELDTYPE_FILE)) continue;
      if ($this->customSetSql($id,$data,$dummy,$err,$k)) continue;

      if ($old_row==null) $old_row=$this->get($id);
      if ($data[$k]=="_delete") {
        $fn="../".$this->getFileDir($dataField).$old_row[$k];
        if (file_exists($fn)) unlink($fn);
        executeSql("update `{$this->tableName}` set `{$k}`=null where `id`={$id}");
        unset($data[$k]);
      }

      if (!isset($data[$k]) && !isset($_FILES["upload_".$k])) continue;

      if (isset($data[$k])) $arr=unserialize($data[$k]);
      else $arr=$_FILES["upload_".$k];
      
      if (empty($arr["tmp_name"]) || !is_uploaded_file($arr["tmp_name"])) continue;

      $src=$arr["tmp_name"];
      $dest=sprintf("%s.%s.%s.%s.%s", $this->getName(),$k,$id,md5(rand().time()),getSafeName($arr["name"]));
      move_uploaded_file($src,"../".$this->getFileDir($dataField).$dest);
      executeSql("update `{$this->tableName}` set `{$k}`=".dbStr($dest)." where `id`={$id}");
    }
  }
  
  #-- meta
  public function checkMyMeta() {
    $this->checkMeta(true,true);
  }
  protected function checkMeta($create=false,$update=true) {
    _log("checkMeta c:{$create} u:{$update} > {$this->tableName}");
    if ((getConfigItem("logging_level")!==LOG_LEVEL_TRACE) && isset($_SESSION["meta.checked.{$this->tableName}"]) && !isset($_SERVER['checkMeta.force'])) return;
    $_SESSION["meta.checked.{$this->tableName}"]=true;

    #--
    if (!isset($_SESSION['meta.checked.history_data'])) {
      $this->checkHistory();
      $_SESSION['meta.checked.history_data']=true;
    }
    #--

    $res=null;
    try {
      $res=getTableArray("describe {$this->tableName}","Field");
    } catch (MySqlException $ex) {
      if ($ex->error!=1146 || !$create) throw $ex;
    }
    
    if ($res==null) {
      $this->createTable();
      return;
    }
    
    if ($update) $this->checkTableFields($res);
  }

  protected function checkHistory() {
    if ($no=getConfigItem('no_history_tracking')) {
      if (!is_array($no)) return;
      if (in_array($this->tableName,$no)) return;
    }
    try {
      $res=getTableArray("describe ccms_history_data","Field");
    } catch (MySqlException $ex) {
      if ($ex->error!=1146) return; //throw $ex;
      try {
        executeSql("
CREATE TABLE `".$this->getTablePrefix()."history_data` (
`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`table` VARCHAR( 128 ) NOT NULL ,
`table_id` INT NOT NULL ,
`user` INT NULL ,
`dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`data` LONGTEXT NULL ,
INDEX ( `table` , `table_id` ),
INDEX ( `user` )
) ENGINE = InnoDB");
        executeSql("ALTER TABLE `".$this->getTablePrefix()."history_data` ADD FOREIGN KEY ( `user` ) REFERENCES `".$this->getTablePrefix()."user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE");
      } catch (MySqlException $ex) {
        return; //throw $ex;
      }
    }
  }
  
  protected function createTable() {
    executeSql("create table `{$this->tableName}` (`id` int not null auto_increment primary key) engine = InnoDB;");
    $res=getTableArray("describe {$this->tableName}","Field");
    $this->checkTableFields($res);
  }

  protected function checkTableFields($describe) {
    $alter=array();
    $index=array();
    $fk=array();
    foreach ($this->fields as $field) {
      if (isset($describe[$field->name])) {
        if (!$field->isMysqlCompatible($describe[$field->name])) {
          //executeSql("alter table `{$this->tableName}` change `{$field->name}` `{$field->name}` {$field->getMysqlDDL()}");
          $alter[]="change `{$field->name}` `{$field->name}` {$field->getMysqlDDL()}";
        }
      } else {
        //executeSql("alter table `{$this->tableName}` add `{$field->name}` {$field->getMysqlDDL()}");
        $alter[]="add `{$field->name}` {$field->getMysqlDDL()}";
        if ($field->getType()==CCMSDomainField::FIELDTYPE_FK) {
          //executeSql("alter table `{$this->tableName}` add index (`{$field->name}`)");
          //executeSql("alter table `{$this->tableName}` add foreign key (`{$field->name}`) {$field->getMysqlFK()}");
          $index[]="add index (`{$field->name}`)";
          $fk[]="add foreign key (`{$field->name}`) {$field->getMysqlFK()}";
        }
      }
    }
    if (!empty($alter)) executeSql("alter table `{$this->tableName}` ".implode(', ',$alter));
    if (!empty($index)) executeSql("alter table `{$this->tableName}` ".implode(', ',$index));
    if (!empty($fk)) executeSql("alter table `{$this->tableName}` ".implode(', ',$fk));
  }

}

//end