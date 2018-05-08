<?

class CCMSDomainField {

  const FIELDTYPE_STRING="STRING";
  const FIELDTYPE_INT="INT";
  const FIELDTYPE_DECIMAL="DECIMAL";
  const FIELDTYPE_CUR="CUR";
  const FIELDTYPE_PERCENT="PERCENT";
  const FIELDTYPE_FLOAT="FLOAT";
  const FIELDTYPE_TEXT="TEXT";
  const FIELDTYPE_SIMPLETEXT="SIMPLETEXT";
  const FIELDTYPE_JSON="JSON";
  const FIELDTYPE_DATE="DATE";
  const FIELDTYPE_YEAR="YEAR";
  const FIELDTYPE_DATETIME="DATETIME";
  const FIELDTYPE_TIME="TIME";
  const FIELDTYPE_TIMESTAMP="TIMESTAMP";
  const FIELDTYPE_GPS="GPS";

  const FIELDTYPE_BOOL="BOOL";
  const FIELDTYPE_IMG="IMG";
  const FIELDTYPE_FILE="FILE";
  const FIELDTYPE_FK="FK";
  const FIELDTYPE_ENUM="ENUM";
  const FIELDTYPE_SET="SET";

  const FIELDTYPE_LINK="LINK"; // link to website
  const FIELDTYPE_EMAIL="EMAIL";

  const FIELDTYPE_ORDERINDEX="ORDERINDEX"; 
  const FIELDTYPE_LANGUAGE="LANGUAGE";

  public $name;
  public $type;
  public $length;
  public $defaultValue;
  public $required;
  public $attributes;
  public $translatable;
  public $editable;

  function __construct($name,$type=self::FIELDTYPE_STRING,$length=0,$defaultValue="",$required=false,$attributes=false,$editable=true) {
    $this->type=$type;
    $this->name=$name;
    $this->length=$length;
    $this->defaultValue=$defaultValue;
    $this->required=$required;
    $this->attributes=$attributes;
    $this->translatable= ($type==self::FIELDTYPE_STRING) || ($type==self::FIELDTYPE_TEXT) || ($type==self::FIELDTYPE_SIMPLETEXT);
    $this->editable=$editable;
  }
  
  function getName() {
    return $this->name;
  }
  function setName($name) {
    $this->name=$name;
  }
  function getType() {
    return $this->type;
  }
  function setType($type) {
    $this->type=$type;
  }
  function getLength() {
    return $this->length;
  }
  function setLength($length) {
    $this->length=$length;
  }
  function getDefaultValue() {
    return $this->defaultValue;
  }
  function setDefaultValue($defaultValue) {
    $this->defaultValue=$defaultValue;
  }
  function isRequired() {
    return $this->required;
  }
  function setRequired($required) {
    $this->required=$required;
  }
  function getAttributes() {
    return $this->attributes;
  }
  function setAttributes($attributes) {
    $this->attributes=$attributes;
  }
  function isTranslatable() { 
    return $this->translatable;
  }
  function setTranslatable($translatable) {
    $this->translatable=$translatable;
  }
  function isEditable() {
    return $this->editable;
  }
  function setEditable($editable) {
    $this->editable=$editable;
  }
  public function isDate() {
    switch ($this->type) {
    case self::FIELDTYPE_DATE:
    case self::FIELDTYPE_YEAR:
    case self::FIELDTYPE_DATETIME:
    case self::FIELDTYPE_TIMESTAMP:
      return true;
    }
    return false;
  }
  public function isNumeric() {
    switch ($this->type) {
    case self::FIELDTYPE_INT:
    case self::FIELDTYPE_DECIMAL:
    case self::FIELDTYPE_CUR:
    case self::FIELDTYPE_FLOAT:
    case self::FIELDTYPE_BOOL:
    case self::FIELDTYPE_PERCENT:
      return true;
    }
    return false;
  }
  public function isFloating() {
    switch ($this->type) {
    case self::FIELDTYPE_DECIMAL:
    case self::FIELDTYPE_CUR:
    case self::FIELDTYPE_FLOAT:
    case self::FIELDTYPE_PERCENT:
      return true;
    }
    return false;
  }
  
  public function valueFromPost($value, array &$err) {
    $value=trim($value);
    
    if ($this->type==self::FIELDTYPE_TEXT) {
      if ($value==='<br />') $value='';
    }

    if ($this->type==self::FIELDTYPE_DATETIME) {
      if (!empty($_POST['input_'.$this->name.'_time'])) {
        $value.=' '.trim($_POST['input_'.$this->name.'_time']);
        $value=date('Y-m-d H:i:s',strtotime($value));
      }
    }

    if ($this->type==self::FIELDTYPE_LINK) {
      if (!empty($value) && !preg_match('#^(http|https|mailto|ftp|skype)://#i',$value) && (substr($value,0,1)!='/')) $value='http://'.$value;
    }

    if ($this->type==self::FIELDTYPE_BOOL) {
      $res=($value!=="" && $value!=="0" && $value!=="false")?"1":"0";
      log_message("trace",get_class().":valueFromPost bool {$this->name} = {$res}");
      return $res;
    }
    
    log_message("trace",get_class().":valueFromPost {$this->name} = {$value}");
    
    if ($this->isRequired() && (strlen($value)==0)) {
      $err[]=$this->name;
      return null;
    }
    
    if (strlen($value)==0) {
      return null;
    }
    
    if ($this->isFloating()) return floatval($value);
    if ($this->isNumeric()) return intval($value);
    return $value;
  }
  
  static function getEnumSet($a) {
    $attr=array();
    foreach (explode(",",$a) as $s) $attr[]="'".db_escape(trim($s))."'";
    return implode(",",$attr);
  }

  function getMysqlDDL() {
    
    $null = $this->required?"NOT NULL":"NULL";
    $default = (strlen($this->defaultValue)>0)?"DEFAULT '".db_escape($this->defaultValue)."'":"";

    switch ($this->type) {
    case self::FIELDTYPE_ENUM:
    case self::FIELDTYPE_SET:
      $attr=self::getEnumSet($this->attributes);
      if ($this->type==self::FIELDTYPE_ENUM) return "ENUM( {$attr} ) {$null} {$default}";
      if ($this->type==self::FIELDTYPE_SET) return "SET( {$attr} ) {$null} {$default}";
      break;

    case self::FIELDTYPE_STRING:
    case self::FIELDTYPE_LINK:
    case self::FIELDTYPE_EMAIL:
    case self::FIELDTYPE_IMG:
    case self::FIELDTYPE_FILE:
    case self::FIELDTYPE_GPS:
      $l=$this->length>0?$this->length:255;
      $t='VARCHAR';
      if (preg_match('#type_is_char#',$this->attributes)) $t='CHAR';
      return "{$t}({$l}) {$null} {$default}";

    case self::FIELDTYPE_JSON:
    case self::FIELDTYPE_SIMPLETEXT:
    case self::FIELDTYPE_TEXT:
      return "TEXT {$null} {$default}";

    case self::FIELDTYPE_DECIMAL:
    case self::FIELDTYPE_CUR:
      $l=$this->length?$this->length:"10,2";
      return "DECIMAL({$l}) {$null} {$default}";
      
    case self::FIELDTYPE_BOOL:
      return "TINYINT {$null} {$default}";

    case self::FIELDTYPE_ORDERINDEX:
      return "INT not null default '1'";

    case self::FIELDTYPE_FK:
    case self::FIELDTYPE_INT:
      return "INT {$null} {$default}";
      
    case self::FIELDTYPE_FLOAT:
    case self::FIELDTYPE_PERCENT:
      return "FLOAT {$null} {$default}";

    case self::FIELDTYPE_YEAR:
      return "YEAR {$null} {$default}";

    case self::FIELDTYPE_DATE:
      return "DATE {$null} {$default}";
      
    case self::FIELDTYPE_DATETIME:
      return "DATETIME {$null} {$default}";
      
    case self::FIELDTYPE_TIME:
      return "TIME {$null} {$default}";
      
    case self::FIELDTYPE_TIMESTAMP:
      return "TIMESTAMP {$null} {$this->defaultValue}";
      
    default:
      return null;
    }
  }
  
  function getMysqlFK() {
    $field="id";
    $delete="cascade";
    $update="cascade";
    foreach (explode(",",$this->attributes) as $expr) if (preg_match("#(.+):(.+)#",$expr,$match)) $$match[1]=$match[2];
    if (!isset($table)) throw new Exception("fk table not set");
    return "references `{$table}` (`{$field}`) on delete {$delete} on update {$update} ";
  }
  
  function isMysqlCompatible($line) {
    switch ($this->type) {
    case self::FIELDTYPE_FK:
      return true;
    case self::FIELDTYPE_ENUM:
      if (preg_match('#^enum\s*\((.*)\)#i',$line['Type'],$match)) {
        $a=self::getEnumSet($this->attributes);
        return strcmp($a,$match[1])==0;
      }
      return preg_match("#^(varchar|char)#i",$line["Type"]);
    case self::FIELDTYPE_STRING:
    case self::FIELDTYPE_GPS:
    case self::FIELDTYPE_LINK:
    case self::FIELDTYPE_EMAIL:
    case self::FIELDTYPE_IMG:
    case self::FIELDTYPE_FILE:
      return preg_match("#^(varchar|char|text|tinytext)#i",$line["Type"]);
    case self::FIELDTYPE_FLOAT:
    case self::FIELDTYPE_DECIMAL:
    case self::FIELDTYPE_CUR:
    case self::FIELDTYPE_PERCENT:
      return preg_match("#^(decimal|float|double|real)#i",$line["Type"]);
    case self::FIELDTYPE_ORDERINDEX:
    case self::FIELDTYPE_INT:
      return preg_match("#^((|tiny|small|medium|big)int|decimal)#i",$line["Type"]);
    case self::FIELDTYPE_BOOL:
      return preg_match("#^(bit|bool|tinyint)#i",$line["Type"]);
    case self::FIELDTYPE_JSON:
    case self::FIELDTYPE_SIMPLETEXT:
    case self::FIELDTYPE_TEXT:
      return preg_match("#^(|tiny|medium|long)text#i",$line["Type"]);
    case self::FIELDTYPE_YEAR:
      return preg_match("#^(year|int|date|datetime|timestamp)#i",$line["Type"]);
    case self::FIELDTYPE_DATE:
    case self::FIELDTYPE_DATETIME:
    case self::FIELDTYPE_TIMESTAMP:
      return preg_match("#^(date|datetime|timestamp)#i",$line["Type"]);
    case self::FIELDTYPE_TIME:
      return preg_match("#^(time|datetime|timestamp)#i",$line["Type"]);
    case self::FIELDTYPE_SET:
      if (!preg_match('#^set\s*\((.*)\)#i',$line['Type'],$match)) return false;
      $a=self::getEnumSet($this->attributes);
      return strcmp($a,$match[1])==0;
    default:
      return false;
    }
  }
}

//end