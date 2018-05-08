<?

class CoreGenericEditor extends BodyView {

  protected $id=0;
  protected $line=null;
  
  protected $duplicating=false;
  
  protected $formName='editForm';

  public function __construct(CCMSModel $model) {
    parent::__construct($model);
    $id=$this->id=0;
    $this->duplicating=false;
    if (isset($_GET["duplicate"])) {
      $id=max(1,intval($_GET["duplicate"]));
      $this->duplicating=true;
    } else if (isset($_GET["edit"])) {
      $id=$this->id=max(0,intval($_GET["edit"]));
    }
    log_message("trace",get_class($this)." constructed, id={$id}");
  }

  public function getLine() {
    if (!$this->line) $this->line=$this->id>0?$this->getModel()->get($this->id):$this->getModel()->create();
    return $this->line;
  }

  public function setLine(array $line, $duplicating=false) {
    $this->id=isset($line["id"])?$line["id"]:0;
    $this->line=$line;
    $this->duplicating=$duplicating;
  }
  
  public function setLineValue($k,$v) {
    $this->getLine();
    $this->line[$k]=$v;
  }

  public function setGroup(array $group) {
    $this->id=array_keys($group);
    $this->group_lines=$group;
    $this->line=array();
    $arr=array();
    foreach ($group as $i=>$line) {
      foreach ($line as $k=>$v) {
        $arr[$k][$i]=$v;
      }
    }
    foreach ($arr as $k=>$va) {
      $vf=current($va);
      $eq=true;
      foreach ($va as $v) if ($vf!==$v) $eq=false;
      $this->line[$k]=$eq?$vf:null;
    }
    $this->duplicating=false;
  }

  public function getId() {
    return $this->id;
  }
  public function setId($id) {
    $this->id=$id;
  }
  
  public function isDuplicating() {
    return $this->duplicating;
  }
  public function setDuplicating($d) {
    $this->duplicating=$d;
  }

  public function outputScripts() {
?>
  function inputChanged(input)  {
    var name=input.name+"_changed";
    var expr="if (window."+name+") window."+name+"(input);"
    eval(expr);
  }
<?
  }
  
  protected function getWidth() {
    return getConfigItem('GenericEditor.width','60em');
  }

  public function outputStyles() {
    log_message("trace",get_class($this)." outputStyles");
?>

.editFieldSet {
  width:<?= $this->getWidth() ?>;
}

<?
  }

  public function getManager() {
    return $this->getModel()->getDomainManager();
  }
  
  protected function customEdit($field) {
    return false;
  }
  protected function editFieldByName($name) {
    throw new Exception('not implemented by base class: edit '.$name);
  }
  
  protected function getFields() {
    return $this->getManager()->getEditFields();
  }
  
  protected function getTranslateFields() {
    return $this->getManager()->getTranslateFields();
  }
  
  protected function outputField($fieldName) {
    if ($fieldName=='') {
      echo "<p style='margin:0;padding:0;line-height:1em'>&nbsp;</p>\n";
      return;
    }
    if ($fieldName=='-') {
      echo "<p style='margin:0;padding:0;line-height:0.1em'><div style='width:600px'><hr /></div></p>\n";
      return;
    }
    $field=$this->getManager()->getField($fieldName);
    #--
    if (!empty($field)) $this->editField($field);
    else $this->editFieldByName($fieldName);
    #--
  }
  
  protected function translateTabs($count_translate,$arr_translate,$lconf) {
    _require('CcmsLanguage.class.php');
?>
<p>&nbsp;</p>
<ul class="nav nav-tabs">
<? 
    foreach ($lconf['available'] as $i=>$code) {
      $o=CcmsLanguage::parse($code);
?>
  <li <?= $i==0?'class="active"':'' ?>>
    <a data-toggle="tab" href="#tabs-translate-<?= $count_translate ?>-<?= (string)$o ?>"><img src='<?= $this->resources_url() ?>/img/flag/<?= (string)$o ?>.png'> <?= $this->_('l.'.$o) ?></a>
  </li>
<?
    }
?>
</ul>

<div id="tabs-translate-<?= $count_translate ?>" class="tab-content">
<?
    foreach ($lconf['available'] as $i=>$code) {
      if ($code==$lconf['base']) {
        unset($this->activeLanguage);
        unset($this->translation);
      } else {
        $this->activeLanguage=$code;
        $this->translation=null;
      }
      $o=CcmsLanguage::parse($code);
?>
  <div id="tabs-translate-<?= $count_translate ?>-<?= (string)$o ?>" class="<?= $i==0?'tab-pane active':'tab-pane' ?>">
<?
      foreach ($arr_translate as $fieldName) {
        $this->outputField($fieldName);
      }
?>
  </div>
<?
    }
?>
</div>
<?
    unset($this->activeLanguage);
    unset($this->translation);
  }
  
  protected function outputFields() {
    $lconf=getConfigItem('language',array('default'=>'en','base'=>'en','available'=>array('en')));
    $fields=$this->getFields();
    $transl=count($lconf['available'])>1?$this->getTranslateFields():array();
    log_message("trace",get_class($this)." outputContent, fields={0}, transl={1}",array($fields,$transl));
    
    $count_translate=1;
    $arr_translate=array();
    foreach ($fields as $fieldName) {
      if (in_array($fieldName,$transl)) {
        $arr_translate[]=$fieldName;
        continue;
      } else if (!empty($arr_translate)) {
          $this->translateTabs($count_translate++,$arr_translate,$lconf);
          $arr_translate=array();
      }
      $this->outputField($fieldName);
    }
    if (!empty($arr_translate)) $this->translateTabs($count_translate++,$arr_translate,$lconf);
  }
  
  public function outputContent() {
    $this->getLine();
    $this->outputStart();
    $this->outputFields();
    $this->outputEnd();
  }
  
  protected function editField($field) {
    if (empty($field)) throw new Exception('null field');
    log_message("trace",get_class($this)." editField {$field->name} : {0}",array($field));
    if ($this->customEdit($field)) return;
    
    if (!$field->isEditable()) {
      $this->editHidden($field);
      return;
    }

    switch ($field->type) {
      case CCMSDomainField::FIELDTYPE_EMAIL :
      case CCMSDomainField::FIELDTYPE_LINK :
      case CCMSDomainField::FIELDTYPE_FLOAT :
      case CCMSDomainField::FIELDTYPE_INT :
      case CCMSDomainField::FIELDTYPE_YEAR :
      case CCMSDomainField::FIELDTYPE_ORDERINDEX :
      case CCMSDomainField::FIELDTYPE_CUR :
      case CCMSDomainField::FIELDTYPE_PERCENT :
      case CCMSDomainField::FIELDTYPE_STRING :
      case CCMSDomainField::FIELDTYPE_GPS :
      case CCMSDomainField::FIELDTYPE_SIMPLETEXT :
        $this->editString($field);
        return;
      case CCMSDomainField::FIELDTYPE_BOOL :
        $this->editBool($field);
        return;
      case CCMSDomainField::FIELDTYPE_FK :
        $this->editFK($field);
        return;
      case CCMSDomainField::FIELDTYPE_ENUM :
        $this->editEnum($field);
        return;
      case CCMSDomainField::FIELDTYPE_SET :
        $this->editSet($field);
        return;
      case CCMSDomainField::FIELDTYPE_TEXT :
        $this->editText($field);
        return;
      case CCMSDomainField::FIELDTYPE_TIMESTAMP :
      case CCMSDomainField::FIELDTYPE_DATE :
        $this->editDate($field);
        return;
      case CCMSDomainField::FIELDTYPE_TIME :
        $this->editTime($field);
        return;
      case CCMSDomainField::FIELDTYPE_DATETIME :
        $this->editDateTime($field);
        return;
      case CCMSDomainField::FIELDTYPE_IMG :
        if (!isset($this->group_lines)) $this->editImg($field);
        return;
      case CCMSDomainField::FIELDTYPE_FILE :
        if (!isset($this->group_lines)) $this->editFile($field);
        return;
      default :
        throw new Exception("No editor for {$field->type}");
    }
  }
  
  protected function outputStart() {
    if (!isset($this->editFieldSetClass)) $this->editFieldSetClass='grayborder editFieldSet';
    
    //$this->isDuplicating()?"d:{$this->id}":(is_array($this->id)?'many:'.implode(',',$this->id):$this->id)
    $saveId=$this->id;
    if ($this->isDuplicating()) $saveId="d:{$this->id}";
    else if (is_array($saveId)) {
      $saveId='many:'.implode(',',$this->id);
      if (isset($_GET['all']) && isset($_SESSION['edit.many.where'])) $saveId='many:all';
    }
?>

<form role="form" id="<?= $this->formName ?>" name="<?= $this->formName ?>" action="<?= $_SERVER['_URI'] ?>" class="form-horizontal" method="post" enctype="multipart/form-data">
<fieldset class="<?= $this->editFieldSetClass ?>"><!--legend>&#160;</legend-->
<input type='hidden' name='__save' value='<?= $saveId ?>' />
<input type='hidden' name='__redirect' value='' />
<?
  }
  
  protected function getOkButtonCaption() {
    return $this->cmsTranslate('Save');
  }
  
  protected function buttonPanel() {
?>
<div class="btnpanel">
  <button type="submit" class="btn btn-primary ok-button"><i class="glyphicon glyphicon-ok"></i> <?= $this->getOkButtonCaption() ?></button>
  <button type="button" class="minor" onclick="window.location.href='<?= $_SERVER["_URI"] ?>'"><i class="glyphicon glyphicon-remove"></i> <?= $this->cmsTranslate('Cancel') ?></button>
  <? if (!is_array($this->id) && ($this->id>0)) { ?>
  <button type="submit" class="minor" onclick="document.<?= $this->formName ?>.__redirect.value='<?= $_SERVER['REQUEST_URI'] ?>';"><i class="glyphicon glyphicon-save"></i> <?= $this->cmsTranslate('Apply') ?></button>
  <? } ?>
  <div class="clearfix"></div>
</div>
<?
  }
  
  protected function outputEnd() {
    $this->buttonPanel();
?>

</fieldset></form>
<?
  }
  
  protected function getInputName($field, $prefix='input') {
    $result=array();
    if (isset($this->activeLanguage)) $result[]=$this->activeLanguage;
    $result[]=$prefix;
    $result[]=$field->name;
    return implode('_',$result);
  }
  
  protected function getInputID($field) {
    $result=array();
    $result[]=$this->formName;
    if (isset($this->activeLanguage)) $result[]=$this->activeLanguage;
    $result[]=$field->name;
    return implode('_',$result);
  }
  
  protected function getValue(CCMSDomainField $field) {
    $fieldName=$field->getName();
    $result=isset($this->line[$fieldName])?$this->line[$fieldName]:"";
    if (isset($this->activeLanguage) && isset($this->line['id']) && ($this->line['id']>0)) {
      //@todo eztranslation
      $tblid=strtolower("{$this->getManager()->getName()}.{$this->line['id']}");
      $tblfld=strtolower("{$this->getManager()->getName()}.{$fieldName}");
      if (!isset($this->translation)) $this->translation=getTableArray("select * from ".tbl_name('translation')." where lang='{$this->activeLanguage}' and tblid='{$tblid}'","tblfld");
      if (isset($this->translation[$tblfld]) && ($this->translation[$tblfld]['status']=='translated')) {
        $result=$this->translation[$tblfld]['value'];
      } else if (empty($result)) {
        $result=$field->required?'-':'';
      }
    } else if (isset($this->activeLanguage)) {
      $result=$field->required?'-':'';
    }
    return $result;
  }
  
  protected function grpLbl($name,$id) {
    if (empty($this->group_lines)) return;
    return '<input type="checkbox" name="_grp_'.$name.'" value="1" onclick="if (this.checked) $(document.getElementById(\''.$id.'\')).fadeIn(); else $(document.getElementById(\''.$id.'\')).fadeOut()" />&nbsp;';
  }
  
  protected function labelCaption($field) {
    return $this->domainTranslate($this->getModel()->getName(),$field->name);
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-

  protected function editHidden(CCMSDomainField $field) {
?>
    <div class="p" style="padding:0.5em 0;margin-bottom:1px">
      <label><?= $this->grpLbl($field->name,$this->getInputID($field)).$this->labelCaption($field) ?></label>
      <input type="hidden" name="input_<?= $field->getName() ?>" value="<?= utf8_ent($this->getValue($field)) ?>" />
      <div style="margin-left:200px">
<?
    if (!isset($this->viewList)) {
      global $ctrlObj;
      $listCls=$ctrlObj->getCrud()->getViewClass($this->getManager()->getName().'List','GenericList');
      $this->viewList=new $listCls($ctrlObj->getModel());
    }
    echo $this->viewList->getListValue($this->getManager(),$field->getName(),$this->line,255);
?>
      </div>
      <br style="clear:both"/>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
 
  protected function editString(CCMSDomainField $field) {
    $value=$this->getValue($field);
    
    $cls=array('form-control');
    if (!empty($this->group_lines)) $field->required=false;
    if ($field->required) $cls[]='required';
    if ($field->type==CCMSDomainField::FIELDTYPE_EMAIL) $cls[]='email';
    else if ($field->type==CCMSDomainField::FIELDTYPE_LINK) $cls[]='url';
    else if (($field->type==CCMSDomainField::FIELDTYPE_CUR) || $field->isFloating()) $cls[]='number';
    else if ($field->isNumeric()) $cls[]='digits';

    $onblur=array('this.value=$.trim(this.value)','inputChanged(this)');
    if ($field->type==CCMSDomainField::FIELDTYPE_CUR) array_unshift($onblur,"if (!isNaN(parseFloat(this.value))) this.value=$().number_format(this.value)");
    if ($field->type==CCMSDomainField::FIELDTYPE_LINK) {
      $value=trim($value);
      if (!empty($value) && !preg_match('#^\w+:#',$value)) $value='http://'.$value;
      array_unshift($onblur,"if (/^www/.test(this.value)) this.value='http://'+this.value");
    }
    
    $input_type='type="text"';
    if ($field->type==CCMSDomainField::FIELDTYPE_EMAIL) $input_type='type="email"';
    if ($field->type==CCMSDomainField::FIELDTYPE_CUR) $input_type='type="number" step="0.01"';
    if ($field->type==CCMSDomainField::FIELDTYPE_INT) $input_type='type="number" step="1"';

?>
    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>>
        <?= $this->grpLbl($field->name,$this->getInputID($field)) ?>
        <?= $this->labelCaption($field) ?>
        <?= ($field->required?" *":"") ?>
      </label>
      <div class="controls input-group col-sm-10">
<?
    if ($field->type==CCMSDomainField::FIELDTYPE_SIMPLETEXT) { 
?>  <textarea onchange="inputChanged(this)" onblur="<?= implode('; ',$onblur) ?>" cols="60" rows="10" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" class="<?= implode(' ',$cls) ?>" style="<?= empty($this->group_lines)?'':'display:none' ?>"><?= utf8_ent($value) ?></textarea><?
    } else { 
?>  <input onchange="inputChanged(this)" onblur="<?= implode('; ',$onblur) ?>" <?= $input_type ?> <?= $field->required?'required="required"':'' ?> value="<?= utf8_ent($value) ?>" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" class="<?= implode(' ',$cls) ?>" style="<?= empty($this->group_lines)?'':'display:none' ?>" /><?
    }

    if ($field->type==CCMSDomainField::FIELDTYPE_GPS) {
      //http://api.mygeoposition.com/geopicker/#startPositionInputFieldIds
      if (!isset($this->gps_js_loaded)) {
?>
<script src="//api.mygeoposition.com/api/geopicker/api.js" type="text/javascript"></script>
<?
        $this->gps_js_loaded=true;
      }
      
      $start_fields=array($this->getInputID($field));
      if (strlen($field->getAttributes())>0) {
        $e=explode(',',$field->getAttributes());
        foreach ($e as $f) {
          $fld=$this->getManager()->getField($f);
          $start_fields[]=$this->getInputID($fld);
        }
      }
?>
  <div style="padding:10px;">
    <a href="#" onclick="myGeoPositionGeoPicker({startPositionInputFieldIds: ['<?= implode("','",$start_fields)  ?>'], zoomLevel:'17', returnFieldMap : {'<?= $this->getInputID($field) ?>' : '<LAT>,<LNG>'}});return false;">browse map...</a>
  </div>
<?
    }
    
?>

      </div>
    </div>

<?

  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editDate(CCMSDomainField $field) {
    $value=!empty($this->line[$field->getName()])?$this->line[$field->getName()]:null;
    if (empty($value) && preg_match('#(on_insert|on_update)#',$field->getAttributes())) $value=date('Y-m-d');
    if (empty($value) && $field->required) $value=date('Y-m-d');
    if (empty($value)) $value=$field->getDefaultValue();

    if (($value=='now()') || ($value=='0000-00-00') || ($value=='DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')) $value=date('Y-m-d');
    $timestamp=empty($value)?null:strtotime($value);

    $cls=array('form-control');
    if ($field->required) $cls[]='required';

    $inp_id=$this->getInputID($field);
    $inp_name=$this->getInputName($field);
    
    $dateFormat='yyyy-mm-dd';

?>

    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->grpLbl($field->name,'_control_'.$inp_name).$this->labelCaption($field).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-10 input-append date" data-provide="datepicker" data-date-format="<?= $dateFormat ?>" data-date-today-highlight="true" data-date-autoclose="true" >
        <input id="<?= $inp_id ?>" name="<?= $inp_name ?>" value="<?= utf8_ent($value) ?>" class="<?= implode(' ',$cls) ?>" onchange="inputChanged(this)" type="text" style="<?= empty($this->group_lines)?'':'display:none' ?>" />
        <span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editTime(CCMSDomainField $field) {
    $value=isset($this->line[$field->getName()])?$this->line[$field->getName()]:$field->defaultValue;
?>
    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>>
        <?= $this->grpLbl($field->name,$this->getInputID($field)) ?>
        <?= $this->labelCaption($field) ?>
        <?= ($field->required?" *":"") ?>
      </label>
      <div class="controls input-group col-sm-10">
        <input class="form-control" type="text" value="<?= $value ?>" onchange="inputChanged(this)" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" />
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editDateTime(CCMSDomainField $field) {
    $value=!empty($this->line[$field->getName()])?$this->line[$field->getName()]:null;
    if (empty($value) && preg_match('#(on_insert|on_update)#',$field->getAttributes())) $value=date('Y-m-d H').':00:00';
    if (empty($value) && $field->required) $value=date('Y-m-d H').':00:00';
    if (empty($value)) $value=$field->getDefaultValue();
    
    if (($value=='now()') || ($value=='0000-00-00 00:00:00')) $value=date('Y-m-d H').':00:00';
    $timestamp=empty($value)?null:strtotime($value);

    $cls=array('form-control');
    if ($field->required) $cls[]='required';

    $inp_id=$this->getInputID($field);
    $inp_name=$this->getInputName($field);
    
    $dateFormat='yyyy-mm-dd';

?>

    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->grpLbl($field->name,'_control_'.$inp_name).$this->labelCaption($field).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-4 input-append date" data-provide="datepicker" data-date-format="<?= $dateFormat ?>" data-date-today-highlight="true" data-date-autoclose="true" style="float:left">
        <input id="<?= $inp_id ?>" name="<?= $inp_name ?>[]" value="<?= empty($timestamp)?'':date('Y-m-d',$timestamp) ?>" class="<?= implode(' ',$cls) ?>" onchange="inputChanged(this)" type="text" style="<?= empty($this->group_lines)?'':'display:none' ?>" />
        <span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
      </div>
      <div class="controls input-group col-sm-4" style="float:left">
        <input class="<?= implode(' ',$cls) ?>" size="10" type="text" value="<?= empty($timestamp)?'':date('H:i',$timestamp) ?>" onchange="inputChanged(this)" id="<?= $this->getInputID($field) ?>_time" name="<?= $inp_name ?>[]" style="width:100px;" />
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-

  protected function editImg(CCMSDomainField $field) {
    $value=isset($this->line[$field->getName()])?$this->line[$field->getName()]:"";
    
    if (getConfigItem('jquery.jqupload',true)) {
      return $this->editImgJQ($field, $value);
    }    
?>
    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->labelCaption($field).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-10">
        <table cellspacing="0" cellpadding="0"><tr><td>
          <input onchange="inputChanged(this)" type="file" value="" style="width:300px;" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field,'upload') ?>" <?= $field->required?'required="true"':"" ?> />
        </td><td>
<?  if ($value) { ?>
          <img src='<?= $this->shared_url() ?>/cyane/thumb.php?maxwidth=100&maxheight=60&path=<?= base64_encode($this->getManager()->getImgDir($field->name).$value) ?>' alt='' border='0' />
<?    if (!$field->required) { ?>
          <input style='float:none;padding:0;margin:0' type='checkbox' value='1' name='<?= $this->getInputName($field,'_delete_img') ?>' id='delete:<?= $this->getInputID($field) ?>' >
          <label style='float:none;padding:0;margin:0' for='delete:<?= $this->getInputID($field) ?>'><?= $this->_("Delete") ?></label>
<?    } ?>
<?  } ?>
        </td></tr></table>
        <br style='clear:both' />
      </div>
    </div>
<?   
  }
  
  protected function editImgJQ(CCMSDomainField $field, $value) {
    $my_id=$this->getInputID($field);
?>
    <div class="form-group" id="div-<?= $my_id ?>" onmouseover="this.style.backgroundColor='#EFE'" onmouseout="this.style.backgroundColor=''">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->labelCaption($field).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-10">
        <table cellspacing="0" cellpadding="0"><tr>
          <td width="300">
<!-- -->
            <div class="row fileupload-buttonbar"><div class="col-lg-7">
              <span class="btn btn-primary fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>Select file...</span>
                <input style="width:auto;" id="<?= $my_id ?>" type="file" name="<?= $this->getInputName($field,'upload') ?>" multiple>
              </span>
            </div></div>
            <input type="hidden" name="<?= $this->getInputName($field,'ajax') ?>" value="" />
<!-- -->
          </td>
          <td>
<?  if ($value) { ?>
            <img id="<?= $this->getInputName($field,'preview') ?>" src='<?= $this->shared_url() ?>/cyane/thumb.php?maxwidth=100&maxheight=60&path=<?= base64_encode($this->getManager()->getImgDir($field->name).$value) ?>' alt='' border='0' />
<?    if (!$field->required) { ?>
          </td><td>
            <div class="checkbox" style="margin:10px">
              <label>
                <input type="checkbox" value="1" name="<?= $this->getInputName($field,'_delete_img') ?>" id="delete:<?= $this->getInputID($field) ?>" >
                <?= $this->_("Delete") ?>
              </label>
            </div>
<?    } ?>
<?  } else { ?>
            <img id="<?= $this->getInputName($field,'preview') ?>" src='<?= $this->shared_url() ?>/dot.gif' alt='' border='0' />
<?  } ?>
          </td>
        </tr><tr>
          <td colspan="2">
            <table class="table table-striped" width="100%"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
          </td>
        </tr></table>
        <br style='clear:both' />
      </div>
    </div>
    <script type="text/javascript">
$(document).ready(function() {
  $('#div-<?= $my_id ?>').fileupload({
    dropZone:$('#div-<?= $my_id ?>'),
    url:'/ccms/ajax/<?= preg_replace('#^Custom#','',get_class($this->getManager())) ?>.html?imageUpload&name=<?= $field->name ?>',
    autoUpload:true,
    dataType: 'json',
    done: function(e,data) {
      //var that = $(this).data('fileupload');
      if (data && data.result && data.result.ok) {
        document.<?= $this->formName ?>.<?= $this->getInputName($field,'ajax') ?>.value=data.result.ok;
        document.getElementById('<?= $this->getInputName($field,'preview') ?>').src="<?= $this->shared_url() ?>/cyane/thumb.php?maxwidth=100&maxheight=60&path="+encodeBase64('<?= $this->getManager()->getImgDir($field->name) ?>'+data.result.ok);
      }
      else alert('upload failed');
      //that.options.filesContainer.html('');
      $('#div-<?= $my_id ?> .files').html('');
      //that._trigger('completed', e, data);
      $(this).fileupload('option', 'done');
    }
  });
  //$('#div-<?= $my_id ?> .files').imagegallery();
});
    </script>
<?
  }

#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editFile(CCMSDomainField $field) {
    $value=isset($this->line[$field->getName()])?$this->line[$field->getName()]:"";
    $max='Max ';
    $post_max_size=ini_get('post_max_size');
    $upload_max_filesize=ini_get('upload_max_filesize');
    if ($post_max_size>$upload_max_filesize) $max.=$upload_max_filesize; else $max.=$post_max_size;
?>
    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->labelCaption($field).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-10">
        <? if ($value) { ?>
          <? if (!$field->required) { ?>
        <div class="checkbox" style="float:left;">
          <label>
            <input type="checkbox" value="1" name="_delete_file_<?= $field->name ?>" id="delete:<?= $this->getInputID($field) ?>" >
            <?= $this->_("Delete") ?>
          </label>
        </div>
          <? } ?>
        <div class="checkbox" style="float:left;">
          <a href="<?= $this->getManager()->getFileUrl($field->name).$value ?>">
            download (<?= return_size(filesize('../'.$this->getManager()->getFileDir($field->name).$value)) ?>)
          </a>
        </div>
        <? } ?>
        <div style="clear:left">
          <input onchange="inputChanged(this)" type="file" value="" size="40" id="<?= $this->getInputID($field) ?>" name="upload_<?= $field->name ?>" <?= $field->required?'required="true"':"" ?> />
        </div>
        <em><?= $max ?></em>
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editBool(CCMSDomainField $field) {
    $value=isset($this->line[$field->getName()])?intval($this->line[$field->getName()]):0;
?>
   <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->grpLbl($field->name,$this->getInputID($field)).$this->labelCaption($field) /**.($field->required?" *":"") **/ ?></label>
      <div class="controls input-group col-sm-10">
        <div class="checkbox"><label>
          <input type="checkbox" <?= $value?"checked='checked'":"" ?> onchange="inputChanged(this)" onclick="inputChanged(this)" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" style="<?= empty($this->group_lines)?'':'display:none' ?>" />
         </label></div>
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editEnum(CCMSDomainField $field) {
    $value=isset($this->line[$field->getName()])?$this->line[$field->getName()]:"";
    
    $cls=array('form-control');
    if ($field->required) $cls[]='required';
    
?>
    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>>
        <?= $this->grpLbl($field->name,$this->getInputID($field)) ?>
        <?= $this->labelCaption($field) ?>
        <?= ($field->required?" *":"") ?>
      </label>
      <div class="controls input-group col-sm-10">
        <select class="<?= implode(' ',$cls) ?>" onchange="inputChanged(this)" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" value="<?= utf8_ent($value) ?>" style="<?= empty($this->group_lines)?'':'display:none' ?>">
<? if (!$field->required) { ?><option value=""></option><? } ?>
<? 
    if (is_array($field->attributes)) $arr=$field->attributes;
    else {
      $arr=array();
      foreach (explode(",",$field->attributes) as $v) { 
        $arr[$v]=$this->domainTranslate($this->getModel()->getDomainManager()->getClassName().".{$field->getName()}.{$v}",null,null,$v);
      }
    }
    foreach ($arr as $v=>$text) { 
      ?><option <?= $v==$value?'selected="selected"':'' ?> value="<?= utf8_ent($v) ?>"><?= utf8_ent($text) ?></option><?
    }
?>
        </select>
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function editSet(CCMSDomainField $field) {
    $values=isset($this->line[$field->getName()])?explode(',',$this->line[$field->getName()]):array();

    $cls=array();
    if ($field->required) $cls[]='required';
    
    $n=$field->name;
    $input_name=$this->getInputName($field);
    
?>

    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>><?= $this->grpLbl($n,$this->getInputID($field)).$this->domainTranslate($this->getModel()->getName(),$n).($field->required?" *":"") ?></label>
      <div class="controls input-group col-sm-10">
        <? foreach (explode(",",$field->attributes) as $i=>$v) { ?>
          <div class="checkbox"><label>
            <input type="checkbox" class="<?= implode(' ',$cls) ?>" id="set_<?= $n ?>_<?= $i ?>" name="<?= $input_name ?>[]" value="<?= $v ?>" <? if (in_array($v,$values)) echo 'checked="checked"'; ?> />
            <?= $this->domainTranslate($this->getModel()->getDomainManager()->getClassName().".{$n}.{$v}") ?>
          </label></div>
        <? } ?>
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
  
  protected function getFkOptions(CCMSDomainField $field) {
    $fieldName = $field->getName();
    $attributes = $field->getAttributes();
    _log(get_class().":getFkOptions {$fieldName} {$attributes}");
    $result = array();
    if ($attributes) {
      foreach (explode(",",$attributes) as $expr) if (preg_match("#^([^:]+):(.*)$#",$expr,$match)) {
        $match[1]="lookup_".$match[1];
        $$match[1]=$match[2];
        _log(get_class().":getFkOptions {$match[1]}={$match[2]}");
      }
      if (isset($lookup_method)) $result=$this->getManager()->$lookup_method($field,$this->line);
      else {
        $lookup_where=isset($lookup_where)?"where {$lookup_where}":"";
        if (!empty($lookup_table) && !empty($lookup_caption)) {
          if (empty($lookup_orderby)) $lookup_orderby=$lookup_caption;
          if (empty($lookup_id)) $lookup_id="id";
          $a = getTableArray("select {$lookup_id},{$lookup_caption} from {$lookup_table} {$lookup_where} order by {$lookup_orderby}");
          foreach ($a as $line) $result[$line[$lookup_id]]=$line[$lookup_caption];
        }
      }
    }
    _log(get_class().":getFkOptions returns ".print_r($result,true));
    return $result;
  }
  
  protected function editFK(CCMSDomainField $field) {
    $value=isset($this->line[$field->getName()])?intval($this->line[$field->getName()]):"";
    $arr=$this->getFkOptions($field);
    
    $cls=array('form-control');
    if ($field->required) $cls[]='required';

?>
    <div class="form-group">
      <label class="control-label col-sm-2" <? if (empty($this->group_lines)) { ?> for="<?= $this->getInputID($field) ?>"<? } ?>>
        <?= $this->grpLbl($field->name,$this->getInputID($field)) ?>
        <?= $this->labelCaption($field) ?>
        <?= ($field->required?" *":"") ?>
      </label>
      <div class="controls input-group col-sm-10">
        <select class="<?= implode(' ',$cls) ?>" onchange="inputChanged(this)" id="<?= $this->getInputID($field) ?>" name="<?= $this->getInputName($field) ?>" value="<?= utf8_ent($value) ?>" style="<?= empty($this->group_lines)?'':'display:none' ?>">
          <? if (!$field->required) { ?><option value=""></option><? } ?>
          <? foreach ($arr as $v=>$text) { ?><option <?= $v==$value?'selected="selected"':'' ?> value="<?= utf8_ent($v) ?>"><?= utf8_ent($text) ?></option><? } ?>
        </select>
      </div>
    </div>
<?
  }
  
#-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-

  protected function editText(CCMSDomainField $field) {
    $value=$this->getValue($field);
    $inputName=$this->getInputName($field);
    $inputID=$this->getInputID($field);

    $ck_style=getConfigItem("cke_css");
    $width="800px";
    $height="500px";
    $attributes = $field->getAttributes();
    if ($attributes) foreach (explode(",",$attributes) as $expr) if (preg_match("/^([^=]{1,})=(.*)$/",$expr,$match)) $$match[1]=$match[2];
  
?>
    <div class="form-group" style="margin-bottom:0">
      <label class="control-label col-sm-2"><?= $this->grpLbl($field->name,'p:'.$inputName).$this->labelCaption($field).($field->required?" *":"") ?></label>
    </div>
    <div id='p:<?= $inputName ?>' style="<?= empty($this->group_lines)?'':'display:none' ?>">
      <textarea style="width:<?= $width ?>;height:<?= $height ?>;display:none;" class="replaceme" id="<?= $inputID ?>" name="<?= $inputName ?>" ><?= htmlentities($value,ENT_QUOTES,"UTF-8") ?></textarea>
      <div id="cke_preview_<?= $inputID ?>" class="ck-preview" style="width:<?= $width ?>;" onclick="$(document.getElementById('cke_preview_<?= $inputID ?>')).fadeOut('fast',function() { CKEDITOR.replace('<?= $inputID ?>', {height: <?= str_replace('px','',$height) ?>, customConfig : '<?= $this->resources_url('/cke/ckconfig.php') ?>'});});">
        <p class='ck-click-here'><?= $this->_('ck_editor.click_here') ?></p>
        <iframe src="about:blank" name="iframe_<?= $inputName ?>" id="iframe_<?= $inputName ?>" border="0" frameborder="0" style="width:100%;height:<?= $height ?>"></iframe>
        <script type="text/javascript">
jQuery(document).ready(function() {
  var jq_contents=jQuery('#iframe_<?= $inputName ?>').contents();
  if (jq_contents.length==1) {
    jq_contents[0].open();
    jq_contents[0].write('<'+'html'+'><'+'head'+'><'+'style type="text/css"'+'>@import url("<?= $ck_style ?>");</'+'style'+'></'+'head'+'><'+'body'+'>'+jQuery('#<?= $inputID ?>').val()+'</'+'body'+'></'+'html'+'>');
    jq_contents[0].close();
  }
  <? if ($this->id<1) { ?>
  $(document.getElementById('cke_preview_<?= $inputID ?>')).hide();
  CKEDITOR.replace('<?= $inputID ?>', {height: <?= str_replace('px','',$height) ?>, customConfig : '<?= $this->resources_url('/cke/ckconfig.php') ?>'});
  <? } ?>
});
        </script>
      </div>
    </div>
<?
  }

}

//end