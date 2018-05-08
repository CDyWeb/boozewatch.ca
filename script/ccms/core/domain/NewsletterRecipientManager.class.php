<?

class NewsletterRecipientManager extends CCMSDomainManager {

  public function __construct() {
    
    parent::__construct("NewsletterRecipient",$this->getTablePrefix()."newsletter_subscribe");
    // newsletter_group //hash='%s', email='%s', name='%s', residence='%s', language

    $this->addFieldConfig("name=newsletter_group;type=".CCMSDomainField::FIELDTYPE_FK.";required=0;attributes=table:".$this->getTablePrefix()."newsletter_group,caption:name,delete:restrict");
    $this->addFieldConfig("name=hash;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1;length=40;attributes=type_is_char;");
    $this->addFieldConfig("name=email;type=".CCMSDomainField::FIELDTYPE_EMAIL.";required=1");
    $this->addFieldConfig("name=name;type=".CCMSDomainField::FIELDTYPE_STRING.";required=1");
    $this->addFieldConfig("name=residence;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0");
    $this->addFieldConfig("name=language;type=".CCMSDomainField::FIELDTYPE_STRING.";required=0;length=2;attributes=type_is_char;");
    
    $this->addFieldConfig("name=status;type=".CCMSDomainField::FIELDTYPE_ENUM.";defaultValue=new;attributes=new,pending,confirmed,bounce;required=1;editable=0");
    $this->addFieldConfig("name=log_pending;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0;editable=0");
    $this->addFieldConfig("name=log_confirmed;type=".CCMSDomainField::FIELDTYPE_TEXT.";required=0;editable=0");

    $this->setListFields(array("email","name","language","status"));
    $this->setEditFields(array("language","newsletter_group","email","name"));

    $this->setFilterFieldName('newsletter_group');

    $this->init();
  }
  
  function bounceCheck() {
  
    require_once getConfigItem('script_base').'shared/cyane/CcmsObjectCache.class.php';
    $cache=CcmsObjectCache::getInstance();
    $str=$cache->get(getConfigItem('domain').':NewsletterRecipientManager:bounce_check_str');
    if (empty($str)) {
      $str=file_get_contents(SettingsManager::setting('bounce_check','http://api.bhosted.ca/bounce/?l&d='.getConfigItem('domain')));
      $cache->set(getConfigItem('domain').':NewsletterRecipientManager:bounce_check_str',$str,60*60);
#echo $str; die();
    }
    $json=json_decode($str,true);
    if (!empty($json)) {
      if (!empty($json[0])) foreach ($json[0] as $hash) {
        executeSql('update `'.$this->tableName.'` set `status`='.dbStr('bounce').' where `hash`='.dbStr($hash));
      }
      if (!empty($json[1])) foreach ($json[1] as $hash) {
        $log=getOneRow('select * from `'.tbl_name('newsletter_log').'` where id='.dbStr($hash));
        if (!empty($log)) executeSql('update `'.tbl_name('newsletter_log').'` set `status`='.dbStr('bounce').' where id='.dbStr($hash));
        if (!empty($log['recipient'])) executeSql('update `'.$this->tableName.'` set `status`='.dbStr('bounce').' where id='.intval($log['recipient']));
      }
      if (!empty($json[2])) foreach ($json[2] as $email) {
        executeSql('update `'.$this->tableName.'` set `status`='.dbStr('bounce').' where `email`='.dbStr($email));
      }
    }
  }

  //@Override
  protected function createTable() {
    parent::createTable();
    executeSql("ALTER TABLE `".$this->getTablePrefix()."newsletter_subscribe` ADD INDEX ( `hash` );");
  }
  
  //@Override
  protected function getOrderBy() {
    return "email";
  }

  //@Override
  protected function getFilter() {
    $res=parent::getFilter();
    if ($res==-1) $res=null;
    return $res;
  }
  
  //@Override
  public function fetchPostData($id, &$err) {
    $res=parent::fetchPostData($id, $err);
    if (($id==0) && isset($_POST['many'])) {
      foreach ($err as $i=>$k) {
        if ($k=='name') unset($err[$i]);
        if ($k=='email') unset($err[$i]);
      }
      $e=explode("\n",str_replace(',',"\n",$_POST['many']));
      $arr=array();
      require getConfigItem('script_base').'shared/cyane/valid_email.inc.php';
      foreach ($e as $address) if (isValidEmail($address=trim($address))) $arr[$address]=$address;
      if (count($arr)==0) $err[]='many';
      else $res['_many']=$arr;
    }
    if (($id==0) && isset($_FILES['csv']['tmp_name']) && file_exists($_FILES['csv']['tmp_name']) && (filesize($_FILES['csv']['tmp_name'])>0)) {
      foreach ($err as $i=>$k) {
        if ($k=='name') unset($err[$i]);
        if ($k=='email') unset($err[$i]);
      }
      $fp=fopen($_FILES['csv']['tmp_name'],'rb');
      
      $delimiter=',';
      $row=fgetcsv($fp,1024,$delimiter);
      if (empty($row) || !is_array($row) || (count($row)==1)) { 
        fseek($fp,0);
        $delimiter=';';
        $row=fgetcsv($fp,1024,$delimiter);
        if (empty($row) || !is_array($row) || (count($row)==1)) { 
          fclose($fp); $err[]='csv'; return;
        }
      }

      $col_email=null;
      $col_name=null;
      foreach ($row as $i=>$s) {
        if (strcasecmp('email',trim($s))==0) $col_email=$i;
        if (strcasecmp('name',trim($s))==0) $col_name=$i;
      }
      if ($col_email===null) {
        fclose($fp); $err[]='csv'; return;
      }

      $arr=array();
      require getConfigItem('script_base').'shared/cyane/valid_email.inc.php';
      while($row=fgetcsv($fp,1024,$delimiter)) {
        if (isValidEmail($address=trim($row[$col_email]))) $arr[$address]=$col_name!==null?$row[$col_name]:' ';
      }
      if (count($arr)==0) $err[]='csv';
      else $res['_csv']=$arr;
      fclose($fp);
    }
    $res['_invite_txt']=isset($_POST['input__invite_txt'])?$_POST['input__invite_txt']:SettingsManager::setting('newsletter.email.invite');
    return $res;
  }
  
  //@Override
  protected function customSetSql($id, array $data, array &$res, array &$err, $fieldName) {
    if ($fieldName=='_invite_txt') return true;
    if ($fieldName=='_many') return true;
    if ($fieldName=='_csv') return true;
    return false;
  }
  
  private function getSubscriber() {
    if (empty($this->subscriber)) {
      _require('inc/CcmsNewsletter.class.php');
      $this->subscriber=new ZZCcmsNewsletter($this);
    }
    return $this->subscriber;
  }

  //@Override
  public function save($id, $data, &$err) {
    if (($id==0) && !empty($data['_many'])) {
      foreach ($data['_many'] as $address) {
        $d=array_merge($data,array('name'=>' ','email'=>$address));
        unset($d['_many']);
        if (!($res=$this->save($id,$d,$err))) return false;
      }
      return $res;
    }
    if (($id==0) && !empty($data['_csv'])) {
      foreach ($data['_csv'] as $email=>$name) {
        $d=array_merge($data,array('name'=>$name,'email'=>$email));
        unset($d['_csv']);
        if (!($res=$this->save($id,$d,$err))) return false;
      }
      return $res;
    }
    #--
    if (empty($data['language'])) {
      $l=getConfigItem('language');
      $data['language']=$l['default'];
    }
    #--
    $grp='newsletter_group is null ';
    if ($data['newsletter_group']) $grp="newsletter_group = {$data['newsletter_group']} ";
    if ($id) {
      executeSql("delete from ".$this->tableName." where id={$id} or ({$grp} and email='".db_escape($data['email'])."')");
      $id=null;
    } else {
      executeSql("delete from ".$this->tableName." where ({$grp} and email='".db_escape($data['email'])."')");
    }
    $hash=sha1($_SERVER['HTTP_HOST'].$data['newsletter_group'].$data['email'].$data['name'].$data['residence'].$data['language']);
    $res=parent::save($id,$data,$err);
    if ($res) {
      executeSql("update ".$this->tableName." set status='pending', log_pending=".dbStr($this->getSubscriber()->log()).", hash='{$hash}' where id={$res}");
      #--
      $auto_activate=false;
      if (!empty($data['newsletter_group'])) {
        $auto_activate=getOneValue('select auto_activate from '.tbl_name('newsletter_group').' where id='.intval($data['newsletter_group']));
      }
      if ($auto_activate) {
        $this->getSubscriber()->confirm($hash);
      } else {
        $this->getSubscriber()->sendActivateMsg($hash,$data['email'],$data['name'],$data['language'],@$data['_invite_txt']);
      }
      #--
    }
    return $res;
  }

  function getGroupName() {
    $f=$this->getFilter();
    if (empty($f)) return null;
    return getOneValue("select name from ".tbl_name('newsletter_group').' where id='.$f);
  }

}

// end