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

if (($_SERVER["REQUEST_METHOD"]=="POST") && (strlen($_POST["password"])>4)) {
	$set="password='".db_escape($_POST["password"])."'";
	$sql="update {$this->tbl("customer")} set {$set} where id={$_SESSION["customer"]["id"]}";
	if ($profile_saved=executeSql($sql)) {
		$row=getOneRow("select * from ".tbl_name("customer")." where id={$_SESSION["customer"]["id"]}");
		if ($row) customerToSession($row);
		else customerLogout();
	}
}


die("todo");