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

if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['email']) && isset($_POST['last_name'])) require_once 'register_post.inc';

if (empty($_SESSION["_submit_token"])) $_SESSION["_submit_token"]=sha1(uniqid(rand().time(), true));

?>
<h1>Register</h1>