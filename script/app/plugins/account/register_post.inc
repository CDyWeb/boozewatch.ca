<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 103 $
$LastChangedDate: 2009-05-22 22:51:00 +0200 (vr, 22 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

$debug=false;
_log('register_post');

if (!isValidSessionPost()) {
	_log('!register_post:isValidSessionPost');
  //echo translate("your_session_has_expired");
  return;
}

_log('register_post:isValidSessionPost');

$set=array();
$err=array();

require_once "../shared/cyane/valid_email.inc.php";

if (!isValidEmail($_POST["email"])) $err['email_invalid']='Invalid email address';
else {
  $row=getOneRow("select * from {$this->tbl("customer")} where email='".db_escape($_POST["email"])."'");
  if ($row) {
    $err['email_exists']='This e-mail address has been registered already';
  }
}

$p1=preg_replace('#\s#','',$_POST['password']);
$p2=preg_replace('#\s#','',$_POST['password2']);
if (strcmp($_POST['password'],$p1)!=0) $err[]='Password: No spaces allowed';
if (strcmp($p1,$p2)!=0) $err[]='Password: The two provided passwords did not match';
if (strlen($p1)<4) $err[]='Password: Use 4 or more characters please';

if (!empty($err)) return;

foreach ($_POST as $k=>$v) {
  if ($k=="_submit_token") continue;
  if ($k=="password2") continue;
  if ($k=="terms") continue;
  if (is_array($v)) $v=trim(implode(',',$v),',');
  $set[]="`{$k}`='".db_escape($v)."'";
}

$set=implode(",",$set);
$sql="insert into {$this->tbl("customer")} set {$set}";
if ($debug || $profile_saved=executeSql($sql)) {
  #--
  $id=mysql_insert_id();
  if ($id) customerToSession(getOneRow('select * from ccms_customer where id='.$id));
  #--
  if (!empty($_POST['newsletter'])) {
    require_once('../shared/plugins/newsletter/subscribe.inc');
    $subscribe=new MyCcmsNewsletter($this);
    $subscribe->subscribe($_POST["email"],getSafeName(trim(@$_POST['first_name'].' '.@$_POST['last_name']),' '),getSessionLanguage(),'.Customers',getSafeName(trim(@$_POST['adr1_city'].', '.@$_POST['adr1_country'])));
  }
  #--
  require getConfigItem('script_base').'shared/cyane/phpmailer.inc.php';
  $email_msg=translate('customer.email.register');
  if (empty($email_msg) || (substr($email_msg,0,1)=='_')) $email_msg=nl2br(setting('plugin.customer.register_msg',
<<<HTML
Dear %name%,

Here are your user credentials:

Username:
%username%

Password:
%password%

Regards
%domain%
HTML
));
  
  $email_template=setting('plugin.customer.register_template',null);
  if (!empty($email_template)) {
    if (preg_match('#^file:(.+)$#',$email_template,$match)) {
      $email_template=preg_replace('#\<\!-- T1.*T2 --\>#Usi','%s',file_get_contents(getConfigItem('html_template_dir').$match[1]));
      $email_template=$this->fixRelativeLinksInDir($email_template,$this->htmlbase().dirname($match[1]).'/');
    } else {
      $email_template=$this->fixRelativeLinks($email_template);
    }
  } else {
    $email_css=getConfigItem('cke_css');
    if (!empty($email_css)) $email_css='<link href="'.$email_css.'" rel="stylesheet" type="text/css" />';
    $email_template=
<<<HTML
  <html xmlns="http://www.w3.org/1999/xhtml"><head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  {$email_css}
  </head><body>
  %s
  </body></html>
HTML;
  }

  $name=trim(@$_POST['first_name'].' '.@$_POST['last_name']);

  $str_domain=getConfigItem('domain');
  $str_website=setting('company_website','www.'.$str_domain);
  $str_email=setting('company_email','info@'.$str_domain);
  $email_msg=sprintf(
    $email_template,
    str_replace(
      array(
        '%name%',
        '%naam%',
        '%username%',
        '%password%',
        '%domain%',
        '%website%',
        '%email%'
      ),array(
        $this->output->ent($name),
        $this->output->ent($name),
        $this->output->ent(@$_POST['email']),
        $this->output->ent(@$_POST['password']),
        $str_domain,
        '<a href="http://'.$str_website.'">'.$str_website.'</a>',
        '<a href="mailto:'.$str_email.'">'.$str_email.'</a>'
      ),
      $email_msg
    )
  );
  if ($debug) die($email_msg);
#  ini_set('display_errors','off');
  $subject='Welcome to '.getConfigItem('domain'); //$this->_('plugin.customer.register_subject');
  CcmsPHPMailer::mail($str_email,$_POST["email"],$subject,$email_msg);
  #--
}

