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

function is_customer() {
	$customer=getSessionCustomer();
	$res=(!empty($customer) && ($customer["id"]>0));
	return $res;
}

function customerLogout() {
  hook('customer','logout');
  unset($_SESSION["customer"]);
	session_regenerate_id(true);
}

function customerLogin($output,$login,$password,$remember=false) {
	$line=getOneRow("select * from ".tbl_name("customer")." where email='".db_escape($login)."'");
	if (strcmp($line["password"],sha1(getConfigItem('customer.salt',getConfigItem('database_password').'|').$password))!==0) {
    if (strcmp($line["password"],$password)!==0) return false;
  }

	if ($remember) {
		$output->setCookie("login",$login,time()+(3600*24*365),"/");
		$output->setCookie("password",$password,time()+(3600*24*365),"/");
		$output->setCookie("remember",true,time()+(3600*24*365),"/");
	} else {
		if (!empty($_COOKIE['login'])) $output->setCookie("login",null);
		if (!empty($_COOKIE['password'])) $output->setCookie("password",null);
		if (!empty($_COOKIE['remember'])) $output->setCookie("remember",null);
	}

	customerToSession($line);
  hook('customer','login');
	return true;
}

function customerProfilePost($frontend,&$error_email_invalid=null,&$error_email_exists=null,&$profile_saved=null) {
  hook('customer','profilepost');
  $set=array();
	$error_email_exists=false;
	$error_email_invalid=false;

	require_once "../shared/cyane/valid_email.inc.php";

	if (!isset($_POST["email"]) || !isValidEmail($_POST["email"])) $error_email_invalid=true;
	else {
		$row=getOneRow("select * from ".tbl_name("customer")." where email='".db_escape($_POST["email"])."'");
		if ($row && ($row["id"]!=$_SESSION["customer"]["id"])) {
			$error_email_exists=true;
		}
	}
	
	if (!$error_email_exists && !$error_email_invalid) {
		$oldrow=getOneRow("select * from ".tbl_name("customer")." where id={$_SESSION["customer"]["id"]}");
		foreach ($_POST as $k=>$v) {
			if ($k=='_submit_token') continue;
			if ($k=="_profile") continue;
			if ($k=="terms") continue;
      if ($k=="password_confirm") continue;
      if ($k=='password') $v=sha1(getConfigItem('customer.salt',getConfigItem('database_password').'|').$v);
			$set[]="`{$k}`='".db_escape($v)."'";
		}
		$set=implode(",",$set);
		$sql="update ".tbl_name("customer")." set {$set} where id={$_SESSION["customer"]["id"]}";
		$profile_saved=executeSql($sql);
		#--
    if (getOneValue("select `active` from ccms_tree where page='newsletter'")) {
      require_once('../shared/plugins/newsletter/subscribe.inc');
      $subscribe=new MyCcmsNewsletter($frontend);
      if (empty($_POST['newsletter']) || ($oldrow['email']!=$_POST['email'])) {
        $subscribe->unsubscribe($oldrow['email']);
      }
      if (!empty($_POST['newsletter']) && (!$oldrow['newsletter'] || ($oldrow['email']!=$_POST['email']))) {
        $subscribe->subscribe($_POST["email"],getSafeName(trim(@$_POST['first_name'].' '.@$_POST['last_name']),' '),getSessionLanguage(),'.Customers',getSafeName(trim(@$_POST['adr1_city'].', '.@$_POST['adr1_country'])));
      }
    }
		#--
		$row=getOneRow("select * from ".tbl_name("customer")." where id={$_SESSION["customer"]["id"]}");
		if ($row) customerToSession($row);
		else customerLogout();
	}
}