<?php
/*
------------------------------------------------------------

	CyaneCMS

$LastChangedRevision: 108 $
$LastChangedDate: 2009-05-24 17:33:49 +0200 (zo, 24 mei 2009) $
$LastChangedBy: erwin $

 Copyright (c) 2006-2009 Cyane Dynamic Web Solutions
 IT IS NOT ALLOWED TO USE OR MODIFY ANYTHING OF THIS SITE,
 WITHOUT THE PERMISION OF THE AUTHOR.    

 Info? Mail to ccms@cyane.nl
------------------------------------------------------------
*/

function is_https() {
  return !empty($_SERVER["HTTPS"]);
}

function w3cDate($time=null) {
  if (empty($time)) $time=time();
  $offset = date("O",$time);
  return date("Y-m-d\TH:i:s",$time).substr($offset,0,3).":".substr($offset,-2);
} 

//source: http://php.net/manual/en/function.rawurldecode.php
function utf8RawUrlDecode($source) {
  $decodedStr = "";
  $pos = 0;
  $len = strlen ($source);
  while ($pos < $len) {
    $charAt = substr ($source, $pos, 1);
    if ($charAt == '%') {
      $pos++;
      $charAt = substr ($source, $pos, 1);
      if ($charAt == 'u') {
        // we got a unicode character
        $pos++;
        $unicodeHexVal = substr ($source, $pos, 4);
        $unicode = hexdec ($unicodeHexVal);
        $entity = "&#". $unicode . ';';
        $decodedStr .= utf8_encode ($entity);
        $pos += 4;
      }
      else {
        // we have an escaped ascii character
        $hexVal = substr ($source, $pos, 2);
        $decodedStr .= chr (hexdec ($hexVal));
        $pos += 2;
      }
    } else {
      $decodedStr .= $charAt;
      $pos++;
    }
  }
  return $decodedStr;
}


function utf8_ent($s,$style=ENT_QUOTES) {
	return htmlentities($s,$style,"UTF-8");
}

function make_url($prefix,$uri,$append,$ext=".html") {
  if ($prefix && (substr($prefix, -1)=='/')) $res=$prefix.trim($uri,'/');
  else $res=$prefix.'/'.trim($uri,'/');

  if ($append) {
    if (is_array($append)) $append=implode('/',$append);
    $res.='/'.trim($append,'/');
  }
  $res.=$ext;
  return $res;
}

function text_limit($str,$limit,$append="...",$enc="UTF-8") {
  return mb_strimwidth($str,0,$limit+strlen($append),$append,$enc);
}

function html_limit($str,$limit,$append="...",$enc="UTF-8") {
	$str=trim(strip_tags($str));
	$str=text_limit($str,$limit,$append,$enc);
	return $str;
}

function getRequestURI() {
	if (isset($_SERVER["_URI"])) return $_SERVER["_URI"];
	return $_SERVER["_URI"] = isset($_SERVER["REQUEST_URI"])?preg_replace("#\?.*$#","",$_SERVER["REQUEST_URI"]):"/";
}

function getSafeName($name,$repl='-',$charset='UTF-8') {
	$mod=$charset=='UTF-8'?'ui':'i';
	$name = str_replace("'",' ',$name);
	$name = html_entity_decode(preg_replace('#&(\w)(uml|cedil|grave|acute|circ);#i','$1',htmlentities($name,ENT_COMPAT,$charset)),ENT_COMPAT,$charset);
	$name = preg_replace("#[^A-Za-z0-9~:_\-]#".$mod,$repl,$name);
	return preg_replace('#'.preg_quote($repl).'{2,}#',$repl,trim($name,$repl));
}

function getPermalinkName($permalink,$repl='-',$charset='UTF-8') {
	return strtolower(getSafeName($permalink,$repl,$charset));
}

function mb_unserialize($serial_str) {
	if (is_array($res=@unserialize($serial_str))) return $res;
	$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
	return unserialize($out);
}

//thank you CI
function getClientIp() {
	if (!empty($_SERVER["_IP"])) return $_SERVER["_IP"];
	
	$remote_addr=isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:null;
	
	if (function_exists('getConfigItem') && getConfigItem('proxy_ips',false) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($remote_addr)) {
		$proxies = preg_split('/[\s,]/', getConfigItem('proxy_ips'), -1, PREG_SPLIT_NO_EMPTY);
		$proxies = is_array($proxies) ? $proxies : array($proxies);
		$_SERVER["_IP"] = in_array($remote_addr, $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $remote_addr;
	} else if (!empty($remote_addr) AND isset($_SERVER['HTTP_CLIENT_IP'])) {
		$_SERVER["_IP"] = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($remote_addr)) {
		$_SERVER["_IP"] = $remote_addr;
	} else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$_SERVER["_IP"] = $_SERVER['HTTP_CLIENT_IP'];
	} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$_SERVER["_IP"] = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	if (empty($_SERVER["_IP"])) {
		$_SERVER["_IP"] = '0.0.0.0';
		return $_SERVER["_IP"];
	}

	if (strstr($_SERVER["_IP"], ',')) {
		$x = explode(',', $_SERVER["_IP"]);
		$_SERVER["_IP"] = trim(end($x));
	}

	if (!preg_match('#^(\d+)\.(\d+)\.(\d+)\.(\d+)$#',$_SERVER["_IP"],$match) || ($match[1]>255) || ($match[2]>255) || ($match[3]>255) || ($match[4]>255)) {
		$_SERVER["_IP"] = '0.0.0.0';
	}
	return $_SERVER["_IP"];
}

function getClientHost() {
  if (isset($_SERVER["_HOST"])) return $_SERVER["_HOST"];
  $_SERVER["_HOST"]=@gethostbyaddr(getClientIp());
  return $_SERVER["_HOST"];
}

function clientIsRobot() {
	if (defined("IS_BOT")) return IS_BOT;
	if (!empty($_SERVER["HTTP_USER_AGENT"]) && preg_match("#^(Java|Apache|SiteSucker|Wget|Jigsaw|SVN|Huaweisymantecspider)#i",$_SERVER["HTTP_USER_AGENT"],$match)) {
		_log(" ** Client is a robot! Identified by user agent token: ".$match[1]);
		if (!defined('PHPUnit_MAIN_METHOD')) define("IS_BOT",true);
		return true;
	}
	if (!empty($_SERVER["HTTP_USER_AGENT"]) && preg_match("#^Mozilla.*(MSIE|Firefox|Safari|Chrome|Opera|BlackBerry|iPhone)#",$_SERVER["HTTP_USER_AGENT"],$match)) {
		_log("Client is not a robot, user agent is ".$match[1]);
		if (!defined('PHPUnit_MAIN_METHOD')) define("IS_BOT",false);
		return false;
	}
	if (!empty($_SERVER["HTTP_USER_AGENT"]) && preg_match("#(yahoo|slup|googlebot|MSNBot|Twiceler|Ingrid|crawl|AskJeeves|bot/|bot-|GigaBot|mlbot|AdsBot| Bot|spider|ilse|robot|libwww-perl|W3C_Validator|/web/snippet/|Google Web Preview)#i",$_SERVER["HTTP_USER_AGENT"],$match)) {
		_log(" ** Client is a robot! Identified by user agent token: ".$match[1]);
		if (!defined('PHPUnit_MAIN_METHOD')) define("IS_BOT",true);
		return true;
	}
	$hostname = getClientHost();
	if (preg_match("#(yahoo|slup|googlebot|MSNBot|Twiceler|Ingrid|crawl|AskJeeves|bot/|bot-|GigaBot|spider|ilse|robot)#i",$hostname,$match)) {
		_log(" ** Client is a robot! Identified by hostbyaddr token: ".$match[1]);
		if (!defined('PHPUnit_MAIN_METHOD')) define("IS_BOT",true);
		return true;
	}

	_log("Client robot status not clear... clientIsRobot returns false for client:{$hostname}, user agent:".(isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:'none'));
	if (!defined('PHPUnit_MAIN_METHOD')) define("IS_BOT",false);
	return false;
}

if (!function_exists('ctype_alpha')) {
	# ctype dus niet geinst

	function ctype_alpha( $t ) {
		return preg_match("/[A-Za-z]*/",$t);
	}

	function ctype_digit( $t ) {
		return preg_match("/[0-9]*/",$t);
	}

	function ctype_alnum( $t ) {
		return preg_match("/[A-Za-z0-9]*/",$t);
	}
}

function return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val{strlen($val)-1});
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}

	return $val;
}

function return_size($bytes,$dec=1) {
  if ($bytes<1024) return '1k';
  $bytes/=1024;
  if ($bytes<1024) return ($dec?number_format($bytes,$dec):round($bytes)).'K';
  $bytes/=1024;
  if ($bytes<1024) return ($dec?number_format($bytes,$dec):round($bytes)).'M';
  $bytes/=1024;
  return ($dec?number_format($bytes,$dec):round($bytes)).'G';
}

function readdir_ls($path) {
	if ($ph = opendir($path)) {
		$res=array();
		while (($dn = readdir($ph))!==false) {
			if ($dn[0]===".") continue;
			$res[$dn] = "{$path}/{$dn}";
		}
		closedir($ph);
		return $res;
	}
	throw new Exception("{$path} cannot be read");
}

function read_url($url, $timeout=null) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  if (!empty($timeout)) curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	if (isset($_SERVER["HTTP_HOST"])) curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_HOST"]);

	$result=curl_exec ($ch);
	curl_close($ch);
	return $result;
}

function get_curl_web_page($url, $cookie='') {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'], //"Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.17) Gecko/20110420 Firefox/3.6.17 ( .NET CLR 3.5.30729; .NET4.0C)", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_COOKIE         => $cookie,
        CURLOPT_SSL_VERIFYPEER => false,
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

function redirect_url($url,$status_code=301,$with_js=false) {
	if (!headers_sent()) header("Location: {$url}",true,$status_code);
	if ($with_js) echo '<script type="text/javascript">window.location.href="'.$url.'";</script>';
}

function minutesToSeconds($i) { return $i*60; }
function hoursToSeconds($i) { return $i*minutesToSeconds(60); }
function daysToSeconds($i) { return $i*hoursToSeconds(24); }
function weeksToSeconds($i) { return $i*daysToSeconds(7); }
function yearsToSeconds($i) { return $i*daysToSeconds(365); }

function add_weekdays($timestamp,$diff) {
  while ($diff<0) {
    $timestamp=strtotime(date('Y-m-d',$timestamp).' -1 day');
    $weekday=date('w',$timestamp);
    if ($weekday==0) continue; //sunday
    if ($weekday==6) continue; //saturday
    $diff++;
  }
  while ($diff>0) {
    $timestamp=strtotime(date('Y-m-d',$timestamp).' +1 day');
    $weekday=date('w',$timestamp);
    if ($weekday==0) continue; //sunday
    if ($weekday==6) continue; //saturday
    $diff--;
  }
  return $timestamp;
}

function raw_post($host, $uri, array $data, $secure=false) {
  $req = http_build_query($data);

  $header  = "POST {$uri} HTTP/1.0\r\n";
  $header .= "Host: {$host}\r\n";
  $header .= "User-Agent: ccms:util.inc:raw_post\r\n";
  $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
  $header .= "Connection: close\r\n";
  $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

  if ($secure) $fp = fsockopen ('ssl://'.$host, "443", $errno, $errstr, 30);
  else $fp = fsockopen ($host, 80, $errno, $errstr, 30);

  if (!$fp) {
    throw new Exception("{$errno}, {$errstr}");
  }
  #echo "sending to [{$host}{$uri}]: [{$header}{$req}]";
  fputs ($fp, $header . $req);

  $res='';
  while (!feof($fp)) {
    $res .= fgets ($fp, 1024);
  }
  return $res;
}




//end