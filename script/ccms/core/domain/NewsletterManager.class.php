<?

class NewsletterManager extends CCMSDomainManager {

  function __construct() {
    
    parent::__construct("Newsletter");

    $this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
    $this->addFieldConfig("name=dt_sent;type=".CCMSDomainField::FIELDTYPE_DATETIME.";required=0");
    $this->addFieldConfig("name=template;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=text_top;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0");
    $this->addFieldConfig("name=text_bottom;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0");
    $this->addFieldConfig("name=language;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1;length=5;attributes=type_is_char;");
    
    $this->setListFields(array("name","dt_sent"));
    $this->setEditFields(array("language","name","template","","__ITEMS","text_top","text_bottom"));

    $this->init();
  }
  
  //@Override
  protected function getOrderBy() {
    return "dt_sent desc, id desc";
  }
  
  //@Override
  public function valueFromPost($fieldName, $value, array &$err) {
    if ($fieldName=='__ITEMS') return null;
    return parent::valueFromPost($fieldName, $value, $err);
  }
  
  //@Override
  protected function customSetSql($id, array $data, array &$res, array &$err, $fieldName) {
    if ($fieldName=='__ITEMS') {
      return true;
    }
    return false;
  }
  
  public function delete($id) {
    $itemManager = CCMSManagedModel::getManager("NewsletterItemManager");
    $err=array();
    foreach (getTableArray('select id from '.tbl_name('newsletteritem').' where newsletter='.intval($id)) as $item) {
      $itemManager->safeDelete($item['id'],$err);
    }
    if (!empty($err)) return $err;
    return parent::delete($id);
  }

  //@Override
  public function save($id, $data, &$err) {
    $res=parent::save($id,$data,$err);
    if ($res) {
      $itemManager = CCMSManagedModel::getManager("NewsletterItemManager");
      foreach ($_POST as $k=>$v) if (preg_match('#^_item_(\d+)$#',$k,$match)) {
        $item_id=$v;
        $item_index=$match[1];
        $posted=array();
        foreach ($_POST as $k=>$v) if (preg_match('#^((input|ajax).+)_'.$item_index.'$#',$k,$match)) {
          $posted[$match[1]]=$v;
        }
        
        $err=array();
        $item_data = $itemManager->fetchInputData($item_id, $err, $posted);
        if (!empty($err)) return false;
        
        if (empty($item_data['type'])) $item_data['type']='text';

        if ($item_data['type']=='news') {
          if (empty($_POST['input_fk_'.$item_index.'_news'])) unset($item_data['fk']);
          else {
            $item_data['fk']=intval($_POST['input_fk_'.$item_index.'_news']);
            $ext=getOneRow('select * from '.tbl_name('pagenews').' where id='.$item_data['fk']);
            if (empty($ext)) {
              unset($item_data['fk']);
            } else {
              $item_data['title']=$ext['title'];
              $item_data['text']=$ext['description'];
              $item_data['image']=null;
              if ($ext['enclosure'] && file_exists($fn='../'.$itemManager->getImgDir('image').$ext['enclosure'])) {
                copy($fn,$fn.'.tmp');
                $item_data['image']=serialize(array('name'=>$ext['enclosure'],'tmp_name'=>$fn.'.tmp'));
              }
            }
          }
        }
        if ($item_data['type']=='product') {
          if (empty($_POST['input_fk_'.$item_index.'_product'])) unset($item_data['fk']);
          else {
            $item_data['fk']=intval($_POST['input_fk_'.$item_index.'_product']);
            $ext=getOneRow('select * from '.tbl_name('product').' where id='.$item_data['fk']);
            if (empty($ext)) {
              unset($item_data['fk']);
            } else {
              $item_data['title']=$ext['name'];
              $item_data['text']=$ext['description'];
              $item_data['image']=null;
              if ($ext['img'] && file_exists($fn='../'.$itemManager->getImgDir('image').$ext['img'])) {
                copy($fn,$fn.'.tmp');
                $item_data['image']=serialize(array('name'=>$ext['img'],'tmp_name'=>$fn.'.tmp'));
              }
              $item_data['caption']=$ext['sku'];
            }
          }
        }
        
        $item_data['newsletter']=$res;
        if (!$itemManager->save($item_id,$item_data,$err)) return false;
      }
    }
    return $res;
  }
  
  #--
  //@Override
  //check dependencies
  protected function checkMeta($create=false,$update=true) {
    if (!isset($_SESSION["meta.checked.{$this->tableName}"]) || (getConfigItem("logging_level")==LOG_LEVEL_TRACE)) {
      $dependencies=true;
    }
    parent::checkMeta($create,$update);
    if (isset($dependencies)) {
      CCMSManagedModel::getManager("NewsletterItemManager");
      CCMSManagedModel::getManager("NewsletterGroupManager");
      CCMSManagedModel::getManager("NewsletterRecipientManager");
      $this->check_newsletter_log();
      $this->check_newsletter_track();
    }
  }

  function check_newsletter_log() {
    $t=getTableArray('show tables');
    foreach ($t as $l) if (current($l)==$this->getTablePrefix().'newsletter_log') {
      $c=getOneRow('show create table '.$this->getTablePrefix().'newsletter_log');
      if (preg_match('#`id` varchar\(40\)#i',$c['Create Table'])) {
        executeSql('ALTER TABLE `'.$this->getTablePrefix().'newsletter_log` CHANGE `id` `id` CHAR( 40 ) NOT NULL');
      }
      if (!preg_match('#FOREIGN KEY \(`recipient`\)#i',$c['Create Table'])) {
        executeSql('ALTER TABLE `'.$this->getTablePrefix().'newsletter_log` ADD FOREIGN KEY ( `recipient` ) REFERENCES `'.$this->getTablePrefix().'newsletter_subscribe` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
      }
      if (!preg_match('#`status` enum#i',$c['Create Table'])) {
        executeSql("ALTER TABLE `".$this->getTablePrefix()."newsletter_log` ADD `status` enum( 'sent', 'open', 'bounce' ) default 'sent'");
      }
      return;
    }
    $sql="
create table `{$this->tableName}_log` (`id` char(40) not null primary key, `dt` timestamp, `newsletter` int not null, `recipient` int null, `email` varchar(255) null, `status` enum('sent','open','bounce') default 'sent') engine = InnoDB;
ALTER TABLE `".$this->getTablePrefix()."newsletter_log` ADD INDEX ( `newsletter` );
ALTER TABLE `".$this->getTablePrefix()."newsletter_log` ADD INDEX ( `recipient` ) ;
ALTER TABLE `".$this->getTablePrefix()."newsletter_log` ADD FOREIGN KEY ( `newsletter` ) REFERENCES `".$this->getTablePrefix()."newsletter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `".$this->getTablePrefix()."newsletter_log` ADD FOREIGN KEY ( `recipient` ) REFERENCES `".$this->getTablePrefix()."newsletter_subscribe` (`id`) ON DELETE SET NULL ON UPDATE CASCADE ;
";
    foreach (explode(';',$sql) as $s) if (strlen($s=trim($s))) executeSql($s);
  }

  function check_newsletter_track() {
    $t=getTableArray('show tables');
    foreach ($t as $l) if (current($l)==$this->getTablePrefix().'newsletter_track') return;
    $sql="
create table `{$this->tableName}_track` (`dt` timestamp, `id` char(40) not null, `ip` char(16) not null, `op` char(16) not null, `arg` varchar(50) null default null, `exarg` text null default null) engine = InnoDB;
ALTER TABLE `".$this->getTablePrefix()."newsletter_track` ADD FOREIGN KEY ( `id` ) REFERENCES `".$this->getTablePrefix()."newsletter_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `".$this->getTablePrefix()."newsletter_track` ADD UNIQUE ( `id`, `ip`, `op`, `arg` ) ;
";
    foreach (explode(';',$sql) as $s) if (strlen($s=trim($s))) executeSql($s);
  }
  
}

// end