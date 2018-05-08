<?
header("Content-type: text/plain");
ini_set("display_errors","on");

define("SQL_DEBUG",true);
define("DEBUG_TO_ERRORLOG",true);

require "../../../../config.inc.php";
require "../../../../mysql.inc.php";
require "../../../inc/debug.inc.php";

function myErrorHandler($errno, $errstr, $errfile, $errline) {
	error_log("errno $errno, errstr $errstr, errfile $errfile, errline $errline ");
	$msg="
errno: $errno
errstr: $errstr
errfile: $errfile
errline: $errline";
	die($msg);
}

set_error_handler("myErrorHandler");

require "Webshop.class.php";
$webshop = new Webshop();

$_REQUEST["add"]=1;
$_REQUEST["amount"]=1;
$webshop->getCart()->addProductFromRequest();

var_dump($webshop);

//end