<?

$archive="cke.zip";
$listAllowed=true;

$my_dir=dirname($_SERVER["PHP_SELF"]);
//$req_file=preg_replace("#^{$my_dir}/#","",preg_replace("#\?.*$#","",$_SERVER["REQUEST_URI"]));
$req_file=preg_replace("#.*/".basename($my_dir)."/#","",preg_replace("#\?.*$#","",$_SERVER["REQUEST_URI"]));

require "../PclZip.class.php";
$zip = new PclZip($archive);
if ($req_file) {
	$index=false;
	foreach($zip->listContent() as $i=>$a) if ($a["filename"]==$req_file) {
		$index=$i;
		break;
	}
	if ($index===false) {
		header("HTTP/1.0 404 Not Found");
		die("404 - `{$req_file}` not found");
	}
	
	$res = $zip->extractByIndex($index,PCLZIP_OPT_EXTRACT_AS_STRING);
	if (count($res)>0) $res=current($res);
	if (@$res["status"]!="ok") {
		header("HTTP/1.0 500");
		die("500 - `{$req_file}` error: ".print_r($res,true));
	} 
	
	if      (preg_match("#\.(html|htm|xhtml)$#i",$req_file)) header("Content-type: text/html");
	else if (preg_match("#\.(js)$#i",$req_file)) header("Content-type: text/javascript");
	else if (preg_match("#\.(xml)$#i",$req_file)) header("Content-type: text/xml");
	else if (preg_match("#\.(css)$#i",$req_file)) header("Content-type: text/css");
	else if (preg_match("#\.(gif)$#i",$req_file)) header("Content-type: image/gif");
	else if (preg_match("#\.(png)$#i",$req_file)) header("Content-type: image/x-png");
	else if (preg_match("#\.(jpg|jpeg|jpe)$#i",$req_file)) header("Content-type: image/jpeg");
	else header("Content-type: application/octet-stream");
	header('Expires: '.date("r",time()+60*60*24*7));
	
	die($res["content"]);
	
} else {
	if ($listAllowed) {
		foreach($zip->listContent() as $a) {
			if ($a["folder"]) continue;
			echo "<a href='{$my_dir}/{$a["filename"]}'>{$a["filename"]}</a><br />";
		}
	} else {
		header("HTTP/1.0 403 Directory listing not allowed");
		die("403 - Directory listing not allowed");
	}
}

//end