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

function dieVoid() {
	global $maxwidth,$maxheight;
	if ($maxheight<5) $maxheight=$maxwidth;
	$img = @imagecreatetruecolor(max(1,intval($maxwidth)),max(1,intval($maxheight)));
	ob_end_clean();
	header('Content-type: image/png');
	imagepng($img);
	die();
}

function _log($s) {
	//echo $s; //error_log($s);
}

ob_start();
if (!isset($chdir_to)) $chdir_to='../../';
chdir($chdir_to);

$contenttype="png";
$func="thumb";
$thumb=0;
$maxwidth=0;
$maxheight=0;
$path="";
$margin_top=0;
$margin_right=0;
$margin_bottom=0;
$margin_left=0;
$jpgq=85;

if (preg_match("#(thumb|crop)/(.+)/(\d+)/(\d+)/.*\.(\w+)(\?.*|)$#i",$_SERVER["REQUEST_URI"],$match)) {
	_log($func=strtolower($match[1]));
	_log($path=$match[2]);
	_log($maxwidth=$match[3]);
	_log($maxheight=$match[4]);
	_log($contenttype=str_replace("jpg","jpeg",strtolower($match[5])));
}

foreach ($_GET as $var=>$value) $$var=$value;

if (!$path) { header("HTTP/1.0 400 Bad Request"); dieVoid(); }
$path=base64_decode($path);
if (empty($path) || (substr($path,0,1)=='/')) { header("HTTP/1.0 400 Bad Request"); dieVoid(); }
if (!file_exists($path)) { header("HTTP/1.0 404 Not Found"); dieVoid(); }
$imagesize = getimagesize($path);
if (!$imagesize[0] || !$imagesize[1]) { header("HTTP/1.0 401 Access Denied"); dieVoid(); }

if (!$thumb && !($maxwidth || $maxheight)) {
	_log("no resize args");
	ob_end_clean();
  switch ($imagesize['2']) {
    case IMAGETYPE_GIF : 
    case IMAGETYPE_JPEG : 
    case IMAGETYPE_PNG : {
      header('Content-type: '.$imagesize['mime']);
      readfile($path);
      break;
    }
    default : {
      header("HTTP/1.0 401 Access Denied"); dieVoid();
    }
  }
	die();
}

$raw=file_get_contents($path);
$src = imagecreatefromstring($raw) or dieVoid();

header('Content-type: image/'.$contenttype);
header('Expires: '.date("r",time()+60*60*24*7));

$old_w = imagesx($src);
$old_h = imagesy($src);
if ($maxwidth && $maxheight) {
	if (($old_w>1) && ($old_h>1) && ($old_w<=$maxwidth) && ($old_h<=$maxheight)) {
		_log("too small src");
		ob_end_clean();
		echo $raw;
		die();
	}
	$ratio1 = $maxwidth/$old_w;
	$ratio2 = $maxheight/$old_h;
	$ratio = $func=="crop"?max($ratio1,$ratio2):min($ratio1,$ratio2);
} else if ($maxwidth) {
	if ($old_w<=$maxwidth) {
		_log("too small src width");
		ob_end_clean();
		echo $raw;
		die();
	}
	$ratio = $maxwidth/$old_w;
} else if ($maxheight) {
	if ($old_h<=$maxheight) {
		_log("too small src height");
		ob_end_clean();
		echo $raw;
		die();
	}
	$ratio = $maxheight/$old_h;
} else {
	if ($old_w>$old_h) $ratio = $thumb/$old_w; else $ratio = $thumb/$old_h;
}

_log(" old_w {$old_w} old_h {$old_h} ratio {$ratio}");

function img_create($w,$h) {
  $img = imagecreatetruecolor ($w,$h) or die ("Cannot Initialize new GD image stream");
  $bg = imagecolorallocate($img, 255, 255, 255);
  imagefill($img, 0, 0, $bg);
  return $img;
}

if ($func=="crop") {

	$new_w = $maxwidth?$maxwidth:$ratio*$old_w;
	$new_h = $maxheight?$maxheight:$ratio*$old_h;
  
  $xoffset=floor(max(0,($ratio*$old_w)-$new_w) / 2);
  $yoffset=floor(max(0,($ratio*$old_h)-$new_h) / 2);
	
	_log(" new_w {$new_w} new_h {$new_h}");
  
  $inv=(1/$ratio);

	$img = img_create ($new_w,$new_h) or die ("Cannot Initialize new GD image stream");
	//imagecopyresampled($img,$src,0+$margin_left,0+$margin_top,0,0,$new_w,$new_h,$new_w*(1/$ratio),$new_h*(1/$ratio));
  imagecopyresampled($img,$src,0+$margin_left,0+$margin_top,$xoffset*$inv,$yoffset*$inv,$new_w,$new_h,$new_w*$inv,$new_h*$inv);

} else {

	$ext_new_w=$new_w = $ratio*$old_w;
	$ext_new_h=$new_h = $ratio*$old_h;
	if ($margin_top>0) $ext_new_h+=$margin_top;
	if ($margin_bottom>0) $ext_new_h+=$margin_bottom;
	if ($margin_left>0) $ext_new_w+=$margin_left;
	if ($margin_right>0) $ext_new_w+=$margin_right;

	$img = img_create ($ext_new_w,$ext_new_h) or die ("Cannot Initialize new GD image stream");
	imagecopyresampled($img,$src,0+$margin_left,0+$margin_top,0,0,$new_w,$new_h,$old_w,$old_h);

}

if (file_exists($fn="../gfx/Image/Watermark.png")) {
	$watermark_src = imagecreatefromstring(file_get_contents($fn)) or dieVoid();
	$watermark_overlay = imagecreatetruecolor ($ext_new_w,$ext_new_h) or die ("Cannot Initialize new GD image stream");
	$bg = imagecolorallocate($watermark_overlay,255,255,255);
	$bg = imagecolortransparent ($watermark_overlay,$bg);
	imagefilledrectangle ($watermark_overlay,0,0,$ext_new_w,$ext_new_h,$bg);
	
	$watermark_w = imagesx($watermark_src);
	$watermark_h = imagesy($watermark_src);
	
	imagecopyresized($watermark_overlay,$watermark_src,0+$margin_left,0+$margin_top,0,0,$new_w,$new_h,$watermark_w,$watermark_h);
	imageCopyMerge($img, $watermark_overlay,0,0,0,0,$new_w,$new_h,50); 
}

if (($new_w<200) && ($new_h<200)) {
  $jpgq=95;
  if (($new_w<150) || ($new_h<150)) $jpgq=100;
}

ob_end_clean();

if ($contenttype=="gif") imagegif($img);
else if ($contenttype=="png") imagepng($img,null,9);
else if ($contenttype=="jpeg" || $contenttype=="jpg") imagejpeg($img,null,$jpgq);
else error_log("thumb.php contenttype {$contenttype} ??");


// end
