<?php

$db=db_connect();
require_once "inc/customer.inc";

$req=preg_replace("#.*/(\w+)(|\.\w+)$#","$1",$_SERVER["_URI"]);
_log("req = {$req}");

if ($req=="logout") {
	customerLogout();
	$this->output->redirect($this->uri());
	return;
}

$customer=getSessionCustomer();
if (isset($customer["id"]) && ($customer["id"]>0)) {

  if (empty($customer['stores']) && !empty($_COOKIE['stores'])) {
    $st = $db->prepare('update ccms_customer set `stores`=? where id=?');
    $st->execute(array($_COOKIE['stores'], $customer['id']));
    $customer['stores'] = $_COOKIE['stores'];
  }
  if (!empty($customer['stores']) && empty($_COOKIE['stores'])) {
    $this->output->setCookie('stores', $customer['stores'], time()+(60*60*24*365*5), '/');
    $_COOKIE['stores'] = $customer['stores'];
  }

  $valid_page=getConfigItem('plugin.customer.valid_page',array('profile'));
  if (empty($req) || !in_array($req,$valid_page)) $req=current($valid_page);

  if (
    file_exists($fn=dirname(__FILE__).'/'.$req.'.inc')
  ) require $fn;
  #--
  else throw new Exception($req);
  #--
  
  return;
  
} else {
	
	if (isset($_POST["login"]) && isset($_POST["password"]) && (strlen($_POST["login"]=trim($_POST["login"]))>0)) { 
		if (customerLogin($this->output,$_POST["login"],$_POST["password"],isset($_POST["remember"]) && $_POST["remember"])) {
			$this->output->redirect($this->uri());
		} else {
			$login_error=true;
		}
	}

	switch ($req) {
		case "signup" : require "signup.inc"; break;
		case "pwlost" : require "pwlost.inc"; break;
		default :
			require "login.inc";
	}
}


//end
