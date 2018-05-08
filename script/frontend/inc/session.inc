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

if (!class_exists('CcmsLanguage')) require '../shared/cyane/CcmsLanguage.class.php';

if (!defined('NO_DATABASE_SESSION')) {
  if (!class_exists('CcmsSessionHandler')) require '../shared/cyane/CcmsSessionHandler.class.php';
  session_set_save_handler('CcmsSessionHandler::open', 'CcmsSessionHandler::close', 'CcmsSessionHandler::read', 'CcmsSessionHandler::write', 'CcmsSessionHandler::destroy', 'CcmsSessionHandler::gc');
  if (!isset($_COOKIE['frontend_session'])) $_COOKIE['frontend_session']='_new';
}

hook('session','before_start');

session_name('frontend_session');
session_start();

hook('session','after_start');

//@deprecated
function getSessionLanguage($default=null) {
  global $instance;
  return $instance->getHtmlOutput()->getLanguage();
}

function getSessionCurrency() {
	if (!isset($_SESSION['currency']) || isset($_GET['resetCurrency'])) {
		$arr=getConfigItem('currency',array('default'=>'EUR'));
		$_SESSION['currency']=$arr['default'];
	}
	return $_SESSION['currency'];
}

//@Todo
function getSessionCurrencyFactor() {
	return 1;
}

function getSessionCustomer() {
	if (!isset($_SESSION['customer'])) return null;
	$res=$_SESSION['customer'];
	if (!isset($res['id']) || ($res['id']<1)) {
		require_once('customer.inc');
    customerLogout();
		return null;
	}
	if ($res['REMOTE_ADDR']!=$_SERVER['REMOTE_ADDR']) {
		require_once('customer.inc');
    customerLogout();
		return null;
	}
	if ($res['HTTP_USER_AGENT']!=$_SERVER['HTTP_USER_AGENT']) {
		require_once('customer.inc');
    customerLogout();
		return null;
	}
	return $res;
}

function customerToSession(array $arr) {
	$arr['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
	$arr['HTTP_USER_AGENT']=$_SERVER['HTTP_USER_AGENT'];
	$_SESSION['customer']=$arr;
  hook('customer','toSession');
}

function isValidSessionPost() {
	if (function_exists('_log')) {
    _log('isValidSessionPost _SESSION _submit_token : '.@$_SESSION['_submit_token']);
    _log('isValidSessionPost _POST _submit_token : '.@$_POST['_submit_token']);
  }
  $res = 
		($_SERVER['REQUEST_METHOD']=='POST') && 
		isset($_POST['_submit_token']) && 
		isset($_SESSION['_submit_token']) && 
		(strlen($_POST['_submit_token'])>0) && 
		($_POST['_submit_token']==$_SESSION['_submit_token']);
	#unset($_SESSION['_submit_token']);
	#unset($_POST['_submit_token']);
	return $res;
}

//end