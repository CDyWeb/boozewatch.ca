<?php
/*
------------------------------------------------------------

 Copyright (c) 2010-2011 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cdyweb.com
 
------------------------------------------------------------
*/

function getConfigItem($k,$defaultValue=null) {
	global $config;
	if (is_array($config) && array_key_exists($k,$config)) return $config[$k];
	global $site_config;
	if (is_array($site_config) && array_key_exists($k,$site_config)) return $site_config[$k];
	return $config[$k]=$defaultValue;
}

function get_site_url() {
	return SITE_BASE_URL;
}

function get_site_uri() {
	return SITE_BASE_URI;
}

//end