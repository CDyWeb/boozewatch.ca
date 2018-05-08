<?

class CorePageEditor extends GenericEditor {

  protected $pluginSettingsFile=null;

  public function outputContent() {
    $this->pluginSettingsFile=$this->getManager()->getPluginSettingsFile($this->line);
    parent::outputContent();
  }

  protected function getFkOptions(CCMSDomainField $field) {
    if ($field->name!=="parent_id") return parent::getFkOptions($field);
    return $this->getManager()->getParentOptions($this->line);
  }

  protected function outputStart() {
    if ($this->pluginSettingsFile) {
      require_once $this->pluginSettingsFile;
    }

    $func = "pageEditor_preStartContents";
    if (function_exists($func)) $func($this);

    parent::outputStart();

    $func = "pageEditor_postStartContents";
    if (function_exists($func)) $func($this);
  }
  
  protected function getPlugins(&$value) {
    $result=array();
    
    global $config;
    $enable=array();
    foreach ($config as $k=>$v) if (preg_match("#^plugin\.enable(\.\w+)$#",$k,$match)) $enable[$match[1]]=$v;
    log_message('trace',$enable);
    
    $arr=readdir_ls(SHARED_PATH.'/plugins');
    if (file_exists($pn=APP_PATH.'plugins')) $arr=array_merge(readdir_ls($pn),$arr); 

    foreach ($arr as $fn=>$path) {
      if ($fn=="_base") continue;
      if (!file_exists($path."/id.txt")) continue;

      $enabled=true;
      foreach ($enable as $k=>$v) if (!$v && file_exists($path."/".$k)) $enabled=getConfigItem('plugin.enabled.'.basename($path),false);
      if (!$enabled) {
        log_message('trace',$fn.' not enabled > '.$path."/".$k);
        continue;
      }
      
      $name=$fn;
      $txt=explode("\n",@file_get_contents($path."/id.txt"));
      if (count($txt)>0) {
        $name=$txt[0];
        $lang=preg_replace('#_.*$#','',getConfigItem('system_lang'));
        foreach ($txt as $i=>$expr) if (($i>0) && preg_match("#^".$lang.":(.*)$#",$expr,$match)) {
          $name=trim($match[1]);
          break;
        }
      }
      $result[$fn]=$name;
    }
    return $result;
  }
  
  protected function customEdit($field) {
    if ($field->name=="page_type") {
      $this->editEnum($field);
?>
<div class="edit_attributes_plugin" <?= ($this->line['page_type']=='plugin')?'':'style="display:none"' ?>>
<?
      $attr_field=$this->getManager()->getField('attributes');
      $clone = clone $attr_field;
      $clone->name.="_plugin";
      $clone->required=false;
      $clone->attributes=$this->getPlugins($value);
      $this->line[$clone->name]=($this->line['page_type']=='plugin')?$this->getValue($attr_field):null;
      $this->editEnum($clone);
?>
</div>
<?
      return true;
    }
    if ($field->name=='attributes') {
?>

<div class="edit_attributes_link" <?= ($this->line['page_type']=='link')?'':'style="display:none"' ?>>
<?
      $clone = clone $field;
      $clone->name.='_link';
      $clone->required=false;
      $this->line[$clone->name]=($this->line['page_type']=='link')?$this->getValue($field):null;
      $this->editString($clone);
?>
</div>

<?
      return true;
    }
    if ($field->name=='parent_id') {
      $arr=$this->getManager()->getParentOptions($this->line);
      if (empty($arr)) {
      
?>
<input type="hidden" name="input_parent_id" value="" />
<?
        return true;
      }
    }
    if ($field->name=='page_type') {
      $arr=$this->getManager()->getParentOptions($this->line);
      if (empty($arr)) {
        $e=explode(',',$field->attributes);
        foreach ($e as $i=>$s) if ($s=='menu') unset($e[$i]);
        $field->attributes=implode(',',$e);
      }
    }
    return parent::customEdit($field);
  }
  
  protected function selectHistory() {
    if (empty($this->line['id'])) return;
    $arr=getTableArray('select d.id,d.dt,u.first_name,u.last_name from `'.tbl_name('history_data').'` d left join `'.tbl_name('user').'` u on d.`user`=u.id  where `table`=\''.tbl_name('page').'\' and `table_id`='.intval($this->line['id']).' order by dt desc');
    if (empty($arr)) return;
?>

<hr />
<div class="form-group">
  <label class="control-label col-sm-2">
    &nbsp;
  </label>
  <div class="controls input-group col-sm-10" style="text-align:right">
    <select onchange="window.location.href='<?= $_SERVER['_URI'].'?'.http_build_query($_GET) ?>&history='+this.value" class="form-control">
      <option value="0">Go back in history to:</option>
<?
    foreach ($arr as $line) { 
      $caption=strftime('%x %X',strtotime($line['dt']));
      $name=trim($line['first_name'].' '.$line['last_name']);
      if (!empty($name)) $caption.=' - changed by '.$name;
      ?><option value="<?= $line['id'] ?>"><?= $caption ?></option><? 
    } ?>
    </select>    
  </div>
</div>


<?
  }
  
  //@Override
  protected function outputFields() {
    if (!empty($_GET['history'])) {
      $h=getOneValue('select `data` from `'.tbl_name('history_data').'` where id='.intval($_GET['history']));
      if (!empty($h)) {
        $json=json_decode($h,true);
        if (!empty($json)) foreach ($json as $k=>$v) $this->line[$k]=$v;
      }
    }
    parent::outputFields();
  }
  
  //@Override
  public function editField($field) {
    #--
    $func = "pageEditor_preEditField";
    if (function_exists($func)) $func($this,$field);
    #--
    if ($field->name=="meta_title") echo "<div class='edit_for_text' ".(($this->line["page_type"]=="text" || $this->line["page_type"]=="plugin")?"":"style='display:none'").">\n";
    parent::editField($field);
    if ($field->name=="text") {
      echo $this->selectHistory()."</div>\n";
    }
    #--
    $func = "pageEditor_postEditField";
    if (function_exists($func)) $func($this,$field);
    #--
  }
  
  protected function outputEnd() {
    if ($this->pluginSettingsFile) {
      require_once $this->pluginSettingsFile;
    }

    $func = "pageEditor_preEndContents";
    if (function_exists($func)) $func($this);

    parent::outputEnd();
?>
    <script type="text/javascript">
    function input_page_type_changed(input) {
      if (input.value=='link') jq('.edit_attributes_link').show(); else jq('.edit_attributes_link').hide(); 
      if (input.value=='plugin') jq('.edit_attributes_plugin').show(); else jq('.edit_attributes_plugin').hide(); 
      if (input.value=='text' || input.value=='plugin') jq('.edit_for_text').show(); else jq('.edit_for_text').hide(); 
    }
    </script>
<?
    $func = "pageEditor_postEndContents";
    if (function_exists($func)) $func($this);
  }

}

//end