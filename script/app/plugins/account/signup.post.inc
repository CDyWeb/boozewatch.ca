<?php

$debug=false;
_log('signup_post');

$post=array();
foreach ($_POST as $k=>$v) {
  if (is_array($v)) foreach ($v as $vk=>$vv) $post[$k][$vk]=trim(strip_tags($vv));
  else $post[$k]=trim(strip_tags($v));
}

$err=array();
require_once "../shared/cyane/valid_email.inc.php";
if (!isValidEmail($post["email"])) $err['email_invalid']='Invalid email address';
else {
  $row=getOneRow("select * from {$this->tbl("customer")} where email='".db_escape($post["email"])."'");
  if (!empty($row)) {
    $err['email_exists']='This e-mail address has been registered already';
  }
}

$p1=preg_replace('#\s#','',$post['password']);
$p2=preg_replace('#\s#','',$post['password2']);
if (strcmp($post['password'],$p1)!=0) $err[]='Password: No spaces allowed';
if (strcmp($p1,$p2)!=0) $err[]='Password: The two provided passwords did not match';
if (strlen($p1)<4) $err[]='Password: Use 4 or more characters please';

if (!empty($err)) {
  $user_data=$post;
  return;
}

$_SESSION['signup.data']=$post;
$this->output->redirect($this->uri('signup?done'));

//end