<?

class LoginView extends CCMSDefaultView {
	public $login_error="";
	
	public function template_repl($key) {
		switch($key) {
			case 'passwordreset' :
        $msg='';
        if (!isset($_GET['i']) || !isset($_GET['c'])) return '';
        $line=$this->model->getDomainManager()->get(intval($_GET['i']));
        if (empty($line) || (sha1($line['email'].'.'.$line['password'])!=$_GET['c'])) return ''; //sha1($line['email'].'.'.$line['password']); //'';
        if (!empty($_POST['reset'])) {
          $newpwd=trim(stripslashes($_POST['reset']));
          if ((strlen($newpwd)>3) && preg_match('#\d#',$newpwd) && !preg_match('#\s#',$newpwd)) {
            $data=array('password'=>$newpwd);
            $err=array();
            $res=$this->model->getDomainManager()->save($line['id'],$data,$err);
            if (!$res || !empty($err)) $msg='<p>An unexpected error occurred while saving your password</p>';
            else {
              return '<p><b>'.$this->getCcmsTranslation()->getTranslation('Your password has been changed').'</b></p>';
            }
          } else {
            $msg='<p><strong>'.$this->getCcmsTranslation()->getTranslation('Password too simple').'</strong></p>';
          }
        }
        $q=http_build_query($_GET);
        return <<<HTML
<form name="resetform" id="resetform" action="{$this->base_url('/')}?{$q}" method="post" style="display:none">
  {$msg}
  {$this->getCcmsTranslation()->getTranslation('Enter your new password')}
  <p><label>{$this->getCcmsTranslation()->getTranslation('Password')}:<br /> <input type="password" name="reset" id="reset" value="" size="20" tabindex="1" /></label></p>
  <p class="submit">
    <input type="submit" name="submit" id="submit3" value="{$this->getCcmsTranslation()->getTranslation('Next')} &raquo;" tabindex="3" />
  </p>
  <p style="text-align:right"><a href="#" onclick="$('#resetform').hide();$('#loginform').fadeIn(); return false;">{$this->getCcmsTranslation()->getTranslation('Cancel')}</a></p>
</form>
<script>
$(document).ready(function() {
  $('#loginform').hide();
  $('#resetform').fadeIn();
});
</script>
HTML;
      
      case 'error' : 
        if (isset($this->lost_sent)) return '<strong>'.$this->getCcmsTranslation()->getTranslation('Password reset link sent').'</strong>';
        return empty($this->login_error)?
        '':
        '<div id="login_error"><strong>'.$this->getCcmsTranslation()->getTranslation('Error').'</strong>: '.$this->getCcmsTranslation()->getTranslation($this->login_error).'</div>';

			case 'profile' : {
				global $site_config;
				if (isset($site_config['database_profile'])) {
					$opt="";
					foreach ($site_config['database_profile'] as $i=>$p) $opt.="<option value='{$i}'>{$p['database_profile_name']}</option>";
					return <<<HTML

			<p>
				<label>
					Profile:<br />
					<select name="profile" id="profile" tabindex="0">{$opt}</select>
				</label>
			</p>

HTML;
				}
				return "";
			}
		}
	}
}

class LoginController extends CCMSController {
	
	public function __construct() {
		log_message('trace','LoginController::__construct');
		$model=new UserModel();
		log_message('trace','LoginController::__construct delegate to parent');
		parent::__construct(new LoginView("login.php",$model),$model);
	}
	
	function handlePost() {
  
    if (!empty($_POST['lost'])) {
      $lost=trim(strip_tags($_POST['lost']));
      $line=$this->model->getDomainManager()->getByEmail($lost);
      if (empty($line)) {
        $this->view->login_error='user not found';
      } else {
        require_once('../shared/cyane/phpmailer.inc.php');
        $mailer=new CcmsPHPMailer();
        $mailer->setSubject($this->getView()->_('Lost your password'));
        $link=getConfigItem('base_url').'?'.http_build_query(array('i'=>$line['id'],'c'=>sha1($line['email'].'.'.$line['password'])));
        $mailer->setHTML('<a href="'.$link.'">'.$link.'</a>');
        $mailer->setBcc('security@bhosted.ca');
        $mailer->setHeader('from','noreply@'.getConfigItem('domain'));
        $mailer->setHeader('sender','noreply@'.getConfigItem('domain'));
        $mailer->send($line['email']);
        $this->view->lost_sent=true;
      }
    }
  
		if (!isset($_POST["log"]) || strlen($_POST["log"])<1) return;
		if (!isset($_POST["pwd"])) return;
		if (isset($_POST["profile"])) {
			setcookie("ccms_dbp",intval($_POST["profile"]),0,'/',preg_replace('#^www\.#','.',$_SERVER['HTTP_HOST']));
			$_COOKIE['ccms_dbp']=intval($_POST["profile"]);
			$_SESSION['ccms_dbp']=intval($_POST["profile"]);
		}

		global $config;
		$config['database_auto_enabled']=true;
		$this->model->getDomainManager()->init();

		if ($this->model->authUser($_POST["log"],$_POST["pwd"],$this->view->login_error)) {
			global $config;
			$this->redirect=$config["base_url"].'/';
		}
	}
	
}

if (!isset($override_core_login)) {
	$c=new LoginController();
	$c->invoke();
}

//end