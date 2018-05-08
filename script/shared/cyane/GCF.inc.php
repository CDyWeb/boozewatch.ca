<?php

//Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Trident/4.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729)

if (!isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT']='';

$ua=$_SERVER['HTTP_USER_AGENT'];
if (preg_match('#MSIE\s+([.\d]+);#',$ua,$match)) {
	$version=floatval($match[1]);
	if ($version<7) {
		define('MSIE_LT_7',$version);
	}
}

define('CHROMEFRAME',(bool)preg_match('#chromeframe/\d#',$ua));

//end