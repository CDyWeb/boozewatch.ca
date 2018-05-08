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

function getLanguageConfig($k,$s=null) {
	$arr=getConfigItem("language");
	if ($s==null) return $arr[$k];
	return $arr[$k][$s];
}
function getCurrencyConfig($k,$s=null) {
	$arr=getConfigItem("currency");
	if ($s==null) return $arr[$k];
	return $arr[$k][$s];
}

//@todo eztranslate
function translate($key,$args=null,$default=null,$insert=true) {
  global $instance;
	return translate_to($instance->getHtmlOutput()->getLanguage()->language,$key,$args,$default,$insert);
}

//@todo eztranslate
function translate_to($lang,$key,$args=null,$default=null,$insert=true) {
	if (!function_exists('static_translate')) require getConfigItem('script_base').'shared/cyane/static_translate.inc.php';
	if (empty($lang)) $lang=getLanguageConfig('default');
	return static_translate($lang,$key,$args,$default,$insert);
}

function getCurrencyFull($num,$cur=null) {
  return getCurrency($num,$cur,false);
}

function getCurrency($num,$cur=null,$roundDash=null) {
	if (!$cur) $cur=getSessionCurrency();
	$html=getCurrencyConfig("html",$cur);

	if (isset($_SERVER["WINDIR"])) {
		$frac_digits=2;
    if ($cur=='EUR') {
      $mon_decimal_point=",";
      $mon_thousands_sep="";
    } else {
      $mon_decimal_point=".";
      $mon_thousands_sep=",";
    }
	} else {
		$arr=localeconv();
		extract($arr);
	}
  if ($frac_digits==127) {
    $frac_digits=2;
    if ($cur=='EUR') {
      $mon_decimal_point=",";
      $mon_thousands_sep="";
    } else {
      $mon_decimal_point=".";
      $mon_thousands_sep=",";
    }
  }
   //_log(get_class()."::getCurrency - frac_digits {$frac_digits}");
	$res = number_format($num,$frac_digits,$mon_decimal_point,$mon_thousands_sep);
  if (($cur=='EUR') && ($roundDash===null)) $roundDash=true;
	if ($roundDash) $res=preg_replace('#00$#','-',$res);
	return $html."&nbsp;".$res;
}
