<?

function getExpires($extention) {
	switch($extention) {
		case 'js' :
			return '+1 year';

		case 'json' :
			return '+1 year';

		case 'jpg' :
		case 'jpeg' :
		case 'jpe' :
			return '+1 year';

		case 'png' :
		case 'gif' :
		case 'bmp' :
		case 'tiff' :
			return '+1 year';

		case 'css' :
			return '+1 year';

		case 'xml' :
			return '+1 year';

		case 'txt' :
			return '+1 year';

		case 'swf' :
			return '+1 year';
	}
	return '+1 year';
}

function isGZ($extention) {
	switch($extention) {
		case 'js' :
			return true;

		case 'json' :
			return true;

		case 'jpg' :
		case 'jpeg' :
		case 'jpe' :
			return false;

		case 'png' :
		case 'gif' :
		case 'bmp' :
		case 'tiff' :
			return false;

		case 'css' :
			return true;

		case 'xml' :
			return true;

		case 'txt' :
			return true;

		case 'swf' :
			return false;

	}
	return false;
}


function getMIMEType($extention,$filename) {
	switch($extention) {
		case 'js' :
			return 'application/x-javascript';

		case 'json' :
			return 'application/json';

		case 'jpg' :
		case 'jpeg' :
		case 'jpe' :
			return 'image/jpg';

		case 'png' :
		case 'gif' :
		case 'bmp' :
		case 'ico' :
		case 'tiff' :
			return 'image/'.strtolower($extention);

		case 'css' :
			return 'text/css';

		case 'xml' :
			return 'application/xml';

		case 'txt' :
			return 'text/plain';
		
		case 'swf' :
			return 'application/x-shockwave-flash';

		default :
			if (function_exists('mime_content_type')) {
				$fileSuffix = mime_content_type($filename);
			}
			return 'unknown/'.trim($extention,'.');
	}
}

function d($t) {
	return str_replace('+0000','GMT',gmdate('r',$t));
}

$uri=preg_replace('#\?.*$#','',$_SERVER['REQUEST_URI']);
$fn='../../'.rawurldecode($uri);
if (!file_exists($fn)) { header('HTTP/1.0 404 Not Found'); exit(); }

$extention=(preg_match('|\.([a-z0-9]{2,4})$|i', $uri, $match))?strtolower($match[1]):'';

$etag='"'.sha1_file($fn).'"';
$mtime=filemtime($fn);

$modified=null;
if (!empty($_SERVER['HTTP_IF_NONE_MATCH']))  $modified = (bool)$modified || (strcmp($_SERVER['HTTP_IF_NONE_MATCH'],$etag)!=0);
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) $modified = (bool)$modified || ($mtime!=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']));
if ($modified===false) {
	header('HTTP/1.0 304 Not Modified'); exit();
}

header('Content-type: '.getMIMEType($extention,$fn));
header('Etag: '.$etag);
header('Last-Modified: '.d($mtime));

$exp=strtotime(getExpires($extention));
header('Expires: '.d($exp));
header('Cache-Control: max-age='.($exp-time()).', public, must-revalidate');

if (isGZ($extention)) {
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && preg_match('#gzip#',$_SERVER['HTTP_ACCEPT_ENCODING'])) {
		if (filesize($fn)>250) {
			header("Content-Encoding: gzip");
			ob_start("ob_gzhandler");
		}
	}
}

if (($extention=='js') && !isset($_SERVER['WINDIR'])) {
	require 'jsmin.php';
	echo JSMin::minify(file_get_contents($fn));
	exit();
}

readfile($fn);

//end