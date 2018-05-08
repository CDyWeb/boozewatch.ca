<?php

define('NO_DATABASE_SESSION', true);

$site_config["database_scheme"]=""; // aka database name
$site_config["database_username"]="";
$site_config["database_password"]="";

if (isset($_SERVER["HTTP_HOST"]) && (!isset($_SERVER['WINDIR']))) {
	//define('HTMLMIMEMAIL5_TYPE', 'sendmail');
  $site_config["domain"]=$_SERVER["HTTP_HOST"];
  $site_config["host_base"]=$_SERVER["HTTP_HOST"];
  #--
} else {
	define('HTMLMIMEMAIL5_TYPE', 'mock-db');
  $site_config['mock-db-mailer']=false;
  $site_config["host_base"]=$site_config["domain"]=$_SERVER["HTTP_HOST"];
}

$site_config["url_server"]="http".(isset($_SERVER["HTTPS"])?"s":"")."://".$site_config["host_base"];
$site_config["url_base"]=$site_config["url_server"].$site_config["rel_base"];

$site_config['jquery.uploadify']=false;
$site_config['database_layer'] = 'pdo';


//end
