<?php

require 'vendor/autoload.php';

global $db;
$db = new PDO('mysql:host=localhost;dbname=boozewatch;charset=utf8', 'boozewatch', 'uSss4ghY9zPJue7V', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

require 'alert.php';
alerts(true);

