<?php

function thumb_img($name,$maxWidth,$maxHeight=0,$s="img",$dir=null) {
  return img_url("thumb",$name,$maxWidth,$maxHeight,$s,$dir);
}

function crop_img($name,$maxWidth,$maxHeight=0,$s="img",$dir=null) {
  return img_url("crop",$name,$maxWidth,$maxHeight,$s,$dir);
}

function img_url($func,$name,$maxWidth,$maxHeight=0,$s="img",$dir=null) {
  $ext=".jpg";
  if (stripos($name,'http://')===0) return get_site_url()."{$func}/".base64_encode($name)."/{$maxWidth}/{$maxHeight}/".getPermalinkName($s).$ext;
  if (!$dir) $dir=getConfigItem("rel_userfiles_userimg");
  if (preg_match("#(\.\w+)$#",$name,$match)) $ext=$match[1];
  if (!file_exists(getConfigItem('public_base').'/'.$dir.$name)) { $dir='shared/'; $name='dot.gif'; }
  $res = get_site_url()."{$func}/".base64_encode($dir.$name)."/{$maxWidth}/{$maxHeight}/".getPermalinkName($s).$ext;
  return $res;
}

//end