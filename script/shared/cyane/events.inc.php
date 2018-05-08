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

function on_order_payed($order) {
  if (is_array($order)) $id=$order['id']; else $id=$order;
  if (file_exists($fn=getConfigItem('script_app').'frontend/plugin_webshop_on_order_payed.inc')) require $fn; 
}

function on_order_cancelled($order) {
  if (is_array($order)) $id=$order['id']; else $id=$order;
  if (file_exists($fn=getConfigItem('script_app').'frontend/plugin_webshop_on_order_cancelled.inc')) require $fn; 
}

//end