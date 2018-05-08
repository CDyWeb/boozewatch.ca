<?php
/*
------------------------------------------------------------

  CyaneCMS

$LastChangedRevision: 112 $
$LastChangedDate: 2009-05-27 17:55:01 +0200 (wo, 27 mei 2009) $
$LastChangedBy: erwin $

Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
WITHOUT THE PERMISION OF THE AUTHOR.    

Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

if (!function_exists("setting")) {
  function setting($key,$default=null) {
    global $settings;
    if (!$settings) {
      $settings=array();
      foreach (getTableArray("select * from `".tbl_name("settings")."`","key") as $k=>$line) $settings[$k]=$line["str"].$line["txt"];
    }
    if (!isset($settings[$key])) return $default;
    return $settings[$key];
  }
}

if (@$_GET["id"]) {
  $order=getOneRow("select * from ".tbl_name('order')." where id=".db_escape($_GET["id"]));
}

if (@$_GET["uid"]) {
  $order=getOneRow("select * from ".tbl_name('order')." where uid='".db_escape($_GET["uid"])."'");
}

if (!@$order) die ("order not found.");

global $translator;
if (class_exists("CCMSController")) {
  $controller=CCMSController::getInstance();
  $view=$controller->getView();
  $config=getConfigItem('language');
  $translator=CCMSTranslator::instance(empty($order['language'])?$config['default']:$order['language'])->getStaticTranslation();
} else {
  $config=getConfigItem('language');
  $translator=getInstance()->getStaticTranslator(true,empty($order['language'])?$config['default']:$order['language']);
}

if (!function_exists('translate')) {
  function translate($k) {
    global $translator;
    return $translator->getTranslation($k);
  }
}

$cart=unserialize($order["cart"]);
$customer=unserialize($order["customer_details"]);
$log=getTableArray("select * from ".tbl_name('order_log')." where `order`={$order["id"]} order by dt","status");

global $site_config;
$currency_html=$site_config["currency"]["html"][$order["currency"]];

if (file_exists($fn=getConfigItem('script_app').'frontend/invoice.inc')) {
  require $fn;
  return;
}

global $invoice_cls;
$invoice_cls='CcmsInvoice';
require('CcmsInvoice.class.php');
if (file_exists($fn=getConfigItem('script_app').'frontend/CcmsInvoice.class.php')) {
  require $fn;
}
$data=array_merge($_GET,array(
'order'=>$order,
'cart'=>$cart,
'customer'=>$customer,
'log'=>$log,
'currency_html'=>$currency_html,
));
$o=new $invoice_cls($translator, $data);
$o->output();

//end
